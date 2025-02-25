<?php
session_start();
require_once '../db.php';
require_once '../includes/function.php';

if ($_SESSION['role'] != 'teacher') {
    header("Location: ../index.php");
    exit();
}

$teacherId = $_SESSION['user_id'];
$searchTerm = isset($_GET['search']) ? "%{$_GET['search']}%" : '%%';

// Get individual results with search
$stmt = $conn->prepare("
    SELECT sd.full_name AS student_name,  -- Use full_name from student_details
           e.exam_name, 
           r.score, 
           r.submitted_at 
    FROM results r
    JOIN users u ON r.student_id = u.id
    JOIN student_details sd ON u.id = sd.user_id  -- Join student_details to get full_name
    JOIN exams e ON r.exam_id = e.id
    WHERE e.created_by = :teacher_id
    AND e.exam_name LIKE :search
");
$stmt->execute(['teacher_id' => $teacherId, 'search' => $searchTerm]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get average results
$avgStmt = $conn->prepare("
    SELECT sd.full_name AS student_name,  -- Use full_name from student_details
           AVG(r.score) as avg_score
    FROM results r
    JOIN users u ON r.student_id = u.id
    JOIN student_details sd ON u.id = sd.user_id  -- Join student_details to get full_name
    JOIN exams e ON r.exam_id = e.id
    WHERE e.created_by = :teacher_id
    GROUP BY u.id
");
$avgStmt->execute(['teacher_id' => $teacherId]);
$averageResults = $avgStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Results</title>
    <link rel="stylesheet" href="../assets/css/teacher_view_result.css">
</head>
<body>
    <div class="container">
        <h2>Exam Results</h2>
        
        <!-- Search Form -->
        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search by exam name..." 
                   value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button type="submit">Search</button>
        </form>

        <h3>Individual Results</h3>
        <table>
            <tr>
                <th>Student</th>
                <th>Exam</th>
                <th>Score</th>
                <th>Submitted Date</th>
            </tr>
            <?php foreach ($results as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['student_name']) ?></td>
                <td><?= htmlspecialchars($row['exam_name']) ?></td>
                <td><?= $row['score'] ?></td>
                <td><?= date('M j, Y H:i', strtotime($row['submitted_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h3>Average Results</h3>
        <table>
            <tr>
                <th>Student</th>
                <th>Average Score</th>
            </tr>
            <?php foreach ($averageResults as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['student_name']) ?></td>
                <td><?= number_format($row['avg_score'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>