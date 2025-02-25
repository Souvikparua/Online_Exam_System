<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

require '../db.php';

// Add User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];
    $email = $_POST['email'];
    $phone = $_POST['phone']; // Get phone number from the form

    $stmt = $conn->prepare("INSERT INTO users (username, password, role, email, phone) VALUES (:username, :password, :role, :email, :phone)");
    $stmt->execute([
        'username' => $username,
        'password' => $password,
        'role' => $role,
        'email' => $email,
        'phone' => $phone // Add phone number to the query
    ]);
    echo "<div class='alert alert-success'>User added successfully!</div>";
}

// Delete User
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    echo "<div class='alert alert-success'>User deleted successfully!</div>";
}

// Fetch all users
$stmt = $conn->prepare("SELECT * FROM users");
$stmt->execute();
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../assets/css/manage_user.css"> <!-- Link to CSS file -->
</head>
<body>
    <div class="container">
        <h1>Manage Users</h1>
        <a href="dashboard.php" class="btn">Back to Dashboard</a>

        <!-- Add User Form -->
        <div class="form-container">
            <h2>Add User</h2>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <select name="role" required>
                    <option value="admin">Admin</option>
                    <option value="question_setter">Question Setter</option>
                    <option value="teacher">Teacher</option>
                    <option value="student">Student</option>
                </select>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="phone" placeholder="Phone Number" required> <!-- Phone number input -->
                <button type="submit" name="add_user" class="btn">Add User</button>
            </form>
        </div>

        <!-- User List -->
        <h2>User List</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Phone</th> <!-- New column for phone number -->
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo $user['username']; ?></td>
                        <td><?php echo $user['role']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo $user['phone']; ?></td> <!-- Display phone number -->
                        <td class="action-buttons">
                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn">Edit</a>
                            <a href="manage_users.php?delete_user=<?php echo $user['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>