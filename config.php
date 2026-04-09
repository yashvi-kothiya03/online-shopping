<?php

// Keep legacy mysqli behavior consistent across PHP versions.
if (function_exists('mysqli_report')) {
	mysqli_report(MYSQLI_REPORT_OFF);
}

$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';
$dbName = getenv('DB_NAME') ?: 'ceramic';

// Try common local MySQL ports (3306 default, 3307 often used by local stacks).
$dbPorts = array(3306, 3307);
$dbPortEnv = getenv('DB_PORT');
if ($dbPortEnv !== false && ctype_digit($dbPortEnv)) {
	$dbPorts = array((int)$dbPortEnv);
}

$conn = false;
$lastError = '';

foreach ($dbPorts as $dbPort) {
	try {
		$conn = @mysqli_connect($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
		if ($conn) {
			break;
		}
		$lastError = mysqli_connect_error();
	} catch (Throwable $e) {
		$lastError = $e->getMessage();
	}
}

if (!$conn) {
	die('Database connection failed. Start MySQL and verify DB config in config.php (host/user/password/database/port). Last error: ' . $lastError);
}

@mysqli_set_charset($conn, 'utf8mb4');

// Keep schema compatible after fresh DB imports from older dumps.
if (!function_exists('ensure_column_exists')) {
	function ensure_column_exists($conn, $table, $column, $definition) {
		if (!preg_match('/^[A-Za-z0-9_]+$/', $table) || !preg_match('/^[A-Za-z0-9_]+$/', $column)) {
			return;
		}

		$columnEscaped = mysqli_real_escape_string($conn, $column);
		$check = @mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$columnEscaped'");
		if ($check && mysqli_num_rows($check) === 0) {
			@mysqli_query($conn, "ALTER TABLE `$table` ADD COLUMN `$column` $definition");
		}
	}
}

if ($conn) {
	// Normalize account types for compatibility across old/imported databases.
	@mysqli_query($conn, "UPDATE `users` SET user_type = LOWER(TRIM(user_type)) WHERE user_type <> LOWER(TRIM(user_type))");

	// orders table
	ensure_column_exists($conn, 'orders', 'otp', "VARCHAR(6) DEFAULT NULL");
	ensure_column_exists($conn, 'orders', 'otp_verified', "TINYINT(1) NOT NULL DEFAULT 0");
	ensure_column_exists($conn, 'orders', 'otp_created_at', "TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP");
	ensure_column_exists($conn, 'orders', 'seller_ids', "VARCHAR(255) NULL DEFAULT NULL");
	ensure_column_exists($conn, 'orders', 'order_items_json', "LONGTEXT NULL");
	ensure_column_exists($conn, 'orders', 'return_requested', "TINYINT(1) NOT NULL DEFAULT 0");
	ensure_column_exists($conn, 'orders', 'return_otp', "VARCHAR(6) DEFAULT NULL");
	ensure_column_exists($conn, 'orders', 'return_otp_verified', "TINYINT(1) NOT NULL DEFAULT 0");
	ensure_column_exists($conn, 'orders', 'return_otp_created_at', "TIMESTAMP NULL DEFAULT NULL");
	ensure_column_exists($conn, 'orders', 'return_reason', "TEXT NULL");
	ensure_column_exists($conn, 'orders', 'return_details', "TEXT NULL");
	ensure_column_exists($conn, 'orders', 'return_processed', "TINYINT(1) NOT NULL DEFAULT 0");
	ensure_column_exists($conn, 'orders', 'return_approved', "TINYINT(1) NULL DEFAULT NULL");

	// products table
	ensure_column_exists($conn, 'products', 'seller_id', "INT(11) NULL DEFAULT NULL");
	ensure_column_exists($conn, 'products', 'stock', "INT(11) NOT NULL DEFAULT 0");

	// users table
	ensure_column_exists($conn, 'users', 'user_type', "VARCHAR(20) NOT NULL DEFAULT 'user'");
	ensure_column_exists($conn, 'users', 'login_otp', "VARCHAR(6) DEFAULT NULL");
	ensure_column_exists($conn, 'users', 'otp_created_at', "TIMESTAMP NULL DEFAULT NULL");
}

// Razorpay Test Credentials (replace with your own test keys)
if (!defined('RAZORPAY_KEY_ID')) {
	define('RAZORPAY_KEY_ID', 'rzp_test_SSYCnJX5bsX28Y');
}
if (!defined('RAZORPAY_KEY_SECRET')) {
	define('RAZORPAY_KEY_SECRET', '84vEhQ5897NEhLuI6wN93mfx');
}

?>