<?php
session_start();
include('../db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch student details
$stmt = $pdo->prepare("SELECT u.*, sd.grade 
                      FROM users u
                      JOIN student_details sd ON u.id = sd.user_id
                      WHERE u.id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

// Get profile picture
$profile_picture = !empty($student['profile_picture']) ? $student['profile_picture'] : 'default_avatar.png';

// Fetch exam statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as attended FROM exam_attempts WHERE student_id = ?");
$stmt->execute([$student_id]);
$attended_exams = $stmt->fetch()['attended'];

$total_exams = $pdo->prepare("SELECT COUNT(*) as total FROM exams WHERE grade_level = ?");
$total_exams->execute([$student['grade']]);
$total_exams = $total_exams->fetch()['total'];

$pending_exams = max(0, $total_exams - $attended_exams);

// Handle profile picture upload
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
            $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $stmt->execute([$target_file, $student_id]);
            header("Refresh:0");
            exit();
        } else {
            $error = "Error uploading file.";
        }
    }
}

// Fetch recent exams (5 most recent) for the table
$stmt_recent = $pdo->prepare("
    SELECT 
        e.exam_name, 
        ea.score AS obtained_marks,
        (SELECT SUM(marks) FROM exam_questions WHERE exam_id = e.id) AS total_marks,
        ea.attempt_date 
    FROM exam_attempts ea
    JOIN exams e ON ea.exam_id = e.id
    WHERE ea.student_id = ?
    ORDER BY ea.attempt_date DESC
    LIMIT 5
");
$stmt_recent->execute([$student_id]);
$recent_exams = $stmt_recent->fetchAll();

// Calculate percentages for recent exams
foreach ($recent_exams as &$exam) {
    $total = $exam['total_marks'] ?: 1; // Prevent division by zero
    $percentage = ($exam['obtained_marks'] / $total) * 100;
    $exam['percentage'] = number_format($percentage, 2);
}

// Fetch all exams for the graph
$stmt_all = $pdo->prepare("
    SELECT 
        e.exam_name, 
        ea.score AS obtained_marks,
        (SELECT SUM(marks) FROM exam_questions WHERE exam_id = e.id) AS total_marks,
        ea.attempt_date 
    FROM exam_attempts ea
    JOIN exams e ON ea.exam_id = e.id
    WHERE ea.student_id = ?
    ORDER BY ea.attempt_date ASC
");
$stmt_all->execute([$student_id]);
$all_exams = $stmt_all->fetchAll();

// Prepare data for the chart
$exam_percentages = [];
$exam_names = [];
foreach ($all_exams as $exam) {
    $total = $exam['total_marks'] ?: 1;
    $percentage = ($exam['obtained_marks'] / $total) * 100;
    $exam_percentages[] = $percentage;
    $exam_names[] = $exam['exam_name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../assets/css/student_dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="hamburger">
        <div class="line1"></div>
        <div class="line2"></div>
        <div class="line3"></div>
    </div>

    <div class="sidebar">
        <div class="profile-header">
            <img src="<?= htmlspecialchars($profile_picture) ?>" 
                 alt="Profile Picture" 
                 class="profile-pic"
                 id="profilePic">
            <h2><?= htmlspecialchars($student['username']) ?></h2>
            <p>Student - Grade <?= htmlspecialchars($student['grade']) ?></p>
        </div>
        <ul>
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="#">Study Materials</a></li>
            <li><a href="take_exam.php">Exams</a></li>
            <li><a href="view_results.php">Results</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>

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

    <div class="main-content">
        <div class="header">
            <h1>Welcome, <?= htmlspecialchars($student['username']) ?>!</h1>
            <div class="stats">
                <div class="stat-card">
                    <h3>Exams Attended</h3>
                    <p><?= $attended_exams ?></p>
                </div>
                <div class="stat-card">
                    <h3>Pending Exams</h3>
                    <p><?= $pending_exams ?></p>
                </div>
                <div class="stat-card">
                    <h3>Grade</h3>
                    <p><?= $student['grade'] ?> Grade level</p>
                </div>
            </div>
        </div>

        <div class="content-section">
            <h2>Recent Exam Attempts</h2>
            <div class="recent-exams">
                <?php if(count($recent_exams) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Exam Name</th>
                                <th>Score</th>
                                <th>Percentage</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_exams as $exam): ?>
                                <tr>
                                    <td><?= htmlspecialchars($exam['exam_name']) ?></td>
                                    <td><?= $exam['obtained_marks'] ?></td>
                                    <td><?= $exam['percentage'] ?>%</td>
                                    <td><?= date('M d, Y', strtotime($exam['attempt_date'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No recent exam attempts found.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="content-section">
            <h2>Performance Overview</h2>
            <div class="chart-container">
                <canvas id="performanceChart"></canvas>
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

        // Hamburger Menu
        const hamburger = document.querySelector('.hamburger');
        const sidebar = document.querySelector('.sidebar');

        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            sidebar.classList.toggle('active');
        });

        // Performance Chart with Enhanced Animations
        const ctx = document.getElementById('performanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($exam_names) ?>,
                datasets: [{
                    label: 'Exam Performance',
                    data: <?= json_encode($exam_percentages) ?>,
                    borderColor: '#3498db',
                    tension: 0.4, // Smoother curve
                    backgroundColor: 'rgba(52, 152, 219, 0.2)',
                    borderWidth: 3,
                    pointRadius: 5,
                    pointBackgroundColor: '#3498db',
                    pointHoverRadius: 8,
                    pointHoverBackgroundColor: '#2980b9',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 2000, // Longer animation duration
                    easing: 'easeOutQuart', // Smooth easing
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Percentage (%)',
                            font: {
                                size: 14,
                                weight: 'bold',
                            }
                        },
                        grid: {
                            color: '#ecf0f1',
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Exams',
                            font: {
                                size: 14,
                                weight: 'bold',
                            }
                        },
                        grid: {
                            display: false,
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        backgroundColor: '#2c3e50',
                        titleFont: {
                            size: 14,
                            weight: 'bold',
                        },
                        bodyFont: {
                            size: 12,
                        },
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + '%';
                            }
                        }
                    },
                    legend: {
                        display: true,
                        labels: {
                            font: {
                                size: 14,
                                weight: 'bold',
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>