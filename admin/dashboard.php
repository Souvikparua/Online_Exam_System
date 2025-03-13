<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

require '../db.php';

// Get admin's profile data
$admin_username = "Admin";
$profile_picture = "default_avatar.png";
if (isset($_SESSION['user_id'])) {
    try {
        // Get username
        $stmt = $conn->prepare("SELECT username, profile_picture FROM users WHERE id = ? AND role = 'admin'");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $admin_username = $result['username'];
            if (!empty($result['profile_picture'])) {
                $profile_picture = $result['profile_picture'];
            }
        }
    } catch (PDOException $e) {
        error_log("Error fetching admin data: " . $e->getMessage());
    }
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $target_dir = "../uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_file = $target_dir . basename($_FILES['profile_picture']['name']);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $error = '';

    // Validate image
    $check = getimagesize($_FILES['profile_picture']['tmp_name']);
    if ($check === false) {
        $uploadOk = 0;
        $error = "File is not an image.";
    }

    if ($_FILES['profile_picture']['size'] > 2000000) {
        $uploadOk = 0;
        $error = "File size exceeds 2MB limit.";
    }

    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        $uploadOk = 0;
        $error = "Only JPG, JPEG, PNG & GIF files are allowed.";
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $stmt->execute([$target_file, $_SESSION['user_id']]);
            header("Refresh:0");
            exit();
        } else {
            $error = "Error uploading file.";
        }
    }
}

// Database queries (remain the same)
$stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users");
$stmt->execute();
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

$stmt = $conn->prepare("SELECT COUNT(*) as total_exams FROM exams");
$stmt->execute();
$total_exams = $stmt->fetch(PDO::FETCH_ASSOC)['total_exams'];

$stmt = $conn->prepare("SELECT COUNT(*) as total_results FROM exam_results");
$stmt->execute();
$total_results = $stmt->fetch(PDO::FETCH_ASSOC)['total_results'];

$stmt = $conn->prepare("SELECT grade, COUNT(*) as count FROM student_details GROUP BY grade");
$stmt->execute();
$classWiseStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT role, COUNT(*) as count FROM users WHERE role IN ('teacher', 'student', 'question_setter') GROUP BY role");
$stmt->execute();
$roleCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare chart data (remain the same)
$classLabels = [];
$classData = [];
foreach ($classWiseStudents as $student) {
    $classLabels[] = 'Grade ' . $student['grade'];
    $classData[] = $student['count'];
}

$roleLabels = [];
$roleData = [];
foreach ($roleCounts as $role) {
    $roleLabels[] = ucfirst($role['role']);
    $roleData[] = $role['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
</head>
<body>
    <!-- Hamburger Menu -->
    <div class="hamburger">
        <div class="line1"></div>
        <div class="line2"></div>
        <div class="line3"></div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="profile-header">
            <img src="<?= htmlspecialchars($profile_picture) ?>" 
                 alt="Profile Picture" 
                 class="profile-pic"
                 id="profilePic">
            <h2>Admin Panel</h2>
        </div>
        <ul>
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="manage_users.php">Manage Users</a></li>
            <li><a href="manage_exams.php">Manage Exams</a></li>
            <li><a href="view_results.php">View Results</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Upload Modal -->
    <div class="upload-modal" id="uploadModal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Upload Profile Picture</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="profile_picture" accept="image/*" required>
                <button type="submit">Upload Photo</button>
                <?php if (!empty($error)): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Main Content (remainder remains the same) -->
    <div class="main-content">
        <div class="header">
            <h1>Welcome, <?php echo htmlspecialchars($admin_username); ?>!</h1>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="cards">
            <div class="card">
                <h3>Total Users</h3>
                <p><?php echo $total_users; ?></p>
            </div>
            <div class="card">
                <h3>Total Exams</h3>
                <p><?php echo $total_exams; ?></p>
            </div>
            <div class="card">
                <h3>Results Published</h3>
                <p><?php echo $total_results; ?></p>
            </div>
        </div>

        <div class="charts">
            <div class="chart-container">
                <h3>Class-wise Student Count</h3>
                <canvas id="classWiseChart"></canvas>
            </div>
            <div class="chart-container">
                <h3>User Distribution</h3>
                <canvas id="roleDistributionChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Profile Picture Upload Modal
        const profilePic = document.getElementById('profilePic');
        const uploadModal = document.getElementById('uploadModal');
        const closeModal = document.querySelector('.close');

        profilePic.addEventListener('click', () => {
            uploadModal.style.display = 'block';
        });

        closeModal.addEventListener('click', () => {
            uploadModal.style.display = 'none';
        });

        window.addEventListener('click', (e) => {
            if (e.target === uploadModal) {
                uploadModal.style.display = 'none';
            }
        });

        // Original JavaScript remains the same
        const hamburger = document.querySelector('.hamburger');
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');

        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            sidebar.classList.toggle('active');
        });

        mainContent.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && sidebar.classList.contains('active')) {
                if (!e.target.closest('.sidebar')) {
                    hamburger.classList.remove('active');
                    sidebar.classList.remove('active');
                }
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                hamburger.classList.remove('active');
                sidebar.classList.remove('active');
            }
        });

        // Chart initialization remains the same
        const classWiseChart = new Chart(document.getElementById('classWiseChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($classLabels); ?>,
                datasets: [{
                    label: 'Number of Students',
                    data: <?php echo json_encode($classData); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        const roleDistributionChart = new Chart(document.getElementById('roleDistributionChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($roleLabels); ?>,
                datasets: [{
                    label: 'User Count',
                    data: <?php echo json_encode($roleData); ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(75, 192, 192, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(75, 192, 192, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        enabled: true
                    }
                }
            }
        });
    </script>
</body>
</html>