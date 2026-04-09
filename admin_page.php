<?php

@include 'config.php';
@include 'email_functions.php';

@include 'session_admin.php';

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
};

if (!($conn instanceof mysqli)) {
   die('database connection is not available');
}

// Ensure return columns exist so dashboard counts work even if DB is not yet updated
if (function_exists('ensureReturnOtpColumns')) {
    ensureReturnOtpColumns($conn);
}

function getCount($conn, $sql) {
   $res = mysqli_query($conn, $sql) or die('query failed');
   return mysqli_num_rows($res);
}

function getSum($conn, $sql, $field = 'total') {
   $res = mysqli_query($conn, $sql) or die('query failed');
   $row = mysqli_fetch_assoc($res);
   return (int)($row[$field] ?? 0);
}

$total_pendings = getSum($conn, "SELECT COALESCE(SUM(total_price), 0) AS total FROM `orders` WHERE payment_status = 'pending'");
$total_revenue = getSum($conn, "SELECT COALESCE(SUM(total_price), 0) AS total FROM `orders` WHERE payment_status IN ('completed','prepaid')");

$number_of_orders = getCount($conn, "SELECT * FROM `orders`");
$number_of_products = getCount($conn, "SELECT * FROM `products`");
$number_of_users = getCount($conn, "SELECT * FROM `users` WHERE LOWER(TRIM(user_type)) = 'user'");
$number_of_sellers = getCount($conn, "SELECT * FROM `users` WHERE LOWER(TRIM(user_type)) = 'seller'");
$number_of_admin = getCount($conn, "SELECT * FROM `users` WHERE LOWER(TRIM(user_type)) = 'admin'");
$number_of_account = getCount($conn, "SELECT * FROM `users`");
$number_of_returns = getCount($conn, "SELECT * FROM `orders` WHERE return_requested = 1");
$number_of_messages = getCount($conn, "SELECT * FROM `message`");

