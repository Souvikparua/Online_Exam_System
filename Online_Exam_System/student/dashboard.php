<?php
session_start();
include('../db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch student details
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

// Fetch attended exams count
$stmt = $pdo->prepare("SELECT COUNT(*) as attended FROM exam_attempts WHERE student_id = ?");
$stmt->execute([$student_id]);
$attended_exams = $stmt->fetch()['attended'];

// Fetch total exams count
$total_exams = $pdo->query("SELECT COUNT(*) as total FROM exams")->fetch()['total'];

// Calculate pending exams and ensure it's not negative
$pending_exams = max(0, $total_exams - $attended_exams);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../assets/css/student_dashboard.css">
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($student['username']); ?></h1>
        <p>Email: <?php echo htmlspecialchars($student['email']); ?></p>
        <p>Exams Attended: <?php echo $attended_exams; ?></p>
        <p>Exams Pending: <?php echo $pending_exams; ?></p>
        <a href="take_exam.php">Take Exam</a>
        <a href="view_results.php">View Results</a>
        <a href="../logout.php">Logout</a>
    </div>
</body>
</html>
