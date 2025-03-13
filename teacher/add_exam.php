<?php
session_start();
ob_start(); // Start output buffering

require_once '../db.php';
require_once '../includes/function.php';

if ($_SESSION['role'] != 'teacher') {
    header("Location: ../login.php");
    exit();
}

// Fetch subjects
$subjects = $conn->query("SELECT * FROM subjects")->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission for creating an exam
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_exam'])) {
    $examName = sanitizeInput($_POST['exam_name']);
    $scheduleTime = $_POST['schedule_time'];
    $duration = (int)$_POST['duration'];
    $retakeAllowed = isset($_POST['retake_allowed']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0; // Capture active status
    $teacherId = $_SESSION['user_id'];

    // Get additional fields
    $gradeLevel = (int)$_POST['grade_level'];
    $subjectId = (int)$_POST['subject_id'];
    $chapterId = (int)$_POST['chapter_id'];
    $subconceptId = (int)$_POST['subconcept_id'];

    try {
        // Insert exam with additional fields and active status
        $stmt = $conn->prepare("INSERT INTO exams (exam_name, created_by, schedule_time, duration, retake_allowed, grade_level, subject_id, chapter_id, subconcept_id, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$examName, $teacherId, $scheduleTime, $duration, $retakeAllowed, $gradeLevel, $subjectId, $chapterId, $subconceptId, $isActive]);

        $examId = $conn->lastInsertId();

        // Insert exam questions
        if (isset($_POST['questions'])) {
            foreach ($_POST['questions'] as $questionId => $data) {
                if (isset($data['selected'])) {
                    $marks = (int)$data['marks'];
                    $modifiedText = sanitizeInput($data['modified_text']);

                    $stmt = $conn->prepare("INSERT INTO exam_questions (exam_id, question_id, modified_text, marks) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$examId, $questionId, $modifiedText, $marks]);
                }
            }
        }

        header("Location: add_exam.php?success=1");
        exit();
    } catch (PDOException $e) {
        // Log error message
        error_log($e->getMessage());
        die("Database error: " . $e->getMessage());
    }
}

// Get filter values from POST or set to NULL if not provided
$gradeLevel = $_POST['grade_level'] ?? NULL;
$subjectId = $_POST['subject_id'] ?? NULL;
$chapterId = $_POST['chapter_id'] ?? NULL;
$subconceptId = $_POST['subconcept_id'] ?? NULL;

// Fetch questions based on selected filters
$questions = [];
if ($gradeLevel && $subjectId && $chapterId && $subconceptId) {
    $questionsQuery = "
        SELECT q.id, q.question, q.options, 
               q.grade_level, q.subject_id, q.chapter_id, q.subconcept_id
        FROM questions q
        WHERE q.grade_level = :grade_level
        AND q.subject_id = :subject_id
        AND q.chapter_id = :chapter_id
        AND q.subconcept_id = :subconcept_id
    ";
    $stmt = $conn->prepare($questionsQuery);
    $stmt->execute([
        ':grade_level' => $gradeLevel,
        ':subject_id' => $subjectId,
        ':chapter_id' => $chapterId,
        ':subconcept_id' => $subconceptId
    ]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>  
<!DOCTYPE html>
<html>
<head>
    <title>Add Exams</title>
    <link rel="stylesheet" href="../assets/css/teacher_man.css">
    <script>
    // Function to fetch chapters based on selected subject
    function fetchChapters(subjectId) {
        if (!subjectId) return;

        fetch(`get_chapters.php?subject_id=${subjectId}`)
            .then(response => response.json())
            .then(data => {
                const chapterDropdown = document.getElementById("chapterDropdown");
                chapterDropdown.innerHTML = '<option value="">Select Chapter</option>';
                data.forEach(chapter => {
                    chapterDropdown.innerHTML += `<option value="${chapter.id}">${chapter.name}</option>`;
                });
            })
            .catch(error => {
                console.error("Error fetching chapters:", error);
            });
    }

    // Function to fetch subconcepts based on selected chapter
    function fetchSubconcepts(chapterId) {
        if (!chapterId) return;

        fetch(`get_subconcepts.php?chapter_id=${chapterId}`)
            .then(response => response.json())
            .then(data => {
                const subconceptDropdown = document.getElementById("subconceptDropdown");
                subconceptDropdown.innerHTML = '<option value="">Select Subconcept</option>';
                data.forEach(subconcept => {
                    subconceptDropdown.innerHTML += `<option value="${subconcept.id}">${subconcept.name}</option>`;
                });
            })
            .catch(error => {
                console.error("Error fetching subconcepts:", error);
            });
    }

    document.addEventListener("DOMContentLoaded", function() {
        const subjectDropdown = document.getElementById("subjectDropdown");
        const chapterDropdown = document.getElementById("chapterDropdown");
        const subconceptDropdown = document.getElementById("subconceptDropdown");

        // Populate chapters when subject is selected
        subjectDropdown.addEventListener("change", function() {
            fetchChapters(this.value);
            subconceptDropdown.innerHTML = '<option value="">Select Chapter First</option>';
        });

        // Populate subconcepts when chapter is selected
        chapterDropdown.addEventListener("change", function() {
            fetchSubconcepts(this.value);
        });

        // Initial population if values exist
        if (subjectDropdown.value) fetchChapters(subjectDropdown.value);
        if (chapterDropdown.value) fetchSubconcepts(chapterDropdown.value);
    });
    </script>
</head>
<body>
<div class="container">
    <!-- Back to Dashboard Button -->
    <a href="dashboard.php" class="btn-back">Back to Dashboard</a>

    <!-- Success Message -->
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="success-message">
            Exam created successfully!
        </div>
    <?php endif; ?>

    <!-- Filter Form -->
    <form method="post" action="" id="filterForm">
        <div>
            <label>Grade Level:</label>
            <select name="grade_level" required>
                <?php for ($i = 6; $i <= 12; $i++): ?>
                    <option value="<?= $i ?>" <?= ($gradeLevel == $i) ? 'selected' : '' ?>>Grade <?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div>
            <label>Subject:</label>
            <select name="subject_id" id="subjectDropdown" required>
                <option value="">Select Subject</option>
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?= $subject['id'] ?>" <?= ($subjectId == $subject['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($subject['subject_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Chapter:</label>
            <select name="chapter_id" id="chapterDropdown" required>
                <option value="">Select Subject First</option>
            </select>
        </div>
        <div>
            <label>Subconcept:</label>
            <select name="subconcept_id" id="subconceptDropdown" required>
                <option value="">Select Chapter First</option>
            </select>
        </div>
        
        <button type="submit" name="filter_questions">Filter Questions</button>
    </form>

    <!-- Exam Creation Form -->
    <?php if (!empty($questions)): ?>
    <form method="post" action="" id="examForm">
        <!-- Hidden fields to maintain the filter state -->
        <input type="hidden" name="grade_level" value="<?= htmlspecialchars($gradeLevel) ?>">
        <input type="hidden" name="subject_id" value="<?= htmlspecialchars($subjectId) ?>">
        <input type="hidden" name="chapter_id" value="<?= htmlspecialchars($chapterId) ?>">
        <input type="hidden" name="subconcept_id" value="<?= htmlspecialchars($subconceptId) ?>">

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
        <div>
            <label>Active:</label>
            <input type="checkbox" name="is_active" value="1" checked>
        </div>

        <h3>Select Questions</h3>
        <?php foreach ($questions as $question): ?>
        <div class="question">
            <input type="checkbox" name="questions[<?= $question['id'] ?>][selected]">
            <div class="question-text">
                <textarea name="questions[<?= $question['id'] ?>][modified_text]"><?= htmlspecialchars($question['question']) ?></textarea>
            </div>
            <div class="marks">
                Marks: <input type="number" 
                       name="questions[<?= $question['id'] ?>][marks]" 
                       value="1" 
                       min="1" required>
            </div>
        </div>
        <?php endforeach; ?>
        
        <button type="submit" name="create_exam">Create Exam</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>