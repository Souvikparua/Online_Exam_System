<?php
session_start();
include('../db.php');
include('../includes/function.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch scheduled exams
$stmt = $conn->prepare("
    SELECT DISTINCT e.* 
    FROM exams e
    LEFT JOIN exam_attempts ea ON e.id = ea.exam_id AND ea.student_id = :student_id
    WHERE (ea.student_id IS NULL OR e.retake_allowed = 1)
");
$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
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
        let isActive = true;

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

        window.onfocus = function () {
            isActive = true;
        };

        window.onblur = function () {
            isActive = false;
            setTimeout(() => {
                if (!isActive) {
                    alert("You switched tabs or minimized the browser. Your exam will be auto-submitted.");
                    document.getElementById("examForm").submit();
                }
            }, 10000);
        };

        document.getElementById('examForm')?.addEventListener('submit', () => {
            document.getElementById('loading').style.display = 'flex';
        });
    </script>
</head>
<body>
    <button class="dark-mode-toggle" onclick="toggleDarkMode()">
        <i class="fas fa-moon"></i>
        <i class="fas fa-sun"></i>
    </button>

    <div id="loading">
        <i class="fas fa-spinner fa-spin"></i>
        <span>Loading Exam...</span>
    </div>

    <div class="container">
        <h1><i class="fas fa-pencil-alt"></i> Take Exam</h1>
        <?php if (count($exams) > 0): ?>
            <form id="examForm" action="start_exam.php" method="GET">
    <?php foreach ($exams as $exam): ?>
        <div class="exam">
            <h2><i class="fas fa-file-alt"></i> <?php echo htmlspecialchars($exam['exam_name']); ?></h2>
            <p><?php echo htmlspecialchars($exam['description']); ?></p>
            <button type="submit" name="exam_id" value="<?php echo htmlspecialchars($exam['id']); ?>">
                <i class="fas fa-play"></i> Start Exam
            </button>
        </div>
    <?php endforeach; ?>
</form>
        <?php else: ?>
            <p><i class="fas fa-info-circle"></i> No exams available at the moment.</p>
        <?php endif; ?>
        <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</body>
</html>
