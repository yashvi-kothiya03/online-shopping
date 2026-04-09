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
   mysqli_query($conn, "DELETE FROM `users` WHERE id = '$delete_id' AND LOWER(TRIM(user_type)) = 'user'") or die('query failed');
   header('location:admin_users.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Users Management</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php @include 'admin_header.php'; ?>

<main class="main admin-main">

<section class="users">

   <h1 class="title">Users</h1>

   <?php
      $select_users = mysqli_query($conn, "SELECT * FROM `users` WHERE LOWER(TRIM(user_type)) = 'user' ORDER BY id") or die('query failed');
      $user_count = mysqli_num_rows($select_users);
   ?>

   <div style="max-width: 1100px; margin: 30px auto;">
      <div style="background: var(--white); border: var(--border); box-shadow: var(--box-shadow); border-radius: 10px; overflow: hidden;">
         <div style="padding: 20px 25px; border-bottom: 1px solid rgba(0,0,0,0.08); display:flex; justify-content:space-between; align-items:center;">
            <h2 style="margin:0; font-size: 24px;">User List (<?php echo $user_count; ?>)</h2>
         </div>
         <div style="padding: 20px;">
            <?php if ($user_count > 0): ?>
               <table style="width:100%; border-collapse: collapse; font-size: 15px;">
                  <thead>
                     <tr>
                        <th style="text-align:left; padding: 14px 12px; border-bottom: 1px solid #eee; font-weight: 700;">ID</th>
                        <th style="text-align:left; padding: 14px 12px; border-bottom: 1px solid #eee; font-weight: 700;">Username</th>
                        <th style="text-align:left; padding: 14px 12px; border-bottom: 1px solid #eee; font-weight: 700;">Email</th>
                        <th style="text-align:left; padding: 14px 12px; border-bottom: 1px solid #eee; font-weight: 700;">Type</th>
                        <th style="text-align:left; padding: 14px 12px; border-bottom: 1px solid #eee; font-weight: 700;">Action</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php while($fetch_users = mysqli_fetch_assoc($select_users)): ?>
                        <tr>
                           <td style="padding: 14px 12px; border-bottom: 1px solid #f0f0f0; vertical-align: top;"><?php echo $fetch_users['id']; ?></td>
                           <td style="padding: 14px 12px; border-bottom: 1px solid #f0f0f0; vertical-align: top;"><?php echo htmlspecialchars($fetch_users['name']); ?></td>
                           <td style="padding: 14px 12px; border-bottom: 1px solid #f0f0f0; vertical-align: top;"><?php echo htmlspecialchars($fetch_users['email']); ?></td>
                           <td style="padding: 14px 12px; border-bottom: 1px solid #f0f0f0; vertical-align: top;"><?php echo $fetch_users['user_type']; ?></td>
                           <td style="padding: 14px 12px; border-bottom: 1px solid #f0f0f0; vertical-align: top;">
                              <a href="admin_users.php?delete=<?php echo $fetch_users['id']; ?>" onclick="return confirm('delete this user?');" class="delete-btn" style="padding: 8px 14px;">delete</a>
                           </td>
                        </tr>
                     <?php endwhile; ?>
                  </tbody>
               </table>
            <?php else: ?>
               <p class="empty">No users found.</p>
            <?php endif; ?>
         </div>
      </div>
   </div>

</section>

</main>

<script src="js/admin_script.js"></script>

</body>
</html>