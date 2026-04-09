<?php
@include 'config.php';
@include 'session_client.php';

// Fetch categories
$select_categories = mysqli_query($conn, "SELECT * FROM `categories`") or die('query failed');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Categories</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php @include 'header.php'; ?>

<section class="categories">
    <h1 class="title">Categories</h1>

    <?php
        if (mysqli_num_rows($select_categories) > 0) {
            while ($category = mysqli_fetch_assoc($select_categories)) {
    ?>
    <div class="category-box">
        <h2><?php echo $category['name']; ?></h2>
        <a href="view_products.php?category_id=<?php echo $category['id']; ?>" class="btn">View Products</a>
    </div>
    <?php
            }
        } else {
            echo '<p class="empty">No categories available</p>';
        }
    ?>
</section>

<?php @include 'footer.php'; ?>

</body>
</html>
