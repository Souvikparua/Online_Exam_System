<?php
session_start();
if ($_SESSION['role'] != 'question_setter') {
    header("Location: ../index.php");
    exit();
}

require '../db.php';

// Fetch question details
if (isset($_GET['id'])) {
    $question_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM questions WHERE id = :id");
    $stmt->execute(['id' => $question_id]);
    $question = $stmt->fetch();

    if (!$question) {
        die("Question not found!");
    }

    // Decode options from JSON
    $options = json_decode($question['options'], true);
} else {
    die("Invalid request!");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $question_text = $_POST['question'];
        $is_multiple_answer = isset($_POST['is_multiple_answer']) ? 1 : 0;
        $updated_options = [];

        // Handle question image upload
        $question_image = $question['question_image'];
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
        $solution_pdf = $question['solution_pdf'];
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
            $option_image = $options[$index]['image'] ?? null;

            // Handle option image upload
            if ($_FILES['options']['size'][$index]['image'] > 0) {
                $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/Online_Exam_System/question_setter/question_images/';
                $filename = uniqid() . '_' . basename($_FILES['options']['name'][$index]['image']);
                $target_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['options']['tmp_name'][$index]['image'], $target_path)) {
                    $option_image = 'question_images/' . $filename;
                }
            }

            $updated_options[] = [
                'text' => $option_text,
                'image' => $option_image,
                'is_correct' => in_array($index, $_POST['correct_answers'] ?? []) ? 1 : 0
            ];
        }

        // Update question in the database
        $stmt = $conn->prepare("
            UPDATE questions 
            SET question = :question, 
                options = :options, 
                is_multiple_answer = :is_multiple_answer, 
                question_image = :question_image, 
                solution_pdf = :solution_pdf 
            WHERE id = :id
        ");
        $stmt->execute([
            'question' => $question_text,
            'options' => json_encode($updated_options),
            'is_multiple_answer' => $is_multiple_answer,
            'question_image' => $question_image,
            'solution_pdf' => $solution_pdf,
            'id' => $question_id
        ]);

        echo "<div class='alert alert-success'>Question updated successfully!</div>";
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
    <title>Edit Question</title>
    <link rel="stylesheet" href="../assets/css/edit_question.css">
    <script>
        // Add dynamic option fields
        function addOption() {
            const optionsContainer = document.getElementById('options-container');
            const optionIndex = optionsContainer.children.length;

            const optionDiv = document.createElement('div');
            optionDiv.className = 'option';

            optionDiv.innerHTML = `
                <label>Option ${optionIndex + 1}</label>
                <input type="text" name="options[]" placeholder="Enter option text" required>
                <input type="file" name="options[${optionIndex}][image]" accept="image/*">
                <input type="checkbox" name="correct_answers[]" value="${optionIndex}"> Correct Answer
                <button type="button" onclick="removeOption(this)" class="btn btn-danger">Remove</button>
            `;

            optionsContainer.appendChild(optionDiv);
        }

        // Remove an option field
        function removeOption(button) {
            button.parentElement.remove();
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Edit Question</h1>
        <a href="manage_questions.php" class="btn">Back to Manage Questions</a>

        <!-- Edit Question Form -->
        <form method="POST" enctype="multipart/form-data">
            <!-- Question Text -->
            <div class="form-group">
                <label for="question">Question</label>
                <textarea name="question" id="question" required><?php echo htmlspecialchars($question['question']); ?></textarea>
            </div>

            <!-- Question Image -->
            <div class="form-group">
                <label for="question_image">Question Image (Optional)</label>
                <input type="file" name="question_image" id="question_image" accept="image/*">
                <?php if ($question['question_image']): ?>
                    <p>Current Image: <a href="../question_setter/<?php echo $question['question_image']; ?>" target="_blank">View Image</a></p>
                <?php endif; ?>
            </div>

            <!-- Options -->
            <div class="form-group">
                <label>Options</label>
                <div id="options-container">
                    <?php foreach ($options as $index => $option): ?>
                        <div class="option">
                            <label>Option <?php echo $index + 1; ?></label>
                            <input type="text" name="options[]" value="<?php echo htmlspecialchars($option['text']); ?>" required>
                            <input type="file" name="options[<?php echo $index; ?>][image]" accept="image/*">
                            <?php if ($option['image']): ?>
                                <p>Current Image: <a href="../question_setter/<?php echo $option['image']; ?>" target="_blank">View Image</a></p>
                            <?php endif; ?>
                            <input type="checkbox" name="correct_answers[]" value="<?php echo $index; ?>" <?php echo $option['is_correct'] ? 'checked' : ''; ?>> Correct Answer
                            <button type="button" onclick="removeOption(this)" class="btn btn-danger">Remove</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" onclick="addOption()" class="btn">Add Option</button>
            </div>

            <!-- Multiple Answers -->
            <div class="form-group">
                <label class="checkbox">
                    <input type="checkbox" name="is_multiple_answer" <?php echo $question['is_multiple_answer'] ? 'checked' : ''; ?>>
                    Allow Multiple Correct Answers
                </label>
            </div>

            <!-- Solution PDF -->
            <div class="form-group">
                <label for="solution_pdf">Solution PDF (Optional)</label>
                <input type="file" name="solution_pdf" id="solution_pdf" accept="application/pdf">
                <?php if ($question['solution_pdf']): ?>
                    <p>Current PDF: <a href="../<?php echo $question['solution_pdf']; ?>" target="_blank">View PDF</a></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn">Update Question</button>
        </form>
    </div>
</body>
</html>