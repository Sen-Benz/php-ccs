<?php
require_once '../../classes/Auth.php';
require_once '../includes/layout.php';

$auth = new Auth();
$auth->requireRole('super_admin');

$user = $auth->getCurrentUser();
$error = '';
$success = '';

// Handle admin status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['admin_id'])) {
        $admin_id = $_POST['admin_id'];
        $action = $_POST['action'];

        try {
            $database = new Database();
            $conn = $database->getConnection();

            switch ($action) {
                case 'activate':
                    $status = 'active';
                    break;
                case 'deactivate':
                    $status = 'inactive';
                    break;
                default:
                    throw new Exception('Invalid action');
            }

            $query = "UPDATE users SET status = ? WHERE id = ? AND role = 'admin'";
            $stmt = $conn->prepare($query);
            $stmt->execute([$status, $admin_id]);

            if ($stmt->rowCount() > 0) {
                $auth->logActivity(
                    $user['id'],
                    'admin_' . $action,
                    "Admin account (ID: $admin_id) has been $action" . "d"
                );
                $success = 'Admin status updated successfully';
            } else {
                $error = 'Failed to update admin status';
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

// Fetch all admin accounts
try {
    $database = new Database();
    $conn = $database->getConnection();

    $query = "SELECT u.id, u.email, u.status, u.created_at, 
              a.first_name, a.last_name, a.department,
              (SELECT COUNT(*) FROM activity_logs WHERE user_id = u.id) as activity_count
              FROM users u
              JOIN admins a ON u.id = a.user_id
              WHERE u.role = 'admin'
              ORDER BY u.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
    $admins = [];
}

admin_header('View Admin Accounts');
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Admin Accounts</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="create_admin.php" class="btn btn-sm btn-primary">
            <i class="bi bi-person-plus"></i> Create Admin Account
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

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Activities</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($admins)): ?>
                    <tr>
                        <td colspan="7" class="text-center">No admin accounts found</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($admins as $admin): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($admin['email']); ?></td>
                            <td><?php echo htmlspecialchars($admin['department']); ?></td>
                            <td>
                                <span class="badge <?php echo $admin['status'] === 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo ucfirst(htmlspecialchars($admin['status'])); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <?php echo htmlspecialchars($admin['activity_count']); ?> actions
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($admin['created_at'])); ?></td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu">
                                        <?php if ($admin['status'] === 'active'): ?>
                                        <li>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                                <input type="hidden" name="action" value="deactivate">
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-person-x"></i> Deactivate
                                                </button>
                                            </form>
                                        </li>
                                        <?php else: ?>
                                        <li>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                                <input type="hidden" name="action" value="activate">
                                                <button type="submit" class="dropdown-item text-success">
                                                    <i class="bi bi-person-check"></i> Activate
                                                </button>
                                            </form>
                                        </li>
                                        <?php endif; ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="view_admin_activity.php?id=<?php echo $admin['id']; ?>">
                                                <i class="bi bi-clock-history"></i> View Activity
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
admin_footer();
?>
