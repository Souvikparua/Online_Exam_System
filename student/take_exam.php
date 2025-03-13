<?php
session_start();
include('../db.php');
include('../includes/function.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Get student's grade
$stmt = $conn->prepare("SELECT grade FROM student_details WHERE user_id = ?");
$stmt->execute([$student_id]);
$student_grade = $stmt->fetchColumn();

// Fetch scheduled exams matching student's grade
$stmt = $conn->prepare("
    SELECT DISTINCT e.* 
    FROM exams e
    LEFT JOIN exam_attempts ea ON e.id = ea.exam_id AND ea.student_id = :student_id
    WHERE (ea.student_id IS NULL OR e.retake_allowed = 1)
    AND e.grade_level = :student_grade
");
$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
$stmt->bindParam(':student_grade', $student_grade, PDO::PARAM_INT);
$stmt->execute();
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Exam</title>
    <link rel="stylesheet" href="../assets/css/take_exam.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        function openExamPopup(examId) {
            const width = 900;
            const height = 600;
            const left = (window.screen.width - width) / 2;
            const top = (window.screen.height - height) / 2;

            const examWindow = window.open(`start_exam.php?exam_id=${examId}`, "ExamWindow",
                `width=${width},height=${height},top=${top},left=${left},resizable=no,scrollbars=yes`);

            if (!examWindow || examWindow.closed || typeof examWindow.closed == 'undefined') {
                alert("Popup blocked! Please allow popups for this site.");
            } else {
                examWindow.focus();
            }
        }

        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            const isDarkMode = document.body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDarkMode);
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (localStorage.getItem('darkMode') === 'true') {
                document.body.classList.add('dark-mode');
            }
        });
    </script>
</head>
<body>
    <button class="dark-mode-toggle" onclick="toggleDarkMode()">
        <i class="fas fa-moon"></i>
        <i class="fas fa-sun"></i>
    </button>

    <div class="container">
        <h1><i class="fas fa-pencil-alt"></i> Take Exam</h1>
        <?php if (count($exams) > 0): ?>
            <?php foreach ($exams as $exam): ?>
                <div class="exam">
                    <h2><i class="fas fa-file-alt"></i> <?php echo htmlspecialchars($exam['exam_name']); ?></h2>
                    <p><?php echo htmlspecialchars($exam['description']); ?></p>
                    <button type="button" onclick="openExamPopup(<?php echo htmlspecialchars($exam['id']); ?>)">
                        <i class="fas fa-play"></i> Start Exam
                    </button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p><i class="fas fa-info-circle"></i> No exams available at the moment.</p>
        <?php endif; ?>
        <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</body>
</html>
