<?php

/**
 * Email sending utility for order confirmation with OTP
 * Supports both PHP mail() and Gmail SMTP
 */

// Gmail SMTP Configuration
// UPDATE THESE WITH YOUR GMAIL ACCOUNT OR YOUR LOCAL SMTP TOOL
// To use Gmail: enable 2-step verification and create an app password.
// To test locally on Windows you can install a dummy SMTP server such as
// Papercut (https://github.com/ChangemakerStudios/Papercut-SMTP) or
// smtp4dev and listen on port 25, 2525, etc. When using such a tool, set
// USE_SMTP to false and the ini_set entries below will point mail() at it.
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'ajudiyaharit75@gmail.com');   // your Gmail address
define('SMTP_PASS', 'lnhfbdvpumhnuzhd');          // your 16-char app password (no spaces)
define('USE_SMTP', true);                         // ← CHANGE THIS TO true

// local mail settings for PHP mail() fallback
// if you're not using a real SMTP provider, configure your php.ini or
// call ini_set() before sending.
ini_set('SMTP', '127.0.0.1');      // address of your local SMTP server
ini_set('smtp_port', '25');        // port your local SMTP listens on
ini_set('sendmail_from', 'noreply@ceramicstore.com');

// Function to generate OTP
function generateOTP($length = 6) {
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= mt_rand(0, 9);
    }
    return $otp;
}

// Ensure order return OTP columns exist (for return verification flow)
function ensureReturnOtpColumns($conn) {
    // These ALTER statements are safe to run repeatedly on MySQL 8+ (IF NOT EXISTS).
    // If you run on older MySQL versions, they may fail; in that case run a one-time ALTER manually.
    @mysqli_query($conn, "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `return_requested` TINYINT(1) DEFAULT 0");
    @mysqli_query($conn, "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `return_otp` VARCHAR(6) DEFAULT NULL");
    @mysqli_query($conn, "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `return_otp_verified` TINYINT(1) DEFAULT 0");
    @mysqli_query($conn, "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `return_otp_created_at` TIMESTAMP NULL DEFAULT NULL");
    @mysqli_query($conn, "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `return_reason` TEXT NULL");
    @mysqli_query($conn, "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `return_details` TEXT NULL");
    @mysqli_query($conn, "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `return_processed` TINYINT(1) DEFAULT 0");
    @mysqli_query($conn, "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `return_approved` TINYINT(1) DEFAULT NULL");
}

// Send OTP for login (passwordless)
function sendLoginOTPEmail($email, $otp) {
    $subject = "Your Login OTP Code";

    // Determine base URL for links
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $baseUrl = rtrim($protocol . '://' . $host . dirname($_SERVER['PHP_SELF']), '/');
    $verifyUrl = $baseUrl . '/login.php?showOtp=1&email=' . urlencode($email);

    $body = "<html><body>" .
            "<p>Use the following one-time password to sign in to your account:</p>" .
            "<h2 style='color:#333;'>" . htmlspecialchars($otp) . "</h2>" .
            "<p>This code is valid for 10 minutes. If you did not request this, please ignore this email.</p>" .
            "<div style='margin:20px 0; text-align:center;'>" .
            "<a href='" . $verifyUrl . "' style='background:#1a73e8; color:#fff; padding:12px 20px; text-decoration:none; border-radius:5px; font-weight:bold;'>Verify Email</a>" .
            "</div>" .
            "</body></html>";

    if (USE_SMTP && !empty(SMTP_USER) && !empty(SMTP_PASS)) {
        return sendEmailViaSMTP($email, $subject, $body);
    } else {
        return sendEmailViaPhpMail($email, $subject, $body);
    }
}

// Format OTP for display when email fails
function getOTPDisplayMessage($otp) {
    return '<div style="background-color: #fff3cd; border: 2px solid #ffc107; padding: 15px; border-radius: 5px; margin: 15px 0; text-align: center;">' .
           '<p style="color: #333; margin-bottom: 10px;"><strong>⚠️ Testing Mode: OTP Display</strong></p>' .
           '<p style="color: #666; margin-bottom: 10px;">Your verification code:</p>' .
           '<p style="font-size: 28px; font-weight: bold; color: #ff6b00; letter-spacing: 5px; margin: 10px 0;">' . htmlspecialchars($otp) . '</p>' .
           '<p style="color: #999; font-size: 12px; margin-top: 10px;">To send emails via Gmail, configure SMTP_USER & SMTP_PASS in email_functions.php and set USE_SMTP to true.</p>' .
           '</div>';
}

