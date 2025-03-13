<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include('../db.php');
include('../includes/function.php');

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (isset($_SESSION['exam_submitted']) && $_SESSION['exam_submitted']) {
    header("Location: dashboard.php");
    exit();
}

if (!$conn) {
    die("Database connection failed: " . print_r($conn->errorInfo(), true));
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['exam_id']) || !is_numeric($_GET['exam_id'])) {
    die("Invalid exam ID.");
}

$exam_id = (int)$_GET['exam_id'];
$id = $_SESSION['user_id'];

// Fetch exam details and ensure it's active
$stmt = $conn->prepare("SELECT * FROM exams WHERE id = :exam_id AND is_active = 1");
$stmt->bindParam(':exam_id', $exam_id, PDO::PARAM_INT);
$stmt->execute();
$exam = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$exam) {
    die("Exam not found or is inactive.");
}

// Fetch questions for the exam
$stmt = $conn->prepare("SELECT q.* FROM questions q INNER JOIN exam_questions eq ON q.id = eq.question_id WHERE eq.exam_id = :exam_id");
$stmt->bindParam(':exam_id', $exam_id, PDO::PARAM_INT);
$stmt->execute();
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($questions) === 0) {
    die("No questions found for this exam.");
}

$_SESSION['exam_started'] = true;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam: <?php echo htmlspecialchars($exam['exam_name']); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .option-image {
            max-width: 200px;
            max-height: 150px;
            display: block;
            margin-top: 5px;
            border: 1px solid #ddd;
            padding: 2px;
        }
        li {
            margin-bottom: 10px;
        }
        .multiple-answer-note {
            color: #ff0000;
            font-weight: bold;
            margin: 10px 0;
        }
    </style>
    <script>
        // ======== Enhanced Back Button Handling ========
        (function() {
            window.history.replaceState(null, null, window.location.href);
            window.history.pushState(null, null, window.location.href);
            
            window.onpopstate = function(event) {
                window.history.pushState(null, null, window.location.href);
                alert("Back navigation detected! Submitting your exam...");
                document.getElementById("examForm").submit();
            };
        })();

        // ======== Existing Exam Functionality ========
        let isActive = true;
        let formSubmitted = false;
        
        window.onfocus = function () { isActive = true; };
        window.onblur = function () {
            isActive = false;
            setTimeout(() => {
                if (!isActive) {
                    alert("Tab switch detected! Your exam will be auto-submitted.");
                    document.getElementById("examForm").submit();
                }
            }, 10000);
        };

        // ======== Window Close Detection ========
        window.addEventListener('beforeunload', function(e) {
            if (!formSubmitted) {
                const form = document.getElementById('examForm');
                const formData = new FormData(form);
                navigator.sendBeacon(form.action, formData);
            }
        });
        
        let timeLeft = <?php echo $exam['duration'] * 60; ?>;
        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            document.getElementById('timer').textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                alert("Time's up! Your exam will be submitted.");
                document.getElementById("examForm").submit();
            }
            timeLeft--;
        }
        const timerInterval = setInterval(updateTimer, 1000);
        
        let currentQuestionIndex = 0;
        let questions = <?php echo json_encode($questions); ?>;
        let answers = {};

        function showQuestion(index) {
            console.log("Showing question index:", index);
            let questionBox = document.getElementById("questionBox");
            let question = questions[index];

            if (typeof question.options === "string") {
                question.options = JSON.parse(question.options);
            }

            let optionsHtml = "";
            const isMultiple = question.is_multiple_answer == 1;
            
            if (isMultiple) {
                optionsHtml += `<div class="multiple-answer-note">(Select multiple answers)</div>`;
            }

            question.options.forEach(option => {
                const checked = answers[question.id] && answers[question.id].includes(option.text) ? 'checked' : '';
                const inputType = isMultiple ? 'checkbox' : 'radio';
                const inputName = isMultiple ? `answer_${question.id}[]` : `answer_${question.id}`;
                const imageTag = option.image 
                    ? `<img src="../question_setter/${option.image}" alt="Option Image" class="option-image">`
                    : '';
                
                optionsHtml += `
                    <li>
                        <label>
                            <input type="${inputType}" 
                                   name="${inputName}" 
                                   value="${option.text}" 
                                   ${checked}
                                   onchange="saveAnswer(${question.id}, this.checked, '${option.text}', ${isMultiple})"
                                   ${!isMultiple ? 'required' : ''}>
                            <span>${option.text}</span>
                            ${imageTag}
                        </label>
                    </li>`;
            });

            questionBox.innerHTML = `
                <div class="question-content">
                    <h3>Q.${index + 1} ${question.question}</h3>
                    ${question.question_image ? `
                    <div class="question-image">
                        <img src="../question_setter/${question.question_image}" alt="Question Image">
                    </div>` : ""}
                    <ul class="options-list">${optionsHtml}</ul>
                </div>`;

            document.getElementById("prevBtn").style.display = index === 0 ? "none" : "inline-block";
            document.getElementById("nextBtn").style.display = index === questions.length - 1 ? "none" : "inline-block";
            document.getElementById("submitBtn").style.display = index === questions.length - 1 ? "inline-block" : "none";
        }

        function saveAnswer(questionId, isChecked, value, isMultiple) {
            if (isMultiple) {
                if (!answers[questionId]) answers[questionId] = [];
                if (isChecked) {
                    answers[questionId].push(value);
                } else {
                    answers[questionId] = answers[questionId].filter(item => item !== value);
                }
            } else {
                answers[questionId] = [value];
            }

            let hiddenInput = document.getElementById("answer_" + questionId);
            if (!hiddenInput) {
                hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = "answer_" + questionId;
                hiddenInput.id = "answer_" + questionId;
                document.getElementById("examForm").appendChild(hiddenInput);
            }
            hiddenInput.value = JSON.stringify(answers[questionId] || []);
        }

        function nextQuestion() {
            if (currentQuestionIndex < questions.length - 1) {
                currentQuestionIndex++;
                showQuestion(currentQuestionIndex);
            }
        }

        function prevQuestion() {
            if (currentQuestionIndex > 0) {
                currentQuestionIndex--;
                showQuestion(currentQuestionIndex);
            }
        }
        
        window.onload = function () {
            showQuestion(0);
        };
    </script>
</head>
<body>
    <div class="container">
        <h1>Exam: <?php echo htmlspecialchars($exam['exam_name']); ?></h1>
        <p>Time Left: <span id="timer"></span></p>
        <form id="examForm" action="submit_exam.php" method="POST" onsubmit="formSubmitted = true;">
            <input type="hidden" name="exam_id" value="<?php echo htmlspecialchars($exam_id); ?>">
            <div id="questionBox" class="question-container"></div>
            <div class="navigation-buttons">
                <button type="button" id="prevBtn" onclick="prevQuestion()" style="display: none;">Previous</button>
                <button type="button" id="nextBtn" onclick="nextQuestion()">Next</button>
                <button type="submit" id="submitBtn" style="display: none;">Submit Exam</button>
            </div>
        </form>
    </div>
</body>
</html>