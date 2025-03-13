<?php
session_start();

if ($_SESSION['role'] != 'question_setter') {
    header("Location: ../index.php");
    exit();
}

require '../db.php';

// Initialize variables
$selected_subject = $_POST['subject_id'] ?? null;
$selected_chapter = $_POST['chapter_id'] ?? null;
$selected_subconcept = $_POST['subconcept_id'] ?? null;

// Fetch grades
$grades = $conn->query("SELECT grade_level FROM grades ORDER BY grade_level")->fetchAll(PDO::FETCH_COLUMN, 0);

// Fetch subjects
$subjects = $conn->query("SELECT * FROM subjects")->fetchAll();

// Fetch chapters based on selected subject
if ($selected_subject) {
    $stmt = $conn->prepare("SELECT * FROM chapters WHERE subject_id = ?");
    $stmt->execute([$selected_subject]);
    $chapters = $stmt->fetchAll();
} elseif (count($subjects) > 0) {
    $selected_subject = $subjects[0]['id'];
    $stmt = $conn->prepare("SELECT * FROM chapters WHERE subject_id = ?");
    $stmt->execute([$selected_subject]);
    $chapters = $stmt->fetchAll();
}

// Fetch subconcepts based on selected chapter
if ($selected_chapter) {
    $stmt = $conn->prepare("SELECT * FROM subconcepts WHERE chapter_id = ?");
    $stmt->execute([$selected_chapter]);
    $subconcepts = $stmt->fetchAll();
} elseif (isset($chapters[0])) {
    $selected_chapter = $chapters[0]['id'];
    $stmt = $conn->prepare("SELECT * FROM subconcepts WHERE chapter_id = ?");
    $stmt->execute([$selected_chapter]);
    $subconcepts = $stmt->fetchAll();
}

