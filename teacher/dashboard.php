<?php
session_start();
require_once '../db.php';
require_once '../includes/function.php';

if ($_SESSION['role'] != 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacherId = $_SESSION['user_id'];

// Fetch teacher's name from teacher_details
$sql = "SELECT full_name FROM teacher_details WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$teacherId]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);
$teacherName = $teacher['full_name'] ?? 'Teacher'; // Fallback if name not found
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
            <h1>Welcome, <?php echo htmlspecialchars($teacherName); ?>!</h1>
            <nav class="dashboard-nav">
                <a href="add_exam.php" class="nav-link">Add Exam</a>
                <a href="manage_exam.php" class="nav-link">Manage Exam</a>
                <a href="view_results.php" class="nav-link">View Results</a>
                <a href="../logout.php" class="nav-link logout">Logout</a>
            </nav>
        </div>
    </div>
</body>
</html>