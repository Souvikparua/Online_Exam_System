<?php
session_start();
if ($_SESSION['role'] != 'question_setter') {
    header("Location: ../index.php");
    exit();
}

require '../db.php';

// Fetch chapters
$stmt = $conn->prepare("SELECT * FROM chapters WHERE created_by = :user_id");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$chapters = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $chapter_id = $_POST['chapter_id'];
        $question = $_POST['question'];
        $is_multiple_answer = isset($_POST['is_multiple_answer']) ? 1 : 0;
        $created_by = $_SESSION['user_id'];
        $options = [];

        // Handle question image upload
        $question_image = null;
        if ($_FILES['question_image']['size'] > 0) {
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/Online_Exam_System/question_setter/question_images/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $filename = uniqid() . '_' . basename($_FILES['question_image']['name']);
            $target_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['question_image']['tmp_name'], $target_path)) {
                $question_image = 'question_images/' . $filename;
            }
        }

        // Handle solution PDF upload
        $solution_pdf = null;
        if ($_FILES['solution_pdf']['size'] > 0) {
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/Online_Exam_System/question_setter/solution_pdfs/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $filename = uniqid() . '_' . basename($_FILES['solution_pdf']['name']);
            $target_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['solution_pdf']['tmp_name'], $target_path)) {
                $solution_pdf = 'solution_pdfs/' . $filename;
            }
        }

        // Process options
        foreach ($_POST['options'] as $index => $option_text) {
            $option_image = null;
            
            // Handle option image upload
            if ($_FILES['options']['size'][$index]['image'] > 0) {
                $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/Online_Exam_System/question_setter/question_images/';
                $filename = uniqid() . '_' . basename($_FILES['options']['name'][$index]['image']);
                $target_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['options']['tmp_name'][$index]['image'], $target_path)) {
                    $option_image = 'question_images/' . $filename;
                }
            }

            // Determine if this option is correct
            $is_correct = in_array($index, $_POST['correct_answer'] ?? []) ? 1 : 0;

            $options[] = [
                'text' => $option_text,
                'image' => $option_image,
                'is_correct' => $is_correct
            ];
        }

        // Insert into database
        $stmt = $conn->prepare("
            INSERT INTO questions 
            (chapter_id, question, options, is_multiple_answer, question_image, solution_pdf, created_by) 
            VALUES (:chapter_id, :question, :options, :is_multiple_answer, :question_image, :solution_pdf, :created_by)
        ");
        
        $stmt->execute([
            'chapter_id' => $chapter_id,
            'question' => $question,
            'options' => json_encode($options),
            'is_multiple_answer' => $is_multiple_answer,
            'question_image' => $question_image,
            'solution_pdf' => $solution_pdf,
            'created_by' => $created_by
        ]);

        echo "<div class='alert alert-success'>Question added successfully!</div>";
        
    } catch (PDOException $e) {
        echo "<div class='alert alert-error'>Database Error: " . $e->getMessage() . "</div>";
    } catch (Exception $e) {
        echo "<div class='alert alert-error'>Error: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Questions</title>
    <link rel="stylesheet" href="../assets/css/add_questions.css">
    <script>
        function addOption() {
            const container = document.getElementById('options-container');
            const index = container.children.length;
            
            const div = document.createElement('div');
            div.className = 'option';
            div.innerHTML = `
                <div class="option-header">
                    <h4>Option ${index + 1}</h4>
                    <button type="button" onclick="this.parentElement.parentElement.remove()" class="btn-danger">Remove</button>
                </div>
                <textarea name="options[]" placeholder="Option text" required></textarea>
                <input type="file" name="options[${index}][image]" accept="image/*">
                <label class="correct-answer">
                    <input type="checkbox" name="correct_answer[]" value="${index}">
                    Correct Answer
                </label>
            `;
            container.appendChild(div);
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Add Questions</h1>
        <a href="dashboard.php" class="btn">Back to Dashboard</a>

        <form method="POST" enctype="multipart/form-data">
            <!-- Chapter Selection -->
            <div class="form-group">
                <label for="chapter_id">Chapter</label>
                <select name="chapter_id" id="chapter_id" required>
                    <?php foreach ($chapters as $chapter): ?>
                        <option value="<?= $chapter['id'] ?>"><?= htmlspecialchars($chapter['chapter_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Question Input -->
            <div class="form-group">
                <label for="question">Question</label>
                <textarea name="question" id="question" required></textarea>
            </div>

            <!-- Question Image -->
            <div class="form-group">
                <label for="question_image">Question Image (Optional)</label>
                <input type="file" name="question_image" id="question_image" accept="image/*">
            </div>

            <!-- Options Container -->
            <div class="form-group">
                <label>Options</label>
                <div id="options-container"></div>
                <button type="button" onclick="addOption()" class="btn">Add Option</button>
            </div>

            <!-- Multiple Answers -->
            <div class="form-group">
                <label class="checkbox">
                    <input type="checkbox" name="is_multiple_answer">
                    Allow Multiple Correct Answers
                </label>
            </div>

            <!-- Solution PDF -->
            <div class="form-group">
                <label for="solution_pdf">Solution PDF (Optional)</label>
                <input type="file" name="solution_pdf" id="solution_pdf" accept="application/pdf">
            </div>

            <button type="submit" class="btn">Save Question</button>
        </form>
    </div>
</body>
</html>