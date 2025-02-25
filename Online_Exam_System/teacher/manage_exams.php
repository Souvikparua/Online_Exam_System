<?php
session_start();
require_once '../db.php';
require_once '../includes/function.php';

if ($_SESSION['role'] != 'teacher') {
    header("Location: ../index.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $examName = sanitizeInput($_POST['exam_name']);
    $scheduleTime = $_POST['schedule_time'];
    $duration = (int)$_POST['duration'];
    $retakeAllowed = isset($_POST['retake_allowed']) ? 1 : 0;
    $teacherId = $_SESSION['user_id'];

    try {
        // Insert exam
        $stmt = $conn->prepare("INSERT INTO exams (exam_name, created_by, schedule_time, duration, retake_allowed) VALUES (?, ?, ?, ?, ?)");
        $stmt->bindValue(1, $examName, PDO::PARAM_STR);
        $stmt->bindValue(2, $teacherId, PDO::PARAM_INT);
        $stmt->bindValue(3, $scheduleTime, PDO::PARAM_STR);
        $stmt->bindValue(4, $duration, PDO::PARAM_INT);
        $stmt->bindValue(5, $retakeAllowed, PDO::PARAM_INT);
        $stmt->execute();
        $examId = $conn->lastInsertId();

        // Insert exam questions
        foreach ($_POST['questions'] as $questionId => $data) {
            if (isset($data['selected'])) {  // Check if the question is selected
                $marks = (int)$data['marks'];
                $modifiedText = sanitizeInput($data['modified_text']);

                $stmt = $conn->prepare("INSERT INTO exam_questions (exam_id, question_id, modified_text, marks) VALUES (?, ?, ?, ?)");
                $stmt->bindValue(1, $examId, PDO::PARAM_INT);
                $stmt->bindValue(2, $questionId, PDO::PARAM_INT);
                $stmt->bindValue(3, $modifiedText, PDO::PARAM_STR);
                $stmt->bindValue(4, $marks, PDO::PARAM_INT);
                $stmt->execute();
            }
        }

        // Redirect with success query parameter
        header("Location: manage_exams.php?success=1");
        exit();
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}

// Get available questions from question setters
$questions = $conn->query("
    SELECT q.id, q.question AS question_text
    FROM questions q
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Exams</title>
    <link rel="stylesheet" href="../assets/css/teacher_man.css">
    <script>
        // Check for success query parameter and show alert
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success') && urlParams.get('success') === '1') {
                alert("Exam created successfully!");
            }
        };
    </script>
</head>
<body>
    <div class="container">
        <a href="../teacher/dashboard.php">
            <button>Back To Dashboard</button>
        </a>
        <h2>Create New Exam</h2>
        <form method="post">
            <div>
                <label>Exam Name:</label>
                <input type="text" name="exam_name" required>
            </div>
            <div>
                <label>Schedule Time:</label>
                <input type="datetime-local" name="schedule_time" required>
            </div>
            <div>
                <label>Duration (minutes):</label>
                <input type="number" name="duration" required>
            </div>
            <div>
                <label>Allow Retake:</label>
                <input type="checkbox" name="retake_allowed" value="1">
            </div>

            <h3>Select Questions</h3>
            <?php while ($question = $questions->fetch(PDO::FETCH_ASSOC)): ?>
            <div class="question">
                <input type="checkbox" name="questions[<?= $question['id'] ?>][selected]">
                <div class="question-text">
                    <textarea name="questions[<?= $question['id'] ?>][modified_text]">
                        <?= htmlspecialchars($question['question_text']) ?>
                    </textarea>
                </div>
                <div class="marks">
                    Marks: <input type="number" 
                           name="questions[<?= $question['id'] ?>][marks]" 
                           value="<?= $question['default_marks'] ?? 1 ?>" 
                           min="1" required>
                </div>
            </div>
            <?php endwhile; ?>
            
            <button type="submit">Create Exam</button>
        </form>
    </div>
</body>
</html>
