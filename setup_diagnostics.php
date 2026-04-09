<?php
/**
 * Environment and database diagnostics for cross-laptop setup checks.
 * Open in browser: /setup_diagnostics.php
 */

declare(strict_types=1);

function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function boolBadge(bool $ok): string {
    $label = $ok ? 'PASS' : 'FAIL';
    $class = $ok ? 'ok' : 'fail';
    return '<span class="badge ' . $class . '">' . $label . '</span>';
}

$checks = [];
$fixes = [];
$warnings = [];

$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';
$dbName = getenv('DB_NAME') ?: 'ceramic';

$dbPorts = [3306, 3307];
$dbPortEnv = getenv('DB_PORT');
if ($dbPortEnv !== false && ctype_digit($dbPortEnv)) {
    $dbPorts = [(int)$dbPortEnv];
}

$mysqliLoaded = extension_loaded('mysqli');
$checks[] = [
    'name' => 'PHP mysqli extension loaded',
    'ok' => $mysqliLoaded,
    'detail' => $mysqliLoaded ? 'mysqli extension is available.' : 'Enable mysqli in php.ini.',
];

$conn = false;
$lastError = 'Not attempted';
$connectedPort = null;

if ($mysqliLoaded) {
    if (function_exists('mysqli_report')) {
        mysqli_report(MYSQLI_REPORT_OFF);
    }

    foreach ($dbPorts as $port) {
        $try = @mysqli_connect($dbHost, $dbUser, $dbPass, $dbName, $port);
        if ($try) {
            $conn = $try;
            $connectedPort = $port;
            @mysqli_set_charset($conn, 'utf8mb4');
            break;
        }
        $lastError = (string)mysqli_connect_error();
    }
}

$checks[] = [
    'name' => 'Database connection',
    'ok' => (bool)$conn,
    'detail' => $conn
        ? 'Connected to ' . $dbHost . ':' . $connectedPort . ' database ' . $dbName
        : 'Could not connect. Last error: ' . $lastError,
];

if (!$conn) {
    $fixes[] = 'Start MySQL service (XAMPP Control Panel -> MySQL -> Start).';
    $fixes[] = 'Check DB config in config.php or env vars: DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT.';
    $fixes[] = 'Confirm MySQL is listening on one of these ports: ' . implode(', ', $dbPorts) . '.';
}

$requiredTables = [
    'users' => ['id', 'name', 'email', 'password', 'user_type', 'login_otp', 'otp_created_at'],
    'products' => ['id', 'name', 'price', 'seller_id', 'stock'],
    'orders' => ['id', 'user_id', 'payment_status', 'otp', 'otp_verified', 'otp_created_at', 'seller_ids', 'order_items_json'],
    'cart' => ['id', 'user_id', 'pid', 'quantity'],
    'wishlist' => ['id', 'user_id', 'pid'],
    'category' => ['id', 'c_name'],
    'subcategory' => ['id', 'category_id', 'name'],
];

$roleSummary = [];
$sellerCount = 0;
$unknownRoleCount = 0;

if ($conn) {
    foreach ($requiredTables as $table => $columns) {
        $safeTable = preg_replace('/[^A-Za-z0-9_]/', '', $table);
        $tableQuery = @mysqli_query($conn, "SHOW TABLES LIKE '" . mysqli_real_escape_string($conn, $safeTable) . "'");
        $tableExists = ($tableQuery && mysqli_num_rows($tableQuery) > 0);

        $checks[] = [
            'name' => 'Table exists: ' . $table,
            'ok' => $tableExists,
            'detail' => $tableExists ? 'Found.' : 'Missing table ' . $table,
        ];

        if (!$tableExists) {
            $fixes[] = 'Import ceramic.sql to create missing table: ' . $table;
            continue;
        }

        foreach ($columns as $col) {
            $safeCol = preg_replace('/[^A-Za-z0-9_]/', '', $col);
            $colQuery = @mysqli_query(
                $conn,
                "SHOW COLUMNS FROM `" . $safeTable . "` LIKE '" . mysqli_real_escape_string($conn, $safeCol) . "'"
            );
            $colExists = ($colQuery && mysqli_num_rows($colQuery) > 0);

            $checks[] = [
                'name' => 'Column exists: ' . $table . '.' . $col,
                'ok' => $colExists,
                'detail' => $colExists ? 'Found.' : 'Missing column ' . $table . '.' . $col,
            ];

            if (!$colExists) {
                $fixes[] = 'Open any app page once to let config.php auto-add missing column: ' . $table . '.' . $col;
            }
        }
    }

    $rolesRes = @mysqli_query(
        $conn,
        "SELECT LOWER(TRIM(user_type)) AS role_name, COUNT(*) AS total FROM users GROUP BY LOWER(TRIM(user_type))"
    );
    if ($rolesRes) {
        while ($row = mysqli_fetch_assoc($rolesRes)) {
            $roleSummary[] = [
                'role' => (string)($row['role_name'] ?? ''),
                'count' => (int)($row['total'] ?? 0),
            ];
        }
    }

    $sellerRes = @mysqli_query($conn, "SELECT COUNT(*) AS c FROM users WHERE LOWER(TRIM(user_type)) = 'seller'");
    if ($sellerRes) {
        $sellerRow = mysqli_fetch_assoc($sellerRes);
        $sellerCount = (int)($sellerRow['c'] ?? 0);
    }

    $unknownRes = @mysqli_query(
        $conn,
        "SELECT COUNT(*) AS c FROM users WHERE LOWER(TRIM(user_type)) NOT IN ('admin','user','seller')"
    );
    if ($unknownRes) {
        $unknownRow = mysqli_fetch_assoc($unknownRes);
        $unknownRoleCount = (int)($unknownRow['c'] ?? 0);
    }

    $checks[] = [
        'name' => 'At least one seller account',
        'ok' => $sellerCount > 0,
        'detail' => $sellerCount > 0 ? 'Seller count: ' . $sellerCount : 'No seller accounts found.',
    ];

    if ($sellerCount === 0) {
        $fixes[] = "Run SQL: INSERT INTO users (name, email, password, user_type) VALUES ('sellerdemo','seller@gmail.com','seller123','seller');";
    }

    $checks[] = [
        'name' => 'Unknown user_type values',
        'ok' => $unknownRoleCount === 0,
        'detail' => $unknownRoleCount === 0
            ? 'All user_type values are valid.'
            : 'Found ' . $unknownRoleCount . ' invalid role values.',
    ];

    if ($unknownRoleCount > 0) {
        $fixes[] = "Run SQL: UPDATE users SET user_type = LOWER(TRIM(user_type));";
        $warnings[] = 'Some account roles are invalid and may break login routing.';
    }

    @mysqli_close($conn);
}

