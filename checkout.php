<?php

@include 'config.php';
@include 'email_functions.php';

@include 'session_client.php';

if (!($conn instanceof mysqli)) {
    die('database connection is not available');
}

// Ensure seller mapping column exists so seller panel only shows relevant orders.
if (function_exists('ensure_column_exists')) {
    ensure_column_exists($conn, 'orders', 'seller_ids', "VARCHAR(255) NULL DEFAULT NULL");
    ensure_column_exists($conn, 'orders', 'order_items_json', "LONGTEXT NULL");
}

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:login.php');
}

$error_message = '';
$success_message = '';

// Razorpay order creation helper (uses test credentials from config.php)
function createRazorpayOrder($amountPaise) {
    // Prevent bad credentials from generating confusing errors.
    if (empty(RAZORPAY_KEY_ID) || empty(RAZORPAY_KEY_SECRET) || strpos(RAZORPAY_KEY_SECRET, 'yourKey') !== false) {
        return ['error' => 'Razorpay test keys are not configured correctly. Please set RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET in config.php.'];
    }

    $url = 'https://api.razorpay.com/v1/orders';
    $data = http_build_query([
        'amount' => $amountPaise,
        'currency' => 'INR',
        'receipt' => 'rcpt_' . time(),
        'payment_capture' => 1
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        return ['error' => $err];
    }

    $resp = json_decode($result, true);
    if (isset($resp['id'])) {
        return ['order_id' => $resp['id']];
    }

    $errorMessage = $resp['error']['description'] ?? 'Unknown Razorpay error';
    return ['error' => $errorMessage];
}

// Pre-calculate cart total for Razorpay and display
$grand_total = 0;
$select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
if (mysqli_num_rows($select_cart) > 0) {
    while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
        $total_price = ($fetch_cart['price'] * $fetch_cart['quantity']);
        $grand_total += $total_price;
    }
}

// Generate a Razorpay order if required (test mode)
$razorpay_order_id = '';
$razorpay_error = '';
$razorpay_keys_valid = (defined('RAZORPAY_KEY_ID') && defined('RAZORPAY_KEY_SECRET') && strpos(RAZORPAY_KEY_SECRET, 'yourKey') === false);

