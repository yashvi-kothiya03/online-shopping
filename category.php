<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ceramic";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch categories
$categories_sql = "SELECT * FROM category";
$categories_result = $conn->query($categories_sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Categories and Subcategories</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- Custom CSS file -->
   <link rel="stylesheet" href="css/style.css">

   <style>
       body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

.container {
    width: 80%;
    margin: 50px auto;
    background-color: #fff;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

h2 {
    text-align: center;
    margin-bottom: 20px;
    font-size: 24px;
}

.category-list {
    list-style: none;
    padding: 0;
}

.category-list > li {
    margin-bottom: 20px;
    padding: 15px;
    background-color: #f8f8f8;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.category-name {
    font-weight: bold;
    font-size: 18px;
    color: #333;
}

.subcategory-list {
    list-style-type: disc; /* Use a bullet point for subcategories */
    padding-left: 20px;
    margin-top: 10px;
}

.subcategory-list li {
    margin-bottom: 10px;
    font-size: 16px;
    color: #555;
    padding-left: 10px; /* Added padding for better indentation */
}

   </style>

</head>
<body>

<div class="container">
    <h2>Categories and Subcategories</h2>
    <ul class="category-list">
        <?php
        // Display categories and their subcategories
        if ($categories_result->num_rows > 0) {
            while ($category = $categories_result->fetch_assoc()) {
                echo "<li>";
                echo "<div class='category-name'>" . htmlspecialchars($category['category_name']) . "</div>";

                // Fetch subcategories for this category
                $subcategory_sql = "SELECT * FROM subcategories WHERE category_id = " . $category['id'];
                $subcategory_result = $conn->query($subcategory_sql);

                if ($subcategory_result && $subcategory_result->num_rows > 0) {
                    echo "<ul class='subcategory-list'>";
                    while ($subcategory = $subcategory_result->fetch_assoc()) {
                        echo "<li>" . htmlspecialchars($subcategory['subcategory_name']) . "</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<ul class='subcategory-list'><li>No subcategories available</li></ul>";
                }

                echo "</li>";
            }
        } else {
            echo "<li>No categories available</li>";
        }
        ?>
    </ul>
</div>

<script src="js/script.js"></script>

</body>
</html>

<?php
$conn->close();
?>
