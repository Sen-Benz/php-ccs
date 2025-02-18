<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/config.php';
require_once '../../middleware/SessionManager.php';
require_once '../../classes/Auth.php';
require_once '../../config/Database.php';
require_once '../includes/layout.php';

// Start session first
SessionManager::start();

// Initialize Auth
$auth = new Auth();

// Check authentication and role
try {
    // This will redirect to login if not authenticated
    if (!$auth->requireRole('super_admin')) {
        exit(); // requireRole will handle the redirect
    }
    
    // Get user data after confirming authentication
    $user = $auth->getCurrentUser();
    if (!$user) {
        throw new Exception('User data not found');
    }

    // Initialize variables
    $logs = [];
    $error = '';
    $success = '';
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
    $date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
    $action_type = isset($_GET['action_type']) ? trim($_GET['action_type']) : '';

    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Enable query logging in development
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            if (defined('PDO::MYSQL_ATTR_LOG_QUERIES')) {
                $conn->setAttribute(PDO::MYSQL_ATTR_LOG_QUERIES, true);
            }
        }
        
        // Build the base query for fetching logs
        $query = "SELECT al.*, 
                  CONCAT(u.first_name, ' ', u.last_name) as user_name,
                  u.email as user_email,
                  u.role as user_role
                  FROM activity_logs al
                  LEFT JOIN users u ON al.user_id = u.id
                  WHERE 1=1";
        $params = [];
        
        // Build the count query
        $countQuery = "SELECT COUNT(*) as total FROM activity_logs al 
                      LEFT JOIN users u ON al.user_id = u.id 
                      WHERE 1=1";
        
        // Add search conditions to both queries
        if (!empty($search)) {
            $searchCondition = " AND (al.details LIKE :search OR al.action LIKE :search OR u.email LIKE :search)";
            $query .= $searchCondition;
            $countQuery .= $searchCondition;
            $params[':search'] = "%{$search}%";
        }
        
        // Add date range conditions to both queries
        if (!empty($date_from)) {
            $dateFromCondition = " AND DATE(al.created_at) >= :date_from";
            $query .= $dateFromCondition;
            $countQuery .= $dateFromCondition;
            $params[':date_from'] = $date_from;
        }
        if (!empty($date_to)) {
            $dateToCondition = " AND DATE(al.created_at) <= :date_to";
            $query .= $dateToCondition;
            $countQuery .= $dateToCondition;
            $params[':date_to'] = $date_to;
        }
        
        // Add action type filter to both queries
        if (!empty($action_type)) {
            $actionCondition = " AND al.action = :action_type";
            $query .= $actionCondition;
            $countQuery .= $actionCondition;
            $params[':action_type'] = $action_type;
        }
        
        // Log the queries and parameters in development
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            error_log("Count Query: " . $countQuery);
            error_log("Main Query: " . $query);
            error_log("Parameters: " . print_r($params, true));
        }
        
        // Get total count for pagination
        $countStmt = $conn->prepare($countQuery);
        $countStmt->execute($params);
        $result = $countStmt->fetch(PDO::FETCH_ASSOC);
        $totalRows = $result['total'] ?? 0;
        $totalPages = max(1, ceil($totalRows / $limit));
        
        // Ensure page number is within valid range
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $limit;
        
        // Add sorting and pagination to the main query
        $query .= " ORDER BY al.created_at DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        // Execute the main query
        $stmt = $conn->prepare($query);
        
        // Bind parameters explicitly to handle LIMIT and OFFSET
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, 
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get unique action types for filter dropdown
        $actionTypesStmt = $conn->query("SELECT DISTINCT action FROM activity_logs ORDER BY action");
        $actionTypes = $actionTypesStmt->fetchAll(PDO::FETCH_COLUMN);
        
    } catch (PDOException $e) {
        error_log("Database Error in System Logs: " . $e->getMessage() . "\n" . 
                  "Stack trace: " . $e->getTraceAsString());
        $error = "A database error occurred. Please contact the administrator.";
    } catch (Exception $e) {
        error_log("General Error in System Logs: " . $e->getMessage() . "\n" . 
                  "Stack trace: " . $e->getTraceAsString());
        $error = "An unexpected error occurred. Please try again later.";
    }

} catch (Exception $e) {
    error_log("System Logs authentication error: " . $e->getMessage() . "\n" . 
              "Stack trace: " . $e->getTraceAsString());
    $_SESSION['error'] = 'An error occurred. Please try logging in again.';
    $auth->logout();
    header('Location: /php-ccs/login.php');
    exit();
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
            <form method="GET" class="row g-3" id="logsFilterForm">
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
                                    <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($log['created_at']))); ?></td>
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
                            <a class="page-link" href="<?php echo buildPaginationUrl($page - 1); ?>">Previous</a>
                        </li>
                        <?php 
                        // Calculate range of pages to show
                        $startPage = max(1, min($page - 2, $totalPages - 4));
                        $endPage = min($totalPages, max($page + 2, 5));
                        
                        // Show first page if we're not starting at 1
                        if ($startPage > 1) {
                            echo '<li class="page-item"><a class="page-link" href="' . buildPaginationUrl(1) . '">1</a></li>';
                            if ($startPage > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }
                        
                        // Show page numbers
                        for ($i = $startPage; $i <= $endPage; $i++): 
                        ?>
                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo buildPaginationUrl($i); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; 
                        
                        // Show last page if we're not ending at the last page
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="' . buildPaginationUrl($totalPages) . '">' . $totalPages . '</a></li>';
                        }
                        ?>
                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo buildPaginationUrl($page + 1); ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Helper function to build pagination URLs
function buildPaginationUrl($pageNum) {
    $params = $_GET;
    $params['page'] = $pageNum;
    return '?' . http_build_query($params);
}
?>

<script>
function exportLogs() {
    const form = document.getElementById('logsFilterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    // Remove page parameter if it exists
    params.delete('page');
    
    // Create the export URL with current filters
    const exportUrl = `export_logs.php?${params.toString()}`;
    window.location.href = exportUrl;
}

// Validate date range
document.getElementById('logsFilterForm').addEventListener('submit', function(e) {
    const dateFrom = this.elements['date_from'].value;
    const dateTo = this.elements['date_to'].value;
    
    if (dateFrom && dateTo && dateFrom > dateTo) {
        e.preventDefault();
        alert('The From Date must be before or equal to the To Date');
    }
});
</script>

<?php
admin_footer();
?>