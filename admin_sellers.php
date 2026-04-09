<?php

@include 'config.php';

@include 'session_admin.php';

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
};

if (!($conn instanceof mysqli)) {
   die('database connection is not available');
}

if(isset($_GET['delete'])){
   $delete_id = (int)$_GET['delete'];
   mysqli_query($conn, "DELETE FROM `users` WHERE id = '$delete_id' AND LOWER(TRIM(user_type)) = 'seller'") or die('query failed');
   header('location:admin_sellers.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Sellers Management</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php @include 'admin_header.php'; ?>

<main class="main admin-main">

<section class="users">

   <h1 class="title">Sellers</h1>

   <div style="max-width: 1100px; margin: 0 auto;">
      <div style="background: var(--white); border: var(--border); box-shadow: var(--box-shadow); border-radius: 10px; overflow: hidden;">
         <div style="padding: 20px 25px; border-bottom: 1px solid rgba(0,0,0,0.08);">
            <h2 style="margin:0; font-size: 24px;">Sellers</h2>
         </div>
         <div style="padding: 20px;">
            <?php
               $select_sellers = mysqli_query($conn, "SELECT * FROM `users` WHERE LOWER(TRIM(user_type)) = 'seller' ORDER BY id") or die('query failed');
               if(mysqli_num_rows($select_sellers) > 0){
            ?>
            <div class="box-container" style="display:flex; flex-wrap:wrap; gap:1rem; justify-content:center;">
               <?php
                  while($seller = mysqli_fetch_assoc($select_sellers)){
                     $product_count = 0;
                     $prod_query = mysqli_query($conn, "SELECT name FROM `products` WHERE seller_id = '{$seller['id']}'") or die('query failed');
                     if(mysqli_num_rows($prod_query) > 0){
                        $product_count = mysqli_num_rows($prod_query);
                     }
               ?>
               <div class="box" style="min-width: 240px; max-width: 320px; flex: 1 1 280px;">
                  <p>seller id : <span><?php echo $seller['id']; ?></span></p>
                  <p>name : <span><?php echo htmlspecialchars($seller['name']); ?></span></p>
                  <p>email : <span><?php echo htmlspecialchars($seller['email']); ?></span></p>
                  <p>products : <span><?php echo $product_count; ?></span></p>
                  <a href="admin_sellers.php?delete=<?php echo $seller['id']; ?>" onclick="return confirm('delete this seller?');" class="delete-btn">delete</a>
               </div>
               <?php } ?>
            </div>
            <?php
               } else {
                  echo '<p class="empty">No sellers found. Register a new account as Seller from login page.</p>';
               }
            ?>
         </div>
      </div>
   </div>

</section>

</main>

<script src="js/admin_script.js"></script>

</body>
</html>
