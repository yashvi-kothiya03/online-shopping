<?php
/**
 * Check PHP Extensions
 */

echo "=== PHP Extensions Check ===\n\n";

// Check OpenSSL
if (extension_loaded('openssl')) {
    echo "✓ OpenSSL: ENABLED\n";
    echo "  Version: " . OPENSSL_VERSION_TEXT . "\n";
} else {
    echo "✗ OpenSSL: NOT LOADED\n";
    echo "  You need to enable OpenSSL for SMTP to work\n";
}

// Check cURL
if (extension_loaded('curl')) {
    echo "✓ cURL: ENABLED\n";
} else {
    echo "✗ cURL: NOT LOADED\n";
}

// Check sockets
if (extension_loaded('sockets')) {
    echo "✓ Sockets: ENABLED\n";
} else {
    echo "✗ Sockets: NOT LOADED\n";
}

// Check if fsockopen is available
if (function_exists('fsockopen')) {
    echo "✓ fsockopen(): AVAILABLE\n";
} else {
    echo "✗ fsockopen(): NOT AVAILABLE\n";
}

// Check if stream functions available
if (function_exists('stream_socket_enable_crypto')) {
    echo "✓ stream_socket_enable_crypto(): AVAILABLE\n";
} else {
    echo "✗ stream_socket_enable_crypto(): NOT AVAILABLE\n";
}

echo "\n=== Mail Configuration ===\n";
echo "SMTP: " . ini_get('SMTP') . "\n";
echo "smtp_port: " . ini_get('smtp_port') . "\n";
echo "sendmail_path: " . ini_get('sendmail_path') . "\n";
echo "sendmail_from: " . ini_get('sendmail_from') . "\n";

?>
