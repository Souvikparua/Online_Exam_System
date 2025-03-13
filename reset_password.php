<?php
session_start();
require 'db.php';

$error = '';
$success = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Validate token
    $stmt = $conn->prepare("SELECT * FROM password_reset_tokens WHERE token = ?");
    $stmt->execute([$token]);
    $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($tokenData) {
        $currentTime = date('Y-m-d H:i:s');
        error_log("Token Expires: {$tokenData['token_expires']}, Current Time: $currentTime");

        if (strtotime($tokenData['token_expires']) > time()) {
            // Token is valid
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $newPassword = $_POST['password'];
                $confirmPassword = $_POST['confirm_password'];

                if ($newPassword !== $confirmPassword) {
                    $error = "Passwords do not match.";
                } else {
                    // Hash the new password
                    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

                    // Update the user's password
                    $conn->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashedPassword, $tokenData['user_id']]);

                    // Delete the used token
                    $conn->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?")->execute([$tokenData['user_id']]);

                    $success = "Password updated successfully! <a href='login.php'>Login here</a>";
                }
            }
        } else {
            $error = "Token has expired.";
        }
    } else {
        $error = "Invalid token.";
    }
} else {
    $error = "Invalid request.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="assets/css/Index.css">
</head>
<body>
    <div class="container">
        <h1>Set New Password</h1>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php else: ?>
            <?php if (isset($tokenData) && strtotime($tokenData['token_expires']) > time()): ?>
                <form method="POST">
                    <input type="password" name="password" placeholder="New Password" required>
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                    <button type="submit">Reset Password</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>