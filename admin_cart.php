<?php

@include 'config.php';

@include 'session_admin.php';

// Check if the admin is logged in
$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit();
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM `cart` WHERE id = '$delete_id'") or die('query failed');
    header('location:admin_cart.php');
}

if (isset($_GET['delete_all'])) {
    $user_id = $_GET['user_id']; // Get the user ID from the URL
    mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
    header('location:admin_cart.php');
}

if (isset($_POST['update_quantity'])) {
    $cart_id = $_POST['cart_id'];
    $cart_quantity = $_POST['cart_quantity'];
    mysqli_query($conn, "UPDATE `cart` SET quantity = '$cart_quantity' WHERE id = '$cart_id'") or die('query failed');
    $message[] = 'Cart quantity updated!';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Cart Management</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
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
}

/* Admin Header */
header {
    background-color: #333;
    text-align: center;
    color: white;
    font-size: 24px;
}

a {
    text-decoration: none;
    color: inherit;
}

.heading {
    text-align: center;
    padding: 20px;
    background-color: #fff;
    color: #000;
    margin-bottom: 30px;
}

.heading h3 {
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
/* Cart Section */
.shopping-cart {
    width: 100%;
    margin: 0 auto;
    background-color: transparent;
    padding: 20px;
    border-radius: 10px;
}

.title {
    text-align: center;
    margin-bottom: 2rem;
    color: var(--black);
    text-transform: uppercase;
    font-size: 4rem;
    padding: 20px 0;
}

.title-2 {
    text-align: center;
    margin-bottom: 2rem;
    color: var(--black);
    text-transform: uppercase;
    font-size: 3rem;
    padding: 20px 0;
}

.box-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    text-align: end;
}
.box-container h2{
    font-size: 18px !important;
}
.box {
    box-shadow: 5px 5px 20px 2px #0000005c;
    border: var(--border);
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    width: calc(33.333% - 1rem);
    margin-bottom: 35px;
    padding: 1rem;
    position: relative;
    text-align: center;
}

.close{
    position: absolute;
    top: 4%;
    right: 5%;
    color: #ff0000 !important;
    font-size: 24px;
    cursor: pointer;
}
.view{
    position: absolute;
    top: 4% ;
    left: 5% ;
    color: #fff ;
    font-size: 30px;
    cursor: pointer;
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
    font-weight: 400;
}

.box .price {
    font-size: 18px;
    color: #000;
    font-weight: 600;
    padding: 10px 0;
}

.box .qty {
    width: 50px;
    padding: 6px 10px;
    font-size: 20px;
    text-align: center;
    margin-bottom: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

.option-btn {
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

.sub-total {
    font-size: 16px;
    margin-top: 10px;
    color: #000;
}

.sub-total span {
    font-weight: bold;
    color: #000;
    font-size: 20px;
}

.total-container{
    display: block;
    text-align: end;
    align-items: center;
    /* padding: 20px 0; */
}
.total-container h2{
    font-size: 18px;
}
.cart-total-container-flex{
    display: flex;
    justify-content: space-between;
    padding: 40px 0;
}
/* Delete All Button */
.more-btn .delete-btn {
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

.delete-btn.disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

/* Grand Total Section */
.cart-total {
    font-size: 18px;
    text-align: right;
    align-content: center;
    font-weight: 400;
}

.cart-total span {
    font-size: 20px;
    font-weight: 600;
    color: #000;

}

.empty {
    font-size: 18px;
    text-align: center;
    color: #777;
}

    </style>

</head>
<body>
   
<?php @include 'admin_header.php'; ?>

<section class="heading">
<h1 class="title">Manage User Carts</h1>

</section>

<section class="shopping-cart">

    <h2 class="title-2">User Carts</h2>

    <div class="box-container">

    <?php
        $grand_total = 0;
        $select_users = mysqli_query($conn, "SELECT DISTINCT user_id FROM `cart`") or die('query failed');
        
        if (mysqli_num_rows($select_users) > 0) {
            while ($user_cart = mysqli_fetch_assoc($select_users)) {
                $user_id = $user_cart['user_id'];
                
                // Fetch user information (optional, you can also add a JOIN to fetch user details)
                $user_info = mysqli_query($conn, "SELECT * FROM `users` WHERE id = '$user_id'") or die('query failed');
                $user = mysqli_fetch_assoc($user_info);
                
                echo "<h2>User: " . $user['name'] . " (ID: " . $user['id'] . ")</h2>";
                
                $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
                
                if (mysqli_num_rows($select_cart) > 0) {
                    while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
    ?>
    </div>
        <div class="box">
            <a href="admin_cart.php?delete=<?php echo $fetch_cart['id']; ?>" class="fas fa-times close" onclick="return confirm('Delete this item from cart?');"></a>
            <!-- <a href="view_page.php?pid=<?php echo $fetch_cart['pid']; ?>" class="fas fa-eye view"></a> -->
            <img src="uploaded_img/<?php echo $fetch_cart['image']; ?>" alt="" class="image">
            <div class="name"><?php echo $fetch_cart['name']; ?></div>
            <div class="price">₹<?php echo $fetch_cart['price']; ?>/-</div>
            <form action="" method="post">
                <input type="hidden" value="<?php echo $fetch_cart['id']; ?>" name="cart_id">
                <input type="number" min="1" value="<?php echo $fetch_cart['quantity']; ?>" name="cart_quantity" class="qty">
            </form>
            <div class="sub-total"> Sub-total: <span>₹<?php echo $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']); ?>/-</span> </div>
        </div>
    <div class="total-container">
            <?php
            $grand_total += $sub_total;
                            }
                        } else {
                        echo '<p class="empty">This user\'s cart is empty</p>';
                        }
            ?>
            <div class="cart-total-container-flex">
                <div class="more-btn">
                    <a href="admin_cart.php?delete_all&user_id=<?php echo $user_id; ?>" class="delete-btn <?php echo ($grand_total > 1) ? '' : 'disabled'; ?>" onclick="return confirm('Delete all items from this user\'s cart?');">Delete all</a>
                </div>

                <div class="cart-total">
                    <p>Grand Total for User: <span>₹<?php echo $grand_total; ?>/-</span></p>
                </div>
            </div>

            <?php
                        $grand_total = 0; // Reset grand total for the next user
                    }
                } else {
                    echo '<p class="empty">No users have items in their carts</p>';
                }
            ?>
    </div>
</section>

<?php @include 'admin_footer.php'; ?>

<script src="js/admin_script.js"></script>

</body>
</html>
