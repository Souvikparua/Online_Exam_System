<?php
session_start();
if ($_SESSION['role'] != 'question_setter') {
    header("Location: ../login.php");
    exit();
}

require '../db.php';

// Initialize filter parameters
$selected_grade = $_GET['grade'] ?? null;
$selected_subject = $_GET['subject_id'] ?? null;
$selected_chapter = $_GET['chapter_id'] ?? null;
$selected_subconcept = $_GET['subconcept_id'] ?? null;

// Fetch filter data
$grades = $conn->query("SELECT grade_level FROM grades ORDER BY grade_level")->fetchAll(PDO::FETCH_COLUMN, 0);
$subjects = $conn->query("SELECT * FROM subjects")->fetchAll();

// Fetch chapters based on selected subject
if ($selected_subject) {
    $stmt = $conn->prepare("SELECT * FROM chapters WHERE subject_id = ?");
    $stmt->execute([$selected_subject]);
    $chapters = $stmt->fetchAll();
} elseif (count($subjects) > 0) {
    $selected_subject = $subjects[0]['id'];
    $stmt = $conn->prepare("SELECT * FROM chapters WHERE subject_id = ?");
    $stmt->execute([$selected_subject]);
    $chapters = $stmt->fetchAll();
}

// Fetch subconcepts based on selected chapter
if ($selected_chapter) {
    $stmt = $conn->prepare("SELECT * FROM subconcepts WHERE chapter_id = ?");
    $stmt->execute([$selected_chapter]);
    $subconcepts = $stmt->fetchAll();
}

// Base query for questions
$query = "SELECT questions.*, 
          subjects.subject_name, 
          chapters.chapter_name,
          subconcepts.subconcept_name,
          grades.grade_level
          FROM questions
          LEFT JOIN subjects ON questions.subject_id = subjects.id
          LEFT JOIN chapters ON questions.chapter_id = chapters.id
          LEFT JOIN subconcepts ON questions.subconcept_id = subconcepts.id
          LEFT JOIN grades ON questions.grade_level = grades.grade_level
          WHERE questions.created_by = :user_id";

$params = [':user_id' => $_SESSION['user_id']];

// Add filters to query
if ($selected_grade) {
    $query .= " AND questions.grade_level = :grade";
    $params[':grade'] = $selected_grade;
}
if ($selected_subject) {
    $query .= " AND questions.subject_id = :subject";
    $params[':subject'] = $selected_subject;
}
if ($selected_chapter) {
    $query .= " AND questions.chapter_id = :chapter";
    $params[':chapter'] = $selected_chapter;
}
if ($selected_subconcept) {
    $query .= " AND questions.subconcept_id = :subconcept";
    $params[':subconcept'] = $selected_subconcept;
}

$stmt = $conn->prepare($query);
$stmt->execute($params);
$questions = $stmt->fetchAll();