// Function to send order confirmation email with OTP
function sendOrderConfirmationEmail($email, $name, $order_id, $otp, $order_details, $total_price, $customer_details = array()) {
    
    $subject = "Order Confirmation - Order #" . $order_id . " | Ceramic Store";
    
    // Determine base URL for customer-facing links
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $baseUrl = rtrim($protocol . '://' . $host . dirname($_SERVER['PHP_SELF']), '/');

    // Parse order details
    $products = explode(',', $order_details);
    $product_html = '';
    foreach ($products as $index => $product) {
        $product = trim($product);
        if (!empty($product)) {
            $product_html .= "<tr style='border-bottom: 1px solid #eee;'><td style='padding: 10px;'>" . ($index + 1) . "</td><td style='padding: 10px;'>" . htmlspecialchars($product) . "</td></tr>";
        }
    }
    
    // Extract customer details if provided
    $phone = isset($customer_details['number']) ? htmlspecialchars($customer_details['number']) : 'N/A';
    $address = isset($customer_details['address']) ? htmlspecialchars($customer_details['address']) : 'N/A';
    $method = isset($customer_details['method']) ? htmlspecialchars($customer_details['method']) : 'N/A';
    
    // Determine public QR image URL if file exists
    $publicQr = '';
    foreach (['png','jpeg','jpg'] as $ext) {
        if (file_exists(__DIR__ . "/images/qr_code.$ext")) {
            $publicQr = "https://yourdomain.com/images/qr_code.$ext";
            break;
        }
    }
    
    // Create professional HTML email body
    $body = '
    <html>
    <head>
        <style>
            * { margin: 0; padding: 0; }
            body {
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                color: #333;
                background-color: #f5f5f5;
            }
            .container {
                max-width: 650px;
                margin: 20px auto;
                background-color: white;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                overflow: hidden;
            }
            .header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 30px 20px;
                text-align: center;
            }
            .header h1 {
                font-size: 28px;
                margin-bottom: 5px;
            }
            .header p {
                font-size: 14px;
                opacity: 0.9;
            }
            .content {
                padding: 30px 20px;
            }
            .greeting {
                margin-bottom: 20px;
                font-size: 16px;
            }
            .section-title {
                font-size: 18px;
                font-weight: bold;
                color: #667eea;
                margin-top: 25px;
                margin-bottom: 15px;
                border-bottom: 2px solid #667eea;
                padding-bottom: 10px;
            }
            .order-info {
                background-color: #f9f9f9;
                border-left: 4px solid #667eea;
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 4px;
            }
            .info-row {
                display: flex;
                justify-content: space-between;
                padding: 8px 0;
                border-bottom: 1px dotted #ddd;
            }
            .info-row:last-child {
                border-bottom: none;
            }
            .info-label {
                font-weight: bold;
                color: #667eea;
            }
            .info-value {
                color: #555;
            }
            .invoice-table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            .invoice-table thead {
                background-color: #667eea;
                color: white;
            }
            .invoice-table th {
                padding: 12px;
                text-align: left;
                font-weight: 600;
            }
            .invoice-table td {
                padding: 10px 12px;
            }
            .invoice-table tbody tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            .total-section {
                background-color: #f0f0f0;
                padding: 15px;
                margin-top: 20px;
                border-radius: 4px;
            }
            .total-row {
                display: flex;
                justify-content: space-between;
                padding: 10px 0;
                font-size: 16px;
            }
            .total-amount {
                font-size: 24px;
                font-weight: bold;
                color: #667eea;
                text-align: right;
            }
            .otp-section {
                background-color: #fff3cd;
                border: 2px solid #ffc107;
                padding: 20px;
                text-align: center;
                border-radius: 6px;
                margin: 25px 0;
            }
            .otp-label {
                font-size: 14px;
                color: #666;
                margin-bottom: 10px;
            }
            .otp-code {
                font-size: 42px;
                font-weight: bold;
                color: #ff6b00;
                letter-spacing: 8px;
                font-family: "Courier New", monospace;
                margin: 10px 0;
            }
            .otp-validity {
                font-size: 12px;
                color: #999;
                margin-top: 10px;
            }
            .warning-box {
                background-color: #ffe6e6;
                border-left: 4px solid #ff0000;
                padding: 12px;
                margin: 15px 0;
                border-radius: 3px;
                color: #721c24;
                font-size: 13px;
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
                color: #555;
            }
            .steps li:before {
                content: "✓";
                position: absolute;
                left: 0;
                color: #667eea;
                font-weight: bold;
                font-size: 18px;
            }
            .footer {
                background-color: #f5f5f5;
                border-top: 1px solid #ddd;
                padding: 20px;
                text-align: center;
                font-size: 12px;
                color: #888;
            }
            .footer-links {
                margin-bottom: 10px;
            }
            .footer-links a {
                color: #667eea;
                text-decoration: none;
                margin: 0 10px;
            }
            .highlight {
                background-color: #ffffcc;
                padding: 2px 4px;
                border-radius: 2px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <!-- Header -->
            <div class="header">
                <h1>🎉 Thank You for Your Order!</h1>
                <p>Order #' . htmlspecialchars($order_id) . ' | Ceramic Store</p>
            </div>
            
            <!-- Content -->
            <div class="content">
                <div class="greeting">
                    <p>Dear <strong>' . htmlspecialchars($name) . '</strong>,</p>
                    <p>We are delighted to confirm your order. Your ceramic pieces are being carefully prepared for shipment!</p>
                </div>
                
                <!-- Order Information -->
                <div class="section-title">📋 Order Information</div>
                <div class="order-info">
                    <div class="info-row">
                        <span class="info-label">Order ID:</span>
                        <span class="info-value">#' . htmlspecialchars($order_id) . '</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Order Date:</span>
                        <span class="info-value">' . date('d M Y, h:i A') . '</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone:</span>
                        <span class="info-value">' . $phone . '</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value">' . htmlspecialchars($email) . '</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Delivery Address:</span>
                        <span class="info-value">' . $address . '</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Payment Method:</span>
                        <span class="info-value">' . $method . '</span>
                    </div>
                </div>
                
                <!-- UPI QR section -->
                ' . ($publicQr && stripos($method, 'upi') !== false ? '
                <div class="section-title">💳 UPI Payment</div>
                <p>Please scan the QR code below with any UPI app and complete the payment:</p>
                <div style="text-align:center;margin:15px 0;">
                    <img src="' . $publicQr . '" alt="UPI QR code" style="max-width:200px;">
                </div>
                <p style="font-size:13px;color:#555;">After payment, your order will be confirmed once we verify the transaction.</p>
                ' : '') . '
                <!-- Order Items -->
                <div class="section-title">📦 Order Items</div>
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th style="width: 10%;">#</th>
                            <th style="width: 90%;">Product Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        ' . $product_html . '
                    </tbody>
                </table>
                
                <!-- Total Section -->
                <div class="total-section">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>₹' . htmlspecialchars($total_price) . '/-</span>
                    </div>
                    <div class="total-row">
                        <span><strong>Total Amount:</strong></span>
                        <strong class="total-amount">₹' . htmlspecialchars($total_price) . '/-</strong>
                    </div>
                </div>
                
                <!-- OTP Verification -->
                <div class="section-title">🔐 Email Verification Required</div>
                <p>To complete your order and enjoy priority processing, please verify your email using the code below:</p>
                
                <div class="otp-section">
                    <div class="otp-label">Your Verification Code:</div>
                    <div class="otp-code">' . htmlspecialchars($otp) . '</div>
                    <div class="otp-validity">⏱️ Valid for 10 minutes | Do not share this code</div>
                </div>
                
                <div class="warning-box">
                    <strong>⚠️ Important:</strong> Never share your verification code with anyone. Our team will never ask for your OTP via email or phone.
                </div>
                
                <!-- Next Steps -->
                <div class="section-title">✨ What Happens Next?</div>
                <ol class="steps">
                    <li>Visit our website and log into your account</li>
                    <li>Navigate to <span class="highlight">Your Orders</span> section</li>
                    <li>Click <span class="highlight">Verify Email</span> button on this order</li>
                    <li>Enter your 6-digit verification code: <strong>' . htmlspecialchars($otp) . '</strong></li>
                    <li>Your order will be confirmed and processing will begin</li>
                    <li>You will receive a tracking number via email</li>
                </ol>

                <div style="text-align: center; margin: 25px 0;">
                    <a href="' . $baseUrl . '/verify_order_otp.php?order_id=' . urlencode($order_id) . '" style="background-color:#4CAF50; color:#fff; padding:12px 20px; text-decoration:none; border-radius:4px; font-weight:600;">Verify Email</a>
                </div>
                
                <p style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
                    If you have any questions or need assistance, please feel free to contact our customer support team.
                    We are here to help!
                </p>
                
                <p style="margin-top: 15px;">
                    Best regards,<br>
                    <strong>The Ceramic Store Team</strong><br>
                    <em>Crafting Excellence, One Piece at a Time</em>
                </p>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <div class="footer-links">
                    <a href="' . $baseUrl . '">Customer Site</a> | 
                    <a href="' . $baseUrl . '/contact.php">Contact Us</a> | 
                    <a href="' . $baseUrl . '/orders.php">Track Order</a> | 
                    <a href="' . $baseUrl . '/contact.php">Returns & Exchanges</a>
                </div>
                <p>&copy; ' . date('Y') . ' Ceramic Store. All rights reserved.</p>
                <p style="margin-top: 10px; font-size: 11px;">This is an automated email. Please do not reply to this email address.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    // Determine sending method
    if (USE_SMTP && !empty(SMTP_USER) && !empty(SMTP_PASS)) {
        return sendEmailViaSMTP($email, $subject, $body);
    } else {
        return sendEmailViaPhpMail($email, $subject, $body);
    }
}

