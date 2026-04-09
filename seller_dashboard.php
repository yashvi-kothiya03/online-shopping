<?php
@include 'config.php';

@include 'session_client.php';
$seller_id = $_SESSION['seller_id'];
if(!isset($seller_id)){
    header('location:login.php');
    exit;
}

// Seller dashboard statistics
$my_products_count = 0;
$has_product_seller_column = mysqli_query($conn, "SHOW COLUMNS FROM `products` LIKE 'seller_id'");
if ($has_product_seller_column && mysqli_num_rows($has_product_seller_column) > 0) {
    $select_my_products = mysqli_query($conn, "SELECT * FROM `products` WHERE seller_id = '$seller_id'") or die('query failed');
    $my_products_count = mysqli_num_rows($select_my_products);
}

$orders_filter = '';
$has_seller_ids_column = mysqli_query($conn, "SHOW COLUMNS FROM `orders` LIKE 'seller_ids'");
if ($has_seller_ids_column && mysqli_num_rows($has_seller_ids_column) > 0) {
    $orders_filter = " WHERE FIND_IN_SET('$seller_id', seller_ids) ";
} else {
    // Never show global orders in seller panel if seller mapping is unavailable.
    $orders_filter = " WHERE 1=0 ";
}

$select_orders = mysqli_query($conn, "SELECT * FROM `orders`" . $orders_filter) or die('query failed');
$total_orders = mysqli_num_rows($select_orders);

$select_customers = mysqli_query($conn, "SELECT DISTINCT user_id FROM `orders`" . $orders_filter) or die('query failed');
$total_customers = mysqli_num_rows($select_customers);
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Seller Dashboard</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

<?php @include 'seller_header.php'; ?>

<section class="dashboard">
    <h1 class="title">Welcome, <?php echo htmlspecialchars($_SESSION['seller_name']); ?></h1>
    <div class="box-container">
        <a href="seller_products.php" class="box">
            <div class="icon"><i class="fas fa-boxes-stacked"></i></div>
            <h3><?php echo $my_products_count; ?></h3>
            <p>My products</p>
        </a>
        <a href="seller_orders.php" class="box">
            <div class="icon"><i class="fas fa-shopping-cart"></i></div>
            <h3><?php echo $total_orders; ?></h3>
            <p>Total orders</p>
        </a>
        <a href="seller_customers.php" class="box">
            <div class="icon"><i class="fas fa-user-friends"></i></div>
            <h3><?php echo $total_customers; ?></h3>
            <p>Customers</p>
        </a>
    </div>
</section>

</body>
</html>