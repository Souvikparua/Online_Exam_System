<?php
// File: index.php

session_start(); // Start the session
require 'db.php'; // Include the database connection

// Handle Login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch user from the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Login successful
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        
        // Redirect to the appropriate dashboard
        header("Location: " . $user['role'] . "/dashboard.php");
        exit();
    } else {
        $login_error = "Invalid username or password!";
    }
}

// Handle Registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    
    $role = 'student'; // Default role for new users
    $email = $_POST['email'];
    $phone = $_POST['phone']; // New mobile number field
    $grade = isset($_POST['grade']) ? $_POST['grade'] : null; // New grade field

    // Check if the username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    if ($stmt->fetch()) {
        $register_error = "Username already exists!";
    } else {
        // Insert new user into the database
        $stmt = $conn->prepare("INSERT INTO users (username, password, role, email, phone, grade) VALUES (:username, :password, :role, :email, :phone, :grade)");
        $stmt->execute([
            'username' => $username,
            'password' => $password,
            'role' => $role,
            'email' => $email,
            'phone' => $phone,
            'grade' => $grade
        ]);
        $register_success = "Registration successful! Please login.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Exam System</title>
    <link rel="stylesheet" href="assets/css/Index.css"> <!-- Link to CSS file -->
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

        <!-- Login Form -->
        <div class="form-container">
            <h2>Login</h2>
            <?php if (isset($login_error)): ?>
                <div class="alert alert-error"><?php echo $login_error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
            </form>
        </div>

        <!-- Registration Form -->
        <div class="form-container">
            <h2>Register</h2>
            <?php if (isset($register_error)): ?>
                <div class="alert alert-error"><?php echo $register_error; ?></div>
            <?php endif; ?>
            <?php if (isset($register_success)): ?>
                <div class="alert alert-success"><?php echo $register_success; ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="phone" placeholder="Mobile Number" required> <!-- New mobile number field -->
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
        </div>
    </div>
</body>
</html>