// Function to send return OTP email
function sendReturnOTPEmail($email, $name, $order_id, $otp) {
    $subject = "Return Request OTP - Order #" . $order_id . " | Ceramic Store";

    // Determine base URL for customer-facing links
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $baseUrl = rtrim($protocol . '://' . $host . dirname($_SERVER['PHP_SELF']), '/');

    $body = '<html><body>' .
            '<div style="font-family: Arial, sans-serif; max-width: 650px; margin: auto; padding: 20px; background: #f9f9f9;">' .
            '<h2 style="color:#333;">Return Request Confirmation</h2>' .
            '<p>Hi <strong>' . htmlspecialchars($name) . '</strong>,</p>' .
            '<p>We received a request to return your order <strong>#' . htmlspecialchars($order_id) . '</strong>. To confirm the return, please enter the OTP below on the return verification page.</p>' .
            '<div style="background: #fff3cd; padding: 20px; border: 1px solid #ffeeba; border-radius: 8px; margin: 20px 0; text-align: center;">' .
            '<p style="margin: 0 0 10px; color: #333;">Your return OTP code is:</p>' .
            '<p style="font-size: 32px; letter-spacing: 8px; font-weight: bold; margin: 0;">' . htmlspecialchars($otp) . '</p>' .
            '<p style="margin: 12px 0 0; color: #777;">Valid for 10 minutes.</p>' .
            '</div>' .
            '<p>Once verified, we will proceed with your return and refund.</p>' .
            '<p style="margin-top: 20px;">You can verify your return here: <a href="' . $baseUrl . '/verify_return_otp.php?order_id=' . urlencode($order_id) . '" style="color:#1a73e8;">Verify Return</a></p>' .
            '<p style="margin-top: 20px;">If you did not request a return, please ignore this email or contact our support team.</p>' .
            '<hr style="border:none;border-top:1px solid #eee; margin:20px 0;" />' .
            '<p style="font-size:12px;color:#777;">This is an automated message. Please do not reply to this email address.</p>' .
            '</div>' .
            '</body></html>';

    if (USE_SMTP && !empty(SMTP_USER) && !empty(SMTP_PASS)) {
        return sendEmailViaSMTP($email, $subject, $body);
    } else {
        return sendEmailViaPhpMail($email, $subject, $body);
    }
}

