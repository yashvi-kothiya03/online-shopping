<?php

@include 'config.php';
@include 'email_functions.php';

@include 'session_client.php';

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:login.php');
    exit;
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Ensure return columns exist (safe to call repeatedly)
if (function_exists('ensureReturnOtpColumns')) {
    ensureReturnOtpColumns($conn);
}

$message = '';
$error = '';
$orders = [];

if ($order_id) {
    $order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE id = '$order_id' AND user_id = '$user_id'") or die('query failed');
    if (mysqli_num_rows($order_query) > 0) {
        $order = mysqli_fetch_assoc($order_query);

        // Only allow return requests on completed orders
        if (strtolower($order['payment_status']) === 'completed') {
            $already_requested = isset($order['return_requested']) && $order['return_requested'];

            // Handle form submission (return reason + details + OTP email)
            if (isset($_POST['request_return'])) {
                $reason = isset($_POST['return_reason']) ? mysqli_real_escape_string($conn, trim($_POST['return_reason'])) : '';
                $details = isset($_POST['return_details']) ? mysqli_real_escape_string($conn, trim($_POST['return_details'])) : '';

                if (empty($reason)) {
                    $error = 'Please provide a reason for the return.';
                } else {
                    $otp = generateOTP();

                    mysqli_query($conn, "UPDATE `orders` SET return_requested = 1, return_reason = '$reason', return_details = '$details', return_otp = '$otp', return_otp_verified = 0, return_otp_created_at = NOW() WHERE id = '$order_id'") or die('query failed');

                    if (sendReturnOTPEmail($order['email'], $order['name'], $order_id, $otp)) {
                        $message = 'Return OTP sent to your email. Please check your inbox and enter it on the verification page below.';
                        $already_requested = true; // show verification link after submit
                    } else {
                        $error = 'Unable to send return OTP email. Please try again later.';
                    }
                }
            }

            if ($already_requested && empty($message) && empty($error)) {
                $message = 'A return request is already in progress for this order. Please verify using the OTP sent to your email.';
            }
        } else {
            $error = 'Returns are only available for completed orders.';
        }
    } else {
        $error = 'Order not found.';
    }
} else {
    // No specific order selected yet; show all user's orders that can be returned
    $orders_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE user_id = '$user_id'") or die('query failed');
    while ($row = mysqli_fetch_assoc($orders_query)) {
        $orders[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Request</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php @include 'header.php'; ?>

<section class="heading">
    <h3>Return Request</h3>
    <p> <a href="home.php">Home</a> / Return Request </p>
</section>

<section class="placed-orders">
    <h1 class="title">Return Request</h1>
    <p style="color:#2a2a2a; font-weight:600; margin-bottom:10px;">Return policy: only damaged products or incorrect product delivery are eligible for return. If the item is in normal condition and not damaged, return cannot be accepted.</p>

    <div class="box-container">
        <?php if (!$order_id) { ?>
            <?php if (!empty($orders)) { ?>
                <?php foreach ($orders as $order_item) { 
                    $return_requested = !empty($order_item['return_requested']);
                    $return_verified = !empty($order_item['return_otp_verified']);
                ?>
                <div class="box">
                    <p> placed on : <span><?php echo $order_item['placed_on']; ?></span> </p>
                    <p> order id : <span>#<?php echo $order_item['id']; ?></span> </p>
                    <p> total price : <span>₹<?php echo $order_item['total_price']; ?>/-</span> </p>
                    <p> payment status : <span style="color:<?php echo (strtolower($order_item['payment_status']) == 'pending' ? 'tomato' : 'green'); ?>"><?php echo $order_item['payment_status']; ?></span> </p>

                    <?php if ($return_requested) { ?>
                        <p> return status : <span style="color:<?php echo ($return_verified ? 'green' : 'orange'); ?>; font-weight:bold;">
                            <?php echo ($return_verified ? '✓ Returned' : '⏳ Return Pending OTP'); ?>
                        </span></p>
                        <?php if (!empty($order_item['return_reason'])) { ?>
                            <p> return reason : <span><?php echo htmlspecialchars($order_item['return_reason']); ?></span> </p>
                        <?php } ?>
                        <?php if (!empty($order_item['return_details'])) { ?>
                            <p> return details : <span><?php echo nl2br(htmlspecialchars($order_item['return_details'])); ?></span> </p>
                        <?php } ?>
                        <?php if (!$return_verified) { ?>
                            <a href="verify_return_otp.php?order_id=<?php echo $order_item['id']; ?>" class="btn">Verify Return OTP</a>
                        <?php } ?>
                    <?php } elseif (strtolower($order_item['payment_status']) === 'completed') { ?>
                        <a href="request_return.php?order_id=<?php echo $order_item['id']; ?>" class="btn">Request Return</a>
                    <?php } else { ?>
                        <p style="color:#555;">Returns are available after order completion.</p>
                    <?php } ?>
                </div>
                <?php } ?>
            <?php } else { ?>
                <p class="empty">no orders placed yet!</p>
            <?php } ?>
        <?php } else { ?>
            <div class="box">
                <?php if ($message) { ?>
                    <div class="message">
                        <span><?php echo htmlspecialchars($message); ?></span>
                    </div>
                <?php } ?>

                <?php if ($error) { ?>
                    <div class="message">
                        <span style="color: tomato;"><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php } ?>

                <?php if (!$error && !$message) { ?>
                    <p>Please enter a brief reason for the return.</p>
                    <form action="" method="POST">
                        <input type="hidden" name="order_id" value="<?php echo intval($order_id); ?>">
                        <div class="form-group">
                            <label for="return_reason">Return Reason</label>
                            <textarea name="return_reason" id="return_reason" rows="3" style="width:100%; padding:10px; margin-top:10px;" required><?php echo isset($_POST['return_reason']) ? htmlspecialchars($_POST['return_reason']) : ''; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="return_details">Additional Details (optional)</label>
                            <textarea name="return_details" id="return_details" rows="3" style="width:100%; padding:10px; margin-top:10px;" placeholder="Please explain any issues, preferred pickup time, or anything else you'd like us to know."><?php echo isset($_POST['return_details']) ? htmlspecialchars($_POST['return_details']) : ''; ?></textarea>
                        </div>
                        <button type="submit" name="request_return" class="btn" style="margin-top:10px;">Submit Return Request</button>
                    </form>
                <?php } ?>

                <?php if ($message) { ?>
                    <p>Next step: verify your return request using the OTP sent to your email.</p>
                    <a href="verify_return_otp.php?order_id=<?php echo intval($order_id); ?>" class="btn">Verify Return OTP</a>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

</section>

<?php @include 'footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>
