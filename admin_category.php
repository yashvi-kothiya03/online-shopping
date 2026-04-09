<?php
include 'config.php'; // Database connection

$message = [];

// Handle category and subcategory actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_category'])) {
        // Add Category (validate duplicates)
        $c_name = mysqli_real_escape_string($conn, trim($_POST['c_name']));
        // Check if category already exists (case-insensitive)
        $check = mysqli_query($conn, "SELECT 1 FROM category WHERE LOWER(c_name) = LOWER('$c_name') LIMIT 1") or die('query failed');
        if (mysqli_num_rows($check) > 0) {
            $message[] = "This category already exists.";
        } else {
            $query = "INSERT INTO category (c_name) VALUES ('$c_name')";
            if (mysqli_query($conn, $query)) {
                $message[] = "Category added successfully.";
            } else {
                $message[] = "Error adding category: " . mysqli_error($conn);
            }
        }
    }

    if (isset($_POST['edit_category'])) {
        // Edit Category
        $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
        $c_name = mysqli_real_escape_string($conn, $_POST['c_name']);
        $query = "UPDATE category SET c_name = '$c_name' WHERE id = '$category_id'";
        if (mysqli_query($conn, $query)) {
            $message[] = "Category updated successfully.";
        } else {
            $message[] = "Error updating category: " . mysqli_error($conn);
        }
    }

    if (isset($_POST['delete_category'])) {
        // Delete Category
        $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
        $query = "DELETE FROM category WHERE id = '$category_id'";
        if (mysqli_query($conn, $query)) {
            $message[] = "Category deleted successfully.";
        } else {
            $message[] = "Error deleting category: " . mysqli_error($conn);
        }
    }

    if (isset($_POST['add_subcategory'])) {
        // Add Subcategory (validate duplicates)
        $subcategory_name = mysqli_real_escape_string($conn, trim($_POST['subcategory_name']));
        $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
        // Check if subcategory already exists for this category (case-insensitive)
        $check_sub = mysqli_query($conn, "SELECT 1 FROM subcategory WHERE category_id = '$category_id' AND LOWER(name) = LOWER('$subcategory_name') LIMIT 1") or die('query failed');
        if (mysqli_num_rows($check_sub) > 0) {
            $message[] = "This subcategory already exists for the selected category.";
        } else {
            $query = "INSERT INTO subcategory (name, category_id) VALUES ('$subcategory_name', '$category_id')";
            if (mysqli_query($conn, $query)) {
                $message[] = "Subcategory added successfully.";
            } else {
                $message[] = "Error adding subcategory: " . mysqli_error($conn);
            }
        }
    }

    if (isset($_POST['edit_subcategory'])) {
        // Edit Subcategory
        $subcategory_id = mysqli_real_escape_string($conn, $_POST['subcategory_id']);
        $subcategory_name = mysqli_real_escape_string($conn, $_POST['subcategory_name']);
        $query = "UPDATE subcategory SET name = '$subcategory_name' WHERE id = '$subcategory_id'";
        if (mysqli_query($conn, $query)) {
            $message[] = "Subcategory updated successfully.";
        } else {
            $message[] = "Error updating subcategory: " . mysqli_error($conn);
        }
    }

    if (isset($_POST['delete_subcategory'])) {
        // Delete Subcategory
        $subcategory_id = mysqli_real_escape_string($conn, $_POST['subcategory_id']);
        $query = "DELETE FROM subcategory WHERE id = '$subcategory_id'";
        if (mysqli_query($conn, $query)) {
            $message[] = "Subcategory deleted successfully.";
        } else {
            $message[] = "Error deleting subcategory: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Categories Management </title>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">
    <style>
        :root {
            --primary: #1a73e8;
            --danger: #c0392b;
            --black: black;
            --white: #fff;
            --light-gray: #f5f5f5;
            --border: .2rem solid var(--black);
            --box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .1);
        }

        * {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--light-gray);
        
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        h1, h2 {
            color: var(--black);
            text-transform: uppercase;
            margin-bottom: 20px;
            font-size: 34px;
            font-weight: 600;
            padding: 20px 0px;
        }

        .title {
    text-align: center;
    margin-bottom: 2rem;
    color: var(--black);
    text-transform: uppercase;
    font-size: 4rem;
    padding: 20px 0;
}

        form {
            background-color: var(--white);
            padding: 20px;
            border: var(--border);
            border-radius: 10px;
            box-shadow: var(--box-shadow);
            max-width: 600px;
            margin: auto;
            margin-bottom: 40px !important;
            box-shadow: 5px 5px 20px 2px #0000005c;
        }

        form h2 {
            margin-bottom: 10px;
            color: #000;
            font-size: 20px;
            font-weight: 600;

        }

        input[type="text"], select {
            width: 100%;
            padding: 16px 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            color: #000;
        
        }

        button[type="submit"] {
            margin-top: 10px;
            padding: 12px 15px;
            background-color: var(--black);
            color: var(--white);
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 400;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: var(--black);
        }

        /* Message styling */
        .message {
            background-color: var(--light-gray);
            color: var(--black);
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .message i {
            cursor: pointer;
            font-size: 1.2rem;
            color: var(--red);
        }

        .message i:hover {
            transform: rotate(90deg);
        }
    </style>
</head>
<body>
<?php include 'admin_header.php';?>

<main class="main admin-main">
<div class="container">

    <?php
    if (!empty($message)) {
        foreach ($message as $msg) {
            echo "<div class='message'><span>$msg</span><i class='fas fa-times'></i></div>";
        }
    }
    ?>

    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px;">
        <h1 class="title" style="margin:0;">Manage Categories</h1>
    </div>

    <?php
    // Display category stats + listing
    $category_count_result = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM category");
    $category_count = mysqli_fetch_assoc($category_count_result)['cnt'];
    // Order by ID to keep sequence stable
    $category_list = mysqli_query($conn, "SELECT c.id, c.c_name, (SELECT COUNT(*) FROM subcategory s WHERE s.category_id = c.id) AS subcount FROM category c ORDER BY c.id");
    ?>

    <div id="category-list" style="max-width: 1100px; margin: 30px auto; padding: 0;">
        <div style="background: var(--white); border: var(--border); box-shadow: var(--box-shadow); border-radius: 10px; overflow: hidden;">
            <div style="padding: 20px 25px; display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid rgba(0,0,0,0.08);">
                <h2 style="margin:0; font-size: 24px;">Category List (<?php echo $category_count; ?>)</h2>
                <button id="show-add-form-top" type="button" style="padding: 10px 14px; background: #28a745; border: none; border-radius: 6px; color: #fff; cursor: pointer; font-weight: 600;">Add New Category</button>
            </div>
            <div style="padding: 20px;">
                <?php if ($category_count > 0): ?>
                    <table style="width:100%; border-collapse: collapse; font-size: 15px;">
                        <thead>
                            <tr>
                                <th style="text-align:left; padding: 14px 12px; border-bottom: 1px solid #eee; font-weight: 700; font-size: 16px;">ID</th>
                                <th style="text-align:left; padding: 14px 12px; border-bottom: 1px solid #eee; font-weight: 700; font-size: 16px;">Name</th>
                                <th style="text-align:left; padding: 14px 12px; border-bottom: 1px solid #eee; font-weight: 700; font-size: 16px;">Subcategories</th>
                                <th style="text-align:left; padding: 14px 12px; border-bottom: 1px solid #eee; font-weight: 700; font-size: 16px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($cat = mysqli_fetch_assoc($category_list)): ?>
                                <tr>
                                    <td style="padding: 14px 12px; border-bottom: 1px solid #f0f0f0;"><?php echo $cat['id']; ?></td>
                                    <td style="padding: 14px 12px; border-bottom: 1px solid #f0f0f0;"><?php echo htmlspecialchars($cat['c_name']); ?></td>
                                    <td style="padding: 14px 12px; border-bottom: 1px solid #f0f0f0;"><?php echo $cat['subcount']; ?></td>
                                    <td style="padding: 14px 12px; border-bottom: 1px solid #f0f0f0;">
                                        <button type="button" class="edit-btn" data-id="<?php echo $cat['id']; ?>" data-name="<?php echo htmlspecialchars($cat['c_name'], ENT_QUOTES); ?>" style="margin-right: 8px; padding: 8px 14px; border: 1px solid #1d7af3; background: #1d7af3; color: #fff; cursor: pointer; border-radius: 6px;">Edit</button>
                                        <form method="post" style="display:inline;" onsubmit="return confirm('Delete this category?');">
                                            <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                            <button type="submit" name="delete_category" style="padding: 8px 14px; border: none; background: #c0392b; color: #fff; cursor: pointer; border-radius: 6px; outline: none; box-shadow: none;">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="margin: 0; font-size: 16px; color: rgba(0,0,0,0.7);">No categories found yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add / Edit category forms (hidden by default) -->
    <div id="category-forms" style="max-width: 600px; margin: 30px auto; display: none;">

        <div id="add-form" style="display: none;">
            <h2>Add Category</h2>
            <form action="" method="post">
                <input type="text" name="c_name" placeholder="Category Name" required>
                <button type="submit" name="add_category">Add Category</button>
                <button type="button" id="cancel-add" style="margin-left: 10px; background: #6c757d;">Cancel</button>
            </form>
        </div>

        <div id="edit-form" style="display: none;">
            <h2>Edit Category</h2>
            <form id="edit-category-form" action="" method="post">
                <input type="hidden" name="category_id" id="edit-category-id" value="">
                <input type="text" name="c_name" id="edit-category-name" placeholder="New Category Name" required>
                <button type="submit" name="edit_category">Save Changes</button>
                <button type="button" id="cancel-edit" style="margin-left: 10px; background: #6c757d;">Cancel</button>
            </form>
        </div>

    </div>

    <script>
        const categoryList = document.getElementById('category-list');
        const addForm = document.getElementById('add-form');
        const editForm = document.getElementById('edit-form');

        function showAddForm() {
            categoryList.style.display = 'none';
            document.getElementById('category-forms').style.display = 'block';
            addForm.style.display = 'block';
            editForm.style.display = 'none';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function showEditForm(id, name) {
            categoryList.style.display = 'none';
            document.getElementById('category-forms').style.display = 'block';
            addForm.style.display = 'none';
            editForm.style.display = 'block';

            document.getElementById('edit-category-id').value = id;
            document.getElementById('edit-category-name').value = name;

            document.getElementById('edit-category-name').focus();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        document.getElementById('cancel-edit').addEventListener('click', () => {
            showList();
        });

        document.getElementById('cancel-add')?.addEventListener('click', () => {
            showList();
        });

        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const name = btn.getAttribute('data-name');
                showEditForm(id, name);
            });
        });

        document.getElementById('show-add-form')?.addEventListener('click', () => {
            showAddForm();
        });

        document.getElementById('show-add-form-top')?.addEventListener('click', () => {
            showAddForm();
        });

        function showList() {
            categoryList.style.display = 'block';
            document.getElementById('category-forms').style.display = 'none';
            addForm.style.display = 'none';
            editForm.style.display = 'none';
        }

        // show list by default when the page loads
        showList();
    </script>

    <br>

    <?php
    // Subcategory list + actions
    $subcategory_count_result = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM subcategory");
    $subcategory_count = mysqli_fetch_assoc($subcategory_count_result)['cnt'];
    // Order by ID to keep sequence stable
    $subcategory_list = mysqli_query($conn, "SELECT s.id, s.name, c.c_name AS category_name FROM subcategory s LEFT JOIN category c ON c.id = s.category_id ORDER BY s.id");
    ?>

    <div id="subcategory-section" style="max-width: 1100px; margin: 50px auto 0; padding: 0;">
        <div style="background: var(--white); border: var(--border); box-shadow: var(--box-shadow); border-radius: 10px; overflow: hidden;">
            <div style="padding: 20px 25px; display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid rgba(0,0,0,0.08);">
                <h2 style="margin:0; font-size: 24px;">Subcategory List (<?php echo $subcategory_count; ?>)</h2>
                <button id="show-add-subcategory" type="button" style="padding: 10px 14px; background: #28a745; border: none; border-radius: 6px; color: #fff; cursor: pointer; font-weight: 600;">Add New Subcategory</button>
            </div>
            <div style="padding: 20px;">
                <?php if ($subcategory_count > 0): ?>
                    <table style="width:100%; border-collapse: collapse; font-size: 15px;">
                        <thead>
                            <tr>
                                <th style="text-align:left; padding: 14px 12px; border-bottom: 1px solid #eee; font-weight: 700;">ID</th>
                                <th style="text-align:left; padding: 14px 12px; border-bottom: 1px solid #eee; font-weight: 700;">Subcategory</th>
                                <th style="text-align:left; padding: 14px 12px; border-bottom: 1px solid #eee; font-weight: 700;">Category</th>
                                <th style="text-align:left; padding: 14px 12px; border-bottom: 1px solid #eee; font-weight: 700;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($sub = mysqli_fetch_assoc($subcategory_list)): ?>
                                <tr>
                                    <td style="padding: 14px 12px; border-bottom: 1px solid #f0f0f0;"><?php echo $sub['id']; ?></td>
                                    <td style="padding: 14px 12px; border-bottom: 1px solid #f0f0f0;"><?php echo htmlspecialchars($sub['name']); ?></td>
                                    <td style="padding: 14px 12px; border-bottom: 1px solid #f0f0f0;"><?php echo htmlspecialchars($sub['category_name']); ?></td>
                                    <td style="padding: 14px 12px; border-bottom: 1px solid #f0f0f0;">
                                        <button type="button" class="edit-sub-btn" data-id="<?php echo $sub['id']; ?>" data-name="<?php echo htmlspecialchars($sub['name'], ENT_QUOTES); ?>" data-category="<?php echo htmlspecialchars($sub['category_name'], ENT_QUOTES); ?>" style="margin-right: 8px; padding: 8px 14px; border: 1px solid #1d7af3; background: #1d7af3; color: #fff; cursor: pointer; border-radius: 6px;">Edit</button>
                                        <form method="post" style="display:inline;" onsubmit="return confirm('Delete this subcategory?');">
                                            <input type="hidden" name="subcategory_id" value="<?php echo $sub['id']; ?>">
                                            <button type="submit" name="delete_subcategory" style="padding: 8px 14px; border: 1px solid #c0392b; background: #c0392b; color: #fff; cursor: pointer; border-radius: 6px;">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="margin: 0; font-size: 16px; color: rgba(0,0,0,0.7);">No subcategories found yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Subcategory forms (hidden by default) -->
    <div id="subcategory-forms" style="max-width: 600px; margin: 30px auto; display: none;">

        <div id="add-sub-form" style="display: none;">
            <h2>Add Subcategory</h2>
            <form action="" method="post">
                <input type="text" name="subcategory_name" placeholder="Subcategory Name" required>
                <select name="category_id" required>
                    <?php
                    $categories = mysqli_query($conn, "SELECT * FROM category");
                    while ($category = mysqli_fetch_assoc($categories)) {
                        echo '<option value="'.$category['id'].'">'.$category['c_name'].'</option>';
                    }
                    ?>
                </select>
                <button type="submit" name="add_subcategory">Add Subcategory</button>
                <button type="button" id="cancel-add-sub" style="margin-left: 10px; background: #6c757d;">Cancel</button>
            </form>
        </div>

        <div id="edit-sub-form" style="display: none;">
            <h2>Edit Subcategory</h2>
            <form id="edit-subcategory-form" action="" method="post">
                <input type="hidden" name="subcategory_id" id="edit-subcategory-id" value="">
                <input type="text" name="subcategory_name" id="edit-subcategory-name" placeholder="New Subcategory Name" required>
                <select name="category_id" id="edit-subcategory-category" required>
                    <?php
                    $categories = mysqli_query($conn, "SELECT * FROM category");
                    while ($category = mysqli_fetch_assoc($categories)) {
                        echo '<option value="'.$category['id'].'">'.$category['c_name'].'</option>';
                    }
                    ?>
                </select>
                <button type="submit" name="edit_subcategory">Save Changes</button>
                <button type="button" id="cancel-edit-sub" style="margin-left: 10px; background: #6c757d;">Cancel</button>
            </form>
        </div>

    </div>

    <script>
        const subcategoryList = document.getElementById('subcategory-section');
        const subForms = document.getElementById('subcategory-forms');
        const addSubForm = document.getElementById('add-sub-form');
        const editSubForm = document.getElementById('edit-sub-form');

        function showSubList() {
            subcategoryList.style.display = 'block';
            subForms.style.display = 'none';
            addSubForm.style.display = 'none';
            editSubForm.style.display = 'none';
        }

        function showAddSubForm() {
            subcategoryList.style.display = 'none';
            subForms.style.display = 'block';
            addSubForm.style.display = 'block';
            editSubForm.style.display = 'none';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function showEditSubForm(id, name, category) {
            subcategoryList.style.display = 'none';
            subForms.style.display = 'block';
            addSubForm.style.display = 'none';
            editSubForm.style.display = 'block';

            document.getElementById('edit-subcategory-id').value = id;
            document.getElementById('edit-subcategory-name').value = name;
            document.getElementById('edit-subcategory-category').value = category;

            document.getElementById('edit-subcategory-name').focus();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        document.getElementById('show-add-subcategory').addEventListener('click', () => {
            showAddSubForm();
        });

        document.getElementById('cancel-add-sub').addEventListener('click', () => {
            showSubList();
        });

        document.getElementById('cancel-edit-sub').addEventListener('click', () => {
            showSubList();
        });

        document.querySelectorAll('.edit-sub-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const name = btn.getAttribute('data-name');
                const category = btn.getAttribute('data-category');
                showEditSubForm(id, name, category);
            });
        });

        // show subcategory list by default
        showSubList();
    </script>

</div>

</main>

</body>
</html>
