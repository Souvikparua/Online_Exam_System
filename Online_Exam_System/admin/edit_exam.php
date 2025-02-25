<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

require '../db.php';

// Fetch exam details
if (isset($_GET['id'])) {
    $exam_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM exams WHERE id = :id");
    $stmt->execute(['id' => $exam_id]);
    $exam = $stmt->fetch();

    if (!$exam) {
        die("Exam not found!");
    }
} else {
    die("Invalid request!");
}

// Update Exam
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_exam'])) {
    $exam_name = $_POST['exam_name'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE exams SET exam_name = :exam_name, description = :description WHERE id = :id");
    $stmt->execute([
        'exam_name' => $exam_name,
        'description' => $description,
        'id' => $exam_id
    ]);
    echo "<div class='alert alert-success'>Exam updated successfully!</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Exam</title>
    <link rel="stylesheet" href="../assets/css/edit_exam.css"> <!-- Link to CSS file -->
</head>
<body>
    <div class="container">
        <h1>Edit Exam</h1>
        <a href="manage_exams.php">Back to Manage Exams</a>

        <!-- Edit Exam Form -->
        <form method="POST">
            <input type="text" name="exam_name" value="<?php echo $exam['exam_name']; ?>" required>
            <textarea name="description" required><?php echo $exam['description']; ?></textarea>
            <button type="submit" name="update_exam" class="btn">Update Exam</button>
        </form>
    </div>
</body>
</html>