<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('CERAMIC_ADMIN');
    session_start();
}
?>
