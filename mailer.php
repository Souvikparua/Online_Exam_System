<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

function sendVerificationEmail($email, $token) {
    $mail = new PHPMailer(true);

    try {
        // Enable debugging
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) {
            file_put_contents('smtp_debug.log', "$level: $str\n", FILE_APPEND);
        };

        // Server settings for Gmail SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'abhishek.orijeen@gmail.com';
        $mail->Password   = 'oxnftigffaovpaar'; // No spaces
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('abhishek.orijeen@gmail.com', 'Online Exam System');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email Address - Online Exam System';
        $verificationLink = "http://localhost/Online_Exam_System/verify.php?token=$token";
        
        // Plain text version
        $mail->AltBody = "Verify your email: $verificationLink";
        
        // HTML version
        $mail->Body = "
            <html>
            <body>
                <h2>Email Verification</h2>
                <p>Click here to verify: <a href='$verificationLink'>Verify Email</a></p>
                <p>Or paste this URL: $verificationLink</p>
            </body>
            </html>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

function sendPasswordResetEmail($email, $token) {
    $mail = new PHPMailer(true);

    try {
        // Enable debugging
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) {
            file_put_contents('smtp_debug.log', "$level: $str\n", FILE_APPEND);
        };

        // Server settings for Gmail SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'abhishek.orijeen@gmail.com';
        $mail->Password   = 'oxnftigffaovpaar'; // No spaces
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('abhishek.orijeen@gmail.com', 'Online Exam System');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request - Online Exam System';
        $resetLink = "http://localhost/Online_Exam_System/reset_password.php?token=" . urlencode($token);
        
        // Plain text version
        $mail->AltBody = "Reset your password: $resetLink";
        
        // HTML version-v
        $mail->Body = "
            <html>
            <body>
                <h2>Password Reset</h2>
                <p>Click here to reset your password: <a href='$resetLink'>Reset Password</a></p>
                <p>Or paste this URL: $resetLink</p>
                <p>This link is valid for 1 hour.</p>
            </body>
            </html>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}