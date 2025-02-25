<?php
session_start();
require_once '../db.php';
require_once '../includes/function.php';

if ($_SESSION['role'] != 'teacher') {
    header("Location: ../index.php");
    exit();
}

// Handle student update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = (int)$_POST['student_id'];
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    
    // Use PDO's bindValue for the update query
    $stmt = $conn->prepare("UPDATE student_details SET full_name = ?, email = ? WHERE id = ?");
    $stmt->bindValue(1, $name, PDO::PARAM_STR);
    $stmt->bindValue(2, $email, PDO::PARAM_STR);
    $stmt->bindValue(3, $studentId, PDO::PARAM_INT);
    $stmt->execute();
    
    header("Location: manage_students.php?success=1");
    exit();
}

// Get all students
$student_details = $conn->query("SELECT * FROM student_details");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
    <link rel="stylesheet" href="../assets/css/manage_students.css">
</head>
<body>
    <div class="container">
        <h2>Manage Students</h2>
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div class="success-message">Student updated successfully!</div>
        <?php endif; ?>
        <table>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
            <?php while ($student = $student_details->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <form method="post">
                    <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                    <td><input type="text" name="name" value="<?= $student['full_name'] ?>"></td>
                    <td><input type="email" name="email" value="<?= $student['email'] ?>"></td>
                    <td><button type="submit">Update</button></td>
                </form>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>