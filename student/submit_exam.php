<?php
session_start();
include('../db.php');
include('../includes/function.php');

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$exam_id = $_POST['exam_id'];

// Prevent resubmission if already completed
if (isset($_SESSION['completed_exams'][$exam_id])) {
    $_SESSION['error'] = "You have already submitted this exam.";
    header("Location: dashboard.php");
    exit();
}

try {
    $stmt = $conn->prepare("SELECT id, retake_allowed FROM exams WHERE id = :exam_id AND deleted_at IS NULL");
    $stmt->bindParam(':exam_id', $exam_id, PDO::PARAM_INT);
    $stmt->execute();
    $exam = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$exam) {
        throw new Exception("Exam not found or has been deleted");
    }

    if ($exam['retake_allowed'] == 0) {
        $stmt = $conn->prepare("SELECT id FROM exam_attempts WHERE student_id = :student_id AND exam_id = :exam_id LIMIT 1");
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $stmt->bindParam(':exam_id', $exam_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "This exam can only be attempted once";
            header("Location: dashboard.php");
            exit();
        }
    }

    // Start Transaction
    $conn->beginTransaction();

    $stmt = $conn->prepare("INSERT INTO exam_attempts (student_id, exam_id, score, status) VALUES (:student_id, :exam_id, 0, 'completed')");
    $stmt->execute([
        ':student_id' => $student_id,
        ':exam_id' => $exam_id
    ]);
    $exam_attempt_id = $conn->lastInsertId();

    if (!$exam_attempt_id) {
        throw new Exception("Failed to record exam attempt");
    }

    $stmt = $conn->prepare("SELECT q.*, eq.marks FROM questions q JOIN exam_questions eq ON q.id = eq.question_id WHERE eq.exam_id = :exam_id");
    $stmt->bindParam(':exam_id', $exam_id, PDO::PARAM_INT);
    $stmt->execute();
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $score = 0;
    foreach ($questions as $question) {
        $question_id = $question['id'];
        $question_marks = (float)$question['marks'];
        
        // Get and decode student's answer
        $student_answer_json = $_POST['answer_' . $question_id] ?? '[]';
        $student_answers = json_decode($student_answer_json, true) ?: [];
        
        // Get correct answers
        $options = json_decode($question['options'], true);
        $correct_answers = array_map(
            fn($opt) => trim((string)$opt['text']), 
            array_filter($options, fn($opt) => ($opt['is_correct'] ?? 0) == 1)
        );

        // Normalize arrays for comparison
        sort($student_answers);
        sort($correct_answers);
        
        // Check if answers match (order insensitive)
        $is_correct = ($student_answers === $correct_answers) ? 1 : 0;
        $marks_obtained = $is_correct ? $question_marks : 0;
        $score += $marks_obtained;

        // Store answers as comma-separated string
        $selected_answer_str = empty($student_answers) ? 'NO_ANSWER' : implode(', ', $student_answers);

        $stmt = $conn->prepare("INSERT INTO exam_results (exam_attempt_id, question_id, selected_answer, is_correct, marks_obtained) VALUES (:attempt_id, :qid, :answer, :correct, :marks)");
        $stmt->execute([
            ':attempt_id' => $exam_attempt_id,
            ':qid' => $question_id,
            ':answer' => $selected_answer_str,
            ':correct' => $is_correct,
            ':marks' => $marks_obtained
        ]);
    }

    $stmt = $conn->prepare("UPDATE exam_attempts SET score = :score WHERE id = :attempt_id");
    $stmt->execute([
        ':score' => $score,
        ':attempt_id' => $exam_attempt_id
    ]);

    $conn->commit();

    // Prevent back navigation
    $_SESSION['exam_submitted'] = true;
    $_SESSION['completed_exams'][$exam_id] = true;

    header("Location: view_single_result.php?attempt_id=" . $exam_attempt_id);
    exit();

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Exam submission error: " . $e->getMessage());
    $_SESSION['error'] = "Failed to submit exam: " . $e->getMessage();
    header("Location: take_exam.php?exam_id=" . $exam_id);
    exit();
}
?>