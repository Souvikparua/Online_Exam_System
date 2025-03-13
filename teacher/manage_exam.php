<?php
session_start();
require_once '../db.php';
require_once '../includes/function.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['role'] !== 'teacher') {
    header("Location: ../unauthorized.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$error = $success = '';
$debug_info = '';  // For debugging purposes

// Handle exam deletion
if (isset($_GET['delete'])) {
    $exam_id = (int)$_GET['delete'];
    
    try {
        // First, check if the exam exists and belongs to this teacher
        $checkStmt = $conn->prepare("SELECT id FROM exams WHERE id = ? AND created_by = ?");
        $checkStmt->execute([$exam_id, $teacher_id]);
        
        if ($checkStmt->rowCount() === 0) {
            throw new Exception("Exam not found or you don't have permission to delete it.");
        }
        
        // Start transaction
        $conn->beginTransaction();
        $debug_info .= "Transaction started. ";
        
        // Delete from all related tables in the correct order
        
        // 1. First check and delete from exam_results
        $stmt1 = $conn->prepare("
            DELETE FROM exam_results 
            WHERE exam_attempt_id IN (
                SELECT id FROM exam_attempts WHERE exam_id = ?
            )
        ");
        $stmt1->execute([$exam_id]);
        $debug_info .= "Deleted " . $stmt1->rowCount() . " records from exam_results. ";
        
        // 2. Delete from results table if it exists and has a foreign key to exams
        $stmt2 = $conn->prepare("DELETE FROM results WHERE exam_id = ?");
        $stmt2->execute([$exam_id]);
        $debug_info .= "Deleted " . $stmt2->rowCount() . " records from results. ";
        
        // 3. Delete from exam_attempts
        $stmt3 = $conn->prepare("DELETE FROM exam_attempts WHERE exam_id = ?");
        $stmt3->execute([$exam_id]);
        $debug_info .= "Deleted " . $stmt3->rowCount() . " records from exam_attempts. ";
        
        // 4. Delete from exam_questions
        $stmt4 = $conn->prepare("DELETE FROM exam_questions WHERE exam_id = ?");
        $stmt4->execute([$exam_id]);
        $debug_info .= "Deleted " . $stmt4->rowCount() . " records from exam_questions. ";
        
        // 5. Finally delete the exam itself
        $stmt5 = $conn->prepare("DELETE FROM exams WHERE id = ? AND created_by = ?");
        $stmt5->execute([$exam_id, $teacher_id]);
        $debug_info .= "Deleted " . $stmt5->rowCount() . " records from exams. ";
        
        if ($stmt5->rowCount() > 0) {
            $conn->commit();
            $debug_info .= "Transaction committed. ";
            $success = "Exam deleted successfully!";
        } else {
            throw new Exception("Failed to delete exam. No rows affected.");
        }
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
            $debug_info .= "Transaction rolled back. ";
        }
        $error = "Error deleting exam: " . $e->getMessage();
        // Add the error code if it's a PDO exception
        if ($e instanceof PDOException) {
            $error .= " (Code: " . $e->getCode() . ")";
        }
    }
    
    // Uncomment this line for debugging
    // $error .= " Debug info: " . $debug_info;
}

// Fetch teacher's exams
$exams = [];
try {
    $stmt = $conn->prepare("
        SELECT e.*, s.subject_name, g.grade_level 
        FROM exams e
        JOIN subjects s ON e.subject_id = s.id
        JOIN grades g ON e.grade_level = g.grade_level
        WHERE e.created_by = ?
        ORDER BY e.schedule_time DESC
    ");
    $stmt->execute([$teacher_id]);
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching exams: " . $e->getMessage();
}

// Fetch all subjects for dropdown
$subjects = $conn->query("SELECT * FROM subjects")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Exams</title>
    <link rel="stylesheet" href="../assets/css/teacher_man.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Manage Exams</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <div class="d-flex gap-2 mb-3">
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        <a href="add_exam.php" class="btn btn-primary">Create New Exam</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Exam Name</th>
                    <th>Subject</th>
                    <th>Grade</th>
                    <th>Scheduled Time</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exams as $exam): ?>
                <tr>
                    <td><?= htmlspecialchars($exam['exam_name']) ?></td>
                    <td><?= htmlspecialchars($exam['subject_name']) ?></td>
                    <td>Grade <?= $exam['grade_level'] ?></td>
                    <td><?= date('M j, Y H:i', strtotime($exam['schedule_time'])) ?></td>
                    <td><?= $exam['duration'] ?> mins</td>
                    <td>
                        <span class="badge <?= $exam['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                            <?= $exam['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td>
                        <a href="edit_exam.php?id=<?= $exam['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="manage_exam.php?delete=<?= $exam['id'] ?>" class="btn btn-sm btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this exam? This action cannot be undone!')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>