// Handle question deletion
if (isset($_GET['delete_question'])) {
    try {
        // Get question details first
        $stmt = $conn->prepare("SELECT question_image, solution_pdf FROM questions WHERE id = ?");
        $stmt->execute([$_GET['delete_question']]);
        $question = $stmt->fetch();
        
        // Delete files if they exist
        if ($question['question_image']) {
            unlink($_SERVER['DOCUMENT_ROOT'] . "/Online_Exam_System/question_setter/" . $question['question_image']);
        }
        if ($question['solution_pdf']) {
            unlink($_SERVER['DOCUMENT_ROOT'] . "/Online_Exam_System/question_setter/" . $question['solution_pdf']);
        }
        
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM questions WHERE id = ?");
        $stmt->execute([$_GET['delete_question']]);
        
        $_SESSION['message'] = "Question deleted successfully!";
        header("Location: manage_questions.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting question: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="../assets/css/manage_questions.css">
    <script>
        function fetchChapters() {
            const subjectId = document.getElementById('subject_id').value;
            fetch('get_chapters.php?subject_id=' + subjectId)
                .then(response => response.json())
                .then(data => {
                    const chapterSelect = document.getElementById('chapter_id');
                    chapterSelect.innerHTML = '';
                    data.forEach(chapter => {
                        const option = document.createElement('option');
                        option.value = chapter.id;
                        option.textContent = chapter.chapter_name;
                        chapterSelect.appendChild(option);
                    });
                    fetchSubconcepts();
                });
        }

        function fetchSubconcepts() {
            const chapterId = document.getElementById('chapter_id').value;
            fetch('get_subconcepts.php?chapter_id=' + chapterId)
                .then(response => response.json())
                .then(data => {
                    const subconceptSelect = document.getElementById('subconcept_id');
                    subconceptSelect.innerHTML = '';
                    data.forEach(subconcept => {
                        const option = document.createElement('option');
                        option.value = subconcept.id;
                        option.textContent = subconcept.subconcept_name;
                        subconceptSelect.appendChild(option);
                    });
                });
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Manage Questions</h1>
        <a href="dashboard.php" class="btn">Back to Dashboard</a>
        <a href="add_questions.php" class="btn">Add New Question</a>

        <!-- Filter Form -->
        <form method="GET" class="filter-form">
            <div class="form-group">
                <label for="grade">Grade</label>
                <select name="grade" id="grade">
                    <option value="">All Grades</option>
                    <?php foreach ($grades as $grade): ?>
                        <option value="<?= $grade ?>" <?= $selected_grade == $grade ? 'selected' : '' ?>>
                            Grade <?= $grade ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="subject_id">Subject</label>
                <select name="subject_id" id="subject_id" onchange="fetchChapters()">
                    <option value="">All Subjects</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?= $subject['id'] ?>" 
                            <?= $selected_subject == $subject['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($subject['subject_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="chapter_id">Chapter</label>
                <select name="chapter_id" id="chapter_id" onchange="fetchSubconcepts()">
                    <option value="">All Chapters</option>
                    <?php foreach ($chapters as $chapter): ?>
                        <option value="<?= $chapter['id'] ?>" 
                            <?= $selected_chapter == $chapter['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($chapter['chapter_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="subconcept_id">Subconcept</label>
                <select name="subconcept_id" id="subconcept_id">
                    <option value="">All Subconcepts</option>
                    <?php foreach ($subconcepts as $subconcept): ?>
                        <option value="<?= $subconcept['id'] ?>" 
                            <?= $selected_subconcept == $subconcept['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($subconcept['subconcept_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn">Filter</button>
        </form>

        <!-- Questions Table -->
        <table class="table">
            <thead>
                <tr>
                    <th>Grade</th>
                    <th>Subject</th>
                    <th>Chapter</th>
                    <th>Subconcept</th>
                    <th>Question</th>
                    <th>Correct Answers</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($questions as $question): 
                    $options = json_decode($question['options'], true);
                    $correct_answers = array_filter($options, fn($opt) => $opt['is_correct'] == 1);
                ?>
                    <tr>
                        <td>Grade <?= $question['grade_level'] ?></td>
                        <td><?= htmlspecialchars($question['subject_name']) ?></td>
                        <td><?= htmlspecialchars($question['chapter_name']) ?></td>
                        <td><?= htmlspecialchars($question['subconcept_name']) ?></td>
                        <td><?= htmlspecialchars($question['question']) ?></td>
                        <td>
                            <?php foreach ($correct_answers as $ans): ?>
                                <span class="correct-answer"><?= htmlspecialchars($ans['text']) ?></span><br>
                            <?php endforeach; ?>
                        </td>
                        <td>
                            <a href="edit_question.php?id=<?= $question['id'] ?>" class="btn">Edit</a>
                            <a href="manage_questions.php?delete_question=<?= $question['id'] ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Are you sure? This action cannot be undone.')">
                               Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (empty($questions)): ?>
            <div class="alert alert-info">No questions found matching your criteria</div>
        <?php endif; ?>
    </div>
</body>
</html>