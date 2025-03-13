<?php
require 'mailer.php';

$email = 'paruasouvik7@gmail.com'; // Replace with a valid email
$token = bin2hex(random_bytes(50)); // Generate a random token

if (sendVerificationEmail($email, $token)) {
    echo "Email sent successfully! Check smtp_debug.log for details.";
} else {
    echo "Failed to send email. Check smtp_debug.log for errors.";
}
?>