// Send notification after return request is approved or rejected
function sendReturnDecisionEmail($email, $name, $order_id, $approved) {
    $subject = 'Return ' . ($approved ? 'Approved' : 'Rejected') . ' - Order #' . $order_id . ' | Ceramic Store';

    $statusText = $approved ? 'approved' : 'rejected';
    $statusColor = $approved ? '#28a745' : '#dc3545';

    $body = '<html><body>' .
            '<div style="font-family: Arial, sans-serif; max-width: 650px; margin: auto; padding: 20px; background: #f9f9f9;">' .
            '<h2 style="color:#333;">Return Request ' . ucfirst($statusText) . '</h2>' .
            '<p>Hi <strong>' . htmlspecialchars($name) . '</strong>,</p>' .
            '<p>Your return request for order <strong>#' . htmlspecialchars($order_id) . '</strong> has been <span style="color:' . $statusColor . '; font-weight:bold;">' . strtoupper($statusText) . '</span>.</p>' .
            '<p>If you have any questions, please contact our support team.</p>' .
            '<p style="margin-top:20px;">Thanks,<br><strong>Ceramic Store Team</strong></p>' .
            '</div></body></html>';

    if (USE_SMTP && !empty(SMTP_USER) && !empty(SMTP_PASS)) {
        return sendEmailViaSMTP($email, $subject, $body);
    } else {
        return sendEmailViaPhpMail($email, $subject, $body);
    }
}

