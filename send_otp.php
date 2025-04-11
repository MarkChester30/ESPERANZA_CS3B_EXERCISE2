<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    if (!$email) {
        die("Invalid email format. Please enter a valid email address.");
    }

    // Generate OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $otp_expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

    try {
        // Clear existing OTP
        $clear_sql = "DELETE FROM user_s WHERE email = ?";
        $clear_stmt = $conn->prepare($clear_sql);
        if (!$clear_stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        $clear_stmt->bind_param("s", $email);
        $clear_stmt->execute();

        // Insert new OTP
        $sql = "INSERT INTO user_s (email, otp_code, otp_expiry) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        $stmt->bind_param("sss", $email, $otp, $otp_expiry);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to store OTP: " . $stmt->error);
        }

        // Send email
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'markchesteresperanza321@gmail.com';
        $mail->Password = 'iilj lmen zyul lsow';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('markchesteresperanza321@gmail.com', 'OTP Verification');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body = "
            <h2>OTP Verification</h2>
            <p>Your OTP code is: <b style='font-size: 20px;'>{$otp}</b></p>
            <p>This code will expire in 15 minutes.</p>
            <p>If you didn't request this code, please ignore this email.</p>
        ";

        $mail->send();
        
        // Redirect to verify page
        header("Location: verify.php?email=" . urlencode($email));
        exit();

    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>