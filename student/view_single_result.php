<?php
session_start();
include('../db.php');
include('../includes/function.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$attempt_id = isset($_GET['attempt_id']) ? intval($_GET['attempt_id']) : 0;

// Fetch exam attempt details WITH DYNAMIC TOTAL MARKS CALCULATION
$stmt = $conn->prepare("
    SELECT 
        ea.id AS attempt_id,
        e.exam_name,
        ea.score AS total_score,
        ea.attempt_date AS submitted_at,
        (SELECT SUM(marks) FROM exam_questions WHERE exam_id = e.id) AS exam_total_marks
    FROM exam_attempts ea
    JOIN exams e ON ea.exam_id = e.id
    WHERE ea.id = :attempt_id AND ea.student_id = :student_id
");
$stmt->bindParam(':attempt_id', $attempt_id, PDO::PARAM_INT);
$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
$stmt->execute();
$attempt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$attempt) {
    die("Attempt not found or access denied.");
}

// Calculate percentage
$percentage = 0.00;
if ($attempt['exam_total_marks'] > 0) {
    $percentage = ($attempt['total_score'] / $attempt['exam_total_marks']) * 100;
    $percentage = number_format($percentage, 2);
}

// Fetch detailed question results with solution PDF
$stmt = $conn->prepare("
    SELECT 
        er.question_id,
        eq.modified_text AS question_text,
        er.selected_answer,
        er.is_correct,
        er.marks_obtained,
        eq.marks AS question_marks,
        q.options AS correct_options,
        q.solution_pdf
    FROM exam_results er
    JOIN exam_questions eq ON er.question_id = eq.question_id AND eq.exam_id = (
        SELECT exam_id FROM exam_attempts WHERE id = :attempt_id
    )
    JOIN questions q ON er.question_id = q.id
    WHERE er.exam_attempt_id = :attempt_id
");
$stmt->bindParam(':attempt_id', $attempt_id, PDO::PARAM_INT);
$stmt->execute();
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Result Details</title>
    <link rel="stylesheet" href="../assets/css/view_single_result.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($attempt['exam_name']); ?> Result</h1>
        <p>Total Score: <?php echo $attempt['total_score']; ?>/<?php echo $attempt['exam_total_marks']; ?></p>
        <p>Percentage: <?php echo $percentage; ?>%</p>
        <p>Submitted At: <?php echo htmlspecialchars($attempt['submitted_at']); ?></p>

        <div class="pie-chart-container">
            <div class="pie-chart" data-percentage="<?php echo $percentage; ?>"></div>
        </div>

        <h2>Question-wise Results</h2>
        <?php foreach ($questions as $question): ?>
            <div class="question-result">
                <h3>Question <?php echo htmlspecialchars($question['question_id']); ?></h3>
                <p><strong>Question:</strong> <?php echo htmlspecialchars($question['question_text']); ?></p>
                <p><strong>Your Answer:</strong> <?php echo htmlspecialchars($question['selected_answer']); ?></p>
                <p><strong>Correct Answer(s):</strong>
                    <?php
                    $correct_options = json_decode($question['correct_options'], true);
                    $correct_answers = [];
                    foreach ($correct_options as $opt) {
                        if ($opt['is_correct'] == 1) {
                            $correct_answers[] = htmlspecialchars($opt['text']);
                        }
                    }
                    echo implode(', ', $correct_answers);
                    ?>
                </p>
                <p><strong>Status:</strong> <?php echo $question['is_correct'] ? 'Correct' : 'Incorrect'; ?></p>
                <p><strong>Marks:</strong> <?php echo $question['marks_obtained']; ?>/<?php echo $question['question_marks']; ?></p>
                
                <?php if (!empty($question['solution_pdf'])): ?>
                    <p><strong>Solution:</strong> <a href="../question_setter/<?php echo htmlspecialchars($question['solution_pdf']); ?>" target="_blank">Download PDF</a></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <a href="view_results.php" class="button">Back to All Results</a>
    </div>

    <script>
        const pieChart = document.querySelector('.pie-chart');
        const percentage = parseFloat(pieChart.getAttribute('data-percentage'));
        pieChart.style.background = `conic-gradient(
            #007bff 0% ${percentage}%,
            #e0e0f5 ${percentage}% 100%
        )`;
    </script>
</body>
</html>
