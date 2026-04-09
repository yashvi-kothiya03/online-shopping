<?php

@include 'config.php';
@include 'email_functions.php';

@include 'session_client.php';

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:login.php');
}

$error_message = '';
$success_message = '';
$order_id = '';
$order_data = null;

// Check if user is accessing from order placement or accessing verification page
if (isset($_GET['order_id'])) {
    $order_id = mysqli_real_escape_string($conn, $_GET['order_id']);
    
    // Fetch order details
    $order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE id = '$order_id' AND user_id = '$user_id'") or die('query failed');
    
    if (mysqli_num_rows($order_query) > 0) {
        $order_data = mysqli_fetch_assoc($order_query);
    } else {
        $error_message = 'Order not found!';
        $order_id = '';
    }
}

// Handle OTP verification
if (isset($_POST['verify_otp'])) {
    $order_id = mysqli_real_escape_string($conn, trim($_POST['order_id']));
    $otp = mysqli_real_escape_string($conn, trim($_POST['otp']));
    
    if (empty($otp)) {
        $error_message = 'Please enter the OTP.';
    } else {
        // Verify the OTP
        if (verifyOTP($conn, $order_id, $otp)) {
            $success_message = 'Email verified successfully! Your order has been confirmed. You will receive shipping details soon.';
            // Refresh order data
            $order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE id = '$order_id'") or die('query failed');
            if (mysqli_num_rows($order_query) > 0) {
                $order_data = mysqli_fetch_assoc($order_query);
            }
        } else {
            $error_message = 'Invalid OTP or OTP has expired. Please try again or request a new OTP.';
        }
    }
}

