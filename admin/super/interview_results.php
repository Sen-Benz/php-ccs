<?php
require_once '../../classes/Auth.php';
require_once '../../config/database.php';
require_once '../includes/layout.php';

$auth = new Auth();
$auth->requireRole('super_admin');

$user = $auth->getCurrentUser();
$error = '';
$success = '';

// Initialize variables
$stats = [
    'total_interviews' => 0,
    'passed_count' => 0,
    'failed_count' => 0,
    'average_score' => 0
];
$interviews = [];
$total_pages = 0;
$total_count = 0;

try {
    $database = new Database();
    $conn = $database->getConnection();

    // First, ensure the columns exist
    try {
        $conn->exec("
            ALTER TABLE interview_schedules 
            ADD COLUMN IF NOT EXISTS interview_status ENUM('pending', 'passed', 'failed') NOT NULL DEFAULT 'pending',
            ADD COLUMN IF NOT EXISTS total_score INT DEFAULT NULL
        ");
    } catch (PDOException $e) {
        // Ignore if columns already exist
    }

    // Get filters
    $status = $_GET['status'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $search = $_GET['search'] ?? '';

    // Build where clause
    $where_conditions = ["i.status = 'completed'"];
    $params = [];

    if ($status) {
        $where_conditions[] = "i.interview_status = ?";
        $params[] = $status;
    }

    if ($date_from) {
        $where_conditions[] = "DATE(i.schedule_date) >= ?";
        $params[] = $date_from;
    }

    if ($date_to) {
        $where_conditions[] = "DATE(i.schedule_date) <= ?";
        $params[] = $date_to;
    }

    if ($search) {
        $where_conditions[] = "(a.first_name LIKE ? OR a.last_name LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
    }

    $where_clause = "WHERE " . implode(" AND ", $where_conditions);

    // Get total count
    $query = "SELECT COUNT(*) as total 
              FROM interview_schedules i
              JOIN applicants a ON i.applicant_id = a.id
              $where_clause";
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $total_count = $stmt->fetch()['total'];

    // Get statistics
    $query = "SELECT 
                COUNT(*) as total_interviews,
                SUM(CASE WHEN COALESCE(i.interview_status, 'pending') = 'passed' THEN 1 ELSE 0 END) as passed_count,
                SUM(CASE WHEN COALESCE(i.interview_status, 'pending') = 'failed' THEN 1 ELSE 0 END) as failed_count,
                COALESCE(AVG(NULLIF(i.total_score, 0)), 0) as average_score
              FROM interview_schedules i
              JOIN applicants a ON i.applicant_id = a.id
              $where_clause";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Set default values for stats if null
    $stats['total_interviews'] = $stats['total_interviews'] ?? 0;
    $stats['passed_count'] = $stats['passed_count'] ?? 0;
    $stats['failed_count'] = $stats['failed_count'] ?? 0;
    $stats['average_score'] = $stats['average_score'] ?? 0;

    // Pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 20;
    $offset = ($page - 1) * $per_page;
    $total_pages = ceil($total_count / $per_page);

    // Get interviews
    if ($total_count > 0) {
        $query = "SELECT 
                    i.*,
                    COALESCE(i.interview_status, 'pending') as interview_status,
                    COALESCE(i.total_score, 0) as total_score,
                    a.first_name as applicant_first_name,
                    a.last_name as applicant_last_name,
                    CONCAT(u.first_name, ' ', u.last_name) as interviewer_name
                  FROM interview_schedules i
                  JOIN applicants a ON i.applicant_id = a.id
                  JOIN users u ON i.interviewer_id = u.id
                  $where_clause
                  ORDER BY i.schedule_date DESC, i.start_time DESC
                  LIMIT $per_page OFFSET $offset";
        
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $interviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}

admin_header('Interview Results');
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">Interview Results</h1>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Interviews</h5>
                    <h2 class="mb-0"><?php echo number_format($stats['total_interviews']); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Passed</h5>
                    <h2 class="mb-0"><?php echo number_format($stats['passed_count']); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Failed</h5>
                    <h2 class="mb-0"><?php echo number_format($stats['failed_count']); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Average Score</h5>
                    <h2 class="mb-0"><?php echo number_format($stats['average_score'], 1); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search Applicant</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Enter name">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Result</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All</option>
                        <option value="passed" <?php echo $status === 'passed' ? 'selected' : ''; ?>>Passed</option>
                        <option value="failed" <?php echo $status === 'failed' ? 'selected' : ''; ?>>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($interviews)): ?>
                <div class="alert alert-info">
                    No interview results found.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Applicant</th>
                                <th>Interviewer</th>
                                <th>Score</th>
                                <th>Result</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($interviews as $interview): ?>
                                <tr>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($interview['schedule_date'])); ?><br>
                                        <small class="text-muted">
                                            <?php echo date('h:i A', strtotime($interview['start_time'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong>
                                            <?php echo htmlspecialchars($interview['applicant_first_name'] . ' ' . 
                                                                       $interview['applicant_last_name']); ?>
                                        </strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($interview['interviewer_name']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $interview['total_score'] >= 70 ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $interview['total_score']; ?>/100
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $interview['interview_status'] === 'passed' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo ucfirst($interview['interview_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="view_interview.php?id=<?php echo $interview['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&search=<?php echo urlencode($search); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
admin_footer();
?>
