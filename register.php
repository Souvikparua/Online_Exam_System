<?php
session_start();
require 'db.php'; // Include your database connection
require 'mailer.php'; // Include the mailer function

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle CSRF token validation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }
}

// Handle Registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    // Sanitize inputs
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $grade = isset($_POST['grade']) ? $_POST['grade'] : null;

    // Start database transaction
    $conn->beginTransaction();

    try {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            throw new Exception("Username already exists!");
        }

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, password, role, email, phone, grade, is_verified) 
                              VALUES (?, ?, 'student', ?, ?, ?, 0)");
        $stmt->execute([$username, $password, $email, $phone, $grade]);
        $user_id = $conn->lastInsertId();

        // Generate verification token
        $token = bin2hex(random_bytes(50));
        $expires = date('Y-m-d H:i:s', time() + 86400); // 24 hours

        // Insert token into email_verification_tokens
        $stmt = $conn->prepare("INSERT INTO email_verification_tokens (user_id, token, token_expires) 
                               VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $token, $expires]);

        // Send verification email
        if (sendVerificationEmail($email, $token)) {
            $conn->commit(); // Commit transaction if email is sent
            $register_success = "Registration successful! Please check your email to verify your account.";
        } else {
            throw new Exception("Failed to send verification email. Please contact support.");
        }

    } catch (Exception $e) {
        $conn->rollBack(); // Rollback on error
        $register_error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Online Exam System</title>
    <link rel="stylesheet" href="assets/css/Index.css">
    <!-- Added background and form styling -->
    <style>
        body {
            background-image: url('assets/images/bg.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin: auto;
            width: 90%;
            max-width: 500px;
        }
        .form-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .form-container input, .form-container select, .form-container button {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
        }
        .form-container button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #0056b3;
        }
        .alert {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        .alert-error {
            background-color: #ffebee;
            color: #c62828;
        }
        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        #grade {
            margin-top: 10px;
            width: 100%;
            padding: 8px;
        }
    </style>
    <script>
        function toggleGradeDropdown() {
            var checkbox = document.getElementById('registerAsStudent');
            var gradeDropdown = document.getElementById('grade');
            gradeDropdown.disabled = !checkbox.checked;
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Welcome to the Online Exam System</h1>

        <!-- Display Messages -->
        <?php if (isset($register_error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($register_error) ?></div>
        <?php endif; ?>
        <?php if (isset($register_success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($register_success) ?></div>
        <?php endif; ?>

        <!-- Registration Form -->
        <div class="form-container">
            <h2>Register</h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="phone" placeholder="Mobile Number" required>
                
                <label>
                    <input type="checkbox" id="registerAsStudent" name="registerAsStudent" onclick="toggleGradeDropdown()"> Register as a student
                </label>
                
                <select id="grade" name="grade" disabled required>
                    <option value="">Select Grade</option>
                    <option value="6">Grade 6</option>
                    <option value="7">Grade 7</option>
                    <option value="8">Grade 8</option>
                    <option value="9">Grade 9</option>
                    <option value="10">Grade 10</option>
                    <option value="11">Grade 11</option>
                    <option value="12">Grade 12</option>
                </select>
                
                <button type="submit" name="register">Register</button>
            </form>
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>