<?php
session_start();
include('../db.php');
include('../includes/function.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Modified query to include total marks calculation
$stmt = $conn->prepare("
    SELECT 
        ea.id AS attempt_id,
        ea.score, 
        e.exam_name, 
        ea.attempt_date AS submitted_at,
        SUM(er.marks_obtained) AS total_marks_obtained,
        SUM(er.is_correct) AS correct_count,
        COUNT(er.id) - SUM(er.is_correct) AS wrong_count,
        (SELECT SUM(marks) FROM exam_questions WHERE exam_id = e.id) AS total_marks
    FROM exam_attempts ea 
    JOIN exams e ON ea.exam_id = e.id 
    LEFT JOIN exam_results er ON ea.id = er.exam_attempt_id 
    WHERE ea.student_id = :student_id
    GROUP BY ea.id, e.id
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
                        <th>Percentage</th>
                        <th>Submitted At</th>
                        <th>Correct</th>
                        <th>Wrong</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $result): 
                        $percentage = 0;
                        if ($result['total_marks'] > 0) {
                            $percentage = ($result['total_marks_obtained'] / $result['total_marks']) * 100;
                        }
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($result['exam_name']) ?></td>
                            <td><?= htmlspecialchars($result['total_marks_obtained']) ?> / <?= $result['total_marks'] ?></td>
                            <td><?= number_format($percentage, 2) ?>%</td>
                            <td><?= htmlspecialchars($result['submitted_at']) ?></td>
                            <td><?= $result['correct_count'] ?? 0 ?></td>
                            <td><?= $result['wrong_count'] ?? 0 ?></td>
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