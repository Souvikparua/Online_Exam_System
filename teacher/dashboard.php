<?php
session_start();
require_once '../db.php';
require_once '../includes/function.php';

if ($_SESSION['role'] != 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacherId = $_SESSION['user_id'];
$uploadDir = '../uploads/';
$defaultAvatar = '../images/user-avatar.png';

// Handle avatar upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    $file = $_FILES['avatar'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'teacher_' . $teacherId . '_' . time() . '.' . $extension;
            $targetPath = $uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                if ($stmt->execute([$targetPath, $teacherId])) {
                    $_SESSION['success'] = "Avatar updated successfully!";
                } else {
                    $_SESSION['error'] = "Failed to update database";
                }
            } else {
                $_SESSION['error'] = "Error moving uploaded file";
            }
        } else {
            $_SESSION['error'] = "Invalid file type or size (max 2MB)";
        }
    } else {
        $_SESSION['error'] = "File upload error: " . $file['error'];
    }
    header("Location: dashboard.php");
    exit();
}

// Fetch teacher's details
$stmt = $conn->prepare("SELECT u.profile_picture, t.full_name 
                       FROM users u
                       JOIN teacher_details t ON u.id = t.user_id
                       WHERE u.id = ?");
$stmt->execute([$teacherId]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

$teacherName = $teacher['full_name'] ?? 'Teacher';
$avatarPath = $teacher['profile_picture'] ?? $defaultAvatar;

// Fetch exam statistics
$stats = [
    'total_exams' => 0,
    'total_attempts' => 0,
    'unique_students' => 0
];

// Database queries for stats
$stmt = $conn->prepare("SELECT COUNT(*) FROM exams WHERE created_by = ?");
$stmt->execute([$teacherId]);
$stats['total_exams'] = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM exam_attempts 
                       WHERE exam_id IN (SELECT id FROM exams WHERE created_by = ?)");
$stmt->execute([$teacherId]);
$stats['total_attempts'] = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(DISTINCT student_id) FROM exam_attempts 
                       WHERE exam_id IN (SELECT id FROM exams WHERE created_by = ?)");
$stmt->execute([$teacherId]);
$stats['unique_students'] = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/teacher_dash.css">
</head>
<body>
    <div class="mobile-menu-toggle">
        <i class="fas fa-bars"></i>
    </div>
    
    <div class="sidebar">
        <h2>Teacher Portal</h2>
        <ul>
            <li class="active"><a href="#"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="add_exam.php"><i class="fas fa-plus-circle"></i> Add Exam</a></li>
            <li><a href="manage_exam.php"><i class="fas fa-tasks"></i> Manage Exams</a></li>
            <li><a href="view_results.php"><i class="fas fa-chart-bar"></i> View Results</a></li>
            <li><a href="../logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <h2>Welcome, <?php echo htmlspecialchars($teacherName); ?></h2>
            <div class="user-info">
                <span><?php echo htmlspecialchars($teacherName); ?></span>
                <form class="avatar-upload" method="post" enctype="multipart/form-data">
                    <img src="<?php echo htmlspecialchars($avatarPath); ?>" alt="User Avatar">
                    <input type="file" name="avatar" accept="image/*">
                    <i class="fas fa-pencil edit-icon"></i>
                </form>
            </div>
        </header>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <section class="cards">
            <div class="card card1">
                <h3><?php echo $stats['total_exams']; ?></h3>
                <p>Exams Created</p>
            </div>
            <div class="card card2">
                <h3><?php echo $stats['total_attempts']; ?></h3>
                <p>Results Published</p>
            </div>
            <div class="card card3">
                <h3><?php echo $stats['unique_students']; ?></h3>
                <p>Students Participated</p>
            </div>
        </section>
    </div>

    <div class="mobile-overlay"></div>

    <script>
        // Mobile Menu Toggle
        const menuToggle = document.querySelector('.mobile-menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.mobile-overlay');
        
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });

        // Auto-submit avatar form
        document.querySelector('input[type="file"]').addEventListener('change', function() {
            this.form.submit();
        });

        // Close menu on window resize
        window.addEventListener('resize', () => {
            if(window.innerWidth > 768) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        });
    </script>
</body>
</html>