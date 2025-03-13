<?php
session_start();
require_once '../db.php';
require_once '../includes/function.php';

if ($_SESSION['role'] != 'teacher') {
    header("Location: ../login.php");
    exit();
}

// Fetch exam details
$examId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$exam = $conn->query("SELECT * FROM exams WHERE id = $examId")->fetch(PDO::FETCH_ASSOC);
if (!$exam) {
    die("Exam not found");
}

// Fetch existing questions
$examQuestions = $conn->query("SELECT * FROM exam_questions WHERE exam_id = $examId")->fetchAll(PDO::FETCH_ASSOC);
$questionIds = array_column($examQuestions, 'question_id');

// Handle form submission for editing the exam
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_exam'])) {
    $examName = sanitizeInput($_POST['exam_name']);
    $scheduleTime = $_POST['schedule_time'];
    $duration = (int)$_POST['duration'];
    $retakeAllowed = isset($_POST['retake_allowed']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;  // New active status
    
    // Update exam details
    $stmt = $conn->prepare("UPDATE exams SET exam_name=?, schedule_time=?, duration=?, retake_allowed=?, is_active=? WHERE id=?");
    $stmt->execute([$examName, $scheduleTime, $duration, $retakeAllowed, $isActive, $examId]);
    
    // Update exam questions
    $conn->query("DELETE FROM exam_questions WHERE exam_id = $examId");
    if (isset($_POST['questions'])) {
        foreach ($_POST['questions'] as $questionId => $data) {
            if (isset($data['selected'])) {
                $marks = (int)$data['marks'];
                $stmt = $conn->prepare("INSERT INTO exam_questions (exam_id, question_id, marks) VALUES (?, ?, ?)");
                $stmt->execute([$examId, $questionId, $marks]);
            }
        }
    }
    
    header("Location: edit_exam.php?id=$examId&success=1");
    exit();
}

// Fetch all available questions related to the exam's subject
$subjectId = $exam['subject_id'];
$questions = $conn->query("SELECT * FROM questions WHERE subject_id = $subjectId")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Exam</title>
    <link rel="stylesheet" href="../assets/css/teacher_man.css">
</head>
<body>
<div class="container">
    <a href="manage_exam.php" class="btn-back">Back to Dashboard</a>
    
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="success-message">
            Exam updated successfully!
        </div>
    <?php endif; ?>
    
    <h2>Edit Exam</h2>
    
    <form method="post" action="">
        <label>Exam Name:</label>
        <input type="text" name="exam_name" value="<?= htmlspecialchars($exam['exam_name']) ?>" required>
        
        <label>Schedule Time:</label>
        <input type="datetime-local" name="schedule_time" value="<?= date('Y-m-d\TH:i', strtotime($exam['schedule_time'])) ?>" required>
        
        <label>Duration (minutes):</label>
        <input type="number" name="duration" value="<?= $exam['duration'] ?>" required>
        
        <label>Allow Retake:</label>
        <input type="checkbox" name="retake_allowed" value="1" <?= $exam['retake_allowed'] ? 'checked' : '' ?>>
        
        <!-- Add Active Status Toggle -->
        <label>Active Status:</label>
        <input type="checkbox" name="is_active" value="1" <?= $exam['is_active'] ? 'checked' : '' ?>>
        
        <h3>Select Questions</h3>
        <?php foreach ($questions as $question): ?>
            <div>
                <input type="checkbox" name="questions[<?= $question['id'] ?>][selected]" <?= in_array($question['id'], $questionIds) ? 'checked' : '' ?>>
                <?= htmlspecialchars($question['question']) ?>
                <input type="number" name="questions[<?= $question['id'] ?>][marks]" value="1" min="1" required>
            </div>
        <?php endforeach; ?>
        
        <button type="submit" name="update_exam">Update Exam</button>
    </form>
</div>
</body>
</html>