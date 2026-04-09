<?php
@include 'config.php';

@include 'session_client.php';
$seller_id = $_SESSION['seller_id'];
if(!isset($seller_id)){
    header('location:login.php');
    exit;
}

// fetch distinct customers (by user_id) from orders table
$has_seller_ids_column = mysqli_query($conn, "SHOW COLUMNS FROM `orders` LIKE 'seller_ids'") or die('query failed');
if ($has_seller_ids_column && mysqli_num_rows($has_seller_ids_column) > 0) {
    $customers = mysqli_query($conn, "SELECT DISTINCT user_id, name, email, number FROM `orders` WHERE FIND_IN_SET('$seller_id', seller_ids)") or die('query failed');
} else {
    $customers = mysqli_query($conn, "SELECT DISTINCT user_id, name, email, number FROM `orders` WHERE 1=0") or die('query failed');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Seller Customers</title>

   <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

<?php @include 'seller_header.php'; ?>

<section class="customers">
    <h1 class="title">Customers</h1>
    <?php $customer_count = mysqli_num_rows($customers); ?>
    <div style="max-width: 1100px; margin: 0 auto;">
        <div style="background: var(--white); border: var(--border); box-shadow: var(--box-shadow); border-radius: 10px; overflow: hidden;">
            <div style="padding: 20px 25px; border-bottom: 1px solid rgba(0,0,0,0.08); display:flex; justify-content:space-between; align-items:center;">
                <h2 style="margin:0; font-size: 24px;">Customers (<?php echo $customer_count; ?>)</h2>
            </div>
            <div style="padding: 20px;">
                <?php if ($customer_count > 0): ?>
                    <table style="width:100%; border-collapse: collapse; font-size: 15px;">
                        <thead>
                            <tr>
                                <th style="text-align:left; padding: 14px 12px; border-bottom: 1px solid #eee; font-weight: 700;">Name</th>
                                <th style="text-align:left; padding: 14px 12px; border-bottom: 1px solid #eee; font-weight: 700;">Email</th>
                                <th style="text-align:left; padding: 14px 12px; border-bottom: 1px solid #eee; font-weight: 700;">Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($c = mysqli_fetch_assoc($customers)): ?>
                                <tr>
                                    <td style="padding: 14px 12px; border-bottom: 1px solid #f0f0f0; vertical-align: top;"><?php echo htmlspecialchars($c['name']); ?></td>
                                    <td style="padding: 14px 12px; border-bottom: 1px solid #f0f0f0; vertical-align: top;"><?php echo htmlspecialchars($c['email']); ?></td>
                                    <td style="padding: 14px 12px; border-bottom: 1px solid #f0f0f0; vertical-align: top;"><?php echo htmlspecialchars($c['number']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="empty">no customers found!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

</body>
</html>