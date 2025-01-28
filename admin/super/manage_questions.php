<?php
require_once '../../classes/Auth.php';
require_once '../includes/layout.php';

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$auth = new Auth();
$auth->requireRole('super_admin');

$user = $auth->getCurrentUser();
$error = '';
$success = '';

// Get exam ID from URL
$exam_id = $_GET['exam_id'] ?? null;

if (!$exam_id) {
    header('Location: list_exams.php');
    exit();
}

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Get exam details
    $query = "SELECT * FROM exams WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$exam_id]);
    $exam = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$exam) {
        header('Location: list_exams.php');
        exit();
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'add_question') {
            $question_text = $_POST['question_text'] ?? '';
            $question_type = $_POST['question_type'] ?? '';
            $points = $_POST['points'] ?? 1;
            
            if (empty($question_text) || empty($question_type)) {
                throw new Exception('Question text and type are required');
            }

            if ($question_type === 'multiple_choice') {
                // Handle multiple choice question
                $options = array_values(array_filter($_POST['options'] ?? [], fn($opt) => !empty($opt)));
                $correct_answer = $_POST['correct_answer'] ?? '';
                $explanation = $_POST['explanation'] ?? '';

                if (count($options) < 2) {
                    throw new Exception('At least two options are required');
                }
                if (!isset($options[$correct_answer])) {
                    throw new Exception('Please select a valid correct answer');
                }

                $query = "INSERT INTO questions (exam_id, question_text, question_type, points, options, correct_answer, explanation) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $result = $stmt->execute([
                    $exam_id,
                    $question_text,
                    $question_type,
                    $points,
                    json_encode($options),
                    $correct_answer,
                    $explanation
                ]);

                if (!$result) {
                    throw new Exception('Failed to add question');
                }
            } else {
                // Handle coding question
                $coding_template = $_POST['coding_template'] ?? '';
                $solution = $_POST['solution'] ?? '';
                $explanation = $_POST['explanation'] ?? '';
                
                if (empty($coding_template)) {
                    throw new Exception('Code snippet is required for coding questions');
                }

                $query = "INSERT INTO questions (exam_id, question_text, question_type, points, coding_template, solution, explanation) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $result = $stmt->execute([
                    $exam_id,
                    $question_text,
                    $question_type,
                    $points,
                    $coding_template,
                    $solution,
                    $explanation
                ]);

                if (!$result) {
                    throw new Exception('Failed to add question');
                }
            }

            $auth->logActivity(
                $user['id'],
                'question_added',
                "Added new question to exam ID: $exam_id"
            );

            $_SESSION['success_message'] = 'Question added successfully';
            header("Location: manage_questions.php?exam_id=$exam_id");
            exit();
        }
    }

    // Get all questions for this exam
    $query = "SELECT * FROM questions WHERE exam_id = ? ORDER BY id ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute([$exam_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error = $e->getMessage();
}

