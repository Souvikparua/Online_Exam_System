<?php
session_start();
if ($_SESSION['role'] != 'question_setter') {
    header("Location: ../index.php");
    exit();
}

require '../db.php';

// Get question setter's name
$stmt = $conn->prepare("SELECT full_name FROM question_setter_details WHERE user_id = :user_id");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$full_name = $user['full_name'] ?? 'Question Setter'; // Fallback name
// Fetch subjects for dropdown
$subjects = $conn->query("SELECT * FROM subjects")->fetchAll(PDO::FETCH_ASSOC);

// Handle chapter creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_chapter'])) {
    $chapter_name = trim($_POST['chapter_name']);
    $created_by = $_SESSION['user_id'];
    $subject_id = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0;

    if (!empty($chapter_name) && $subject_id > 0) {
        try {
            $stmt = $conn->prepare("INSERT INTO chapters (chapter_name, created_by, subject_id) VALUES (:chapter_name, :created_by, :subject_id)");
            $stmt->execute([
                'chapter_name' => $chapter_name,
                'created_by' => $created_by,
                'subject_id' => $subject_id
            ]);
            $chapter_success = "Chapter created successfully!";
        } catch (PDOException $e) {
            $chapter_error = "Error creating chapter: " . $e->getMessage();
        }
    } else {
        $chapter_error = "Chapter name and subject must be selected!";
    }
}

// Handle subconcept creation and subject selection
$selected_subject_id = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0;
$chapters = [];
$subconcept_error = '';
$subconcept_success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_subconcept'])) {
    $subconcept_name = trim($_POST['subconcept_name']);
    $chapter_id = isset($_POST['chapter_id']) ? (int)$_POST['chapter_id'] : 0;

    if (!empty($subconcept_name) && $chapter_id > 0) {
        try {
            $stmt = $conn->prepare("INSERT INTO subconcepts (subconcept_name, chapter_id) VALUES (:subconcept_name, :chapter_id)");
            $stmt->execute([
                'subconcept_name' => $subconcept_name,
                'chapter_id' => $chapter_id
            ]);
            $subconcept_success = "Subconcept created successfully!";
            // Clear form fields
            $subconcept_name = '';
            $selected_subject_id = 0;
            $chapter_id = 0;
        } catch (PDOException $e) {
            $subconcept_error = "Error creating subconcept: " . $e->getMessage();
        }
    } else {
        $subconcept_error = "Subconcept name and chapter must be selected!";
    }
}

// Fetch chapters for selected subject (if any)
if ($selected_subject_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM chapters WHERE subject_id = :subject_id");
    $stmt->execute(['subject_id' => $selected_subject_id]);
    $chapters = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total_chapters FROM chapters WHERE created_by = :user_id");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$total_chapters = $stmt->fetch(PDO::FETCH_ASSOC)['total_chapters'];

$stmt = $conn->prepare("SELECT COUNT(*) as total_questions FROM questions WHERE created_by = :user_id");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$total_questions = $stmt->fetch(PDO::FETCH_ASSOC)['total_questions'];

$stmt = $conn->prepare("SELECT COUNT(*) as total_subconcepts 
                       FROM subconcepts 
                       JOIN chapters ON subconcepts.chapter_id = chapters.id 
                       WHERE chapters.created_by = :user_id");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$total_subconcepts = $stmt->fetch(PDO::FETCH_ASSOC)['total_subconcepts'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question Setter Dashboard</title>
    <link rel="stylesheet" href="../assets/css/question_setter_dashboard.css">
    <script>
        function submitSubjectForm() {
            document.getElementById('subjectForm').submit();
        }
    </script>
</head>
<body>
<div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($full_name); ?>!</h1>
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
                <input type="text" name="chapter_name" placeholder="Enter Chapter Name" required maxlength="255">
                
                <select name="subject_id" required>
                    <option value="">Select Subject</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo $subject['id']; ?>">
                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" name="create_chapter" class="btn">Create Chapter</button>
            </form>
        </div>

        <!-- Subconcept Creation Form -->
        <div class="form-container">
            <h2>Create New Subconcept</h2>
            <?php if (!empty($subconcept_error)): ?>
                <div class="alert alert-error"><?php echo $subconcept_error; ?></div>
            <?php endif; ?>
            <?php if (!empty($subconcept_success)): ?>
                <div class="alert alert-success"><?php echo $subconcept_success; ?></div>
            <?php endif; ?>
            
            <form method="POST" id="subjectForm">
                <select name="subject_id" required onchange="submitSubjectForm()">
                    <option value="">Select Subject</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo $subject['id']; ?>" <?php echo ($subject['id'] == $selected_subject_id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="chapter_id" required>
                    <option value="">Select Chapter</option>
                    <?php if ($selected_subject_id > 0 && !empty($chapters)): ?>
                        <?php foreach ($chapters as $chapter): ?>
                            <option value="<?php echo $chapter['id']; ?>">
                                <?php echo htmlspecialchars($chapter['chapter_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>

                <input type="text" name="subconcept_name" placeholder="Enter Subconcept Name" required maxlength="255" value="<?php echo isset($_POST['subconcept_name']) ? htmlspecialchars($_POST['subconcept_name']) : ''; ?>">

                <button type="submit" name="create_subconcept" class="btn">Create Subconcept</button>
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
            <div class="card">
                <h3>Total Subconcepts</h3>
                <p><?php echo $total_subconcepts; ?></p>
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