if ($grand_total > 0) {
    if ($razorpay_keys_valid) {
        $orderResp = createRazorpayOrder($grand_total * 100);
        if (!empty($orderResp['order_id'])) {
            $razorpay_order_id = $orderResp['order_id'];
        } else {
            $razorpay_error = $orderResp['error'] ?? 'Unable to create Razorpay order.';
        }
    } else {
        // Keys not configured; proceed with order without actual Razorpay payment.
        $success_message = 'Razorpay is not configured. Your order will be placed in test mode without real payment.';
    }
}
    $payment_status = 'pending';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assign form values from POST before validation
    $name = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
    $number = mysqli_real_escape_string($conn, trim($_POST['number'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $method = mysqli_real_escape_string($conn, trim($_POST['method'] ?? ''));
    $razorpay_payment_id = mysqli_real_escape_string($conn, trim($_POST['razorpay_payment_id'] ?? ''));
    $flat = mysqli_real_escape_string($conn, trim($_POST['flat'] ?? ''));
    $street = mysqli_real_escape_string($conn, trim($_POST['street'] ?? ''));
    $city = mysqli_real_escape_string($conn, trim($_POST['city'] ?? ''));
    $state = mysqli_real_escape_string($conn, trim($_POST['state'] ?? ''));
    $country = mysqli_real_escape_string($conn, trim($_POST['country'] ?? ''));
    $pin_code = mysqli_real_escape_string($conn, trim($_POST['pin_code'] ?? ''));

    // Validate inputs
    if (empty($name) || strlen($name) < 3) {
        $error_message = "Please enter a valid name (at least 3 characters).";
    } elseif (!preg_match('/^\d{10}$/', $number)) {
        $error_message = "Please enter a valid 10-digit phone number.";
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } elseif (empty($flat) || empty($street) || empty($city) || empty($country) || !preg_match('/^\d{6}$/', $pin_code)) {
        $error_message = "Please fill all address fields correctly and enter a valid pin code.";
    } elseif ($method === 'razorpay' && empty($razorpay_payment_id)) {
        $error_message = 'Please complete Razorpay payment to proceed.';
    } else {
        // Build the address
        $address = "flat no. $flat, $street, $city, $state, $country - $pin_code";
        $placed_on = date('d-M-Y');

        $cart_total = 0;
        $cart_products = [];
        $seller_ids = [];
        $order_items = [];
        $required_stock = [];
        $stock_names = [];

        // Check if products table includes seller_id (optional feature)
        $seller_column_check = mysqli_query($conn, "SHOW COLUMNS FROM `products` LIKE 'seller_id'") or die('query failed');
        $has_seller_id_column = ($seller_column_check && mysqli_num_rows($seller_column_check) > 0);

        $cart_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
        if (mysqli_num_rows($cart_query) > 0) {
            while ($cart_item = mysqli_fetch_assoc($cart_query)) {
                $cart_products[] = $cart_item['name'] . ' (' . $cart_item['quantity'] . ') ';
                $sub_total = ($cart_item['price'] * $cart_item['quantity']);
                $cart_total += $sub_total;
                $item_seller_id = 0;

                // optional: if products table has seller_id, collect seller values for later
                if ($has_seller_id_column && isset($cart_item['pid'])) {
                    $prod = mysqli_query($conn, "SELECT seller_id FROM products WHERE id='{$cart_item['pid']}'") or die('query failed');
                    if (mysqli_num_rows($prod) > 0) {
                        $p = mysqli_fetch_assoc($prod);
                        $item_seller_id = (int)($p['seller_id'] ?? 0);
                        if ($item_seller_id > 0) $seller_ids[] = $item_seller_id;
                    }
                }

                $order_items[] = [
                    'pid' => (int)($cart_item['pid'] ?? 0),
                    'name' => $cart_item['name'],
                    'quantity' => (int)$cart_item['quantity'],
                    'price' => (int)$cart_item['price'],
                    'seller_id' => $item_seller_id
                ];

                $item_pid = (int)($cart_item['pid'] ?? 0);
                $item_qty = (int)$cart_item['quantity'];
                if ($item_pid > 0 && $item_qty > 0) {
                    if (!isset($required_stock[$item_pid])) {
                        $required_stock[$item_pid] = 0;
                    }
                    $required_stock[$item_pid] += $item_qty;
                    $stock_names[$item_pid] = $cart_item['name'];
                }
            }
        }

        $total_products = implode(',', $cart_products);

        // Razorpay payments should appear as prepaid in admin panel.
        if ($method === 'razorpay') {
            $payment_status = 'prepaid';
        }

        $order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE name = '$name' AND number = '$number' AND email = '$email' AND method = '$method' AND address = '$address' AND total_products = '$total_products' AND total_price = '$cart_total'") or die('query failed');

        if ($cart_total == 0) {
            $error_message = 'Your cart is empty. Add products to your cart before placing an order.';
        } elseif (!empty($required_stock)) {
            $stock_errors = [];

            foreach ($required_stock as $pid => $needed_qty) {
                $stock_q = mysqli_query($conn, "SELECT stock, name FROM `products` WHERE id = '$pid' LIMIT 1") or die('query failed');
                if (!$stock_q || mysqli_num_rows($stock_q) === 0) {
                    $product_label = htmlspecialchars($stock_names[$pid] ?? ('Product #' . $pid));
                    $stock_errors[] = $product_label . ' is not available.';
                    continue;
                }

                $stock_row = mysqli_fetch_assoc($stock_q);
                $available_qty = (int)($stock_row['stock'] ?? 0);
                if ($available_qty < $needed_qty) {
                    $product_label = htmlspecialchars($stock_row['name'] ?? ($stock_names[$pid] ?? ('Product #' . $pid)));
                    $stock_errors[] = 'Only ' . $available_qty . ' item(s) left for ' . $product_label . '.';
                }
            }

            if (!empty($stock_errors)) {
                $error_message = implode(' ', $stock_errors);
            }
        }

        if (!empty($error_message)) {
            // No-op. Error already set and shown to user.
        } elseif (mysqli_num_rows($order_query) > 0) {
            $error_message = 'Order placed already!';
        } else {
            // Generate OTP
            $otp = generateOTP();

            // Insert order with OTP
            // to enable seller-specific filtering you can add a seller_ids column to orders:
            // ALTER TABLE `orders` ADD COLUMN `seller_ids` VARCHAR(255) NULL;
            $seller_ids_list = !empty($seller_ids) ? implode(',', array_unique($seller_ids)) : '';
            $order_items_json = mysqli_real_escape_string($conn, json_encode($order_items));
            $insert_query = "INSERT INTO `orders` (user_id, name, number, email, method, address, total_products, total_price, placed_on, payment_status, otp, otp_verified, seller_ids, order_items_json) 
                           VALUES ('$user_id', '$name', '$number', '$email', '$method', '$address', '$total_products', '$cart_total', '$placed_on', '$payment_status', '$otp', 0, '$seller_ids_list', '$order_items_json')";

            mysqli_query($conn, "START TRANSACTION") or die('query failed');
            $stock_update_ok = true;

            foreach ($required_stock as $pid => $needed_qty) {
                $update_stock = mysqli_query($conn, "UPDATE `products` SET stock = stock - $needed_qty WHERE id = '$pid' AND stock >= $needed_qty") or die('query failed');
                if (!$update_stock || mysqli_affected_rows($conn) === 0) {
                    $stock_update_ok = false;
                    break;
                }
            }

            if (!$stock_update_ok) {
                mysqli_query($conn, "ROLLBACK") or die('query failed');
                $error_message = 'Stock changed while placing order. Please review your cart and try again.';
            } elseif (mysqli_query($conn, $insert_query)) {
                // Get the inserted order ID
                $order_id = mysqli_insert_id($conn);

                // Delete from cart
                mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
                mysqli_query($conn, "COMMIT") or die('query failed');

                // Prepare customer details for email
                $customer_details = array(
                    'number' => $number,
                    'address' => $address,
                    'method' => $method
                );

                // Send confirmation email with OTP
                $email_sent = sendOrderConfirmationEmail($email, $name, $order_id, $otp, $total_products, $cart_total, $customer_details);

                if ($email_sent) {
                    $success_message = 'Thank you for your order! An email with your OTP has been sent to ' . htmlspecialchars($email) . '. Please verify your email to confirm your order.';
                } else {
                    $success_message = 'Thank you for your order! ' . getOTPDisplayMessage($otp) . '<p>Verify your order to confirm.</p>';
                }
            } else {
                mysqli_query($conn, "ROLLBACK") or die('query failed');
                $error_message = 'Could not place order. Please try again.';
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>

    <!-- Font Awesome CDN link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom admin CSS file link -->
    <link rel="stylesheet" href="css/style.css">

    <style>
        .message-box {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            font-size: 16px;
            display: flex;
            align-items: center;
        }

        .message-box.success {
            background-color: #d4edda; /* Light green */
            color: #155724; /* Dark green */
            border: 1px solid #c3e6cb; /* Darker green */
        }

        .message-box.error {
            background-color: #f8d7da; /* Light red */
            color: #721c24; /* Dark red */
            border: 1px solid #f5c6cb; /* Darker red */
        }
    </style>
</head>
<body>
   
<?php @include 'header.php'; ?>

<section class="heading">
    <h3>Checkout Order</h3>
    <p> <a href="home.php">Home</a> / Checkout </p>
</section>

<section class="display-order">
    <?php
        $grand_total = 0;
        $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
        if (mysqli_num_rows($select_cart) > 0) {
            while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                $total_price = ($fetch_cart['price'] * $fetch_cart['quantity']);
                $grand_total += $total_price;
    ?>    
    <p> <?php echo $fetch_cart['name'] ?> <span>(<?php echo '₹'.$fetch_cart['price'].'/-'.' x '.$fetch_cart['quantity'] ?>)</span> </p>
    <?php
            }
        } else {
            echo '<p class="empty">Your cart is empty</p>';
        }
    ?>
    <div class="grand-total">Grand Total: <span>₹<?php echo $grand_total; ?>/-</span></div>
</section>

<section class="checkout">

    <form action="" method="POST">

        <h3>Place Your Order</h3>


        <?php
        // Display error or success messages
        if ($error_message) {
            echo '<div class="message-box error">' . $error_message . '</div>';
        } elseif ($success_message) {
            echo '<div class="message-box success">' . $success_message . '</div>';
        }

        // Razorpay order creation issue (if any)
        if (!empty($razorpay_error)) {
            echo '<div class="message-box error">Razorpay error: ' . htmlspecialchars($razorpay_error) . '</div>';
        }
        
        // Inform developer if Razorpay mode is disabled
        if (isset($razorpay_keys_valid) && !$razorpay_keys_valid) {
            echo '<div class="message-box error">Razorpay test mode is disabled because keys are not configured. Orders will be accepted without Razorpay payment.</div>';
        }
        ?>


        <div class="flex">
            <div class="inputBox">
                <span>Your Name:</span>
                <input type="text" name="name" required placeholder="Enter your name">
            </div>
            <div class="inputBox">
                <span>Your Number:</span>
                <input type="text" name="number" required placeholder="Enter your number" maxlength="10" pattern="\d{10}" title="Please enter a valid 10-digit phone number">
            </div>
            <div class="inputBox">
                <span>Your Email:</span>
                <input type="email" name="email" required placeholder="Enter your email">
            </div>
            <div class="inputBox">
                <span>Payment Method:</span>
                <select name="method" id="paymentMethod" required>
                    <option value="cash on delivery">Cash on Delivery</option>
                    <option value="razorpay">Razorpay (Test Mode)</option>
                </select>
            </div>

            <input type="hidden" name="razorpay_payment_id" id="razorpayPaymentId" value="">
            <input type="hidden" name="razorpay_order_id" id="razorpayOrderId" value="<?php echo htmlspecialchars($razorpay_order_id); ?>">
            <div class="inputBox">
                <span>Address Line 01:</span>
                <input type="text" name="flat" required placeholder="e.g. Flat No.">
            </div>
            <div class="inputBox">
                <span>Address Line 02:</span>
                <input type="text" name="street" required placeholder="e.g. Street Name">
            </div>
            <div class="inputBox">
                <span>City:</span>
                <input type="text" name="city" required placeholder="e.g. Surendranagar">
            </div>
            <div class="inputBox">
                <span>State:</span>
                <input type="text" name="state" required placeholder="e.g. Gujarat">
            </div>
            <div class="inputBox">
                <span>Country:</span>
                <input type="text" name="country" required placeholder="e.g. India">
            </div>
            <div class="inputBox">
                <span>Pin Code:</span>
                <input type="text" name="pin_code" required placeholder="e.g. 363001" maxlength="6" pattern="\d{6}" title="Please enter a valid 6-digit pin code">
            </div>
        </div>

        <input type="submit" name="order" value="Order Now" class="btn">

        
    </form>

</section>

<?php @include 'footer.php'; ?>

<!-- Razorpay Checkout (Test Mode) -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.querySelector('.checkout form');
        var paymentMethod = document.getElementById('paymentMethod');
        var paymentIdInput = document.getElementById('razorpayPaymentId');
        var orderIdInput = document.getElementById('razorpayOrderId');

        if (!form) {
            return;
        }

        function showCheckoutError(message) {
            var existing = form.querySelector('.message-box.error.client-error');
            if (existing) {
                existing.textContent = message;
                return;
            }

            var box = document.createElement('div');
            box.className = 'message-box error client-error';
            box.textContent = message;
            form.insertBefore(box, form.firstChild.nextSibling);
        }

        // Prevent normal form submission for Razorpay payments
        form.addEventListener('submit', function(event) {
            if (paymentMethod && paymentMethod.value === 'razorpay' && !paymentIdInput.value) {
                event.preventDefault();

                        // If Razorpay keys are not configured, skip the popup and submit as a fake successful payment.
                        if (!<?php echo $razorpay_keys_valid ? 'true' : 'false'; ?>) {
                            paymentIdInput.value = 'test_payment_id';
                            form.submit();
                            return;
                        }
                if (typeof Razorpay === 'undefined') {
                    showCheckoutError('Razorpay script could not load. Check your internet connection and refresh the page.');
                    return;
                }

                var grandTotalText = (document.querySelector('.grand-total span') || {}).textContent || '';
                var digitsOnly = grandTotalText.replace(/[^0-9]/g, '');
                var amountValue = parseInt(digitsOnly, 10) || 0;
                var amountInPaise = amountValue * 100;

                if (amountInPaise <= 0) {
                    showCheckoutError('Cart total is invalid. Please refresh and try again.');
                    return;
                }

                // if an order was created server-side, include it
                var orderId = orderIdInput ? orderIdInput.value : '';
                var nameInput = form.querySelector('input[name="name"]');
                var emailInput = form.querySelector('input[name="email"]');
                var numberInput = form.querySelector('input[name="number"]');

                var options = {
                    key: '<?php echo addslashes(RAZORPAY_KEY_ID); ?>',
                    currency: 'INR',
                    name: 'Ceramic Store',
                    description: 'Order Payment (Test)',
                    handler: function (response){
                        if (response.razorpay_payment_id) {
                            paymentIdInput.value = response.razorpay_payment_id;
                            form.submit();
                        }
                    },
                    prefill: {
                        name: nameInput ? nameInput.value : '',
                        email: emailInput ? emailInput.value : '',
                        contact: numberInput ? numberInput.value : ''
                    },
                    theme: {
                        color: '#1a73e8'
                    },
                    modal: {
                        ondismiss: function () {
                            showCheckoutError('Payment popup closed. Complete Razorpay payment to place the order.');
                        }
                    }
                };

                if (orderId) {
                    options.order_id = orderId;
                } else {
                    options.amount = amountInPaise;
                }

                try {
                    var rzp = new Razorpay(options);
                    rzp.open();
                } catch (e) {
                    showCheckoutError('Unable to open Razorpay popup. Please refresh and try again.');
                }
            }
        });
    });
</script>
<script src="js/script.js"></script>

</body>
</html>