// Function to send email via PHP mail()
function sendEmailViaPhpMail($email, $subject, $body) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@ceramicstore.com" . "\r\n";
    $headers .= "Reply-To: support@ceramicstore.com" . "\r\n";
    
    return mail($email, $subject, $body, $headers);
}

// Function to send email via Gmail SMTP
function sendEmailViaSMTP($to, $subject, $message) {
    $from = SMTP_USER;
    $host = SMTP_HOST;
    $port = SMTP_PORT;
    $username = SMTP_USER;
    $password = SMTP_PASS;
    
    try {
        // Step 1: Connect to SMTP server (unencrypted first for port 587)
        $connection = @fsockopen($host, $port, $errno, $errstr, 30);
        
        if (!$connection) {
            error_log("SMTP Connection failed: " . $errstr . " (" . $errno . ")");
            return false;
        }
        
        stream_set_timeout($connection, 10);
        
        // Read server greeting
        $response = fgets($connection, 512);
        if (empty($response) || strpos($response, '220') === false) {
            fclose($connection);
            error_log("SMTP: Invalid greeting");
            return false;
        }
        
        // Send EHLO
        fwrite($connection, "EHLO localhost\r\n");
        $response = '';
        while (!feof($connection)) {
            $line = fgets($connection, 512);
            $response .= $line;
            if (substr($line, 3, 1) != '-') break;
        }
        if (strpos($response, '250') === false) {
            fclose($connection);
            error_log("SMTP: EHLO failed");
            return false;
        }
        
        // Step 2: Start TLS upgrade
        fwrite($connection, "STARTTLS\r\n");
        $response = fgets($connection, 512);
        if (strpos($response, '220') === false) {
            fclose($connection);
            error_log("SMTP: STARTTLS not supported");
            return false;
        }
        
        // Step 3: Upgrade connection to TLS
        // disable certificate verification (development environment may lack CA bundle)
        stream_context_set_option($connection, 'ssl', 'verify_peer', false);
        stream_context_set_option($connection, 'ssl', 'verify_peer_name', false);
        stream_context_set_option($connection, 'ssl', 'allow_self_signed', true);
        $crypto_method = STREAM_CRYPTO_METHOD_TLS_CLIENT;
        if (@stream_socket_enable_crypto($connection, true, $crypto_method) === false) {
            fclose($connection);
            error_log("SMTP: TLS upgrade failed");
            return false;
        }
        
        // Step 4: Send EHLO again after TLS
        fwrite($connection, "EHLO localhost\r\n");
        $response = '';
        while (!feof($connection)) {
            $line = fgets($connection, 512);
            $response .= $line;
            if (substr($line, 3, 1) != '-') break;
        }
        
        // Step 5: Authenticate
        fwrite($connection, "AUTH LOGIN\r\n");
        $response = fgets($connection, 512);
        if (strpos($response, '334') === false) {
            fclose($connection);
            error_log("SMTP: AUTH LOGIN not available");
            return false;
        }
        
        // Send username
        fwrite($connection, base64_encode($username) . "\r\n");
        $response = fgets($connection, 512);
        if (strpos($response, '334') === false) {
            fclose($connection);
            error_log("SMTP: Username rejected");
            return false;
        }
        
        // Send password
        fwrite($connection, base64_encode($password) . "\r\n");
        $response = fgets($connection, 512);
        if (strpos($response, '235') === false) {
            fclose($connection);
            error_log("SMTP: Authentication failed - check credentials");
            return false;
        }
        
        // Step 6: Send email
        fwrite($connection, "MAIL FROM: <" . $from . ">\r\n");
        $response = fgets($connection, 512);
        if (strpos($response, '250') === false) {
            fclose($connection);
            error_log("SMTP: MAIL FROM rejected");
            return false;
        }
        
        fwrite($connection, "RCPT TO: <" . $to . ">\r\n");
        $response = fgets($connection, 512);
        if (strpos($response, '250') === false) {
            fclose($connection);
            error_log("SMTP: RCPT TO rejected");
            return false;
        }
        
        fwrite($connection, "DATA\r\n");
        $response = fgets($connection, 512);
        if (strpos($response, '354') === false) {
            fclose($connection);
            error_log("SMTP: DATA not accepted");
            return false;
        }
        
        // Build email with proper headers
        $email_content = "From: " . $from . "\r\n";
        $email_content .= "To: " . $to . "\r\n";
        $email_content .= "Subject: " . $subject . "\r\n";
        $email_content .= "MIME-Version: 1.0\r\n";
        $email_content .= "Content-Type: text/html; charset=UTF-8\r\n";
        $email_content .= "X-Mailer: Ceramic Store\r\n";
        $email_content .= "\r\n";
        $email_content .= $message;
        
        fwrite($connection, $email_content . "\r\n.\r\n");
        $response = fgets($connection, 512);
        if (strpos($response, '250') === false) {
            fclose($connection);
            error_log("SMTP: Message rejected");
            return false;
        }
        
        // Quit
        fwrite($connection, "QUIT\r\n");
        fclose($connection);
        
        error_log("SMTP: Email sent successfully to " . $to);
        return true;
        
    } catch (Exception $e) {
        error_log("SMTP Error: " . $e->getMessage());
        return false;
    }
}

