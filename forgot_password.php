<?php
session_start();
require 'db.php';
require 'mailer.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // Check if user exists and is verified
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND is_verified = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Generate token
        $token = bin2hex(random_bytes(50));
        error_log("[DEBUG] Generated Token: $token");

        // Delete old tokens
        $conn->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?")->execute([$user['id']]);

        // Insert new token
        $stmt = $conn->prepare("INSERT INTO password_reset_tokens (user_id, token, token_expires) VALUES (?, ?, ?)");
        $expires = date('Y-m-d H:i:s', time() + 3600);
        $stmt->execute([$user['id'], $token, $expires]);
        error_log("[DEBUG] Stored Token: $token | Expires: $expires");

        // Send email
        if (sendPasswordResetEmail($email, $token)) {
            $message = "Password reset link sent to your email.";
        } else {
            $error = "Failed to send email. Contact support.";
        }
    } else {
        $error = "No verified account found with this email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="assets/css/Index.css">
</head>
<body>
    <div class="container">
        <h1>Reset Password</h1>
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Send Reset Link</button>
        </form>
        <p>Remember your password? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>