<?php

@include 'config.php';
@include 'email_functions.php';

@include 'session_client.php';

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:login.php');
    exit;
}

$error_message = '';
$success_message = '';
$order_id = '';
$order_data = null;

// Ensure return columns exist
if (function_exists('ensureReturnOtpColumns')) {
    ensureReturnOtpColumns($conn);
}

// Get order data if provided
if (isset($_GET['order_id'])) {
    $order_id = mysqli_real_escape_string($conn, $_GET['order_id']);
    $order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE id = '$order_id' AND user_id = '$user_id'") or die('query failed');
    if (mysqli_num_rows($order_query) > 0) {
        $order_data = mysqli_fetch_assoc($order_query);
    } else {
        $error_message = 'Order not found.';
        $order_id = '';
    }
}

// Handle OTP verification
if (isset($_POST['verify_return_otp'])) {
    $order_id = mysqli_real_escape_string($conn, trim($_POST['order_id']));
    $otp = mysqli_real_escape_string($conn, trim($_POST['otp']));

    if (empty($otp)) {
        $error_message = 'Please enter the OTP.';
    } else {
        $query = mysqli_query($conn, "SELECT * FROM `orders` WHERE id = '$order_id' AND user_id = '$user_id' AND return_otp = '$otp' AND return_otp_verified = 0") or die('query failed');
        if (mysqli_num_rows($query) > 0) {
            $order = mysqli_fetch_assoc($query);
            $otp_time = strtotime($order['return_otp_created_at']);
            $current_time = time();
            $time_diff = ($current_time - $otp_time) / 60; // minutes

            if ($time_diff <= 10) {
                mysqli_query($conn, "UPDATE `orders` SET return_otp_verified = 1, payment_status = 'returned', return_processed = 0 WHERE id = '$order_id'") or die('query failed');
                $success_message = 'Return confirmed! Your order status has been updated.';

                // Refresh order data
                $order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE id = '$order_id'") or die('query failed');
                if (mysqli_num_rows($order_query) > 0) {
                    $order_data = mysqli_fetch_assoc($order_query);
                }
            } else {
                $error_message = 'OTP expired. Please request a new return OTP.';
            }
        } else {
            $error_message = 'Invalid OTP. Please check your email and try again.';
        }
    }
}

// Handle OTP resend
if (isset($_POST['resend_return_otp'])) {
    $order_id = mysqli_real_escape_string($conn, trim($_POST['order_id']));

    $order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE id = '$order_id' AND user_id = '$user_id'") or die('query failed');

    if (mysqli_num_rows($order_query) > 0) {
        $order = mysqli_fetch_assoc($order_query);

        $otp = generateOTP();
        mysqli_query($conn, "UPDATE `orders` SET return_otp = '$otp', return_otp_created_at = NOW(), return_otp_verified = 0, return_requested = 1 WHERE id = '$order_id'") or die('query failed');

        if (sendReturnOTPEmail($order['email'], $order['name'], $order_id, $otp)) {
            $success_message = 'New return OTP has been sent to your email: ' . htmlspecialchars($order['email']);
        } else {
            $error_message = 'Could not resend return OTP. Please try again.';
        }
    } else {
        $error_message = 'Order not found.';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Return OTP</title>

    <!-- Font Awesome CDN link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS file link -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php @include 'header.php'; ?>

<section class="heading">
    <h3>Return Verification</h3>
    <p> <a href="home.php">Home</a> / Return Verification </p>
</section>

<section class="verify-section">
    <div class="verify-container">
        <h3>Enter Return OTP</h3>
        <p class="verify-description">We have sent a 6-digit OTP to your email address. Enter it below to confirm your return request.</p>

        <?php if ($error_message) { ?>
            <div class="message-box error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?></div>
        <?php } ?>

        <?php if ($success_message) { ?>
            <div class="message-box success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?></div>
        <?php } ?>

        <?php if ($order_id) { ?>
            <form action="" method="POST">
                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">

                <div class="form-group">
                    <label for="otp">OTP Code</label>
                    <input type="text" name="otp" id="otp" class="otp-input" maxlength="6" autocomplete="off" required>
                </div>

                <div class="button-group">
                    <button type="submit" name="verify_return_otp" class="verify-btn">Verify OTP</button>
                    <button type="submit" name="resend_return_otp" class="resend-btn">Resend OTP</button>
                </div>
            </form>

            <div class="back-link">
                <a href="orders.php">← Back to Orders</a>
            </div>
        <?php } ?>

        <?php if ($order_data) { ?>
            <div class="order-info" style="margin-top:20px;">
                <p><strong>Order ID:</strong> #<?php echo htmlspecialchars($order_data['id']); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($order_data['payment_status']); ?></p>
            </div>
        <?php } ?>
    </div>
</section>

<?php @include 'footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>
