<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

require '../db.php';

// Fetch all results with student and exam details
$stmt = $conn->prepare("
    SELECT results.id, users.username AS student_name, exams.exam_name, results.score, results.submitted_at
    FROM results
    JOIN users ON results.student_id = users.id
    JOIN exams ON results.exam_id = exams.id
");
$stmt->execute();
$results = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Results</title>
    <link rel="stylesheet" href="../assets/css/view_results.css"> <!-- Link to CSS file -->
</head>
<body>
    <div class="container">
        <h1>View All Results</h1>
        <a href="dashboard.php" class="btn">Back to Dashboard</a>

        <!-- Results Table -->
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student Name</th>
                    <th>Exam Name</th>
                    <th>Score</th>
                    <th>Submitted At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                    <tr>
                        <td><?php echo $result['id']; ?></td>
                        <td><?php echo $result['student_name']; ?></td>
                        <td><?php echo $result['exam_name']; ?></td>
                        <td><?php echo $result['score']; ?></td>
                        <td><?php echo $result['submitted_at']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>