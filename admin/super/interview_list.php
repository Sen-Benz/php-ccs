<?php
require_once '../../classes/Auth.php';
require_once '../../config/database.php';
require_once '../../classes/Email.php';
require_once '../includes/layout.php';

$auth = new Auth();
$auth->requireRole('super_admin');

$user = $auth->getCurrentUser();
$error = '';
$success = '';

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Get filter parameters
    $status = $_GET['status'] ?? 'all';
    $date = $_GET['date'] ?? '';
    $search = $_GET['search'] ?? '';

    // Build query conditions
    $conditions = [];
    $params = [];

    if ($status !== 'all') {
        $conditions[] = "i.status = ?";
        $params[] = $status;
    }

    if ($date) {
        $conditions[] = "i.schedule_date = ?";
        $params[] = $date;
    }

    if ($search) {
        $conditions[] = "(CONCAT(a.first_name, ' ', a.last_name) LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $where_clause = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

    // Get interview schedules
    $query = "SELECT 
                i.*,
                CONCAT(a.first_name, ' ', a.last_name) as applicant_name,
                a.contact_number as applicant_contact,
                u.email as applicant_email,
                CONCAT(u2.first_name, ' ', u2.last_name) as interviewer_name,
                u2.role as interviewer_role
              FROM interview_schedules i
              JOIN applicants a ON i.applicant_id = a.id
              JOIN users u ON a.user_id = u.id
              JOIN users u2 ON i.interviewer_id = u2.id
              $where_clause
              ORDER BY i.schedule_date ASC, i.start_time ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $interviews = $stmt->fetchAll();

    // Handle status updates
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $interview_id = $_POST['interview_id'] ?? '';
        $new_status = $_POST['status'] ?? '';
        $cancel_reason = $_POST['cancel_reason'] ?? '';

        if (!$interview_id || !$new_status) {
            throw new Exception('Invalid request.');
        }

        // Start transaction
        $conn->beginTransaction();

        try {
            // Update interview status
            $query = "UPDATE interview_schedules SET status = ?, notes = CONCAT(COALESCE(notes, ''), ?\n) WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                $new_status,
                $new_status === 'cancelled' ? "Cancellation reason: $cancel_reason" : '',
                $interview_id
            ]);

            // If cancelled, update applicant status back to part2_completed
            if ($new_status === 'cancelled') {
                $query = "UPDATE applicants a
                         JOIN interview_schedules i ON a.id = i.applicant_id
                         SET a.progress_status = 'part2_completed'
                         WHERE i.id = ?";
                $stmt = $conn->prepare($query);
                $stmt->execute([$interview_id]);

                // Send cancellation email
                $email = new Email();
                $query = "SELECT 
                            i.*,
                            CONCAT(a.first_name, ' ', a.last_name) as applicant_name,
                            u.email,
                            CONCAT(u2.first_name, ' ', u2.last_name) as interviewer_name
                          FROM interview_schedules i
                          JOIN applicants a ON i.applicant_id = a.id
                          JOIN users u ON a.user_id = u.id
                          JOIN users u2 ON i.interviewer_id = u2.id
                          WHERE i.id = ?";
                $stmt = $conn->prepare($query);
                $stmt->execute([$interview_id]);
                $interview_data = $stmt->fetch();
                $interview_data['cancel_reason'] = $cancel_reason;

                if (!$email->sendInterviewCancellation($interview_data['email'], $interview_data['applicant_name'], $interview_data)) {
                    // Log email error but don't stop the process
                    error_log("Failed to send interview cancellation email: " . $email->getError());
                }
            }

            // Send notification
            $query = "INSERT INTO notifications (user_id, title, message, type)
                      SELECT 
                        a.user_id,
                        CASE ? 
                            WHEN 'cancelled' THEN 'Interview Cancelled'
                            WHEN 'completed' THEN 'Interview Completed'
                            ELSE 'Interview Status Updated'
                        END,
                        CASE ? 
                            WHEN 'cancelled' THEN CONCAT('Your interview scheduled for ', 
                                DATE_FORMAT(i.schedule_date, '%M %d, %Y'), ' has been cancelled. Reason: ', ?)
                            WHEN 'completed' THEN 'Your interview has been marked as completed.'
                            ELSE 'Your interview status has been updated.'
                        END,
                        'interview'
                      FROM interview_schedules i
                      JOIN applicants a ON i.applicant_id = a.id
                      WHERE i.id = ?";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([
                $new_status,
                $new_status,
                $cancel_reason,
                $interview_id
            ]);

            $conn->commit();
            $success = 'Interview status has been updated successfully.';

            // Refresh interview list
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            $interviews = $stmt->fetchAll();

        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}