// Handle OTP resend
if (isset($_POST['resend_otp'])) {
    $order_id = mysqli_real_escape_string($conn, trim($_POST['order_id']));
    
    $order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE id = '$order_id' AND user_id = '$user_id'") or die('query failed');
    
    if (mysqli_num_rows($order_query) > 0) {
        $order = mysqli_fetch_assoc($order_query);
        
        if (resendOTP($conn, $order_id, $order['email'], $order['name'])) {
            $success_message = 'New OTP has been sent to your email: ' . htmlspecialchars($order['email']);
        } else {
            $error_message = 'Could not resend OTP. Please try again.';
        }
    } else {
        $error_message = 'Order not found!';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Order - Email Verification</title>

    <!-- Font Awesome CDN link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS file link -->
    <link rel="stylesheet" href="css/style.css">

    <style>
        .verify-section {
            min-height: 50vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }

        .verify-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
        }

        .verify-container h3 {
            text-align: center;
            margin-bottom: 10px;
            color: #333;
            font-size: 28px;
        }

        .verify-description {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
            font-size: 14px;
            line-height: 1.6;
        }

        .message-box {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            font-size: 14px;
            display: flex;
            align-items: center;
        }

        .message-box.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message-box.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .message-box i {
            margin-right: 10px;
            font-size: 18px;
        }

        .order-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #4CAF50;
            margin-bottom: 25px;
            border-radius: 3px;
        }

        .order-info p {
            margin: 8px 0;
            font-size: 13px;
        }

        .order-info strong {
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-group input[type="text"],
        .form-group input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="email"]:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
        }

        .otp-input {
            text-align: center;
            font-size: 24px;
            letter-spacing: 10px;
            font-weight: bold;
            padding: 12px !important;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .button-group button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .button-group .verify-btn {
            background-color: #4CAF50;
            color: white;
        }

        .button-group .verify-btn:hover {
            background-color: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .button-group .resend-btn {
            background-color: #008CBA;
            color: white;
        }

        .button-group .resend-btn:hover {
            background-color: #007399;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #4CAF50;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .verified-badge {
            background-color: #d4edda;
            color: #155724;
            padding: 10px 15px;
            border-radius: 4px;
            text-align: center;
            margin-bottom: 20px;
        }

        .verified-badge i {
            margin-right: 8px;
        }

        .steps {
            list-style: none;
            padding: 0;
            margin: 15px 0;
        }

        .steps li {
            padding: 10px 0;
            padding-left: 30px;
            position: relative;
            color: #666;
            font-size: 13px;
        }

        .steps li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #4CAF50;
            font-weight: bold;
        }

        @media (max-width: 600px) {
            .verify-container {
                padding: 20px;
            }

            .button-group {
                flex-direction: column;
            }

            .otp-input {
                font-size: 18px;
                letter-spacing: 5px;
            }
        }
    </style>
</head>
<body>

<?php @include 'header.php'; ?>

<section class="heading">
    <h3>Verify Your Order</h3>
    <p> <a href="home.php">Home</a> / Verify Order </p>
</section>

<section class="verify-section">
    <div class="verify-container">
        <h3><i class="fas fa-shield-alt"></i> Email Verification</h3>
        <p class="verify-description">Verify your email address to confirm your order</p>

        <?php
        // Display error or success messages
        if ($error_message) {
            echo '<div class="message-box error"><i class="fas fa-exclamation-circle"></i>' . $error_message . '</div>';
        } elseif ($success_message) {
            echo '<div class="message-box success"><i class="fas fa-check-circle"></i>' . $success_message . '</div>';
        }
        ?>

        <?php
        // If no order data is fetched, show order not found
        if (!$order_data && !$error_message) {
            echo '<div class="message-box error"><i class="fas fa-exclamation-circle"></i>Please enter a valid Order ID to verify.</div>';
        }

        // If order is verified, show success message
        if ($order_data && $order_data['otp_verified'] == 1) {
            ?>
            <div class="verified-badge">
                <i class="fas fa-check-circle"></i> Email Verified Successfully!
            </div>

            <div class="order-info">
                <p><strong>Order ID:</strong> #<?php echo htmlspecialchars($order_data['id']); ?></p>
                <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($order_data['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order_data['email']); ?></p>
                <p><strong>Total Amount:</strong> ₹<?php echo htmlspecialchars($order_data['total_price']); ?>/-</p>
                <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order_data['method']); ?></p>
                <p><strong>Order Status:</strong> <span style="color: #4CAF50; font-weight: bold;">Confirmed</span></p>
            </div>

            <h4 style="margin-bottom: 10px;">What's Next?</h4>
            <ul class="steps">
                <li>Your order has been confirmed</li>
                <li>You will receive shipping details via email</li>
                <li>Track your order once it ships</li>
                <li>Contact us if you have any questions</li>
            </ul>

            <div class="back-link">
                <a href="orders.php"><i class="fas fa-arrow-left"></i> Back to Orders</a>
            </div>

            <?php
        } elseif ($order_data && $order_data['otp_verified'] == 0) {
            // Show verification form
            ?>
            <div class="order-info">
                <p><strong>Order ID:</strong> #<?php echo htmlspecialchars($order_data['id']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order_data['email']); ?></p>
                <p><strong>Order Amount:</strong> ₹<?php echo htmlspecialchars($order_data['total_price']); ?>/-</p>
            </div>

            <form action="" method="POST" style="margin-top: 20px;">
                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_data['id']); ?>">

                <div class="form-group">
                    <label for="otp">Enter OTP:</label>
                    <input type="text" id="otp" name="otp" class="otp-input" placeholder="000000" maxlength="6" pattern="\d{6}" required autofocus>
                    <p style="text-align: center; font-size: 12px; color: #999; margin-top: 5px;">Check your email for the 6-digit OTP code</p>
                </div>

                <div class="button-group">
                    <button type="submit" name="verify_otp" class="verify-btn">
                        <i class="fas fa-check"></i> Verify OTP
                    </button>
                    <button type="submit" name="resend_otp" class="resend-btn" onclick="return confirm('Are you sure you want to resend OTP?');">
                        <i class="fas fa-redo"></i> Resend OTP
                    </button>
                </div>
            </form>

            <div class="back-link">
                <a href="orders.php"><i class="fas fa-arrow-left"></i> Back to Orders</a>
            </div>

            <?php
        } else {
            // Show order lookup form
            ?>
            <form action="" method="GET" style="margin-top: 20px;">
                <div class="form-group">
                    <label for="order_id">Enter Your Order ID:</label>
                    <input type="text" id="order_id" name="order_id" placeholder="e.g. 12345" required>
                </div>

                <button type="submit" style="width: 100%; padding: 12px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                    <i class="fas fa-search"></i> Search Order
                </button>
            </form>

            <div class="back-link">
                <a href="orders.php"><i class="fas fa-arrow-left"></i> Back to Orders</a>
            </div>

            <?php
        }
        ?>
    </div>
</section>

<?php @include 'footer.php'; ?>

</body>
</html>
