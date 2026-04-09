<?php
require_once 'email_functions.php';

$to = 'ajudiyaharit75@gmail.com';
$subject = 'Manual SMTP Unit Test';
$body = '<h3>This is a manual SMTP test at ' . date('Y-m-d H:i:s') . '</h3>';

$result = sendEmailViaSMTP($to, $subject, $body);
echo 'SMTP send result: ' . ($result ? 'Success' : 'Failure') . "\n";

?>