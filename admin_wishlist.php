<?php

@include 'config.php';

@include 'session_admin.php';

// Check if the admin is logged in
$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit();
}

// Handle adding items to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_image = $_POST['product_image'];
    $product_quantity = 1;
    $user_id = $_POST['user_id']; // Added user_id for context

    $check_cart_numbers = mysqli_query($conn, "SELECT * FROM `cart` WHERE name = '$product_name' AND user_id = '$user_id'") or die('Query failed');

    if (mysqli_num_rows($check_cart_numbers) > 0) {
        $message[] = 'Already added to cart';
    } else {
        $check_wishlist_numbers = mysqli_query($conn, "SELECT * FROM `wishlist` WHERE name = '$product_name' AND user_id = '$user_id'") or die('Query failed');

        if (mysqli_num_rows($check_wishlist_numbers) > 0) {
            mysqli_query($conn, "DELETE FROM `wishlist` WHERE name = '$product_name' AND user_id = '$user_id'") or die('Query failed');
        }

        mysqli_query($conn, "INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES('$user_id', '$product_id', '$product_name', '$product_price', '$product_quantity', '$product_image')") or die('Query failed');
        $message[] = 'Product added to cart';
    }
}

// Handle deleting individual wishlist items
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM `wishlist` WHERE id = '$delete_id'") or die('Query failed');
    header('location:admin_wishlist.php');
    exit();
}

