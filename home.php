<?php

@include 'config.php';

@include 'session_client.php';

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

$message = []; // Initialize message array

if(isset($_POST['add_to_wishlist'])){

   $product_id = $_POST['product_id'];
   $product_name = $_POST['product_name'];
   $product_price = $_POST['product_price'];
   $product_image = $_POST['product_image'];
   
   $check_wishlist_numbers = mysqli_query($conn, "SELECT * FROM wishlist WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed');
   $check_cart_numbers = mysqli_query($conn, "SELECT * FROM cart WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed');

   if(mysqli_num_rows($check_wishlist_numbers) > 0){
       $message[] = 'Product already added to wishlist';
   } elseif(mysqli_num_rows($check_cart_numbers) > 0){
       $message[] = 'Product already added to cart';
   } else {
       mysqli_query($conn, "INSERT INTO wishlist(user_id, pid, name, price, image) VALUES('$user_id', '$product_id', '$product_name', '$product_price', '$product_image')") or die('query failed');
       $message[] = 'product added to wishlist';
   }

}

if(isset($_POST['add_to_cart'])){

   $product_id = $_POST['product_id'];
   $product_name = $_POST['product_name'];
   $product_price = $_POST['product_price'];
   $product_image = $_POST['product_image'];
   $product_quantity = max(1, (int)$_POST['product_quantity']);

   $product_info_q = mysqli_query($conn, "SELECT stock FROM products WHERE id = '$product_id' LIMIT 1") or die('query failed');
   $product_info = mysqli_fetch_assoc($product_info_q);
   $stock_available = (int)($product_info['stock'] ?? 0);

   if($stock_available <= 0){
      $message[] = 'Product is out of stock and cannot be added to cart.';
   }elseif($product_quantity > $stock_available){
      $message[] = 'Only '.$stock_available.' item(s) left in stock. Please reduce quantity.';
   }else{
      $check_cart_numbers = mysqli_query($conn, "SELECT * FROM cart WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed');

      if(mysqli_num_rows($check_cart_numbers) > 0){
         $message[] = 'Product already added to cart';
      } else {
         $check_wishlist_numbers = mysqli_query($conn, "SELECT * FROM wishlist WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed');

         if(mysqli_num_rows($check_wishlist_numbers) > 0){
            mysqli_query($conn, "DELETE FROM wishlist WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed');
         }

         mysqli_query($conn, "INSERT INTO cart(user_id, pid, name, price, quantity, image) VALUES('$user_id', '$product_id', '$product_name', '$product_price', '$product_quantity', '$product_image')") or die('query failed');
         $message[] = 'product added to cart';
      }
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>HOME</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php @include 'header.php'; ?>

<!-- Messages section -->
<?php
if (!empty($message)) {
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

<section class="home">
   <div class="home-hero">
      <div class="content">
         <p class="hero-tag">New Season Ceramic Collection</p>
         <h3>Elegant Pieces For Everyday Living</h3>
         <p>Discover handcrafted ceramic products designed for modern homes. Shop trending items, create your wishlist, and place orders in a few clicks.</p>
         <div class="hero-actions">
            <a href="shop.php" class="btn">shop now</a>
            <a href="orders.php" class="option-btn">my orders</a>
         </div>
      </div>
   </div>
</section>

<section class="home-highlights">
   <div class="highlight-box">
      <i class="fas fa-shipping-fast"></i>
      <h4>Fast Delivery</h4>
      <p>Secure packaging and quick dispatch for fragile products.</p>
   </div>
   <div class="highlight-box">
      <i class="fas fa-star"></i>
      <h4>Top Rated Quality</h4>
      <p>Carefully selected finishes and durable ceramic material.</p>
   </div>
   <div class="highlight-box">
      <i class="fas fa-headset"></i>
      <h4>Easy Support</h4>
      <p>Simple order, OTP, and return flow with responsive help.</p>
   </div>
</section>

<section class="products">
   <h1 class="title">latest products</h1>
   <div class="box-container">

      <?php
         // Limit the number of products displayed to 6
         $select_products = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC LIMIT 6") or die('query failed');
         if(mysqli_num_rows($select_products) > 0){
            while($fetch_products = mysqli_fetch_assoc($select_products)){
      ?>
      <form action="" method="POST" class="box">
         <a href="view_page.php?pid=<?php echo $fetch_products['id']; ?>" class="fas fa-eye"></a>
         <div class="price">₹<?php echo $fetch_products['price']; ?>/-</div>
         <img src="uploaded_img/<?php echo $fetch_products['image']; ?>" alt="" class="image">
         <div class="name"><?php echo $fetch_products['name']; ?></div>
         <!-- <div class="details">Stock: <?php echo intval($fetch_products['stock'] ?? 0) + 100; ?></div> -->
         <input type="hidden" name="product_quantity" value="1">
         <input type="hidden" name="product_id" value="<?php echo $fetch_products['id']; ?>">
         <input type="hidden" name="product_name" value="<?php echo $fetch_products['name']; ?>">
         <input type="hidden" name="product_price" value="<?php echo $fetch_products['price']; ?>">
         <input type="hidden" name="product_image" value="<?php echo $fetch_products['image']; ?>">
         <input type="submit" value="add wishlist" name="add_to_wishlist" class="option-btn">
         <input type="submit" value="add cart" name="add_to_cart" class="btn" <?php echo intval($fetch_products['stock'] ?? 0) > 0 ? '' : 'disabled style="opacity:.5;cursor:not-allowed;"'; ?>>
      </form>
      <?php
            }
         } else {
            echo '<p class="empty">no products added yet!</p>';
         }
      ?>

   </div>

   <div class="more-btn">
      <a href="shop.php" class="option-btn">load more</a>
   </div>

</section>

<?php @include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>