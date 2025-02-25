<?php
session_start();
if ($_SESSION['role'] != 'question_setter') {
    header("Location: ../index.php");
    exit();
}

require '../db.php';

// Fetch questions created by the question setter
$stmt = $conn->prepare("SELECT questions.*, chapters.chapter_name FROM questions JOIN chapters ON questions.chapter_id = chapters.id WHERE questions.created_by = :user_id");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$questions = $stmt->fetchAll();

// Handle question deletion
if (isset($_GET['delete_question'])) {
    $question_id = $_GET['delete_question'];
    $stmt = $conn->prepare("DELETE FROM questions WHERE id = :id");
    $stmt->execute(['id' => $question_id]);
    echo "<div class='alert alert-success'>Question deleted successfully!</div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions</title>
    <link rel="stylesheet" href="../assets/css/manage_questions.css">
</head>
<body>
    <div class="container">
        <h1>Manage Questions</h1>
        <a href="dashboard.php" class="btn">Back to Dashboard</a>

        <!-- Questions Table -->
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Chapter</th>
                    <th>Question</th>
                    <th>Correct Answer</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($questions as $question): ?>
                    <tr>
                        <td><?php echo $question['id']; ?></td>
                        <td><?php echo $question['chapter_name']; ?></td>
                        <td><?php echo $question['question']; ?></td>
                        <td><?php echo $question['correct_answer']; ?></td>
                        <td>
                            <a href="edit_question.php?id=<?php echo $question['id']; ?>" class="btn">Edit</a>
                            <a href="manage_questions.php?delete_question=<?php echo $question['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>