admin_header('Manage Questions');
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">Manage Questions</h1>
        <p class="text-muted">
            Exam: <?php echo htmlspecialchars($exam['title']); ?>
            (<?php echo ucfirst($exam['type']); ?>)
        </p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
            <i class="bi bi-plus-lg"></i> Add Question
        </button>
    </div>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success" role="alert">
        <?php 
        echo htmlspecialchars($_SESSION['success_message']);
        unset($_SESSION['success_message']);
        ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($questions)): ?>
            <p class="text-center text-muted my-5">No questions added yet. Click the "Add Question" button to add your first question.</p>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">Question <?php echo $index + 1; ?></h5>
                            <small><?php echo ucfirst($question['question_type']); ?> - <?php echo $question['points']; ?> points</small>
                        </div>
                        <p class="mb-1"><?php echo nl2br(htmlspecialchars($question['question_text'])); ?></p>
                        
                        <?php if ($question['question_type'] === 'multiple_choice'): ?>
                            <?php 
                            $options = json_decode($question['options'], true);
                            if ($options): 
                            ?>
                                <div class="ms-4 mb-2">
                                    <?php foreach ($options as $i => $option): ?>
                                        <div class="<?php echo $i == $question['correct_answer'] ? 'text-success fw-bold' : ''; ?>">
                                            <?php echo chr(65 + $i) . '. ' . htmlspecialchars($option); ?>
                                            <?php if ($i == $question['correct_answer']) echo ' ✓'; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if (!empty($question['coding_template'])): ?>
                                <div class="mb-3">
                                    <strong>Code Snippet (Find the missing syntax):</strong>
                                    <pre class="bg-light p-2 mt-1"><code><?php echo htmlspecialchars($question['coding_template']); ?></code></pre>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($question['solution'])): ?>
                                <div class="mb-3">
                                    <strong>Correct Solution:</strong>
                                    <pre class="bg-light p-2 mt-1"><code><?php echo htmlspecialchars($question['solution']); ?></code></pre>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (!empty($question['explanation'])): ?>
                            <div class="mt-2">
                                <strong>Explanation:</strong>
                                <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($question['explanation'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Question Modal -->
<div class="modal fade" id="addQuestionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="addQuestionForm">
                <input type="hidden" name="action" value="add_question">
                
                <div class="modal-header">
                    <h5 class="modal-title">Add Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="question_text" class="form-label">Question Text</label>
                        <textarea class="form-control" id="question_text" name="question_text" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="question_type" class="form-label">Question Type</label>
                        <select class="form-select" id="question_type" name="question_type" required>
                            <option value="">Select type...</option>
                            <option value="multiple_choice" <?php echo $exam['type'] === 'mcq' ? 'selected' : ''; ?>>Multiple Choice</option>
                            <option value="coding" <?php echo $exam['type'] === 'coding' ? 'selected' : ''; ?>>Coding</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="points" class="form-label">Points</label>
                        <input type="number" class="form-control" id="points" name="points" value="1" min="1" required>
                    </div>

                    <!-- Multiple Choice Fields -->
                    <div id="multipleChoiceFields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Options</label>
                            <div id="optionsContainer">
                                <div class="input-group mb-2">
                                    <div class="input-group-text">
                                        <input type="radio" name="correct_answer" value="0">
                                    </div>
                                    <input type="text" class="form-control" name="options[]" placeholder="Option 1">
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="addOption()">
                                <i class="bi bi-plus-lg"></i> Add Option
                            </button>
                        </div>
                    </div>

                    <!-- Coding Fields -->
                    <div id="codingFields" style="display: none;">
                        <div class="mb-3">
                            <label for="coding_template" class="form-label">Code Snippet</label>
                            <textarea class="form-control font-monospace" id="coding_template" name="coding_template" rows="3" placeholder="Example:
function sayHello() {
    console.log('Hello World'   // Missing semicolon here
"></textarea>
                            <div class="form-text">Provide the code snippet with missing syntax elements (semicolons, brackets, etc.)</div>
                        </div>

                        <div class="mb-3">
                            <label for="solution" class="form-label">Correct Answer</label>
                            <textarea class="form-control font-monospace" id="solution" name="solution" rows="3" placeholder="Example:
function sayHello() {
    console.log('Hello World');
}"></textarea>
                            <div class="form-text">The complete code with correct syntax</div>
                        </div>

                        <div class="mb-3">
                            <label for="explanation" class="form-label">Explanation</label>
                            <textarea class="form-control" id="explanation" name="explanation" rows="3" placeholder="Example: The code was missing a semicolon after the console.log statement and a closing curly brace for the function."></textarea>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="explanation" class="form-label">Explanation (Optional)</label>
                        <textarea class="form-control" id="explanation" name="explanation" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Question</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Question type fields toggle
document.getElementById('question_type').addEventListener('change', function() {
    const multipleChoiceFields = document.getElementById('multipleChoiceFields')
    const codingFields = document.getElementById('codingFields')
    const testCases = document.getElementById('test_cases')
    const options = document.querySelectorAll('input[name="options[]"]')
    const correctAnswer = document.querySelectorAll('input[name="correct_answer"]')
    
    // Remove required attributes from all fields first
    testCases?.removeAttribute('required')
    options.forEach(opt => opt.removeAttribute('required'))
    correctAnswer.forEach(radio => radio.removeAttribute('required'))
    
    if (this.value === 'multiple_choice') {
        multipleChoiceFields.style.display = 'block'
        codingFields.style.display = 'none'
        // Add required to multiple choice fields
        options.forEach(opt => opt.setAttribute('required', ''))
        correctAnswer[0].setAttribute('required', '')
    } else if (this.value === 'coding') {
        multipleChoiceFields.style.display = 'none'
        codingFields.style.display = 'block'
        // Add required to coding fields
        document.getElementById('coding_template').setAttribute('required', '')
    } else {
        multipleChoiceFields.style.display = 'none'
        codingFields.style.display = 'none'
    }
})

// Multiple choice options management
let optionCount = 1

function addOption() {
    optionCount++
    const container = document.getElementById('optionsContainer')
    const div = document.createElement('div')
    div.className = 'input-group mb-2'
    div.innerHTML = `
        <div class="input-group-text">
            <input type="radio" name="correct_answer" value="${optionCount - 1}">
        </div>
        <input type="text" class="form-control" name="options[]" placeholder="Option ${optionCount}" required>
        <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
            <i class="bi bi-trash"></i>
        </button>
    `
    container.appendChild(div)
}

function removeOption(button) {
    if (document.getElementById('optionsContainer').children.length > 1) {
        button.parentElement.remove()
        updateOptionNumbers()
    }
}

function updateOptionNumbers() {
    const options = document.getElementById('optionsContainer').children
    Array.from(options).forEach((option, index) => {
        option.querySelector('input[type="radio"]').value = index
        option.querySelector('input[type="text"]').placeholder = `Option ${index + 1}`
    })
    optionCount = options.length
}

// Form validation and submission
document.getElementById('addQuestionForm').addEventListener('submit', function(event) {
    event.preventDefault()
    
    const questionType = this.question_type.value
    
    // Validate question text
    if (!this.question_text.value.trim()) {
        alert('Please enter the question text')
        return
    }

    // Validate question type
    if (!questionType) {
        alert('Please select a question type')
        return
    }

    // Validate points
    if (!this.points.value || this.points.value < 1) {
        alert('Please enter valid points')
        return
    }

    // Additional validation for multiple choice
    if (questionType === 'multiple_choice') {
        const options = this.querySelectorAll('input[name="options[]"]')
        const validOptions = Array.from(options).filter(opt => opt.value.trim() !== '')
        if (validOptions.length < 2) {
            alert('Please add at least two options')
            return
        }
        if (!this.querySelector('input[name="correct_answer"]:checked')) {
            alert('Please select the correct answer')
            return
        }
    }

    // Additional validation for coding
    if (questionType === 'coding' && !this.coding_template.value.trim()) {
        alert('Please add the code snippet')
        return
    }

    // Submit the form
    this.submit()
})

// Reset form when modal is closed
document.getElementById('addQuestionModal').addEventListener('hidden.bs.modal', function () {
    const form = document.getElementById('addQuestionForm')
    form.reset()
    
    // Reset options container to initial state
    const container = document.getElementById('optionsContainer')
    while (container.children.length > 1) {
        container.removeChild(container.lastChild)
    }
    optionCount = 1
    updateOptionNumbers()
    
    // Reset question type fields
    document.getElementById('question_type').dispatchEvent(new Event('change'))
})

// Set initial question type based on exam type
document.getElementById('question_type').dispatchEvent(new Event('change'))
</script>

<?php
admin_footer();
?>
