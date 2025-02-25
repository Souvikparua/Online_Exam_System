<?php
session_start();
require_once '../db.php';
require_once '../includes/function.php';

if ($_SESSION['role'] != 'teacher') {
    header("Location: ../index.php");
    exit();
}

$teacherId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="../assets/css/teacher_dash.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="glass-card">
            <h1>Welcome, Teacher!</h1>
            <nav class="dashboard-nav">
                <a href="manage_exams.php" class="nav-link">Manage Exams</a>
                <a href="manage_students.php" class="nav-link">Manage Students</a>
                <a href="view_results.php" class="nav-link">View Results</a>
                <a href="../logout.php" class="nav-link logout">Logout</a>
            </nav>
        </div>
    </div>
</body>
</html>