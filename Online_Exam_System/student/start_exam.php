<?php
error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', 1); // Display errors to the browser
session_start();
include('../db.php');
include('../includes/function.php');

if (!$conn) {
    die("Database connection failed: " . print_r($conn->errorInfo(), true));
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Ensure exam_id is provided and valid
if (!isset($_GET['exam_id']) || !is_numeric($_GET['exam_id'])) {
    die("Invalid exam ID.");
}

$exam_id = $_GET['exam_id'];

// Fetch exam details
$stmt = $conn->prepare("SELECT * FROM exams WHERE id = :exam_id");
$stmt->bindParam(':exam_id', $exam_id, PDO::PARAM_INT);
$stmt->execute();
$exam = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$exam) {
    die("Exam not found. Exam ID: " . $exam_id);
}

// Fetch questions for the exam
$stmt = $conn->prepare("
    SELECT q.* 
    FROM questions q
    INNER JOIN exam_questions eq ON q.id = eq.question_id
    WHERE eq.exam_id = :exam_id
");
$stmt->bindParam(':exam_id', $exam_id, PDO::PARAM_INT);
$stmt->execute();
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($questions) === 0) {
    die("No questions found for this exam. Exam ID: " . $exam_id);
}

// Debug: Print the questions

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Start Exam: <?php echo htmlspecialchars($exam['exam_name']); ?></title>
    <link rel="stylesheet" href="../assets/css/start_exam.css">
    <script>
        let isActive = true;

// Tab detection
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
    }, 10000); // 10 seconds
};

// Timer functionality
document.addEventListener('DOMContentLoaded', function () {
let timeLeft = <?php echo $exam['duration'] * 60; ?>; // Convert minutes to seconds
const timer = document.getElementById('timer');

function updateTimer() {
const minutes = Math.floor(timeLeft / 60);
const seconds = timeLeft % 60;
timer.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;

if (timeLeft <= 0) {
    clearInterval(timerInterval); // Stop the timer
    alert("Time's up! Your exam will be auto-submitted.");
    document.getElementById("examForm").submit();
}
timeLeft--; // Decrease time
}

// Start the timer and update every second
updateTimer(); // Call once to set initial time
const timerInterval = setInterval(updateTimer, 1000);
});
    </script>
</head>
<body>
    <div class="container">
        <h1>Start Exam: <?php echo htmlspecialchars($exam['exam_name']); ?></h1>
        <p>Time Remaining: <span id="timer"><?php echo floor($exam['duration']); ?>:00</span></p>
        <form id="examForm" action="submit_exam.php" method="POST">
            <input type="hidden" name="exam_id" value="<?php echo htmlspecialchars($exam_id); ?>">
            <?php foreach ($questions as $question): ?>
                <div class="question">
                    <h3><?php echo htmlspecialchars($question['question']); ?></h3>
                    <?php if ($question['question_image']): ?>
                        <img src="../question_setter/<?php echo htmlspecialchars($question['question_image']); ?>" alt="Question Image" style="max-width: 100%;">
                    <?php endif; ?>
                    <ul>
                        <?php
                        $options = json_decode($question['options'], true);
                        foreach ($options as $index => $option):
                        ?>
                            <li>
                                <input type="radio" 
                                       name="answer_<?php echo htmlspecialchars($question['id']); ?>" 
                                       value="<?php echo $index + 1; ?>" 
                                       required>
                                <?php echo htmlspecialchars($option['text']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
            <button type="submit">Submit Exam</button>
        </form>
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>