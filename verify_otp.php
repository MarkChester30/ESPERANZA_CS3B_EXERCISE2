<?php
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $otp = trim($_POST['otp']);

    if (!$email || !preg_match('/^[0-9]{6}$/', $otp)) {
        header("Location: verify.php?email=" . urlencode($email) . "&error=Invalid input");
        exit();
    }

    try {
        // Verify OTP
        $sql = "SELECT * FROM user_s WHERE email = ? AND otp_code = ? AND otp_expiry > NOW() AND is_verified = 0";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->bind_param("ss", $email, $otp);
        
        if (!$stmt->execute()) {
            throw new Exception("Execution error: " . $stmt->error);
        }
        
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Mark as verified
            $update_sql = "UPDATE user_s SET is_verified = 1 WHERE email = ?";
            $update_stmt = $conn->prepare($update_sql);
            
            if (!$update_stmt) {
                throw new Exception("Database error: " . $conn->error);
            }
            
            $update_stmt->bind_param("s", $email);
            
            if (!$update_stmt->execute()) {
                throw new Exception("Update error: " . $update_stmt->error);
            }

            // Close statements
            $stmt->close();
            $update_stmt->close();
            
            // Redirect to success page
            header("Location: login.php?email=" . urlencode($email) . "&verified=1");
            exit();
        } else {
            $stmt->close();
            header("Location: verify.php?email=" . urlencode($email) . "&error=Invalid or expired OTP");
            exit();
        }
    } catch (Exception $e) {
        // Log the error (you might want to implement proper logging)
        error_log("OTP Verification Error: " . $e->getMessage());
        
        // Close any open statements
        if (isset($stmt)) $stmt->close();
        if (isset($update_stmt)) $update_stmt->close();
        
        header("Location: verify.php?email=" . urlencode($email) . "&error=An unexpected error occurred");
        exit();
    }
}

// Close the database connection
$conn->close();
?>