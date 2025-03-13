<?php
session_start();
require 'db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    try {
        // Get token details
        $stmt = $conn->prepare("SELECT * FROM email_verification_tokens WHERE BINARY token = ?");
        $stmt->execute([$token]);
        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($tokenData) {
            // Check expiration
            if (strtotime($tokenData['token_expires']) < time()) {
                $_SESSION['error'] = "Verification link expired!";
                header("Location: login.php");
                exit();
            }

            // Start transaction
            $conn->beginTransaction();

            // Mark user as verified
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ? LIMIT 1");
$stmt->execute([$tokenData['user_id']]);

if ($stmt->rowCount() === 0) {
    $_SESSION['error'] = "User verification failed. No matching user found.";
    header("Location: login.php");
    exit();
}


            // Delete the token
            $stmt = $conn->prepare("DELETE FROM email_verification_tokens WHERE id = ?");
            $stmt->execute([$tokenData['id']]);

            // Commit transaction
            $conn->commit();

            $_SESSION['success'] = "Email verified successfully! You can now login.";
        } else {
            $_SESSION['error'] = "Invalid verification link!";
        }
    } catch (PDOException $e) {
        // Rollback on error
        $conn->rollBack();
        $_SESSION['error'] = "Verification failed: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "No verification token provided!";
}

header("Location: login.php");
exit();
?>