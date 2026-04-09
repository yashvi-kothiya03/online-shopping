<?php
@include 'session_admin.php';
?>

<aside class="sidebar">
    <div class="sidebar-top">
        <a href="admin_page.php" class="logo">Admin <span>Panel</span></a>
    </div>

    <nav class="sidebar-nav">
        <a href="admin_page.php"><i class="fas fa-home"></i>Dashboard</a>
        <a href="admin_category.php"><i class="fas fa-sitemap"></i>Category</a>
        <a href="admin_orders.php"><i class="fas fa-shopping-cart"></i>Orders</a>
        <a href="admin_return_inquiry.php"><i class="fas fa-undo-alt"></i>Return Inquiry</a>
        <a href="admin_users.php"><i class="fas fa-users"></i>Users</a>
        <a href="admin_sellers.php"><i class="fas fa-store"></i>Sellers</a>
        <a href="admin_contacts.php"><i class="fas fa-envelope"></i>Messages</a>
    </nav>

    <div class="sidebar-footer">
        <div class="account-info">
            <p><strong><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong></p>
            <p><?php echo htmlspecialchars($_SESSION['admin_email']); ?></p>
        </div>
        <a href="logout.php?role=admin" class="delete-btn">Logout</a>
    </div>
</aside>

<script>
   // ensure admin pages have the correct layout styles applied
   document.body.classList.add('admin');
</script>
