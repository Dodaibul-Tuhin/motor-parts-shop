<?php
// ==========================================
// Database connection settings
// Change these to match your local MySQL setup
// ==========================================
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";          // default XAMPP password is empty
$DB_NAME = "motor_parts_shop";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Use utf8 for Bangla/English text support
$conn->set_charset("utf8mb4");
