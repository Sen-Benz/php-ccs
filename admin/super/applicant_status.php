<?php
require_once '../../classes/Auth.php';
require_once '../../config/database.php';
require_once '../includes/layout.php';

$auth = new Auth();
$auth->requireRole('super_admin');

$user = $auth->getCurrentUser();
$error = '';
$success = '';
$applicants = [];

// Get filters
$status_filter = $_GET['status'] ?? 'all';
$course_filter = $_GET['course'] ?? 'all';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Build where conditions
    $where_conditions = [];
    $params = [];

    if ($status_filter !== 'all') {
        $where_conditions[] = "a.progress_status = :status";
        $params[':status'] = $status_filter;
    }

    if ($course_filter !== 'all') {
        $where_conditions[] = "a.preferred_course = :course";
        $params[':course'] = $course_filter;
    }

    if ($search) {
        $where_conditions[] = "(a.first_name LIKE :search1 OR a.last_name LIKE :search2 OR u.email LIKE :search3)";
        $search_param = "%$search%";
        $params[':search1'] = $search_param;
        $params[':search2'] = $search_param;
        $params[':search3'] = $search_param;
    }

    // Get total count for pagination
    $count_query = "SELECT COUNT(DISTINCT a.id) as total FROM applicants a
                    JOIN users u ON a.user_id = u.id
                    WHERE 1=1";
    if (!empty($where_conditions)) {
        $count_query .= " AND " . implode(" AND ", $where_conditions);
    }

    $stmt = $conn->prepare($count_query);
    $stmt->execute($params);
    $total_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_count / $per_page);

    // Get applicants with pagination
    $main_query = "SELECT 
                a.*,
                u.email,
                COALESCE(e1.score, 0) as part1_score,
                COALESCE(e2.score, 0) as part2_score,
                COALESCE(i.total_score, 0) as interview_score,
                i.interview_status
              FROM applicants a
              JOIN users u ON a.user_id = u.id
              LEFT JOIN (
                SELECT er.applicant_id, er.score
                FROM exam_results er
                JOIN exams e ON er.exam_id = e.id
                WHERE e.part = '1'
              ) e1 ON a.id = e1.applicant_id
              LEFT JOIN (
                SELECT er.applicant_id, er.score
                FROM exam_results er
                JOIN exams e ON er.exam_id = e.id
                WHERE e.part = '2'
              ) e2 ON a.id = e2.applicant_id
              LEFT JOIN (
                SELECT applicant_id, total_score, interview_status
                FROM interview_schedules
                WHERE status = 'completed'
              ) i ON a.id = i.applicant_id
              WHERE 1=1";

    if (!empty($where_conditions)) {
        $main_query .= " AND " . implode(" AND ", $where_conditions);
    }

    $main_query .= " ORDER BY a.id DESC LIMIT " . $per_page . " OFFSET " . $offset;

    $stmt = $conn->prepare($main_query);
    $stmt->execute($params);
    $applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error = $e->getMessage();
}

admin_header('List All Applicants');
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">List All Applicants</h1>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="registered" <?php echo $status_filter === 'registered' ? 'selected' : ''; ?>>Registered</option>
                        <option value="part1_pending" <?php echo $status_filter === 'part1_pending' ? 'selected' : ''; ?>>Part 1 Pending</option>
                        <option value="part1_completed" <?php echo $status_filter === 'part1_completed' ? 'selected' : ''; ?>>Part 1 Completed</option>
                        <option value="part2_pending" <?php echo $status_filter === 'part2_pending' ? 'selected' : ''; ?>>Part 2 Pending</option>
                        <option value="part2_completed" <?php echo $status_filter === 'part2_completed' ? 'selected' : ''; ?>>Part 2 Completed</option>
                        <option value="interview_pending" <?php echo $status_filter === 'interview_pending' ? 'selected' : ''; ?>>Interview Pending</option>
                        <option value="interview_completed" <?php echo $status_filter === 'interview_completed' ? 'selected' : ''; ?>>Interview Completed</option>
                        <option value="passed" <?php echo $status_filter === 'passed' ? 'selected' : ''; ?>>Passed</option>
                        <option value="failed" <?php echo $status_filter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="course" class="form-label">Course</label>
                    <select class="form-select" id="course" name="course">
                        <option value="all" <?php echo $course_filter === 'all' ? 'selected' : ''; ?>>All Courses</option>
                        <option value="BSCS" <?php echo $course_filter === 'BSCS' ? 'selected' : ''; ?>>BSCS</option>
                        <option value="BSIT" <?php echo $course_filter === 'BSIT' ? 'selected' : ''; ?>>BSIT</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name or email">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Applicants Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Status</th>
                            <th>Part 1 Score</th>
                            <th>Part 2 Score</th>
                            <th>Interview Score</th>
                            <th>Interview Result</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($applicants)): ?>
                            <?php foreach ($applicants as $applicant): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($applicant['id']); ?></td>
                                    <td>
                                        <?php 
                                        echo htmlspecialchars($applicant['first_name'] . ' ' . 
                                             ($applicant['middle_name'] ? $applicant['middle_name'] . ' ' : '') . 
                                             $applicant['last_name']); 
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($applicant['email']); ?></td>
                                    <td><?php echo htmlspecialchars($applicant['preferred_course']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($applicant['progress_status']) {
                                                'registered' => 'secondary',
                                                'part1_pending', 'part2_pending', 'interview_pending' => 'warning',
                                                'part1_completed', 'part2_completed', 'interview_completed' => 'info',
                                                'passed' => 'success',
                                                'failed' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo ucwords(str_replace('_', ' ', $applicant['progress_status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $applicant['part1_score']; ?></td>
                                    <td><?php echo $applicant['part2_score']; ?></td>
                                    <td><?php echo $applicant['interview_score']; ?></td>
                                    <td>
                                        <?php if ($applicant['interview_status']): ?>
                                            <span class="badge bg-<?php 
                                                echo match($applicant['interview_status']) {
                                                    'passed' => 'success',
                                                    'failed' => 'danger',
                                                    default => 'secondary'
                                                };
                                            ?>">
                                                <?php echo ucfirst($applicant['interview_status']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Not Interviewed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="view_applicant.php?id=<?php echo $applicant['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center">No applicants found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($status_filter); ?>&course=<?php echo urlencode($course_filter); ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&course=<?php echo urlencode($course_filter); ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($status_filter); ?>&course=<?php echo urlencode($course_filter); ?>&search=<?php echo urlencode($search); ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
admin_footer();
?>
