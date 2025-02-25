<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

require '../db.php'; // Include the database connection

// Fetch total number of users
$stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users");
$stmt->execute();
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

// Fetch total number of exams
$stmt = $conn->prepare("SELECT COUNT(*) as total_exams FROM exams");
$stmt->execute();
$total_exams = $stmt->fetch(PDO::FETCH_ASSOC)['total_exams'];

// Fetch total number of results published
$stmt = $conn->prepare("SELECT COUNT(*) as total_results FROM results");
$stmt->execute();
$total_results = $stmt->fetch(PDO::FETCH_ASSOC)['total_results'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css"> <!-- Link to CSS file -->
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="manage_users.php">Manage Users</a></li>
            <li><a href="manage_exams.php">Manage Exams</a></li>
            <li><a href="view_results.php">View Results</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <h1>Welcome, Admin!</h1>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>

        <!-- Cards -->
        <div class="cards">
            <div class="card">
                <h3>Total Users</h3>
                <p><?php echo $total_users; ?></p>
            </div>
            <div class="card">
                <h3>Total Exams</h3>
                <p><?php echo $total_exams; ?></p>
            </div>
            <div class="card">
                <h3>Results Published</h3>
                <p><?php echo $total_results; ?></p>
            </div>
        </div>
    </div>
</body>
</html>