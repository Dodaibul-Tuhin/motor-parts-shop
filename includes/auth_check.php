<?php
// Include this at the very top of every protected page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION["user_id"])) {
    header("Location: /motor-parts-shop/auth/login.php");
    exit;
}
