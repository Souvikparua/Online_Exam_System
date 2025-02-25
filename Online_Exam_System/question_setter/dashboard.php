<?php
session_start();
if ($_SESSION['role'] != 'question_setter') {
    header("Location: ../index.php");
    exit();
}

require '../db.php';

// Handle chapter creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_chapter'])) {
    $chapter_name = trim($_POST['chapter_name']);
    $created_by = $_SESSION['user_id'];

    if (!empty($chapter_name)) {
        try {
            $stmt = $conn->prepare("INSERT INTO chapters (chapter_name, created_by) VALUES (:chapter_name, :created_by)");
            $stmt->execute([
                'chapter_name' => $chapter_name,
                'created_by' => $created_by
            ]);
            $chapter_success = "Chapter created successfully!";
        } catch (PDOException $e) {
            $chapter_error = "Error creating chapter: " . $e->getMessage();
        }
    } else {
        $chapter_error = "Chapter name cannot be empty!";
    }
}

// Fetch total chapters
$stmt = $conn->prepare("SELECT COUNT(*) as total_chapters FROM chapters WHERE created_by = :user_id");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$total_chapters = $stmt->fetch(PDO::FETCH_ASSOC)['total_chapters'];

// Fetch total questions
$stmt = $conn->prepare("SELECT COUNT(*) as total_questions FROM questions WHERE created_by = :user_id");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$total_questions = $stmt->fetch(PDO::FETCH_ASSOC)['total_questions'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question Setter Dashboard</title>
    <link rel="stylesheet" href="../assets/css/question setter_dashboard.css">
</head>
<body>
    <div class="container">
        <h1>Question Setter Dashboard</h1>
        <div class="dashboard-nav">
            <a href="add_questions.php" class="btn">Add Questions</a>
            <a href="manage_questions.php" class="btn">Manage Questions</a>
            <a href="../logout.php" class="btn btn-danger">Logout</a>
        </div>

        <!-- Chapter Creation Form -->
        <div class="form-container">
            <h2>Create New Chapter</h2>
            <?php if (isset($chapter_error)): ?>
                <div class="alert alert-error"><?php echo $chapter_error; ?></div>
            <?php endif; ?>
            <?php if (isset($chapter_success)): ?>
                <div class="alert alert-success"><?php echo $chapter_success; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="text" 
                       name="chapter_name" 
                       placeholder="Enter Chapter Name" 
                       required
                       maxlength="255">
                <button type="submit" name="create_chapter" class="btn">Create Chapter</button>
            </form>
        </div>

        <!-- Statistics Cards -->
        <div class="cards">
            <div class="card">
                <h3>Total Chapters</h3>
                <p><?php echo $total_chapters; ?></p>
            </div>
            <div class="card">
                <h3>Total Questions</h3>
                <p><?php echo $total_questions; ?></p>
            </div>
        </div>

        <!-- Recent Chapters List -->
        <div class="recent-chapters">
            <h2>Recent Chapters</h2>
            <?php
            $stmt = $conn->prepare("SELECT * FROM chapters 
                                   WHERE created_by = :user_id 
                                   ORDER BY created_at DESC 
                                   LIMIT 5");
            $stmt->execute(['user_id' => $_SESSION['user_id']]);
            $recent_chapters = $stmt->fetchAll();
            
            if (!empty($recent_chapters)): ?>
                <ul class="chapter-list">
                    <?php foreach ($recent_chapters as $chapter): ?>
                        <li>
                            <span><?php echo htmlspecialchars($chapter['chapter_name']); ?></span>
                            <small><?php echo date('M d, Y', strtotime($chapter['created_at'])); ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="muted">No chapters created yet</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>