admin_header('Interview Schedule');
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">Interview Schedule</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="interview_results.php" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-clipboard-check"></i> View Results
                </a>
                <a href="schedule_interview.php" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i> Schedule New Interview
                </a>
            </div>
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

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search by name...">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="scheduled" <?php echo $status === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                        <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" class="form-control" id="date" name="date" 
                           value="<?php echo $date; ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Interview List -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($interviews)): ?>
                <p class="text-center text-muted my-5">No interviews found.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Applicant</th>
                                <th>Interviewer</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($interviews as $interview): ?>
                                <tr>
                                    <td>
                                        <?php echo date('F d, Y', strtotime($interview['schedule_date'])); ?><br>
                                        <small class="text-muted">
                                            <?php echo date('h:i A', strtotime($interview['start_time'])) . ' - ' . 
                                                 date('h:i A', strtotime($interview['end_time'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($interview['applicant_name']); ?></strong><br>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($interview['applicant_email']); ?><br>
                                            <?php echo htmlspecialchars($interview['applicant_contact']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($interview['interviewer_name']); ?><br>
                                        <span class="badge bg-secondary">
                                            <?php echo strtoupper($interview['interviewer_role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge <?php 
                                                echo match($interview['status']) {
                                                    'scheduled' => 'bg-primary',
                                                    'completed' => 'bg-success',
                                                    'cancelled' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                            ?>">
                                                <?php echo ucfirst($interview['status']); ?>
                                            </span>
                                            <?php if ($interview['status'] === 'scheduled'): ?>
                                                <a href="<?php echo htmlspecialchars($interview['meeting_link']); ?>" 
                                                   target="_blank" class="btn btn-sm btn-primary ms-2">
                                                    <i class="bi bi-camera-video"></i> Join
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                    data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <?php if ($interview['status'] === 'scheduled'): ?>
                                                    <li>
                                                        <a href="#" class="dropdown-item" 
                                                           onclick="updateStatus(<?php echo $interview['id']; ?>, 'completed')">
                                                            <i class="bi bi-check-circle"></i> Mark as Completed
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#" class="dropdown-item text-danger"
                                                           onclick="cancelInterview(<?php echo $interview['id']; ?>)">
                                                            <i class="bi bi-x-circle"></i> Cancel Interview
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                                <li>
                                                    <a href="view_interview.php?id=<?php echo $interview['id']; ?>" 
                                                       class="dropdown-item">
                                                        <i class="bi bi-eye"></i> View Details
                                                    </a>
                                                </li>
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
</div>

<!-- Cancel Interview Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="cancelForm" method="post">
                    <input type="hidden" name="interview_id" id="cancel_interview_id">
                    <input type="hidden" name="status" value="cancelled">
                    
                    <div class="mb-3">
                        <label for="cancel_reason" class="form-label">Reason for Cancellation</label>
                        <textarea class="form-control" id="cancel_reason" name="cancel_reason" 
                                  rows="3" required></textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-danger">Cancel Interview</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function updateStatus(interviewId, status) {
    if (confirm('Are you sure you want to mark this interview as ' + status + '?')) {
        const form = document.createElement('form');
        form.method = 'post';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'interview_id';
        idInput.value = interviewId;
        
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = status;
        
        form.appendChild(idInput);
        form.appendChild(statusInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function cancelInterview(interviewId) {
    document.getElementById('cancel_interview_id').value = interviewId;
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}
</script>

<?php
admin_footer();
?>
