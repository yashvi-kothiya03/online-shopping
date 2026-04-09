<?php
// Test email system
require_once 'config.php';
require_once 'email_functions.php';

echo "=== Email System Test ===\n\n";

// Test 1: Check SMTP Configuration
echo "1. SMTP Configuration:\n";
echo "   SMTP Host: " . SMTP_HOST . "\n";
echo "   SMTP Port: " . SMTP_PORT . "\n";
echo "   SMTP User: " . SMTP_USER . "\n";
echo "   SMTP Pass: " . (strlen(SMTP_PASS) > 0 ? "Set (length: " . strlen(SMTP_PASS) . ")" : "Not set") . "\n";
echo "   Use SMTP: " . (USE_SMTP ? "Yes" : "No") . "\n";

// Test 2: Generate OTP
echo "\n2. Testing OTP Generation:\n";
$otp = generateOTP();
echo "   Generated OTP: " . $otp . "\n";

// Test 3: SMTP connectivity diagnostic
echo "\n3. SMTP Connectivity Diagnostic:\n";
$host = SMTP_HOST;
$port = SMTP_PORT;
$username = SMTP_USER;
$password = SMTP_PASS;

$socket = @fsockopen($host, $port, $errNo, $errStr, 10);
if ($socket) {
    echo "   ✓ Connected to $host:$port\n";
    $resp = fgets($socket, 512);
    echo "   Server response: " . trim($resp) . "\n";
    echo "   → Sending EHLO...\n";
    fwrite($socket, "EHLO localhost\r\n");
    while (!feof($socket)) {
        $line = fgets($socket, 512);
        echo "      " . trim($line) . "\n";
        if (substr($line, 3, 1) != '-') break;
    }
    echo "   → Sending STARTTLS...\n";
    fwrite($socket, "STARTTLS\r\n");
    $resp = fgets($socket, 512);
    echo "      " . trim($resp) . "\n";
    if (strpos($resp, '220') !== false) {
        echo "   → Upgrading to TLS...\n";
        stream_context_set_option($socket, 'ssl', 'verify_peer', false);
        stream_context_set_option($socket, 'ssl', 'verify_peer_name', false);
        stream_context_set_option($socket, 'ssl', 'allow_self_signed', true);
        $res = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        if ($res === true) {
            echo "      ✓ TLS encryption enabled\n";
        } else {
            echo "      ✗ TLS upgrade failed: " . var_export($res, true) . "\n";
        }
    }
    fwrite($socket, "QUIT\r\n");
    fclose($socket);
} else {
    echo "   ✗ Unable to connect: $errStr ($errNo)\n";
}

// Test 4: Test SMTP Connection with simple message
echo "\n4. Testing SMTP Connection:\n";
$test_email = "ajudiyaharit75@gmail.com";
$test_subject = "Ceramic Store - Test Email";
$test_message = "<h2>Test Email from Ceramic Store</h2>";
$test_message .= "<p>If you received this, the email system is working!</p>";
$test_message .= "<p>OTP Code: <strong>" . $otp . "</strong></p>";

if (USE_SMTP) {
    $result = sendEmailViaSMTP($test_email, $test_subject, $test_message);
    if ($result) {
        echo "   ✓ SMTP Email sent successfully to: " . $test_email . "\n";
    } else {
        echo "   ✗ SMTP Email send failed\n";
    }
} else {
    echo "   SMTP is disabled. Testing PHP mail()...\n";
    $result = sendEmailViaPhpMail($test_email, $test_subject, $test_message);
    if ($result) {
        echo "   ✓ PHP mail() sent successfully\n";
    } else {
        echo "   ✗ PHP mail() failed\n";
    }
}

echo "\n=== Test Complete ===\n";
?>
