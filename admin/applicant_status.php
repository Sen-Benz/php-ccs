<?php
require_once '../config/config.php';
require_once '../classes/Auth.php';
require_once '../config/database.php';
require_once 'includes/admin_layout.php';

// Initialize Auth and Database
$auth = new Auth();
$auth->requireRole('admin');
$db = Database::getInstance();

// Get current user
$user = $auth->getCurrentUser();

$page_title = 'Applicant Status';
admin_header($page_title);

$program_stats = []; // Default value
$recent_changes = [];

try {
    // Get application statistics with proper joins
    $stmt = $db->query(
        "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
         FROM users u
         WHERE u.role = 'applicant'",
        []
    );
    $stats = $stmt->fetch();

    // Get recent status changes with admin names
    $stmt = $db->query(
        "SELECT u.*, 
                CONCAT(a.first_name, ' ', a.last_name) as admin_name,
                u.updated_at as status_changed_at
         FROM users u
         LEFT JOIN users a ON u.updated_by = a.id
         WHERE u.role = 'applicant'
         ORDER BY u.updated_at DESC
         LIMIT 10",
        []
    );
    $recent_changes = $stmt->fetchAll();

    // Get status by program with proper totals
    
    $stmt = $db->query(
        "SELECT 
            preferred_course AS program, 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
         FROM applicants
         WHERE preferred_course IS NOT NULL
         GROUP BY preferred_course
         ORDER BY preferred_course ASC"
    );
    $program_stats = $stmt->fetchAll();
    
    if (empty($program_stats)) {
        error_log("DEBUG: No data returned for program_stats");
    }

    $program_stats = $stmt->fetchAll();

} catch (Exception $e) {
    error_log("Error in applicant status page: " . $e->getMessage());
    $error = "An error occurred while fetching statistics.";
}

?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include_once './includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Application Status Overview</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                            <i class="bx bx-printer"></i> Print Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Applications</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total']; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bx bx-user fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Pending Review</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['pending']; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bx bx-time fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Approved</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['approved']; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bx bx-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Rejected</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['rejected']; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bx bx-x-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Program Statistics -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Applications by Program</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Program</th>
                                            <th>Total</th>
                                            <th>Pending</th>
                                            <th>Approved</th>
                                            <th>Rejected</th>
                                            <th>Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($program_stats as $stat): ?>
                                            <tr>
                                            <td><?php echo isset($stat['program']) ? htmlspecialchars($stat['program']) : 'N/A'; ?></td>
                                                <td><?php echo $stat['total']; ?></td>
                                                <td><?php echo $stat['pending']; ?></td>
                                                <td><?php echo $stat['approved']; ?></td>
                                                <td><?php echo $stat['rejected']; ?></td>
                                                <td>
                                                    <div class="progress">
                                                        <?php
                                                        $pending_percent = ($stat['pending'] / $stat['total']) * 100;
                                                        $approved_percent = ($stat['approved'] / $stat['total']) * 100;
                                                        $rejected_percent = ($stat['rejected'] / $stat['total']) * 100;
                                                        ?>
                                                        <div class="progress-bar bg-warning" style="width: <?php echo $pending_percent; ?>%">
                                                            <?php echo round($pending_percent); ?>%
                                                        </div>
                                                        <div class="progress-bar bg-success" style="width: <?php echo $approved_percent; ?>%">
                                                            <?php echo round($approved_percent); ?>%
                                                        </div>
                                                        <div class="progress-bar bg-danger" style="width: <?php echo $rejected_percent; ?>%">
                                                            <?php echo round($rejected_percent); ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Status Changes -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Status Changes</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Applicant</th>
                                            <th>Status</th>
                                            <th>Changed By</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_changes as $change): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($change['first_name'] . ' ' . $change['last_name']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $change['status'] === 'approved' ? 'success' : 
                                                            ($change['status'] === 'rejected' ? 'danger' : 'warning'); 
                                                    ?>">
                                                        <?php echo ucfirst($change['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($change['admin_name'] ?? 'System'); ?></td>
                                                <td><?php echo date('M d, Y H:i', strtotime($change['status_changed_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.border-left-primary { border-left: 4px solid #4e73df !important; }
.border-left-success { border-left: 4px solid #1cc88a !important; }
.border-left-warning { border-left: 4px solid #f6c23e !important; }
.border-left-danger { border-left: 4px solid #e74a3b !important; }

.progress {
    height: 20px;
    margin-bottom: 0;
}

.progress-bar {
    text-align: center;
    min-width: 2em;
}

@media print {
    .sidebar-wrapper, .btn-toolbar {
        display: none !important;
    }
    
    .col-md-9 {
        width: 100% !important;
        margin: 0 !important;
    }
}
</style>

<?php admin_footer(); ?>
