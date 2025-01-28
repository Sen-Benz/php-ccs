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

    // Get date range filters
    $start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
    $end_date = $_GET['end_date'] ?? date('Y-m-d'); // Today

    // Overall Statistics
    $query = "SELECT
                (SELECT COUNT(*) FROM applicants) as total_applicants,
                (SELECT COUNT(*) FROM applicants WHERE progress_status = 'passed') as accepted_applicants,
                (SELECT COUNT(*) FROM applicants WHERE progress_status = 'failed') as rejected_applicants,
                (SELECT COUNT(*) FROM applicants WHERE progress_status NOT IN ('passed', 'failed')) as pending_applicants";
    $stmt = $conn->query($query);
    $overall_stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Exam Statistics
    $query = "SELECT 
                COUNT(*) as total_exams,
                COALESCE(AVG(score), 0) as average_score,
                COUNT(CASE WHEN status = 'pass' THEN 1 END) as passed_count,
                COUNT(CASE WHEN status = 'fail' THEN 1 END) as failed_count
              FROM exam_results
              WHERE DATE(completed_at) BETWEEN ? AND ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$start_date, $end_date]);
    $exam_stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Interview Statistics
    $query = "SELECT 
                COUNT(*) as total_interviews,
                COUNT(CASE WHEN interview_status = 'passed' THEN 1 END) as passed_interviews,
                COUNT(CASE WHEN interview_status = 'failed' THEN 1 END) as failed_interviews,
                COALESCE(AVG(total_score), 0) as average_interview_score
              FROM interview_schedules
              WHERE schedule_date BETWEEN ? AND ?
              AND status = 'completed'";
    $stmt = $conn->prepare($query);
    $stmt->execute([$start_date, $end_date]);
    $interview_stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Monthly Application Trends
    $query = "SELECT 
                DATE_FORMAT(u.created_at, '%Y-%m') as month,
                COUNT(*) as application_count
              FROM applicants a
              JOIN users u ON a.user_id = u.id
              WHERE u.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
              GROUP BY DATE_FORMAT(u.created_at, '%Y-%m')
              ORDER BY month DESC";
    $stmt = $conn->query($query);
    $monthly_trends = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Course Distribution (temporary static data until preferred_course column is added)
    $course_distribution = [
        ['preferred_course' => 'BSCS', 'count' => 0],
        ['preferred_course' => 'BSIT', 'count' => 0]
    ];

} catch (Exception $e) {
    $error = $e->getMessage();
}

admin_header('Analytics Dashboard');
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">Analytics Dashboard</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <form class="row g-3 align-items-center">
                <div class="col-auto">
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                           value="<?php echo htmlspecialchars($start_date); ?>">
                </div>
                <div class="col-auto">
                    <input type="date" class="form-control" id="end_date" name="end_date" 
                           value="<?php echo htmlspecialchars($end_date); ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Overall Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Total Applicants</h5>
                    <h2 class="mb-0"><?php echo number_format($overall_stats['total_applicants']); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Accepted</h5>
                    <h2 class="mb-0"><?php echo number_format($overall_stats['accepted_applicants']); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Rejected</h5>
                    <h2 class="mb-0"><?php echo number_format($overall_stats['rejected_applicants']); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Pending</h5>
                    <h2 class="mb-0"><?php echo number_format($overall_stats['pending_applicants']); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Exam Statistics -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Exam Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <p class="mb-1">Total Exams</p>
                            <h3><?php echo number_format($exam_stats['total_exams']); ?></h3>
                        </div>
                        <div class="col-6">
                            <p class="mb-1">Average Score</p>
                            <h3><?php echo number_format($exam_stats['average_score'], 1); ?></h3>
                        </div>
                        <div class="col-6">
                            <p class="mb-1">Passed</p>
                            <h3 class="text-success"><?php echo number_format($exam_stats['passed_count']); ?></h3>
                        </div>
                        <div class="col-6">
                            <p class="mb-1">Failed</p>
                            <h3 class="text-danger"><?php echo number_format($exam_stats['failed_count']); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interview Statistics -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Interview Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <p class="mb-1">Total Interviews</p>
                            <h3><?php echo number_format($interview_stats['total_interviews']); ?></h3>
                        </div>
                        <div class="col-6">
                            <p class="mb-1">Average Score</p>
                            <h3><?php echo number_format($interview_stats['average_interview_score'], 1); ?></h3>
                        </div>
                        <div class="col-6">
                            <p class="mb-1">Passed</p>
                            <h3 class="text-success"><?php echo number_format($interview_stats['passed_interviews']); ?></h3>
                        </div>
                        <div class="col-6">
                            <p class="mb-1">Failed</p>
                            <h3 class="text-danger"><?php echo number_format($interview_stats['failed_interviews']); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Monthly Application Trends -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Monthly Application Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="applicationTrends"></canvas>
                </div>
            </div>
        </div>

        <!-- Course Distribution -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Course Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="courseDistribution"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Monthly Application Trends Chart
const trendsCtx = document.getElementById('applicationTrends').getContext('2d');
new Chart(trendsCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column(array_reverse($monthly_trends), 'month')); ?>,
        datasets: [{
            label: 'Applications',
            data: <?php echo json_encode(array_column(array_reverse($monthly_trends), 'application_count')); ?>,
            borderColor: '#0d6efd',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});

// Course Distribution Chart
const courseCtx = document.getElementById('courseDistribution').getContext('2d');
new Chart(courseCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($course_distribution, 'preferred_course')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($course_distribution, 'count')); ?>,
            backgroundColor: [
                '#0d6efd',
                '#198754',
                '#dc3545',
                '#ffc107',
                '#6610f2',
                '#0dcaf0'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php
admin_footer();
?>
