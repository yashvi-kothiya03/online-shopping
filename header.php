<?php
include 'config.php'; // Database connection
@include 'session_client.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure stock column exists in products table
$check_stock_column = mysqli_query($conn, "SHOW COLUMNS FROM `products` LIKE 'stock'") or die('query failed');
if(mysqli_num_rows($check_stock_column) == 0) {
    mysqli_query($conn, "ALTER TABLE `products` ADD COLUMN stock INT NOT NULL DEFAULT 0") or die('query failed');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="css/style.css">
   
    <style>
        /* Dropdown container */
        .navbar > ul > li {
            position: relative;
        }

        /* Main dropdown menu */
        .dropdown {
            display: none;
            position: absolute;
            top: 100%; /* Dropdown will show below the main menu item */
            left: 0;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 10px 0;
            z-index: 1000;
            min-width: 200px; /* Ensure dropdown has a minimum width */
        }

        /* Show dropdown on hover */
        .navbar > ul > li:hover .dropdown {
            display: block;
        }

        /* Style for dropdown list items */
        .dropdown > li {
            padding: 8px 16px;
            white-space: nowrap; /* Prevent text from wrapping */
        }

        .dropdown > li > a {
            display: block;
            color: #333;
            text-decoration: none;
            padding: 8px 16px;
        }

        /* Hover effect for dropdown links */
        .dropdown > li > a:hover {
            background-color: #f5f5f5;
        }
/* Submenu container - positioned to the left of the dropdown */
.submenu {
    display: none;
    list-style: none;
    position: absolute;
    top: 0;
    left: 100%; /* Change this to position the submenu to the left */
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    padding: 10px 0;
    min-width: 180px; /* Ensure submenu has a minimum width */
}

/* Show submenu on hover */
.dropdown > li:hover .submenu {
    display: block;
}

/* Style for submenu list items */
.submenu > li {
    padding: 8px 16px;
    white-space: nowrap; /* Prevent text from wrapping */
}

.submenu > li > a {
    display: block;
    color: #333;
    text-decoration: none;
    padding: 8px 16px;
}

/* Hover effect for submenu links */
.submenu > li > a:hover {
    background-color: #eee;
}

/* Ensure dropdown and submenu are responsive */
@media (max-width: 768px) {
    .dropdown,
    .submenu {
        position: relative; /* Stack dropdowns vertically */
        width: 100%;
        box-shadow: none; /* Remove box shadow on small screens */
    }
}
    </style>
</head>
<body>

<header class="header">
    <div class="flex">
        <a href="home.php" class="logo">CERAMIC <span>HUB</span>    </a>

        <nav class="navbar">
            <ul>
                <li><a href="home.php">home</a></li>
                <li><a href="about.php">about</a></li>
                <li><a href="shop.php">shop</a></li>
                <li>
                    <a href="#">Category</a>
                    <ul class="dropdown">
                        <?php
                        // Fetch categories from the database
                        $categories_query = mysqli_query($conn, "SELECT * FROM `category`") or die('query failed: ' . mysqli_error($conn));
                        if (mysqli_num_rows($categories_query) > 0) {
                            while ($category = mysqli_fetch_assoc($categories_query)) {
                                $category_name = !empty($category['c_name']) ? $category['c_name'] : 'Unnamed Category';
                                echo '<li><a href="#">' . htmlspecialchars($category_name) . '</a>';
                                
                                $category_id = $category['id'];

                                // Fetch subcategories related to this category
                                $subcategories_query = mysqli_query($conn, "SELECT * FROM `subcategory` WHERE category_id = '$category_id'") or die('query failed: ' . mysqli_error($conn));
                                
                                if (mysqli_num_rows($subcategories_query) > 0) {
                                    // Show subcategories if any
                                    echo '<ol class="submenu">';
                                    while ($subcategory = mysqli_fetch_assoc($subcategories_query)) {
                                        $subcategory_name = !empty($subcategory['name']) ? $subcategory['name'] : 'Unnamed Subcategory';
                                        echo '<li><a href="shop.php?subcategory=' . htmlspecialchars($subcategory['id']) . '">' . htmlspecialchars($subcategory_name) . '</a></li>';
                                    }
                                    echo '</ol>';
                                }
                                echo '</li>';
                            }
                        } else {
                            echo '<li>No categories available</li>';
                        }
                        ?>
                    </ul>
                </li>
                        
                <li><a href="orders.php">orders</a></li>
                <li><a href="contact.php">contact</a></li>
                <li>
                    <a href="#">account +</a>
                    <ul>
                        <li><a href="login.php">login</a></li>
                        <li><a href="register.php">register</a></li>
                    </ul>
                </li>
            </ul>
        </nav>

        <div class="icons">
            <div id="menu-btn" class="fas fa-bars"></div>
            <a href="search_page.php" class="fas fa-search"></a>
            <div id="user-btn" class="fas fa-user"></div>
            <?php
                if (isset($_SESSION['user_id'])) {
                    $user_id = $_SESSION['user_id']; // Assume user is logged in and user_id is stored in session
                    $select_wishlist_count = mysqli_query($conn, "SELECT * FROM `wishlist` WHERE user_id = '$user_id'") or die('query failed: ' . mysqli_error($conn));
                    $wishlist_num_rows = mysqli_num_rows($select_wishlist_count);
                    $select_cart_count = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed: ' . mysqli_error($conn));
                    $cart_num_rows = mysqli_num_rows($select_cart_count);
                } else {
                    $wishlist_num_rows = 0;
                    $cart_num_rows = 0;
                }
            ?>
            <a href="wishlist.php"><i class="fas fa-heart"></i><span>(<?php echo $wishlist_num_rows; ?>)</span></a>
            <a href="cart.php"><i class="fas fa-shopping-cart"></i><span>(<?php echo $cart_num_rows; ?>)</span></a>
        </div>

        <div class="account-box">
            <p>username : <span><?php echo htmlspecialchars(isset($_SESSION['user_name']) ? $_SESSION['user_name'] : ''); ?></span></p>
            <p>email : <span><?php echo htmlspecialchars(isset($_SESSION['user_email']) ? $_SESSION['user_email'] : ''); ?></span></p>
            <a href="logout.php?role=client" class="delete-btn">logout</a>
        </div>
    </div>
</header>



</body>
</html>
