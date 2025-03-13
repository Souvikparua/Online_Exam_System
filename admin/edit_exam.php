<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
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
    $duration = intval($_POST['duration']);
    $retake_allowed = isset($_POST['retake_allowed']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE exams 
                            SET exam_name = :exam_name, 
                                description = :description, 
                                duration = :duration, 
                                retake_allowed = :retake_allowed 
                            WHERE id = :id");
    $stmt->execute([
        'exam_name' => $exam_name,
        'description' => $description,
        'duration' => $duration,
        'retake_allowed' => $retake_allowed,
        'id' => $exam_id
    ]);
    echo "<div class='alert alert-success'>Exam updated successfully!</div>";
    
    // Refresh exam data after update
    $stmt = $conn->prepare("SELECT * FROM exams WHERE id = :id");
    $stmt->execute(['id' => $exam_id]);
    $exam = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Exam</title>
    <link rel="stylesheet" href="../assets/css/edit_exam.css"> <!-- Link to CSS file -->
    <style>
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="number"] {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .checkbox-group {
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Exam</h1>
        <a href="manage_exams.php">Back to Manage Exams</a>

        <!-- Edit Exam Form -->
        <form method="POST">
            <div class="form-group">
                <label for="exam_name">Exam Name:</label>
                <input type="text" id="exam_name" name="exam_name" value="<?php echo htmlspecialchars($exam['exam_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($exam['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="duration">Duration (in minutes):</label>
                <input type="number" id="duration" name="duration" value="<?php echo $exam['duration']; ?>" min="1" required>
            </div>

            <div class="checkbox-group">
                <label>
                    <input type="checkbox" name="retake_allowed" value="1" <?php echo $exam['retake_allowed'] ? 'checked' : ''; ?>>
                    Allow Retake
                </label>
            </div>

            <button type="submit" name="update_exam" class="btn">Update Exam</button>
        </form>
    </div>
</body>
</html>