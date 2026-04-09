<?php

@include 'config.php';

if (isset($_GET['role']) && $_GET['role'] === 'admin') {
   @include 'session_admin.php';
} else {
   @include 'session_client.php';
}

$_SESSION = [];
if (session_status() === PHP_SESSION_ACTIVE) {
   session_unset();
   session_destroy();
}

header('location:login.php');
exit;

?>