<?php
@include 'config.php';

@include 'session_client.php';
$seller_id = $_SESSION['seller_id'];
if(!isset($seller_id)){
    header('location:login.php');
    exit;
}

$message = [];

function buildProductsTextFromItems($items) {
   $parts = [];
   foreach ($items as $it) {
      $name = trim((string)($it['name'] ?? ''));
      $qty = (int)($it['quantity'] ?? 0);
      if ($name !== '' && $qty > 0) {
         $parts[] = $name . ' (' . $qty . ') ';
      }
   }
   return implode(',', $parts);
}

function removeSellerFromCsv($csv, $sellerId) {
   $vals = array_filter(array_map('trim', explode(',', (string)$csv)), function($v) { return $v !== ''; });
   $filtered = [];
   foreach ($vals as $v) {
      if ((int)$v !== (int)$sellerId) {
         $filtered[] = $v;
      }
   }
   return implode(',', array_unique($filtered));
}

// note: if your model stores seller relationship in orders you should use a join here
// for now we show orders and filter optionally by seller_ids and/or specific customer

$customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : null;

$has_seller_ids_column = mysqli_query($conn, "SHOW COLUMNS FROM `orders` LIKE 'seller_ids'") or die('query failed');
$seller_mapping_available = ($has_seller_ids_column && mysqli_num_rows($has_seller_ids_column) > 0);

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   if ($seller_mapping_available) {
      $order_q = mysqli_query($conn, "SELECT * FROM `orders` WHERE id = '$delete_id' AND FIND_IN_SET('$seller_id', seller_ids) LIMIT 1") or die('query failed');
      if (mysqli_num_rows($order_q) > 0) {
         $order = mysqli_fetch_assoc($order_q);
         $items = [];
         if (!empty($order['order_items_json'])) {
            $decoded = json_decode($order['order_items_json'], true);
            if (is_array($decoded)) {
               $items = $decoded;
            }
         }

         if (!empty($items)) {
            $remaining_items = [];
            $removed_total = 0;

            foreach ($items as $it) {
               $item_seller_id = (int)($it['seller_id'] ?? 0);
               $item_qty = (int)($it['quantity'] ?? 0);
               $item_price = (int)($it['price'] ?? 0);
               $line_total = $item_qty * $item_price;

               if ($item_seller_id === (int)$seller_id) {
                  $removed_total += $line_total;
               } else {
                  $remaining_items[] = $it;
               }
            }

            if (empty($remaining_items)) {
               mysqli_query($conn, "DELETE FROM `orders` WHERE id = '$delete_id'") or die('query failed');
               $message[] = 'Your order items were removed. Entire order was deleted because no items remained.';
            } else {
               $new_total_price = max(0, ((int)$order['total_price']) - $removed_total);
               $new_total_products = mysqli_real_escape_string($conn, buildProductsTextFromItems($remaining_items));
               $new_seller_ids = mysqli_real_escape_string($conn, removeSellerFromCsv($order['seller_ids'] ?? '', $seller_id));
               $new_items_json = mysqli_real_escape_string($conn, json_encode(array_values($remaining_items)));

               mysqli_query($conn, "UPDATE `orders` SET total_products = '$new_total_products', total_price = '$new_total_price', seller_ids = '$new_seller_ids', order_items_json = '$new_items_json' WHERE id = '$delete_id'") or die('query failed');
               $message[] = 'Only your items were removed from this order.';
            }
         } else {
            $seller_ids_csv = trim((string)($order['seller_ids'] ?? ''));
            if ($seller_ids_csv === (string)$seller_id) {
               mysqli_query($conn, "DELETE FROM `orders` WHERE id = '$delete_id'") or die('query failed');
               $message[] = 'Order deleted successfully.';
            } else {
               $message[] = 'This order contains items from multiple sellers. Only new orders support per-seller deletion.';
            }
         }
      }
   }
   header('location:seller_orders.php');
   exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Seller Orders</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php @include 'seller_header.php'; ?>

