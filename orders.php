<?php

@include 'config.php';
@include 'email_functions.php';

@include 'session_client.php';

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

// Ensure return columns exist (makes return flow safe even if DB schema is not yet updated)
if (function_exists('ensureReturnOtpColumns')) {
    ensureReturnOtpColumns($conn);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>orders</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

     <!-- custom admin css file link  -->
     <link rel="stylesheet" href="css/style.css">
     <style>
     .placed-orders table {
         border-collapse: collapse;
         width: 100%;
         background: #fff;
         margin-top: 20px;
     }
     .placed-orders th, .placed-orders td {
         border: 1px solid #ddd;
         padding: 10px 8px;
         text-align: left;
         vertical-align: top;
     }
     .placed-orders th {
         background: #f5f5f5;
         font-weight: bold;
         font-size: 16px;
         border-bottom: 2px solid #bbb;
     }
     .placed-orders tr:nth-child(even) {
         background: #fafafa;
     }
     .placed-orders tr:hover {
         background: #f1f1f1;
     }
     .placed-orders .btn {
         display: inline-block;
         padding: 6px 14px;
         background: #222;
         color: #fff;
         border-radius: 4px;
         text-decoration: none;
         font-size: 14px;
         margin-bottom: 4px;
     }
     .placed-orders td {
         font-size: 15px;
     }
     .placed-orders .empty {
         text-align: center;
         color: #888;
         font-size: 18px;
     }
     </style>

</head>
<body>
   
<?php @include 'header.php'; ?>

<section class="heading">
    <h3>your orders</h3>
    <p> <a href="home.php">Home</a> / order </p>
</section>

<section class="placed-orders">

        <h1 class="title">placed orders</h1>
        <div style="overflow-x:auto;">
        <table border="1" cellpadding="8" cellspacing="0" style="width:100%;background:#fff;">
            <thead>
                <tr>
                    <th>Placed On</th>
                    <th>Order ID</th>
                    <th>Name</th>
                    <th>Number</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Payment Method</th>
                    <th>Your Orders</th>
                    <th>Total Price</th>
                    <th>Order Status</th>
                    <th>Email Verification</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $select_orders = mysqli_query($conn, "SELECT * FROM `orders` WHERE user_id = '$user_id' ORDER BY id DESC") or die('query failed');
                if(mysqli_num_rows($select_orders) > 0){
                        $rowCount = 0;
                        while($fetch_orders = mysqli_fetch_assoc($select_orders)){
                ?>
                <tr style="border-bottom:2px solid #eee;">
                    <td><?php echo $fetch_orders['placed_on']; ?></td>
                    <td>#<?php echo $fetch_orders['id']; ?></td>
                    <td><?php echo $fetch_orders['name']; ?></td>
                    <td><?php echo $fetch_orders['number']; ?></td>
                    <td><?php echo $fetch_orders['email']; ?></td>
                    <td><?php echo $fetch_orders['address']; ?></td>
                    <td><?php echo $fetch_orders['method']; ?></td>
                    <td><?php echo $fetch_orders['total_products']; ?></td>
                    <td>₹<?php echo $fetch_orders['total_price']; ?>/-</td>
                    <td style="color:<?php echo ($fetch_orders['payment_status'] == 'pending') ? 'tomato' : 'green'; ?>;">
                        <?php echo $fetch_orders['payment_status']; ?>
                    </td>
                    <td style="color:<?php echo ($fetch_orders['otp_verified'] == 1) ? 'green' : 'orange'; ?>; font-weight:bold;">
                        <?php echo ($fetch_orders['otp_verified'] == 1) ? '✓ Verified' : '⏳ Pending Verification'; ?>
                    </td>
                    <td>
                        <?php if($fetch_orders['otp_verified'] != 1){ ?>
                            <a href="verify_order_otp.php?order_id=<?php echo $fetch_orders['id']; ?>" class="btn" style="margin-bottom:5px;">Verify Email</a><br>
                        <?php } ?>
                        <?php
                            $return_requested = isset($fetch_orders['return_requested']) ? $fetch_orders['return_requested'] : 0;
                            $return_verified = isset($fetch_orders['return_otp_verified']) ? $fetch_orders['return_otp_verified'] : 0;
                        ?>
                        <?php if ($return_requested) { ?>
                            <span style="color:<?php echo ($return_verified ? 'green' : 'orange'); ?>; font-weight:bold;">
                                <?php echo ($return_verified ? '✓ Returned' : '⏳ Return Pending OTP'); ?>
                            </span><br>
                            <?php if (!empty($fetch_orders['return_reason'])) { ?>
                                <span>Reason: <?php echo htmlspecialchars($fetch_orders['return_reason']); ?></span><br>
                            <?php } ?>
                            <?php if (!empty($fetch_orders['return_details'])) { ?>
                                <span>Details: <?php echo nl2br(htmlspecialchars($fetch_orders['return_details'])); ?></span><br>
                            <?php } ?>
                            <?php if (!empty($fetch_orders['return_approved']) || $fetch_orders['return_approved'] === '0') { ?>
                                <span style="color:<?php echo ($fetch_orders['return_approved'] ? 'green' : 'tomato'); ?>; font-weight:bold;">
                                    <?php echo ($fetch_orders['return_approved'] ? 'Approved' : 'Rejected'); ?>
                                </span><br>
                            <?php } ?>
                            <?php if (!$return_verified) { ?>
                                <a href="verify_return_otp.php?order_id=<?php echo $fetch_orders['id']; ?>" class="btn" style="margin-bottom:5px;">Verify Return OTP</a><br>
                            <?php } ?>
                        <?php } else if (!$return_requested && strtolower($fetch_orders['payment_status']) == 'completed' && strtolower($fetch_orders['payment_status']) != 'returned') { ?>
                            <a href="request_return.php?order_id=<?php echo $fetch_orders['id']; ?>" class="btn">Request Return</a>
                        <?php } ?>
                    </td>
                </tr>
                <?php
                        $rowCount++;
                        }
                }else{
                        echo '<tr><td colspan="12" class="empty">no orders placed yet!</td></tr>';
                }
                ?>
            </tbody>
        </table>
        </div>

</section>

<?php @include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>