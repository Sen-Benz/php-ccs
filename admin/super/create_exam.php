<?php
require_once '../../classes/Auth.php';
require_once '../includes/layout.php';

$auth = new Auth();
$auth->requireRole('super_admin');

$user = $auth->getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $type = $_POST['type'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $passing_score = $_POST['passing_score'] ?? '';

    if (empty($title) || empty($type) || empty($duration) || empty($passing_score)) {
        $error = 'All fields except description are required';
    } elseif (!is_numeric($duration) || $duration <= 0) {
        $error = 'Duration must be a positive number';
    } elseif (!is_numeric($passing_score) || $passing_score < 0 || $passing_score > 100) {
        $error = 'Passing score must be between 0 and 100';
    } else {
        try {
            $database = new Database();
            $conn = $database->getConnection();

            $query = "INSERT INTO exams (title, description, type, duration_minutes, passing_score, status, created_by) 
                     VALUES (?, ?, ?, ?, ?, 'draft', ?)";
            $stmt = $conn->prepare($query);
            $stmt->execute([$title, $description, $type, $duration, $passing_score, $user['id']]);
            
            $exam_id = $conn->lastInsertId();

            $auth->logActivity(
                $user['id'],
                'exam_created',
                "Created new exam: $title"
            );

            // Redirect to question management
            header("Location: manage_questions.php?exam_id=$exam_id");
            exit();
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

admin_header('Create New Exam');
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Create New Exam</h1>
</div>

<?php if ($error): ?>
<div class="alert alert-danger" role="alert">
    <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="title" class="form-label">Exam Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
                <div class="invalid-feedback">
                    Please enter an exam title
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description (Optional)</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="type" class="form-label">Exam Type</label>
                    <select class="form-select" id="type" name="type" required>
                        <option value="">Select type...</option>
                        <option value="mcq">Multiple Choice</option>
                        <option value="coding">Coding</option>
                    </select>
                    <div class="invalid-feedback">
                        Please select an exam type
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="duration" class="form-label">Duration (minutes)</label>
                    <input type="number" class="form-control" id="duration" name="duration" min="1" required>
                    <div class="invalid-feedback">
                        Please enter a valid duration
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="passing_score" class="form-label">Passing Score (%)</label>
                    <input type="number" class="form-control" id="passing_score" name="passing_score" min="0" max="100" required>
                    <div class="invalid-feedback">
                        Please enter a valid passing score (0-100)
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="list_exams.php" class="btn btn-outline-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    Create Exam
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php
admin_footer();
?>