// Function to verify OTP
function verifyOTP($conn, $order_id, $otp) {
    $otp = mysqli_real_escape_string($conn, $otp);
    $order_id = mysqli_real_escape_string($conn, $order_id);
    
    // Check if OTP matches and hasn't expired (10 minutes)
    $query = mysqli_query($conn, "SELECT * FROM `orders` WHERE id = '$order_id' AND otp = '$otp' AND otp_verified = 0") or die('query failed');
    
    if (mysqli_num_rows($query) > 0) {
        $order = mysqli_fetch_assoc($query);
        
        // Check if OTP is still valid (10 minutes)
        $otp_time = strtotime($order['otp_created_at']);
        $current_time = time();
        $time_diff = ($current_time - $otp_time) / 60; // in minutes
        
        if ($time_diff <= 10) {
            // OTP is valid, update the order
            mysqli_query($conn, "UPDATE `orders` SET otp_verified = 1 WHERE id = '$order_id'") or die('query failed');
            return true;
        } else {
            return false; // OTP expired
        }
    }
    
    return false; // OTP doesn't match
}

// Function to resend OTP
function resendOTP($conn, $order_id, $email, $name) {
    $order_id = mysqli_real_escape_string($conn, $order_id);
    $email = mysqli_real_escape_string($conn, $email);
    $name = mysqli_real_escape_string($conn, $name);
    
    $query = mysqli_query($conn, "SELECT * FROM `orders` WHERE id = '$order_id' AND email = '$email'") or die('query failed');
    
    if (mysqli_num_rows($query) > 0) {
        $order = mysqli_fetch_assoc($query);
        
        // Generate new OTP
        $new_otp = generateOTP();
        
        // Update OTP in database
        mysqli_query($conn, "UPDATE `orders` SET otp = '$new_otp', otp_created_at = NOW() WHERE id = '$order_id'") or die('query failed');
        
        // Send email with new OTP
        $order_details = $order['total_products'];
        $total_price = $order['total_price'];
        
        // Prepare customer details
        $customer_details = array(
            'number' => $order['number'],
            'address' => $order['address'],
            'method' => $order['method']
        );
        
        return sendOrderConfirmationEmail($email, $name, $order_id, $new_otp, $order_details, $total_price, $customer_details);
    }
    
    return false;
}

?>
