<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "user_verification";

// Create connection without selecting database first
$conn = new mysqli($host, $user, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if (!$conn->query($sql)) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($database);

// Create table if not exists
$table_sql = "CREATE TABLE IF NOT EXISTS user_s (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    otp_code VARCHAR(6) NOT NULL,
    otp_expiry DATETIME NOT NULL,
    is_verified TINYINT(1) DEFAULT 0,
    UNIQUE KEY (email)
)";

if (!$conn->query($table_sql)) {
    die("Error creating table: " . $conn->error);
}

// Set timezone
date_default_timezone_set('Asia/Manila');
?>