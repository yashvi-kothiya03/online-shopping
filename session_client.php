<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('CERAMIC_CLIENT');
    session_start();
}
?>
