<?php

@include 'config.php';

@include 'session_client.php';

$seller_id = $_SESSION['seller_id'];

if(!isset($seller_id)){
    header('location:login.php');
    exit;
}

if(isset($_POST['update_product'])){

   $update_p_id = $_POST['update_p_id'];
   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $price = mysqli_real_escape_string($conn, $_POST['price']);
   $details = mysqli_real_escape_string($conn, $_POST['details']);
   $stock = mysqli_real_escape_string($conn, $_POST['stock']);
   $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
   $subcategory_id = mysqli_real_escape_string($conn, $_POST['subcategory_id']);

   mysqli_query($conn, "UPDATE `products` SET name = '$name', details = '$details', price = '$price', stock = '$stock', category_id = '$category_id', subcategory_id = '$subcategory_id' WHERE id = '$update_p_id' AND seller_id = '$seller_id'") or die('query failed');

   $image = $_FILES['image']['name'];
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;
   $old_image = $_POST['update_p_image'];

   if(!empty($image)){
      if($image_size > 2000000){
         $message[] = 'image file size is too large!';
      }else{
         mysqli_query($conn, "UPDATE `products` SET image = '$image' WHERE id = '$update_p_id' AND seller_id = '$seller_id'") or die('query failed');
         move_uploaded_file($image_tmp_name, $image_folder);
         if(file_exists('uploaded_img/'.$old_image)){
            unlink('uploaded_img/'.$old_image);
         }
         $message[] = 'image updated successfully!';
      }
   }

   $message[] = 'product updated successfully!';

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Seller Update Product</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php @include 'seller_header.php'; ?>

<section class="update-product">

<?php

   $update_id = $_GET['update'] ?? null;
   if ($update_id) {
      $select_products = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$update_id' AND seller_id = '$seller_id'") or die('query failed');
      if(mysqli_num_rows($select_products) > 0){
         while($fetch_products = mysqli_fetch_assoc($select_products)){
?>

<form action="" method="post" enctype="multipart/form-data">
   <img src="uploaded_img/<?php echo $fetch_products['image']; ?>" class="image"  alt="">
   <input type="hidden" value="<?php echo $fetch_products['id']; ?>" name="update_p_id">
   <input type="hidden" value="<?php echo $fetch_products['image']; ?>" name="update_p_image">
   <input type="text" class="box" value="<?php echo $fetch_products['name']; ?>" required placeholder="update product name" name="name">
   <input type="number" min="0" class="box" value="<?php echo $fetch_products['price']; ?>" required placeholder="update product price" name="price">
   <input type="number" min="0" class="box" value="<?php echo $fetch_products['stock'] ?? 0; ?>" required placeholder="update stock quantity" name="stock">

   <select name="category_id" class="box" required>
      <option value="" disabled>Select Category</option>
      <?php
         $categories = mysqli_query($conn, "SELECT * FROM category") or die('query failed');
         while ($category = mysqli_fetch_assoc($categories)) {
            $selected = $category['id'] == $fetch_products['category_id'] ? 'selected' : '';
            echo '<option value="'.$category['id'].'" '.$selected.'>'.$category['c_name'].'</option>';
         }
      ?>
   </select>
   
   <select name="subcategory_id" class="box" required>
      <option value="" disabled>Select Subcategory</option>
      <?php
         $subcategories = mysqli_query($conn, "SELECT * FROM subcategory") or die('query failed');
         while ($subcategory = mysqli_fetch_assoc($subcategories)) {
            $selected = $subcategory['id'] == $fetch_products['subcategory_id'] ? 'selected' : '';
            echo '<option value="'.$subcategory['id'].'" '.$selected.'>'.$subcategory['name'].'</option>';
         }
      ?>
   </select>

   <textarea name="details" class="box" required placeholder="update product details" cols="30" rows="10"><?php echo $fetch_products['details']; ?></textarea>
   <input type="file" accept="image/jpg, image/jpeg, image/png ,image/webp" class="box" name="image">
   <input type="submit" value="update product" name="update_product" class="btn">
   <a href="seller_products.php" class="option-btn">go back</a>
</form>

<?php
         }
      }else{
         echo '<p class="empty">no update product selected or you do not have permission</p>';
      }
   } else {
      echo '<p class="empty">no update product selected</p>';
   }

?>

</section>


<script src="js/admin_script.js"></script>

</body>
</html>
