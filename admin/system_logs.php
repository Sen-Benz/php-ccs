<?php
require_once '../../config/config.php';
require_once '../../middleware/SessionManager.php';
require_once '../../classes/Auth.php';
require_once '../../config/Database.php';
require_once '../includes/admin_layout.php';

// Start session
SessionManager::start();

// Initialize Auth
$auth = new Auth();

// Check authentication and role
if (!$auth->requireRole('super_admin')) {
    exit();
}

// Initialize variables
$logs = [];
$error = '';
$success = '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$action_type = isset($_GET['action_type']) ? trim($_GET['action_type']) : '';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Build the base query
    $query = "SELECT al.*, 
              CONCAT(u.first_name, ' ', u.last_name) as user_name,
              u.email as user_email,
              u.role as user_role
              FROM activity_logs al
              LEFT JOIN users u ON al.user_id = u.id
              WHERE 1=1";
    $params = [];
    
    // Add search conditions
    if (!empty($search)) {
        $query .= " AND (al.details LIKE ? OR al.action LIKE ? OR u.email LIKE ?)";
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
    }
    
    // Add date range conditions
    if (!empty($date_from)) {
        $query .= " AND DATE(al.created_at) >= ?";
        $params[] = $date_from;
    }
    if (!empty($date_to)) {
        $query .= " AND DATE(al.created_at) <= ?";
        $params[] = $date_to;
    }
    
    // Add action type filter
    if (!empty($action_type)) {
        $query .= " AND al.action = ?";
        $params[] = $action_type;
    }
    
    // Get total count for pagination
    $countStmt = $conn->prepare(str_replace("al.*, CONCAT", "COUNT(*) as total", $query));
    $countStmt->execute($params);
    $totalRows = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalRows / $limit);
    
    // Add sorting and pagination
    $query .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    // Execute the main query
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get unique action types for filter dropdown
    $actionTypesStmt = $conn->query("SELECT DISTINCT action FROM activity_logs ORDER BY action");
    $actionTypes = $actionTypesStmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (Exception $e) {
    error_log("System Logs Error: " . $e->getMessage());
    $error = "An error occurred while fetching the logs.";
}

// Start the page
admin_header('System Logs');
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">System Logs</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportLogs()">
                <i class="bi bi-download"></i> Export Logs
            </button>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search logs...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Action Type</label>
                    <select class="form-select" name="action_type">
                        <option value="">All Actions</option>
                        <?php foreach ($actionTypes as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $action_type === $type ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucfirst($type)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                    <a href="system_logs.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No logs found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></td>
                                    <td>
                                        <?php if ($log['user_name']): ?>
                                            <?php echo htmlspecialchars($log['user_name']); ?><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($log['user_email']); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">System</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($log['action']); ?></span></td>
                                    <td><?php echo htmlspecialchars($log['details']); ?></td>
                                    <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&action_type=<?php echo urlencode($action_type); ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&action_type=<?php echo urlencode($action_type); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&action_type=<?php echo urlencode($action_type); ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function exportLogs() {
    // Create the export URL with current filters
    let exportUrl = 'export_logs.php?';
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('search')) exportUrl += `search=${urlParams.get('search')}&`;
    if (urlParams.has('date_from')) exportUrl += `date_from=${urlParams.get('date_from')}&`;
    if (urlParams.has('date_to')) exportUrl += `date_to=${urlParams.get('date_to')}&`;
    if (urlParams.has('action_type')) exportUrl += `action_type=${urlParams.get('action_type')}`;
    
    window.location.href = exportUrl;
}
</script>

<?php
admin_footer();
?>