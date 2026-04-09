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
};

if(isset($_POST['update_order'])){
   $order_id = $_POST['order_id'];
   $update_payment = mysqli_real_escape_string($conn, $_POST['update_payment']);

   $order_status_query = mysqli_query($conn, "SELECT payment_status FROM `orders` WHERE id = '$order_id' LIMIT 1") or die('query failed');
   $order_status_data = mysqli_fetch_assoc($order_status_query);
   $current_status = strtolower(trim($order_status_data['payment_status'] ?? 'pending'));

   // Once confirmed, do not allow status to go back to pending.
   if (($current_status === 'completed' || $current_status === 'prepaid') && strtolower($update_payment) === 'pending') {
      $message[] = 'Confirmed order cannot be changed back to pending!';
   } else {
      mysqli_query($conn, "UPDATE `orders` SET payment_status = '$update_payment' WHERE id = '$order_id'") or die('query failed');
      $message[] = 'payment status has been updated!';
   }
}

if(isset($_POST['mark_return_processed'])){
   $order_id = $_POST['order_id'];
   mysqli_query($conn, "UPDATE `orders` SET return_processed = 1 WHERE id = '$order_id'") or die('query failed');
   $message[] = 'return marked as processed.';
}

if(isset($_POST['return_action'])){
   $order_id = $_POST['order_id'];
   $action = $_POST['return_action'];

   // Fetch order email/name for notification
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

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   mysqli_query($conn, "DELETE FROM `orders` WHERE id = '$delete_id'") or die('query failed');
   header('location:admin_orders.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Order Management</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php @include 'admin_header.php'; ?>

<main class="main admin-main">

<section class="placed-orders">

   <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
      <h1 class="title" style="margin:0;">placed orders</h1>
      <div style="display:flex; align-items:center; gap:0.5rem;">
         <button id="toggleSearch" type="button" class="btn" style="padding:0.6rem 1rem; font-size:1.5rem; display:flex; align-items:center; gap:0.5rem;">
            <i class="fas fa-search"></i>
            Search
         </button>
         <form id="orderSearchForm" method="GET" action="admin_orders.php" style="display:none; align-items:center; gap:0.5rem;">
            <input name="search" id="orderSearch" type="text" placeholder="Search by name/date" style="padding:0.6rem 0.8rem; font-size:1.4rem;" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            <button type="submit" class="btn" style="padding:0.6rem 1rem; font-size:1.4rem;">Go</button>
            <button id="clearSearch" type="button" class="delete-btn" style="padding:0.6rem 1rem; font-size:1.4rem;">Clear</button>
         </form>
      </div>
   </div>

   <div class="box-container">

      <?php
      
      $search = isset($_GET['search']) ? trim(mysqli_real_escape_string($conn, $_GET['search'])) : '';
      $query = "SELECT * FROM `orders`";
      if (!empty($search)) {
         $query .= " WHERE name LIKE '%$search%' OR placed_on LIKE '%$search%' OR number LIKE '%$search%' OR email LIKE '%$search%' OR address LIKE '%$search%'";
      }
      $query .= " ORDER BY id DESC";
      $select_orders = mysqli_query($conn, $query) or die('query failed');
      if(mysqli_num_rows($select_orders) > 0){
         while($fetch_orders = mysqli_fetch_assoc($select_orders)){
            $status_display = $fetch_orders['payment_status'];
            if ($fetch_orders['method'] === 'razorpay' && $status_display === 'completed') {
               $status_display = 'prepaid';
            }
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
         <p> payment method : <span><?php echo $fetch_orders['method']; ?></span> </p>
         <?php if (!empty($fetch_orders['return_requested'])) { ?>
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
                <form action="" method="post" style="margin-top:0.5rem; display:flex; gap:8px;">
                    <input type="hidden" name="order_id" value="<?php echo $fetch_orders['id']; ?>">
                    <button type="submit" name="return_action" value="approve" class="option-btn" style="padding:6px 12px; font-size:12px;">Approve Return</button>
                    <button type="submit" name="return_action" value="reject" class="delete-btn" style="padding:6px 12px; font-size:12px;">Reject Return</button>
                </form>
            <?php } elseif (isset($fetch_orders['return_approved'])) { ?>
                <p> return decision : <span style="color:<?php echo ($fetch_orders['return_approved'] ? 'green' : 'tomato'); ?>; font-weight:bold;">
                    <?php echo ($fetch_orders['return_approved'] ? 'Approved' : 'Rejected'); ?>
                </span></p>
             <?php } ?>
         <?php } ?>
         <form action="" method="post">
            <input type="hidden" name="order_id" value="<?php echo $fetch_orders['id']; ?>">
            <?php $status_lower = strtolower(trim($status_display)); ?>
            <select name="update_payment">
               <option disabled selected><?php echo $status_display; ?></option>
               <?php if ($status_lower === 'pending') { ?>
                  <option value="pending">pending</option>
               <?php } ?>
               <option value="prepaid">prepaid</option>
               <option value="completed">completed</option>
            </select>
            <input type="submit" name="update_order" value="update" class="option-btn">
            <a href="admin_orders.php?delete=<?php echo $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('delete this order?');">delete</a>
         </form>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">no orders placed yet!</p>';
      }
      ?>
   </div>

</section>

</main>

<script>
   document.addEventListener('DOMContentLoaded', function(){
      var toggle = document.getElementById('toggleSearch');
      var form = document.getElementById('orderSearchForm');
      var clear = document.getElementById('clearSearch');
      var input = document.getElementById('orderSearch');

      if (!toggle || !form) return;

      toggle.addEventListener('click', function(){
         if (form.style.display === 'flex' || form.style.display === 'block') {
            form.style.display = 'none';
         } else {
            form.style.display = 'flex';
            if (input) input.focus();
         }
      });

      if (clear) {
         clear.addEventListener('click', function(){
            if (input) input.value = '';
            form.submit();
         });
      }

      // Keep form visible if search term already present
      if (input && input.value.trim() !== '') {
         form.style.display = 'flex';
      }

      // Live filter orders as user types
      var ordersContainer = document.querySelector('.box-container');
      if (input && ordersContainer) {
         input.addEventListener('input', function() {
            var query = input.value.trim().toLowerCase();
            var boxes = ordersContainer.querySelectorAll('.box');
            boxes.forEach(function(box) {
               if (!query) {
                  box.style.display = '';
                  return;
               }
               var text = box.innerText.toLowerCase();
               box.style.display = text.indexOf(query) !== -1 ? '' : 'none';
            });
         });
      }
   });
</script>

<script src="js/admin_script.js"></script>

</body>
</html>