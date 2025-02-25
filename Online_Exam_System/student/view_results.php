<?php
session_start();
include('../db.php');
include('../includes/function.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch exam attempts with correct/wrong counts
$stmt = $conn->prepare("
    SELECT 
        ea.id AS attempt_id,
        ea.score, 
        e.exam_name, 
        ea.attempt_date AS submitted_at,
        SUM(er.is_correct) AS correct_count,
        COUNT(er.id) - SUM(er.is_correct) AS wrong_count
    FROM exam_attempts ea 
    JOIN exams e ON ea.exam_id = e.id 
    LEFT JOIN exam_results er ON ea.id = er.exam_attempt_id 
    WHERE ea.student_id = :student_id
    GROUP BY ea.id
");
$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Results</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Exam Results</h1>
        <?php if (count($results) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Exam Name</th>
                        <th>Score</th>
                        <th>Submitted At</th>
                        <th>Correct</th>
                        <th>Wrong</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $result): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($result['exam_name']); ?></td>
                            <td><?php echo htmlspecialchars($result['score']); ?></td>
                            <td><?php echo htmlspecialchars($result['submitted_at']); ?></td>
                            <td><?php echo $result['correct_count'] ?? 0; ?></td>
                            <td><?php echo $result['wrong_count'] ?? 0; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No results found.</p>
        <?php endif; ?>
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>