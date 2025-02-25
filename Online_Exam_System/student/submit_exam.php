<?php
session_start();
include('../db.php');
include('../includes/function.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$exam_id = $_POST['exam_id'];

// Fetch questions for the exam
$stmt = $conn->prepare("
    SELECT q.* 
    FROM questions q
    JOIN exam_questions eq ON q.id = eq.question_id
    WHERE eq.exam_id = :exam_id
");
$stmt->bindParam(':exam_id', $exam_id, PDO::PARAM_INT);
$stmt->execute();
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$score = 0;

// Insert exam attempt
$stmt = $conn->prepare("
    INSERT INTO exam_attempts (student_id, exam_id, score) 
    VALUES (:student_id, :exam_id, :score)
");
$stmt->bindParam(':student_id', $student_id);
$stmt->bindParam(':exam_id', $exam_id);
$stmt->bindParam(':score', $score);
$stmt->execute();

$exam_attempt_id = $conn->lastInsertId();

foreach ($questions as $question) {
    $question_id = $question['id'];
    $student_answer = trim($_POST['answer' . $question_id] ?? ''); // Trim whitespace
    echo "Question ID: $question_id, Student Answer: '$student_answer'<br>"; // Debugging

    // Parse options JSON to find correct answers
    $options = json_decode($question['options'], true);
    $correct_answers = [];
    foreach ($options as $option) {
        if ($option['is_correct'] == 1) {
            $correct_answers[] = trim($option['text']); // Trim whitespace
        }
    }
    echo "Correct Answers: " . implode(", ", $correct_answers) . "<br>"; // Debugging

    // Check correctness
    if ($question['is_multiple_answer']) {
        $student_answers = $_POST['answer' . $question_id] ?? []; // Array of selected answers
        $is_correct = (count(array_diff($student_answers, $correct_answers)) == 0 && 
                       count(array_diff($correct_answers, $student_answers)) == 0) ? 1 : 0;
    } else {
        $is_correct = in_array($student_answer, $correct_answers) ? 1 : 0;
    }
    echo "Is Correct: $is_correct<br><br>"; // Debugging

    // Insert into exam_results
    $stmt = $conn->prepare("
        INSERT INTO exam_results (exam_attempt_id, question_id, selected_answer, is_correct, marks_obtained)
        VALUES (:exam_attempt_id, :question_id, :selected_answer, :is_correct, :marks_obtained)
    ");
    $marks_obtained = $is_correct ? 1 : 0;
    $stmt->bindParam(':exam_attempt_id', $exam_attempt_id);
    $stmt->bindParam(':question_id', $question_id);
    $stmt->bindParam(':selected_answer', $student_answer);
    $stmt->bindParam(':is_correct', $is_correct);
    $stmt->bindParam(':marks_obtained', $marks_obtained);
    $stmt->execute();

    if ($is_correct) {
        $score++;
    }
}

// Update exam attempt with final score
$stmt = $conn->prepare("
    UPDATE exam_attempts 
    SET score = :score 
    WHERE id = :exam_attempt_id
");
$stmt->bindParam(':score', $score);
$stmt->bindParam(':exam_attempt_id', $exam_attempt_id);
$stmt->execute();

header("Location: view_results.php");
exit();
?>