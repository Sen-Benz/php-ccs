<?php
require_once '../../config/config.php';
require_once '../../middleware/SessionManager.php';
require_once '../../classes/Auth.php';
require_once '../../config/Database.php';

// Start session
SessionManager::start();

// Initialize Auth
$auth = new Auth();

// Check authentication and role
if (!$auth->requireRole('super_admin')) {
    exit();
}

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$action_type = isset($_GET['action_type']) ? trim($_GET['action_type']) : '';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Build the query with filters
    $query = "SELECT al.*, 
              CONCAT(u.first_name, ' ', u.last_name) as user_name,
              u.email as user_email,
              u.role as user_role
              FROM activity_logs al
              LEFT JOIN users u ON al.user_id = u.id
              WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $query .= " AND (al.details LIKE ? OR al.action LIKE ? OR u.email LIKE ?)";
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
    }
    
    if (!empty($date_from)) {
        $query .= " AND DATE(al.created_at) >= ?";
        $params[] = $date_from;
    }
    
    if (!empty($date_to)) {
        $query .= " AND DATE(al.created_at) <= ?";
        $params[] = $date_to;
    }
    
    if (!empty($action_type)) {
        $query .= " AND al.action = ?";
        $params[] = $action_type;
    }
    
    $query .= " ORDER BY al.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="system_logs_' . date('Y-m-d_His') . '.csv"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, [
        'Timestamp',
        'User Name',
        'User Email',
        'User Role',
        'Action',
        'Details',
        'IP Address'
    ]);
    
    // Add data rows
    foreach ($logs as $log) {
        fputcsv($output, [
            $log['created_at'],
            $log['user_name'] ?? 'System',
            $log['user_email'] ?? 'N/A',
            $log['user_role'] ?? 'N/A',
            $log['action'],
            $log['details'],
            $log['ip_address']
        ]);
    }
    
    fclose($output);
    
} catch (Exception $e) {
    error_log("Export Logs Error: " . $e->getMessage());
    header('Location: system_logs.php?error=export_failed');
    exit();
}
?>