// Handle deleting all wishlist items for a user
if (isset($_GET['delete_all'])) {
    $user_id = $_GET['user_id']; // Get the user ID from the URL
    mysqli_query($conn, "DELETE FROM `wishlist` WHERE user_id = '$user_id'") or die('Query failed');
    header('location:admin_wishlist.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Wishlist Management</title>

   <!-- Font Awesome CDN Link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- Custom Admin CSS File Link -->
   <link rel="stylesheet" href="css/admin_style.css">
<style>
/* General Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    background-color: #f5f5f5;
    color: #333;
    line-height: 1.6;
}

/* Header Styles */
header {
    background: #333;
    color: #fff;
    text-align: center;
}

header a {
    color: #fff;
    text-decoration: none;
}
/* Heading Section */
.heading {
    text-align: center;
    padding: 2rem 0;
    background-color: #fff ;
    color: #000;
}

.heading h3{
    font-family: 'Poppins', sans-serif;
    font-size: 34px;
    margin-bottom: 5px;
}
.heading p a {
    color: #d33cf2;
    text-decoration: underline;
    font-size: 18px;
}

.heading p {
    font-size: 14px;
    color: black;
}

/* Wishlist Section */
.wishlist {
    padding: 2rem;
}
.wishlist h2{
    font-size: 18px;
    text-align: right;
    padding-top: 20px;
}

.wishlist .title {
    text-align: center;
    margin-bottom: 2rem;
    color: var(--black);
    text-transform: uppercase;
    font-size: 4rem;
    padding: 20px 0;
}
.wishlist .title-2 {
    text-align: center;
    margin-bottom: 2rem;
    color: var(--black);
    text-transform: uppercase;
    font-size: 3rem;
    padding: 20px 0;
}

.wishlist-user{
    justify-content: flex-start; /* align items to start rather than right */
    flex-direction: column; /* ensure header and boxes stack correctly */
}
.wishlist-user h2{
    font-size: 18px;
    margin: 0.5rem 0; /* spacing for user header */
}

/* Box Container */
.box-container {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.box {
    box-shadow: 5px 5px 20px 2px #0000005c;
    border: var(--border);
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    width: calc(33.333% - 1rem);
    margin-bottom: 1rem;
    padding: 1rem;
    position: relative;
    text-align: center;
}

.box img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    object-position: center center;
    border-bottom: 1px solid #ddd;
    margin-bottom: 1rem;
}

.box .name {
    font-size: 20px;
    margin: 0.5rem 0;
    font-weight: 500;
}

.box .price {
    font-size: 18px;
    color: #555;
}

.box .btn {
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-transform: uppercase;
    font-weight: 500;
    transition: background 0.3s ease;
    background-color: #000;
    font-size: 16px;
    padding: 10px 18px;
}

.box .btn:hover {
    background: #0056b3;
}

/* Remove Item Icon */
.box .fas.fa-times {
    position: absolute;
    top: 4%;
    right: 5%;
    color: #ff0000;
    font-size: 24px;
    cursor: pointer;
}

.box .fas.fa-eye {
    position: absolute;
    top: 4%;
    left: 5%;
    color: #fff;
    font-size: 30px;
    cursor: pointer;
}

/* Wishlist Total */
.totle-price{
    display:flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 0;
}
.wishlist-total {
    text-align: center;
    margin-top: 2rem;
}

.wishlist-total p {
    font-size: 18px;
    font-weight: 400;
    margin-bottom: 1rem;
}

.wishlist-total span {
    font-size: 20px;
    color: #000;
    font-weight: 600;
}

/* Buttons */
.option-btn, .delete-btn {
    display: inline-block;
    color: #fff;
    border-radius: 4px;
    text-decoration: none;
    text-transform: uppercase;
    font-weight: 400;
    margin: 0.5rem;
    transition: background 0.3s ease;
    background-color: #000;
    font-size: 18px;
    padding: 12px 18px;
}

.option-btn:hover, .delete-btn:hover {
    background: #0056b3;
}

.delete-btn.disabled {
    background: #ddd;
    color: #666;
    cursor: not-allowed;
}


</style>
</head>

<body>

<?php @include 'admin_header.php'; ?>

<section class="heading">
    <h1 class="title">Manage User Wishlists</h1>
</section>

<section class="wishlist">
    <h2 class="title-2">User Wishlists</h2>

    <div class="box-container wishlist-user">

    <?php
        $select_users = mysqli_query($conn, "SELECT DISTINCT user_id FROM `wishlist`") or die('Query failed');

        if (mysqli_num_rows($select_users) > 0) {
            while ($user_cart = mysqli_fetch_assoc($select_users)) {
                $user_id = $user_cart['user_id'];

                // Fetch user information (optional)
                $user_info = mysqli_query($conn, "SELECT * FROM `users` WHERE id = '$user_id'") or die('Query failed');
                $user = mysqli_fetch_assoc($user_info);

                // handle case where user record might be missing
                if (!$user) {
                    $user = ['name' => 'Unknown', 'id' => $user_id];
                }

                echo "<h2>User: " . htmlspecialchars($user['name']) . " (ID: " . htmlspecialchars($user['id']) . ")</h2>";

                $grand_total = 0;
                $select_wishlist = mysqli_query($conn, "SELECT * FROM `wishlist` WHERE user_id = '$user_id'") or die('Query failed');

                if (mysqli_num_rows($select_wishlist) > 0) {
                    while ($fetch_wishlist = mysqli_fetch_assoc($select_wishlist)) {
                        if (!$fetch_wishlist) continue;
                        // each wishlist item HTML
    ?>
    <form action="" method="POST" class="box">
        <a href="admin_wishlist.php?delete=<?php echo htmlspecialchars($fetch_wishlist['id'] ?? ''); ?>" class="fas fa-times" onclick="return confirm('Delete this from wishlist?');"></a>
        <a href="view_page.php?pid=<?php echo htmlspecialchars($fetch_wishlist['pid'] ?? ''); ?>" class="fas fa-eye"></a>
        <img src="uploaded_img/<?php echo htmlspecialchars($fetch_wishlist['image'] ?? ''); ?>" alt="" class="image">
        <div class="name"><?php echo htmlspecialchars($fetch_wishlist['name'] ?? ''); ?></div>
        <div class="price">₹<?php echo htmlspecialchars($fetch_wishlist['price'] ?? 0); ?>/-</div>
        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($fetch_wishlist['pid'] ?? ''); ?>">
        <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($fetch_wishlist['name'] ?? ''); ?>">
        <input type="hidden" name="product_price" value="<?php echo htmlspecialchars($fetch_wishlist['price'] ?? 0); ?>">
        <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($fetch_wishlist['image'] ?? ''); ?>">
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
    </form>

    <div class="totle-price">

        <?php
                        if (isset($fetch_wishlist['price'])) {
                            $grand_total += $fetch_wishlist['price'];
                        }
                    }
                } else {
                    echo '<p class="empty">No items in this user\'s wishlist</p>';
                }
        ?>
        <div class="more-btn">
            <a href="admin_wishlist.php?delete_all&user_id=<?php echo htmlspecialchars($user_id); ?>" class="delete-btn <?php echo ($grand_total > 1) ? '' : 'disabled'; ?>" onclick="return confirm('Delete all from this user\'s wishlist?');">Delete All</a>
        </div>
        <div class="wishlist-total">
            <p>Grand Total for User: <span>₹<?php echo htmlspecialchars($grand_total); ?>/-</span></p>
        </div>
    </div>
    <?php
            $grand_total = 0; // Reset grand total for the next user
            }
        } else {
            echo '<p  align="center" class="empty">No users have items in their wishlists</p>';
        }
    ?>

    </div>

</section>

<?php @include 'admin_footer.php'; ?>

<script src="js/admin_script.js"></script>

</body>
</html>
