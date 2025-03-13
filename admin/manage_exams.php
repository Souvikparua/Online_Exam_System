<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

require '../db.php';

// Delete Exam
if (isset($_GET['delete_exam'])) {
    $exam_id = $_GET['delete_exam'];
    $stmt = $conn->prepare("DELETE FROM exams WHERE id = :id");
    $stmt->execute(['id' => $exam_id]);
    echo "<div class='alert alert-success'>Exam deleted successfully!</div>";
}

// Fetch filter options
$grades = $conn->query("SELECT * FROM grades")->fetchAll();
$subjects = $conn->query("SELECT * FROM subjects")->fetchAll();
$chapters = $conn->query("SELECT * FROM chapters")->fetchAll();
$subconcepts = $conn->query("SELECT * FROM subconcepts")->fetchAll();

// Build base query
$sql = "SELECT 
            e.id, 
            e.exam_name, 
            e.description,
            u.username AS creator,
            g.grade_level,
            s.subject_name,
            c.chapter_name,
            sc.subconcept_name
        FROM exams e
        LEFT JOIN users u ON e.created_by = u.id
        LEFT JOIN grades g ON e.grade_level = g.grade_level
        LEFT JOIN subjects s ON e.subject_id = s.id
        LEFT JOIN chapters c ON e.chapter_id = c.id
        LEFT JOIN subconcepts sc ON e.subconcept_id = sc.id";

$params = [];
$whereClauses = [];

// Apply filters
if (!empty($_GET['grade_filter'])) {
    $whereClauses[] = "e.grade_level = :grade";
    $params[':grade'] = $_GET['grade_filter'];
}

if (!empty($_GET['subject_filter'])) {
    $whereClauses[] = "e.subject_id = :subject";
    $params[':subject'] = $_GET['subject_filter'];
}

if (!empty($_GET['chapter_filter'])) {
    $whereClauses[] = "e.chapter_id = :chapter";
    $params[':chapter'] = $_GET['chapter_filter'];
}

if (!empty($_GET['subconcept_filter'])) {
    $whereClauses[] = "e.subconcept_id = :subconcept";
    $params[':subconcept'] = $_GET['subconcept_filter'];
}

if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(" AND ", $whereClauses);
}

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$exams = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Exams</title>
    <link rel="stylesheet" href="../assets/css/manage_exams.css">
    <style>
        .filter-container { margin: 20px 0; padding: 15px; background: #f5f5f5; }
        .filter-group { margin: 10px 0; display: inline-block; margin-right: 15px; }
        select { padding: 5px; margin-left: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage Exams</h1>
        <a href="dashboard.php" class="btn">Back to Dashboard</a>

        <!-- Filter Section -->
        <div class="filter-container">
            <form method="GET">
                <div class="filter-group">
                    <label>Grade:</label>
                    <select name="grade_filter">
                        <option value="">All Grades</option>
                        <?php foreach ($grades as $grade): ?>
                            <option value="<?= $grade['grade_level'] ?>" <?= ($_GET['grade_filter'] ?? '') == $grade['grade_level'] ? 'selected' : '' ?>>
                                Grade <?= $grade['grade_level'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Subject:</label>
                    <select name="subject_filter">
                        <option value="">All Subjects</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?= $subject['id'] ?>" <?= ($_GET['subject_filter'] ?? '') == $subject['id'] ? 'selected' : '' ?>>
                                <?= $subject['subject_name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Chapter:</label>
                    <select name="chapter_filter">
                        <option value="">All Chapters</option>
                        <?php foreach ($chapters as $chapter): ?>
                            <option value="<?= $chapter['id'] ?>" <?= ($_GET['chapter_filter'] ?? '') == $chapter['id'] ? 'selected' : '' ?>>
                                <?= $chapter['chapter_name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Subconcept:</label>
                    <select name="subconcept_filter">
                        <option value="">All Subconcepts</option>
                        <?php foreach ($subconcepts as $subconcept): ?>
                            <option value="<?= $subconcept['id'] ?>" <?= ($_GET['subconcept_filter'] ?? '') == $subconcept['id'] ? 'selected' : '' ?>>
                                <?= $subconcept['subconcept_name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn">Filter</button>
                <a href="manage_exams.php" class="btn">Clear</a>
            </form>
        </div>

        <!-- Exam List -->
        <h2>Exam List</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Exam Name</th>
                    <th>Grade</th>
                    <th>Subject</th>
                    <th>Chapter</th>
                    <th>Subconcept</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exams as $exam): ?>
                    <tr>
                        <td><?= $exam['id'] ?></td>
                        <td><?= htmlspecialchars($exam['exam_name']) ?></td>
                        <td><?= $exam['grade_level'] ? 'Grade '.$exam['grade_level'] : 'N/A' ?></td>
                        <td><?= $exam['subject_name'] ?? 'N/A' ?></td>
                        <td><?= $exam['chapter_name'] ?? 'N/A' ?></td>
                        <td><?= $exam['subconcept_name'] ?? 'N/A' ?></td>
                        <td><?= htmlspecialchars($exam['creator']) ?></td>
                        <td>
                            <a href="edit_exam.php?id=<?= $exam['id'] ?>" class="btn">Edit</a>
                            <a href="manage_exams.php?delete_exam=<?= $exam['id'] ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>