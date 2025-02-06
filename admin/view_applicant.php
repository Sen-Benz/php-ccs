<?php
require_once '../config/config.php';
require_once '../classes/Auth.php';
require_once '../config/database.php';
require_once './includes/layout.php';

// Initialize Auth and Database
$auth = new Auth();
$auth->requireRole('admin');
$db = new Database();

// Get applicant ID from URL
$applicant_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$applicant_id) {
    header('Location: manage_applicants.php');
    exit;
}

try {
    // Fetch applicant details
    $stmt = $db->query(
        "SELECT * FROM users WHERE id = ? AND role = 'applicant'",
        [$applicant_id]
    );
    $applicant = $stmt->fetch();

    if (!$applicant) {
        throw new Exception('Applicant not found.');
    }

    // Fetch exam results
    $stmt = $db->query(
        "SELECT er.*, e.exam_title 
         FROM exam_results er
         JOIN exams e ON er.exam_id = e.id
         WHERE er.user_id = ?
         ORDER BY er.created_at DESC",
        [$applicant_id]
    );
    $exam_results = $stmt->fetchAll();

    // Fetch interview records
    $stmt = $db->query(
        "SELECT * FROM interviews 
         WHERE user_id = ?
         ORDER BY schedule DESC",
        [$applicant_id]
    );
    $interviews = $stmt->fetchAll();

    // Handle status update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        $new_status = $_POST['status'];
        $notes = $_POST['notes'];
        
        $db->beginTransaction();
        try {
            // Update user status
            $db->query(
                "UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?",
                [$new_status, $applicant_id]
            );

            // Add status history
            $db->query(
                "INSERT INTO status_history (user_id, status, notes, created_by) VALUES (?, ?, ?, ?)",
                [$applicant_id, $new_status, $notes, $auth->getCurrentUser()['id']]
            );

            $db->commit();
            $success = "Applicant status updated successfully.";
            
            // Refresh applicant data
            $stmt = $db->query(
                "SELECT * FROM users WHERE id = ? AND role = 'applicant'",
                [$applicant_id]
            );
            $applicant = $stmt->fetch();
        } catch (Exception $e) {
            $db->rollback();
            $error = "Failed to update applicant status.";
            error_log("Error updating applicant status: " . $e->getMessage());
        }
    }

} catch (Exception $e) {
    error_log("Error in view_applicant.php: " . $e->getMessage());
    $error = "An error occurred while fetching applicant details.";
}

admin_header('View Applicant');
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include './includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <!-- Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Applicant Details</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="manage_applicants.php">Manage Applicants</a></li>
                        <li class="breadcrumb-item active">View Applicant</li>
                    </ol>
                </nav>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Applicant Information -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Personal Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">Full Name</label>
                                <p class="h5"><?php echo htmlspecialchars($applicant['first_name'] . ' ' . $applicant['last_name']); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Email</label>
                                <p><?php echo htmlspecialchars($applicant['email']); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Contact Number</label>
                                <p><?php echo htmlspecialchars($applicant['contact_number']); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Preferred Course</label>
                                <p><?php echo htmlspecialchars($applicant['program']); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Registration Date</label>
                                <p><?php echo date('F d, Y', strtotime($applicant['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Status Update Form -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Update Status</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Current Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="pending" <?php echo $applicant['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="approved" <?php echo $applicant['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="rejected" <?php echo $applicant['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Add notes about this status change"></textarea>
                                </div>
                                <button type="submit" name="update_status" class="btn btn-primary">
                                    Update Status
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <!-- Exam Results -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Exam Results</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($exam_results)): ?>
                                <p class="text-muted">No exam results found.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Exam</th>
                                                <th>Score</th>
                                                <th>Status</th>
                                                <th>Date Taken</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($exam_results as $result): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($result['exam_title']); ?></td>
                                                    <td><?php echo htmlspecialchars($result['score']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $result['status'] === 'passed' ? 'success' : 'danger'; ?>">
                                                            <?php echo ucfirst($result['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('M d, Y h:i A', strtotime($result['created_at'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Interview History -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Interview History</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($interviews)): ?>
                                <p class="text-muted">No interviews scheduled.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Schedule</th>
                                                <th>Status</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($interviews as $interview): ?>
                                                <tr>
                                                    <td><?php echo date('M d, Y h:i A', strtotime($interview['schedule'])); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php 
                                                            echo $interview['status'] === 'completed' ? 'success' : 
                                                                ($interview['status'] === 'scheduled' ? 'info' : 'warning'); 
                                                        ?>">
                                                            <?php echo ucfirst($interview['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($interview['notes'] ?? ''); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.card {
    margin-bottom: 1.5rem;
    box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
}

.table > :not(caption) > * > * {
    padding: 0.75rem;
}

@media print {
    .sidebar-wrapper, .btn-toolbar, form {
        display: none !important;
    }
    
    .col-md-9 {
        width: 100% !important;
        margin: 0 !important;
    }
}
</style>

<?php admin_footer(); ?>
