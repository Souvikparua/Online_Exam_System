<?php
session_start();
if ($_SESSION['role'] != 'teacher') {
    header("Location: ../login.php");
    exit();
}

require '../db.php';

// Initialize filter variables
$examFilter = $_GET['exam_filter'] ?? '';
$studentFilter = $_GET['student_filter'] ?? '';
$gradeFilter = $_GET['grade_filter'] ?? '';
$subjectFilter = $_GET['subject_filter'] ?? '';

// Base query
$query = "
    SELECT 
        exam_attempts.id,
        users.username AS student_name,
        exams.exam_name,
        exam_attempts.score,
        exam_attempts.attempt_date AS submitted_at,
        student_details.grade AS student_grade,
        subjects.subject_name
    FROM exam_attempts
    JOIN users ON exam_attempts.student_id = users.id
    JOIN student_details ON users.id = student_details.user_id
    JOIN exams ON exam_attempts.exam_id = exams.id
    JOIN subjects ON exams.subject_id = subjects.id
    WHERE exam_attempts.status = 'completed'
";

// Add filters to the query
$filters = [];
if (!empty($examFilter)) {
    $filters[] = "exams.exam_name LIKE :exam_filter";
}
if (!empty($studentFilter)) {
    $filters[] = "users.username LIKE :student_filter";
}
if (!empty($gradeFilter)) {
    $filters[] = "student_details.grade = :grade_filter";
}
if (!empty($subjectFilter)) {
    $filters[] = "subjects.subject_name LIKE :subject_filter";
}

if (count($filters) > 0) {
    $query .= " AND " . implode(" AND ", $filters);
}

$query .= " ORDER BY student_grade, student_name, submitted_at";

// Prepare and execute the query
$stmt = $conn->prepare($query);

if (!empty($examFilter)) {
    $stmt->bindValue(':exam_filter', "%$examFilter%");
}
if (!empty($studentFilter)) {
    $stmt->bindValue(':student_filter', "%$studentFilter%");
}
if (!empty($gradeFilter)) {
    $stmt->bindValue(':grade_filter', $gradeFilter);
}
if (!empty($subjectFilter)) {
    $stmt->bindValue(':subject_filter', "%$subjectFilter%");
}

$stmt->execute();
$results = $stmt->fetchAll();

// Group results by grade
$groupedResults = [];
foreach ($results as $result) {
    $grade = $result['student_grade'] ?? 'N/A';
    if (!isset($groupedResults[$grade])) {
        $groupedResults[$grade] = [];
    }
    $groupedResults[$grade][] = $result;
}

// Fetch unique grades, subjects, and exam names for filters
$gradeStmt = $conn->query("SELECT DISTINCT grade FROM student_details WHERE grade IS NOT NULL");
$grades = $gradeStmt->fetchAll(PDO::FETCH_COLUMN);

$subjectStmt = $conn->query("SELECT DISTINCT subject_name FROM subjects");
$subjects = $subjectStmt->fetchAll(PDO::FETCH_COLUMN);

$examStmt = $conn->query("SELECT DISTINCT exam_name FROM exams");
$exams = $examStmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Results</title>
    <link rel="stylesheet" href="../assets/css/view_results.css">
</head>
<body>
    <div class="container">
        <h1>View Class-wise Results</h1>
        <a href="dashboard.php" class="btn">Back to Dashboard</a>

        <!-- Filter Form -->
        <form method="GET" action="" class="filter-form">
            <div class="filter-group">
                <label for="exam_filter">Exam Name:</label>
                <input type="text" id="exam_filter" name="exam_filter" value="<?php echo htmlspecialchars($examFilter); ?>" placeholder="Search by exam name">
            </div>
            <div class="filter-group">
                <label for="student_filter">Student Name:</label>
                <input type="text" id="student_filter" name="student_filter" value="<?php echo htmlspecialchars($studentFilter); ?>" placeholder="Search by student name">
            </div>
            <div class="filter-group">
                <label for="grade_filter">Grade:</label>
                <select id="grade_filter" name="grade_filter">
                    <option value="">All Grades</option>
                    <?php foreach ($grades as $grade): ?>
                        <option value="<?php echo $grade; ?>" <?php echo ($gradeFilter == $grade) ? 'selected' : ''; ?>>
                            <?php echo $grade; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="subject_filter">Subject:</label>
                <select id="subject_filter" name="subject_filter">
                    <option value="">All Subjects</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo $subject; ?>" <?php echo ($subjectFilter == $subject) ? 'selected' : ''; ?>>
                            <?php echo $subject; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn">Apply Filters</button>
            <a href="?" class="btn">Clear Filters</a>
        </form>

        <!-- Results Table -->
        <?php foreach ($groupedResults as $grade => $gradeResults): ?>
            <div class="grade-section">
                <h2>Grade <?php echo htmlspecialchars($grade); ?></h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student Name</th>
                            <th>Exam Name</th>
                            <th>Subject</th>
                            <th>Score</th>
                            <th>Submitted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gradeResults as $result): ?>
                            <tr>
                                <td><?php echo $result['id']; ?></td>
                                <td><?php echo $result['student_name']; ?></td>
                                <td><?php echo $result['exam_name']; ?></td>
                                <td><?php echo $result['subject_name']; ?></td>
                                <td><?php echo number_format($result['score'], 2); ?></td>
                                <td><?php echo $result['submitted_at']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>