$passCount = 0;
$failCount = 0;
foreach ($checks as $check) {
    if (!empty($check['ok'])) {
        $passCount++;
    } else {
        $failCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Diagnostics</title>
    <style>
        :root {
            --bg: #f3f7f9;
            --card: #ffffff;
            --text: #12202b;
            --muted: #596b79;
            --ok: #137a3b;
            --ok-bg: #e6f6ed;
            --fail: #a12222;
            --fail-bg: #fdeaea;
            --border: #d9e2e8;
            --accent: #006a8e;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, sans-serif;
            background: linear-gradient(150deg, #edf4f8 0%, #f6f9fb 45%, #fff8ef 100%);
            color: var(--text);
        }

        .wrap {
            max-width: 1024px;
            margin: 24px auto;
            padding: 0 16px 24px;
        }

        .hero {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 8px 24px rgba(18, 32, 43, 0.08);
            margin-bottom: 16px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 30px;
            letter-spacing: 0.2px;
        }

        .subtitle {
            color: var(--muted);
            margin: 0;
            font-size: 14px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 10px;
            margin-top: 14px;
        }

        .stat {
            background: #f9fbfd;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px 12px;
        }

        .stat .n {
            font-size: 22px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 4px;
        }

        .panel {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px;
            margin-bottom: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            text-align: left;
            vertical-align: top;
            border-bottom: 1px solid #edf2f5;
            padding: 9px 8px;
            font-size: 14px;
        }

        th {
            color: #3c4d5a;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge {
            display: inline-block;
            border-radius: 999px;
            font-size: 12px;
            padding: 4px 10px;
            font-weight: 700;
        }

        .ok { color: var(--ok); background: var(--ok-bg); }
        .fail { color: var(--fail); background: var(--fail-bg); }

        .list {
            margin: 0;
            padding-left: 20px;
        }

        .list li { margin-bottom: 8px; }

        .warn {
            margin: 0 0 8px;
            color: #8a5200;
            background: #fff4e0;
            border: 1px solid #ffd8a8;
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 14px;
        }

        code {
            background: #eef4f8;
            border: 1px solid #d8e2e9;
            border-radius: 6px;
            padding: 1px 5px;
        }
    </style>
</head>
<body>
<div class="wrap">
    <section class="hero">
        <h1>Setup Diagnostics</h1>
        <p class="subtitle">Use this page on any laptop to quickly verify environment, database schema, and seller login readiness.</p>
        <div class="stats">
            <div class="stat">
                <div class="n"><?php echo (int)$passCount; ?></div>
                <div>Checks Passed</div>
            </div>
            <div class="stat">
                <div class="n"><?php echo (int)$failCount; ?></div>
                <div>Checks Failed</div>
            </div>
            <div class="stat">
                <div class="n"><?php echo (int)$sellerCount; ?></div>
                <div>Seller Accounts</div>
            </div>
        </div>
    </section>

    <?php if (!empty($warnings)): ?>
    <section class="panel">
        <?php foreach ($warnings as $w): ?>
            <p class="warn"><?php echo h($w); ?></p>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <section class="panel">
        <h2>Check Results</h2>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Check</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($checks as $c): ?>
                <tr>
                    <td><?php echo boolBadge((bool)$c['ok']); ?></td>
                    <td><?php echo h((string)$c['name']); ?></td>
                    <td><?php echo h((string)$c['detail']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="panel">
        <h2>Role Distribution</h2>
        <?php if (empty($roleSummary)): ?>
            <p>No role data available.</p>
        <?php else: ?>
            <ul class="list">
            <?php foreach ($roleSummary as $r): ?>
                <li><strong><?php echo h($r['role'] === '' ? '(empty)' : $r['role']); ?></strong>: <?php echo (int)$r['count']; ?></li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="panel">
        <h2>Suggested Fixes</h2>
        <?php if (empty($fixes)): ?>
            <p>No fixes suggested. Setup looks healthy.</p>
        <?php else: ?>
            <ul class="list">
            <?php foreach ($fixes as $fix): ?>
                <li><?php echo h($fix); ?></li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <p>Quick URL: <code>/setup_diagnostics.php</code></p>
    </section>
</div>
</body>
</html>