$recent_orders = mysqli_query($conn, "SELECT id, name, total_price, method, payment_status, placed_on FROM `orders` ORDER BY id DESC LIMIT 6") or die('query failed');

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Dashboard</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">
   <style>
      /* enforce dashboard cards layout (fix any missing css overrides) */
      .dashboard .box-container {
         display: grid !important;
         grid-template-columns: repeat(auto-fit, minmax(27rem, 1fr)) !important;
         gap: 1.5rem !important;
         align-items: flex-start !important;
         width: 100% !important;
         padding: 0 2rem !important;
         margin: 0 !important;
      }
      .dashboard .box-container .box {
         text-align: center !important;
         border: 1px solid rgba(0,0,0,0.2) !important;
         background-color: rgba(0,0,0,0.85) !important;
         box-shadow: 0 15px 30px rgba(0,0,0,0.25) !important;
         padding: 2.5rem !important;
         border-radius: 1rem !important;
         transition: transform .2s ease, background-color .2s ease !important;
      }
      .dashboard .box-container .box .icon {
         font-size: 4rem !important;
         color: var(--primary) !important;
         margin-bottom: 1.2rem !important;
      }
      .dashboard .box-container .box h3 {
         font-size: 4.5rem !important;
         color: var(--white) !important;
      }
      .dashboard .box-container .box p {
         padding: 1.2rem !important;
         border-radius: .5rem !important;
         background-color: rgba(255,255,255,0.08) !important;
         color: rgba(255,255,255,0.9) !important;
         border: 1px solid rgba(255,255,255,0.12) !important;
         margin-top: 2rem !important;
         font-size: 2rem !important;
         text-transform: capitalize !important;
      }
      .dashboard-quick-actions {
         display: flex;
         flex-wrap: wrap;
         gap: 1rem;
         justify-content: center;
         margin: 0 0 2rem;
      }
      .recent-orders-card {
         margin: 2rem;
         border: 1px solid rgba(0,0,0,.15);
         border-radius: 1rem;
         background: #fff;
         box-shadow: 0 12px 28px rgba(0,0,0,.08);
         overflow: hidden;
      }
      .recent-orders-card .head {
         display: flex;
         align-items: center;
         justify-content: space-between;
         padding: 1.6rem 2rem;
         background: #0f172a;
         color: #fff;
      }
      .recent-orders-card .head h2 {
         font-size: 2rem;
         font-weight: 600;
      }
      .recent-orders-card .table-wrap {
         overflow-x: auto;
      }
      .recent-orders-card table {
         width: 100%;
         border-collapse: collapse;
      }
      .recent-orders-card th,
      .recent-orders-card td {
         padding: 1.2rem 1.4rem;
         border-bottom: 1px solid #eee;
         font-size: 1.45rem;
         text-align: left;
      }
      .recent-orders-card th {
         font-size: 1.35rem;
         text-transform: uppercase;
         letter-spacing: .4px;
         color: #334155;
         background: #f8fafc;
      }
      .status-pill {
         display: inline-block;
         padding: .4rem .9rem;
         border-radius: 999px;
         font-size: 1.2rem;
         font-weight: 600;
         text-transform: capitalize;
      }
      .status-pill.pending { background: #fff7ed; color: #9a3412; }
      .status-pill.completed { background: #ecfdf3; color: #166534; }
      .status-pill.prepaid { background: #eff6ff; color: #1d4ed8; }
   </style>

</head>
<body>
   
<?php @include 'admin_header.php'; ?>

<main class="main admin-main">
   <section class="dashboard">

      <h1 class="title">dashboard</h1>

      <div class="dashboard-quick-actions">
         <a href="admin_orders.php" class="option-btn">Manage Orders</a>
         <a href="admin_products.php" class="option-btn">Manage Products</a>
         <a href="admin_users.php" class="option-btn">Manage Users</a>
         <a href="admin_return_inquiry.php" class="option-btn">Return Requests</a>
      </div>

      <div class="box-container" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(27rem, 1fr)); gap:1.5rem; align-items:flex-start; width:100%; max-width:none; margin:0; padding:0 2rem;">

      <div class="box">
         <div class="icon"><i class="fas fa-wallet"></i></div>
         <h3>₹<?php echo $total_pendings; ?>/-</h3>
         <p>Total pending</p>
      </div>

      <div class="box">
         <div class="icon"><i class="fas fa-check-circle"></i></div>
         <h3>₹<?php echo $total_revenue; ?>/-</h3>
         <p>Paid revenue (completed + prepaid)</p>
      </div>

      <div class="box">
         <div class="icon"><i class="fas fa-shopping-cart"></i></div>
         <h3><?php echo $number_of_orders; ?></h3>
         <p>Orders placed</p>
      </div>

      <div class="box">
         <div class="icon"><i class="fas fa-boxes-stacked"></i></div>
         <h3><?php echo $number_of_products; ?></h3>
         <p>Products added</p>
      </div>

      <div class="box">
         <div class="icon"><i class="fas fa-user-friends"></i></div>
         <h3><?php echo $number_of_users; ?></h3>
         <p>Total users</p>
      </div>

      <!-- new total sellers box -->
      <div class="box">
         <div class="icon"><i class="fas fa-store"></i></div>
         <h3><?php echo $number_of_sellers; ?></h3>
         <p>Total sellers</p>
      </div>

      <div class="box">
         <div class="icon"><i class="fas fa-user-shield"></i></div>
         <h3><?php echo $number_of_admin; ?></h3>
         <p>Admin users</p>
      </div>

      <div class="box">
         <div class="icon"><i class="fas fa-users"></i></div>
         <h3><?php echo $number_of_account; ?></h3>
         <p>Total accounts</p>
      </div>

      <div class="box">
         <div class="icon"><i class="fas fa-undo-alt"></i></div>
         <h3><?php echo $number_of_returns; ?></h3>
         <p>Return orders</p>
      </div>

      <div class="box">
         <div class="icon"><i class="fas fa-envelope"></i></div>
         <h3><?php echo $number_of_messages; ?></h3>
         <p>New messages</p>
      </div>

   </div>

   <div class="recent-orders-card">
      <div class="head">
         <h2>Recent Orders</h2>
         <a href="admin_orders.php" class="option-btn" style="margin-top:0;">View All</a>
      </div>
      <div class="table-wrap">
         <table>
            <thead>
               <tr>
                  <th>Order</th>
                  <th>Customer</th>
                  <th>Method</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Date</th>
               </tr>
            </thead>
            <tbody>
               <?php if(mysqli_num_rows($recent_orders) > 0){ ?>
                  <?php while($row = mysqli_fetch_assoc($recent_orders)){ ?>
                  <tr>
                     <td>#<?php echo (int)$row['id']; ?></td>
                     <td><?php echo htmlspecialchars($row['name']); ?></td>
                     <td><?php echo htmlspecialchars($row['method']); ?></td>
                     <td>₹<?php echo (int)$row['total_price']; ?>/-</td>
                     <td><span class="status-pill <?php echo htmlspecialchars(strtolower($row['payment_status'])); ?>"><?php echo htmlspecialchars($row['payment_status']); ?></span></td>
                     <td><?php echo htmlspecialchars($row['placed_on']); ?></td>
                  </tr>
                  <?php } ?>
               <?php } else { ?>
                  <tr>
                     <td colspan="6" style="text-align:center;">No recent orders found.</td>
                  </tr>
               <?php } ?>
            </tbody>
         </table>
      </div>
   </div>

</section>
</main>

<script src="js/admin_script.js"></script>

</body>
</html>