<?php
if(isset($message)){
   foreach($message as $msg){
      echo '
      <div class="message">
         <span>'.$msg.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<section class="placed-orders">

   <h1 class="title">placed orders</h1>

   <?php if ($customer_id) { ?>
      <div style="text-align:center; margin-bottom:1rem;">
         <a href="seller_orders.php" class="option-btn" style="background:#444;">View all orders</a>
      </div>
   <?php } ?>

   <div class="table-responsive" style="margin-top: 30px;">
      <?php
      // Build order query with optional seller + customer filters
      $whereClauses = [];
      if($seller_mapping_available){
         $whereClauses[] = "FIND_IN_SET('$seller_id', seller_ids)";
      } else {
         $whereClauses[] = "1=0";
      }
      if ($customer_id) {
         $whereClauses[] = "user_id = '$customer_id'";
      }
      $query = "SELECT * FROM `orders`" . (count($whereClauses) ? " WHERE " . implode(' AND ', $whereClauses) : "") . " ORDER BY id DESC";
      $select_orders = mysqli_query($conn, $query) or die('query failed');

      if(mysqli_num_rows($select_orders) > 0){
         echo '<table style="width:100%; border-collapse:collapse; background:#fff;">';
         echo '<thead><tr style="background:#f5f5f5;">';
         echo '<th style="border:1px solid #ddd; padding:10px;">Order ID</th>';
         echo '<th style="border:1px solid #ddd; padding:10px;">User ID</th>';
         echo '<th style="border:1px solid #ddd; padding:10px;">Placed On</th>';
         echo '<th style="border:1px solid #ddd; padding:10px;">Name</th>';
         echo '<th style="border:1px solid #ddd; padding:10px;">Number</th>';
         echo '<th style="border:1px solid #ddd; padding:10px;">Email</th>';
         echo '<th style="border:1px solid #ddd; padding:10px;">Total Products</th>';
         echo '<th style="border:1px solid #ddd; padding:10px;">Total Price</th>';
         echo '<th style="border:1px solid #ddd; padding:10px;">Payment Method</th>';
         echo '<th style="border:1px solid #ddd; padding:10px;">Actions</th>';
         echo '</tr></thead><tbody>';
         while($fetch_orders = mysqli_fetch_assoc($select_orders)){
            echo '<tr>';
            echo '<td style="border:1px solid #ddd; padding:10px;">#'.$fetch_orders['id'].'</td>';
            echo '<td style="border:1px solid #ddd; padding:10px;">'.$fetch_orders['user_id'].'</td>';
            echo '<td style="border:1px solid #ddd; padding:10px;">'.$fetch_orders['placed_on'].'</td>';
            echo '<td style="border:1px solid #ddd; padding:10px;">'.$fetch_orders['name'].'</td>';
            echo '<td style="border:1px solid #ddd; padding:10px;">'.$fetch_orders['number'].'</td>';
            echo '<td style="border:1px solid #ddd; padding:10px;">'.$fetch_orders['email'].'</td>';
            echo '<td style="border:1px solid #ddd; padding:10px;">'.$fetch_orders['total_products'].'</td>';
            echo '<td style="border:1px solid #ddd; padding:10px;">₹'.$fetch_orders['total_price'].'/-</td>';
            echo '<td style="border:1px solid #ddd; padding:10px;">'.$fetch_orders['method'].'</td>';
            echo '<td style="border:1px solid #ddd; padding:10px;">';
            echo '<a href="seller_orders.php?delete='.$fetch_orders['id'].'" class="delete-btn" onclick="return confirm(\'remove your items from this order?\');">Remove My Items</a>';
            echo '</td>';
            echo '</tr>';
         }
         echo '</tbody></table>';
      }else{
         if ($customer_id) {
            echo '<p class="empty">No orders found for this customer.</p>';
         } else {
            echo '<p class="empty">no orders placed yet!</p>';
         }
      }
      ?>
   </div>

</section>

<script src="js/admin_script.js"></script>

</body>
</html>