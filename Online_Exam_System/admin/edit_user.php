<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

require '../db.php';

// Fetch user details
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        die("User not found!");
    }
} else {
    die("Invalid request!");
}

// Update User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $username = $_POST['username'];
    $role = $_POST['role'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];  // Get phone number from form

    $stmt = $conn->prepare("UPDATE users SET username = :username, role = :role, email = :email, phone = :phone WHERE id = :id");
    $stmt->execute([
        'username' => $username,
        'role' => $role,
        'email' => $email,
        'phone' => $phone,  // Add phone to query
        'id' => $user_id
    ]);
    echo "<div class='alert alert-success'>User updated successfully!</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="../assets/css/edit_user.css">
</head>
<body>
    <div class="container">
        <h1>Edit User</h1>
        <a href="manage_users.php">Back to Manage Users</a>

        <!-- Edit User Form -->
        <form method="POST">
            <input type="text" name="username" value="<?php echo $user['username']; ?>" required>
            <select name="role" required>
                <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                <option value="question_setter" <?php echo ($user['role'] == 'question_setter') ? 'selected' : ''; ?>>Question Setter</option>
                <option value="teacher" <?php echo ($user['role'] == 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                <option value="student" <?php echo ($user['role'] == 'student') ? 'selected' : ''; ?>>Student</option>
            </select>
            <input type="email" name="email" value="<?php echo $user['email']; ?>" required>
            <!-- Add Phone Number Input -->
            <input type="text" name="phone" placeholder="Phone Number" 
                   value="<?php echo $user['phone']; ?>" required>
            <button type="submit" name="update_user">Update User</button>
        </form>
    </div>
</body>
</html>