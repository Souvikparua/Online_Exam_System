<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

require '../db.php';

// Add Exam
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_exam'])) {
    $exam_name = $_POST['exam_name'];
    $description = $_POST['description'];
    $created_by = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO exams (exam_name, description, created_by) VALUES (:exam_name, :description, :created_by)");
    $stmt->execute([
        'exam_name' => $exam_name,
        'description' => $description,
        'created_by' => $created_by
    ]);
    echo "<div class='alert alert-success'>Exam added successfully!</div>";
}

// Delete Exam
if (isset($_GET['delete_exam'])) {
    $exam_id = $_GET['delete_exam'];
    $stmt = $conn->prepare("DELETE FROM exams WHERE id = :id");
    $stmt->execute(['id' => $exam_id]);
    echo "<div class='alert alert-success'>Exam deleted successfully!</div>";
}

// Fetch all exams
$stmt = $conn->prepare("SELECT * FROM exams");
$stmt->execute();
$exams = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Exams</title>
    <link rel="stylesheet" href="../assets/css/manage_exams.css"> <!-- Link to CSS file -->
</head>
<body>
    <div class="container">
        <h1>Manage Exams</h1>
        <a href="dashboard.php" class="btn">Back to Dashboard</a>

        <!-- Add Exam Form -->
        <div class="form-container">
            <h2>Add Exam</h2>
            <form method="POST">
                <input type="text" name="exam_name" placeholder="Exam Name" required>
                <textarea name="description" placeholder="Description" required></textarea>
                <button type="submit" name="add_exam" class="btn">Add Exam</button>
            </form>
        </div>

        <!-- Exam List -->
        <h2>Exam List</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Exam Name</th>
                    <th>Description</th>
                    <th>Created By</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exams as $exam): ?>
                    <tr>
                        <td><?php echo $exam['id']; ?></td>
                        <td><?php echo $exam['exam_name']; ?></td>
                        <td><?php echo $exam['description']; ?></td>
                        <td><?php echo $exam['created_by']; ?></td>
                        <td class="action-buttons">
                            <a href="edit_exam.php?id=<?php echo $exam['id']; ?>" class="btn">Edit</a>
                            <a href="manage_exams.php?delete_exam=<?php echo $exam['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>