// Handle question submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'save_question') {
    try {
        // Validate required fields
        $required = ['grade', 'subject_id', 'chapter_id', 'question'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Required field '$field' is missing");
            }
        }

        // Process question data
        $question_data = [
            'grade_level' => $_POST['grade'],
            'subject_id' => $_POST['subject_id'],
            'chapter_id' => $_POST['chapter_id'],
            'subconcept_id' => $_POST['subconcept_id'] ?? null,
            'question' => $_POST['question'],
            'is_multiple_answer' => isset($_POST['is_multiple_answer']) ? 1 : 0,
            'created_by' => $_SESSION['user_id'],
            'options' => [],
            'question_image' => null,
            'solution_pdf' => null
        ];

        // Handle file uploads
        $upload_config = [
            'question_image' => [
                'dir' => 'question_images',
                'types' => ['image/*']
            ],
            'solution_pdf' => [
                'dir' => 'solution_pdfs',
                'types' => ['application/pdf']
            ]
        ];

        foreach ($upload_config as $field => $config) {
            if (!empty($_FILES[$field]['name'])) {
                $upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/Online_Exam_System/question_setter/{$config['dir']}/";
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                
                $filename = uniqid() . '_' . basename($_FILES[$field]['name']);
                $target_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES[$field]['tmp_name'], $target_path)) {
                    $question_data[$field] = "{$config['dir']}/$filename";
                }
            }
        }

        // Process options
        if (empty($_POST['options']) || !is_array($_POST['options'])) {
            throw new Exception("At least one option is required");
        }

        foreach ($_POST['options'] as $index => $option_text) {
            $option = [
                'text' => $option_text,
                'image' => null,
                'is_correct' => in_array($index, $_POST['correct_answer'] ?? []) ? 1 : 0
            ];

            // Handle option images
            if (!empty($_FILES['options']['name'][$index]['image'])) {
                $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/Online_Exam_System/question_setter/question_images/';
                $filename = uniqid() . '_' . basename($_FILES['options']['name'][$index]['image']);
                $target_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['options']['tmp_name'][$index]['image'], $target_path)) {
                    $option['image'] = 'question_images/' . $filename;
                }
            }

            $question_data['options'][] = $option;
        }

        // Insert into database
        $stmt = $conn->prepare("INSERT INTO questions 
            (grade_level, subject_id, chapter_id, subconcept_id, question, options, 
            is_multiple_answer, question_image, solution_pdf, created_by) 
            VALUES (:grade_level, :subject_id, :chapter_id, :subconcept_id, :question, 
            :options, :is_multiple_answer, :question_image, :solution_pdf, :created_by)");

        $question_data['options'] = json_encode($question_data['options']);
        
        if ($stmt->execute($question_data)) {
            echo "<div class='alert alert-success'>Question added successfully!</div>";
            // Clear form values after successful submission
            unset($_POST);
            $selected_subject = $selected_chapter = $selected_subconcept = null;
        }

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
            
            const optionDiv = document.createElement('div');
            optionDiv.className = 'option-group';
            optionDiv.innerHTML = `
                <div class="option-input">
                    <input type="text" name="options[]" placeholder="Option text" required>
                    <input type="file" name="options[${index}][image]" accept="image/*">
                </div>
                <label class="checkbox">
                    <input type="checkbox" name="correct_answer[]" value="${index}">
                    Correct Answer
                </label>
            `;
            container.appendChild(optionDiv);
        }

        function fetchChapters() {
            const subjectId = document.getElementById('subject_id').value;
            fetch('get_chapters.php?subject_id=' + subjectId)
                .then(response => response.json())
                .then(data => {
                    const chapterSelect = document.getElementById('chapter_id');
                    chapterSelect.innerHTML = '';
                    data.forEach(chapter => {
                        const option = document.createElement('option');
                        option.value = chapter.id;
                        option.textContent = chapter.chapter_name;
                        chapterSelect.appendChild(option);
                    });
                    fetchSubconcepts(); // Fetch subconcepts for the first chapter
                });
        }

        function fetchSubconcepts() {
            const chapterId = document.getElementById('chapter_id').value;
            fetch('get_subconcepts.php?chapter_id=' + chapterId)
                .then(response => response.json())
                .then(data => {
                    const subconceptSelect = document.getElementById('subconcept_id');
                    subconceptSelect.innerHTML = '';
                    data.forEach(subconcept => {
                        const option = document.createElement('option');
                        option.value = subconcept.id;
                        option.textContent = subconcept.subconcept_name;
                        subconceptSelect.appendChild(option);
                    });
                });
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Add Questions</h1>
        <a href="dashboard.php" class="btn">Back to Dashboard</a>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="form_type" value="save_question">

            <!-- Grade Selection -->
            <div class="form-group">
                <label for="grade">Grade</label>
                <select name="grade" id="grade" required>
                    <?php foreach ($grades as $grade): ?>
                        <option value="<?= $grade ?>" <?= ($_POST['grade'] ?? '') == $grade ? 'selected' : '' ?>>
                            Grade <?= $grade ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Subject Selection -->
            <div class="form-group">
                <label for="subject_id">Subject</label>
                <select name="subject_id" id="subject_id" required onchange="fetchChapters()">
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?= $subject['id'] ?>" 
                            <?= ($selected_subject == $subject['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($subject['subject_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Chapter Selection -->
            <div class="form-group">
                <label for="chapter_id">Chapter</label>
                <select name="chapter_id" id="chapter_id" required onchange="fetchSubconcepts()">
                    <?php if (empty($chapters)): ?>
                        <option value="">No chapters found</option>
                    <?php else: ?>
                        <?php foreach ($chapters as $chapter): ?>
                            <option value="<?= $chapter['id'] ?>" 
                                <?= ($selected_chapter == $chapter['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($chapter['chapter_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Subconcept Selection -->
            <div class="form-group">
                <label for="subconcept_id">Subconcept</label>
                <select name="subconcept_id" id="subconcept_id">
                    <?php if (empty($subconcepts)): ?>
                        <option value="">No subconcepts found</option>
                    <?php else: ?>
                        <?php foreach ($subconcepts as $subconcept): ?>
                            <option value="<?= $subconcept['id'] ?>" 
                                <?= ($selected_subconcept == $subconcept['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($subconcept['subconcept_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Question Input -->
            <div class="form-group">
                <label for="question">Question</label>
                <textarea name="question" id="question" required><?= $_POST['question'] ?? '' ?></textarea>
            </div>

            <!-- Question Image -->
            <div class="form-group">
                <label for="question_image">Question Image (Optional)</label>
                <input type="file" name="question_image" id="question_image" accept="image/*">
            </div>

            <!-- Options Container -->
            <div class="form-group">
                <label>Options (Mark correct answers)</label>
                <div id="options-container">
                    <?php for($i = 0; $i < max(2, count($_POST['options'] ?? [])); $i++): ?>
                        <div class="option-group">
                            <div class="option-input">
                                <input type="text" name="options[]" 
                                    value="<?= htmlspecialchars($_POST['options'][$i] ?? '') ?>" 
                                    placeholder="Option text" required>
                                <input type="file" name="options[<?= $i ?>][image]" accept="image/*">
                            </div>
                            <label class="checkbox">
                                <input type="checkbox" name="correct_answer[]" value="<?= $i ?>"
                                    <?= isset($_POST['correct_answer']) && in_array($i, $_POST['correct_answer']) ? 'checked' : '' ?>>
                                Correct
                            </label>
                        </div>
                    <?php endfor; ?>
                </div>
                <button type="button" onclick="addOption()" class="btn">Add Option</button>
            </div>

            <!-- Multiple Answers -->
            <div class="form-group">
                <label class="checkbox">
                    <input type="checkbox" name="is_multiple_answer" <?= isset($_POST['is_multiple_answer']) ? 'checked' : '' ?>>
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