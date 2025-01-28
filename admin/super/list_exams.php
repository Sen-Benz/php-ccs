<?php
require_once '../../classes/Auth.php';
require_once '../includes/layout.php';

$auth = new Auth();
$auth->requireRole('super_admin');

$user = $auth->getCurrentUser();
$error = '';
$success = '';

// Handle exam status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['exam_id'])) {
        $exam_id = $_POST['exam_id'];
        $action = $_POST['action'];

        try {
            $database = new Database();
            $conn = $database->getConnection();

            switch ($action) {
                case 'publish':
                    $status = 'published';
                    break;
                case 'archive':
                    $status = 'archived';
                    break;
                case 'draft':
                    $status = 'draft';
                    break;
                default:
                    throw new Exception('Invalid action');
            }

            $query = "UPDATE exams SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$status, $exam_id]);

            if ($stmt->rowCount() > 0) {
                $auth->logActivity(
                    $user['id'],
                    'exam_' . $action,
                    "Exam (ID: $exam_id) status changed to $status"
                );
                $success = 'Exam status updated successfully';
            } else {
                $error = 'Failed to update exam status';
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

// Fetch all exams
try {
    $database = new Database();
    $conn = $database->getConnection();

    $query = "SELECT e.*, 
              CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
              (SELECT COUNT(*) FROM questions WHERE exam_id = e.id) as question_count
              FROM exams e
              JOIN users u ON e.created_by = u.id
              ORDER BY e.part ASC, e.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group exams by part
    $examsByPart = [];
    foreach ($exams as $exam) {
        $examsByPart[$exam['part']][] = $exam;
    }

} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
    $exams = [];
}

admin_header('Manage Exams');
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Exams</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="create_exam.php" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg"></i> Create New Exam
        </a>
    </div>
</div>

<?php if ($error): ?>
<div class="alert alert-danger" role="alert">
    <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success" role="alert">
    <?php echo htmlspecialchars($success); ?>
</div>
<?php endif; ?>

<?php foreach ($examsByPart as $part => $partExams): ?>
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h3 class="h5 mb-0">Part <?php echo $part; ?></h3>
        </div>
        <div class="card-body">
            <?php if (empty($partExams)): ?>
                <p class="text-center text-muted my-5">No exams found for Part <?php echo $part; ?></p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Duration</th>
                                <th>Questions</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($partExams as $exam): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($exam['title']); ?>
                                    <?php if (!empty($exam['description'])): ?>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($exam['description']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $exam['type'] === 'mcq' ? 'bg-info' : 'bg-warning'; ?>">
                                        <?php echo strtoupper($exam['type']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($exam['duration_minutes']); ?> mins</td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo htmlspecialchars($exam['question_count']); ?> questions
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php 
                                        echo $exam['status'] === 'published' ? 'bg-success' : 
                                             ($exam['status'] === 'draft' ? 'bg-warning' : 'bg-secondary'); 
                                    ?>">
                                        <?php echo ucfirst(htmlspecialchars($exam['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($exam['created_by_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($exam['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="edit_exam.php?id=<?php echo $exam['id']; ?>">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="manage_questions.php?exam_id=<?php echo $exam['id']; ?>">
                                                    <i class="bi bi-list-check"></i> Manage Questions
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <?php if ($exam['status'] !== 'published'): ?>
                                            <li>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="exam_id" value="<?php echo $exam['id']; ?>">
                                                    <input type="hidden" name="action" value="publish">
                                                    <button type="submit" class="dropdown-item text-success">
                                                        <i class="bi bi-check-circle"></i> Publish
                                                    </button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                            <?php if ($exam['status'] !== 'draft'): ?>
                                            <li>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="exam_id" value="<?php echo $exam['id']; ?>">
                                                    <input type="hidden" name="action" value="draft">
                                                    <button type="submit" class="dropdown-item text-warning">
                                                        <i class="bi bi-pencil-square"></i> Move to Draft
                                                    </button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                            <?php if ($exam['status'] !== 'archived'): ?>
                                            <li>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="exam_id" value="<?php echo $exam['id']; ?>">
                                                    <input type="hidden" name="action" value="archive">
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-archive"></i> Archive
                                                    </button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

<?php
admin_footer();
?>
