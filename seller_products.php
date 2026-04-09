<?php
@include 'config.php';

@include 'session_client.php';

if (!($conn instanceof mysqli)) {
   die('database connection is not available');
}

$seller_id = $_SESSION['seller_id'];
if(!isset($seller_id)){
    header('location:login.php');
    exit;
}

// Ensure products table has seller_id column (prevents SQL errors if the column is missing)
if (function_exists('ensure_column_exists')) {
   ensure_column_exists($conn, 'products', 'seller_id', "INT(11) NULL DEFAULT NULL");
   ensure_column_exists($conn, 'products', 'stock', "INT(11) NOT NULL DEFAULT 0");
}

if (isset($_POST['add_product'])) {
   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $price = mysqli_real_escape_string($conn, $_POST['price']);
   $details = mysqli_real_escape_string($conn, $_POST['details']);
   $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
   $subcategory_id = mysqli_real_escape_string($conn, $_POST['subcategory_id']);
   $image = $_FILES['image']['name'];
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $stock = mysqli_real_escape_string($conn, $_POST['stock']);
   $image_folder = 'uploaded_img/'.$image;

   $select_product_name = mysqli_query($conn, "SELECT name FROM `products` WHERE name = '$name' AND seller_id = '$seller_id'") or die('query failed');

   if (mysqli_num_rows($select_product_name) > 0) {
       $message[] = 'You have already added a product with this name';
   } else {
       if ($image_size > 2000000) {
           $message[] = 'Image size is too large!';
       } else {
           move_uploaded_file($image_tmp_name, $image_folder);
           $insert_product = mysqli_query($conn, "INSERT INTO `products`(name, details, price, image, category_id, subcategory_id, seller_id, stock) VALUES('$name', '$details', '$price', '$image', '$category_id', '$subcategory_id', '$seller_id', '$stock')") or die('query failed');
           
           if ($insert_product) {
               $message[] = 'Product added successfully!';
           } else {
               $message[] = 'Error adding product: ' . mysqli_error($conn);
           }
       }
   }
}

if (isset($_GET['delete'])) {
   $delete_id = (int)$_GET['delete'];
   // only allow seller to delete their own products
   $sel = mysqli_query($conn, "SELECT image FROM `products` WHERE id = '$delete_id' AND seller_id = '$seller_id'") or die('query failed');
   if(mysqli_num_rows($sel) > 0){
       $fetch_delete_image = mysqli_fetch_assoc($sel);
       unlink('uploaded_img/'.$fetch_delete_image['image']);
       mysqli_query($conn, "DELETE FROM `products` WHERE id = '$delete_id' AND seller_id = '$seller_id'") or die('query failed');
   }
   header('location:seller_products.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Seller | Products</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

<?php @include 'seller_header.php'; ?>

<section class="add-products">

   <h1 class="title">Add new product</h1>

   <form action="" method="POST" enctype="multipart/form-data">
      <input type="text" class="box" required placeholder="enter product name" name="name">
      <input type="number" min="0" class="box" required placeholder="enter product price" name="price">
      <textarea name="details" class="box" required placeholder="enter product details" cols="30" rows="10"></textarea>
      <input type="number" min="0" class="box" required placeholder="enter stock quantity" name="stock">
      <!-- Category dropdown -->
      <select name="category_id" class="box" required>
         <option value="" disabled selected>Select Category</option>
         <?php
            $categories = mysqli_query($conn, "SELECT * FROM category") or die('query failed');
            while ($category = mysqli_fetch_assoc($categories)) {
               echo '<option value="'.$category['id'].'">'.$category['c_name'].'</option>';
            }
         ?>
      </select>
      
      <!-- Subcategory dropdown -->
      <select name="subcategory_id" class="box" required>
         <option value="" disabled selected>Select Subcategory</option>
         <?php
            $subcategories = mysqli_query($conn, "SELECT * FROM subcategory") or die('query failed');
            $seen_subs = [];
            while ($subcategory = mysqli_fetch_assoc($subcategories)) {
               $sub_name = strtolower(trim($subcategory['name']));
               if (!in_array($sub_name, $seen_subs)) {
                  echo '<option value="'.$subcategory['id'].'">'.$subcategory['name'].'</option>';
                  $seen_subs[] = $sub_name;
               }
            }
         ?>
      </select>
      
      <input type="file" accept="image/jpg, image/jpeg, image/png ,image/webp" required class="box" name="image">
      <input type="submit" value="add product" name="add_product" class="btn">
   </form>

</section>

<section class="show-products">

   <h1 class="title">Your products</h1>

   <div class="box-container">

      <?php
         $select_products = mysqli_query($conn, "SELECT * FROM `products` WHERE seller_id = '$seller_id'") or die('query failed');
         if(mysqli_num_rows($select_products) > 0){
            while($fetch_products = mysqli_fetch_assoc($select_products)){
      ?>
      <div class="box">
         <div class="price">₹<?php echo $fetch_products['price']; ?>/-</div>
         <img class="image" src="uploaded_img/<?php echo $fetch_products['image']; ?>" alt="">
         <div class="name"><?php echo $fetch_products['name']; ?></div>
         <div class="details"><?php echo $fetch_products['details']; ?></div>
         <div class="details">Stock: <?php echo intval($fetch_products['stock'] ?? 0); ?></div>
         <a href="seller_update_product.php?update=<?php echo $fetch_products['id']; ?>" class="option-btn">edit</a>
         <a href="seller_products.php?delete=<?php echo $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('delete this product?');">delete</a>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">no products added yet!</p>';
      }
      ?>
   </div>
   
</section>

<script src="js/admin_script.js"></script>

</body>
</html>