<?php

@include 'config.php';
@include 'email_functions.php';

@include 'session_admin.php';

// Ensure return columns exist (safe if DB schema has not been updated yet)
if (function_exists('ensureReturnOtpColumns')) {
    ensureReturnOtpColumns($conn);
}

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
}

$message = [];

if(isset($_POST['return_action'])){
   $order_id = $_POST['order_id'];
   $action = $_POST['return_action'];

   $order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE id = '$order_id'") or die('query failed');
   $order_data = mysqli_fetch_assoc($order_query);

   if($action === 'approve'){
       mysqli_query($conn, "UPDATE `orders` SET return_approved = 1 WHERE id = '$order_id'") or die('query failed');
       $message[] = 'Return approved.';
       if (!empty($order_data['email'])) {
           sendReturnDecisionEmail($order_data['email'], $order_data['name'], $order_id, true);
       }
   } elseif($action === 'reject'){
       mysqli_query($conn, "UPDATE `orders` SET return_approved = 0 WHERE id = '$order_id'") or die('query failed');
       $message[] = 'Return rejected.';
       if (!empty($order_data['email'])) {
           sendReturnDecisionEmail($order_data['email'], $order_data['name'], $order_id, false);
       }
   }
}

if(isset($_POST['mark_return_processed'])){
   $order_id = $_POST['order_id'];
   mysqli_query($conn, "UPDATE `orders` SET return_processed = 1 WHERE id = '$order_id'") or die('query failed');
   $message[] = 'Return marked as processed.';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Return Inquiry</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php @include 'admin_header.php'; ?>

<section class="placed-orders">

   <h1 class="title">return parcel inquiry</h1>

   <div class="box-container">

      <?php
      $select_returns = mysqli_query($conn, "SELECT * FROM `orders` WHERE return_requested = 1 ORDER BY placed_on DESC") or die('query failed');
      if(mysqli_num_rows($select_returns) > 0){
         while($fetch_orders = mysqli_fetch_assoc($select_returns)){
      ?>
      <div class="box">
         <p> user id : <span><?php echo $fetch_orders['user_id']; ?></span> </p>
         <p> placed on : <span><?php echo $fetch_orders['placed_on']; ?></span> </p>
         <p> name : <span><?php echo $fetch_orders['name']; ?></span> </p>
         <p> number : <span><?php echo $fetch_orders['number']; ?></span> </p>
         <p> email : <span><?php echo $fetch_orders['email']; ?></span> </p>
         <p> address : <span><?php echo $fetch_orders['address']; ?></span> </p>
         <p> total products : <span><?php echo $fetch_orders['total_products']; ?></span> </p>
         <p> total price : <span>₹<?php echo $fetch_orders['total_price']; ?>/-</span> </p>

         <p> return status : <span style="color:<?php echo (!empty($fetch_orders['return_otp_verified']) ? 'green' : 'orange'); ?>; font-weight:bold;">
             <?php echo (!empty($fetch_orders['return_otp_verified']) ? '✓ Returned' : '⏳ Return Pending OTP'); ?>
         </span> </p>

         <?php if (!empty($fetch_orders['return_reason'])) { ?>
             <p> return reason : <span><?php echo htmlspecialchars($fetch_orders['return_reason']); ?></span> </p>
         <?php } ?>
         <?php if (!empty($fetch_orders['return_details'])) { ?>
             <p> return details : <span><?php echo nl2br(htmlspecialchars($fetch_orders['return_details'])); ?></span> </p>
         <?php } ?>

         <?php if (!empty($fetch_orders['return_otp_verified']) && empty($fetch_orders['return_approved'])) { ?>
            <form action="" method="post" style="margin-top:0.5rem; display:flex; gap:8px; flex-wrap:wrap;">
                <input type="hidden" name="order_id" value="<?php echo $fetch_orders['id']; ?>">
                <button type="submit" name="return_action" value="approve" class="option-btn" style="padding:6px 12px; font-size:12px;">Approve Return</button>
                <button type="submit" name="return_action" value="reject" class="delete-btn" style="padding:6px 12px; font-size:12px;">Reject Return</button>
            </form>
         <?php } elseif (isset($fetch_orders['return_approved'])) { ?>
            <p> return decision : <span style="color:<?php echo ($fetch_orders['return_approved'] ? 'green' : 'tomato'); ?>; font-weight:bold;">
                <?php echo ($fetch_orders['return_approved'] ? 'Approved' : 'Rejected'); ?>
            </span></p>
         <?php } ?>

         <?php if (!empty($fetch_orders['return_otp_verified'])) { ?>
            <form action="" method="post" style="margin-top:0.5rem; display:flex; gap:8px; flex-wrap:wrap;">
                <input type="hidden" name="order_id" value="<?php echo $fetch_orders['id']; ?>">
                <button type="submit" name="mark_return_processed" class="option-btn" style="padding:6px 12px; font-size:12px;">Mark Processed</button>
            </form>
            <p> processed : <span style="color:<?php echo (!empty($fetch_orders['return_processed']) ? 'green' : 'orange'); ?>; font-weight:bold;">
                <?php echo (!empty($fetch_orders['return_processed']) ? 'Yes' : 'No'); ?>
            </span></p>
         <?php } ?>

      </div>
      <?php
         }
      } else {
         echo '<p class="empty">no return inquiries at the moment.</p>';
      }
      ?>
   </div>

</section>

<script src="js/admin_script.js"></script>

</body>
</html>
