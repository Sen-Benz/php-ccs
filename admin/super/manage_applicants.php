<?php
require_once '../../classes/Auth.php';
require_once '../../config/database.php';
require_once '../includes/layout.php';

$auth = new Auth();
$auth->requireRole('super_admin');

$user = $auth->getCurrentUser();
$error = '';
$success = '';

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Handle applicant approval/rejection
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action']) && isset($_POST['applicant_id'])) {
            $applicant_id = $_POST['applicant_id'];
            $action = $_POST['action'];

            // Start transaction
            $conn->beginTransaction();

            try {
                // Update user status
                $status = ($action === 'approve') ? 'active' : 'rejected';
                $query = "UPDATE users u 
                         JOIN applicants a ON u.id = a.user_id 
                         SET u.status = ? 
                         WHERE a.id = ?";
                $stmt = $conn->prepare($query);
                $stmt->execute([$status, $applicant_id]);

                // Update applicant progress status if approved
                if ($action === 'approve') {
                    $query = "UPDATE applicants 
                             SET progress_status = 'registered' 
                             WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->execute([$applicant_id]);
                }

                // Log the action
                $auth->logActivity(
                    $user['id'],
                    'applicant_' . $action,
                    "Applicant (ID: $applicant_id) has been " . ($action === 'approve' ? 'approved' : 'rejected')
                );

                $conn->commit();
                $success = 'Applicant has been ' . ($action === 'approve' ? 'approved' : 'rejected') . ' successfully';
            } catch (Exception $e) {
                $conn->rollBack();
                throw $e;
            }
        }
    }

    // Get pending applicants
    $query = "SELECT 
                a.id,
                a.first_name,
                a.last_name,
                u.email,
                a.contact_number,
                a.preferred_course,
                u.status,
                u.created_at as registration_date
              FROM applicants a
              JOIN users u ON a.user_id = u.id
              WHERE u.role = 'applicant'
              ORDER BY u.created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error = $e->getMessage();
}

admin_header('Manage Applicants');
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">Manage Applicants</h1>
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

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Course</th>
                            <th>Status</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($applicants)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No applicants found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($applicants as $applicant): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($applicant['first_name'] . ' ' . $applicant['last_name']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($applicant['email']); ?></td>
                                    <td><?php echo htmlspecialchars($applicant['contact_number']); ?></td>
                                    <td><?php echo htmlspecialchars($applicant['preferred_course']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($applicant['status']) {
                                                'active' => 'success',
                                                'pending' => 'warning',
                                                'rejected' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo ucfirst($applicant['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($applicant['registration_date'])); ?></td>
                                    <td>
                                        <?php if ($applicant['status'] === 'pending'): ?>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="applicant_id" value="<?php echo $applicant['id']; ?>">
                                                <button type="submit" name="action" value="approve" 
                                                        class="btn btn-sm btn-success" 
                                                        onclick="return confirm('Are you sure you want to approve this applicant?')">
                                                    <i class="bi bi-check-lg"></i> Approve
                                                </button>
                                                <button type="submit" name="action" value="reject" 
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Are you sure you want to reject this applicant?')">
                                                    <i class="bi bi-x-lg"></i> Reject
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <a href="view_applicant.php?id=<?php echo $applicant['id']; ?>" 
                                           class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
admin_footer();
?>
