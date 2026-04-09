<?php
@include 'config.php';
@include 'session_client.php';
$seller_id = $_SESSION['seller_id'];
if(!isset($seller_id)){
    header('location:login.php');
    exit;
}

if(isset($_GET['order_id'])){
    $order_id = intval($_GET['order_id']);
    $res = mysqli_query($conn, "SELECT * FROM `orders` WHERE id = '$order_id'") or die('query failed');
    if(mysqli_num_rows($res) > 0){
        $order = mysqli_fetch_assoc($res);
        $placed_on = date('Y-m-d H:i:s');
        mysqli_query($conn, "INSERT INTO `orders` (`user_id`,`name`,`number`,`email`,`method`,`address`,`total_products`,`total_price`,`placed_on`,`payment_status`) VALUES('{$order['user_id']}','{$order['name']}','{$order['number']}','{$order['email']}','{$order['method']}','{$order['address']}','{$order['total_products']}','{$order['total_price']}','$placed_on','pending')") or die('query failed');
        header('location:seller_orders.php?msg=repeated');
        exit;
    }
}
header('location:seller_orders.php');
exit;
?>