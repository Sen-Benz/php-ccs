<?php
require_once '../../classes/Auth.php';
require_once '../../config/database.php';
require_once '../includes/layout.php';

$auth = new Auth();
$auth->requireRole('super_admin');

$user = $auth->getCurrentUser();
$error = '';
$success = '';

// Handle export request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $conn = $database->getConnection();

        $report_type = $_POST['report_type'] ?? '';
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $format = $_POST['format'] ?? 'csv';

        // Validate dates
        if (!$start_date || !$end_date) {
            throw new Exception('Please select both start and end dates.');
        }

        // Build query based on report type
        switch ($report_type) {
            case 'applicants':
                $query = "SELECT 
                            a.id,
                            a.first_name,
                            a.last_name,
                            u.email,
                            a.contact_number,
                            a.preferred_course,
                            a.progress_status as status,
                            a.created_at
                         FROM applicants a
                         JOIN users u ON a.user_id = u.id
                         WHERE DATE(a.created_at) BETWEEN ? AND ?
                         ORDER BY a.created_at DESC";
                $filename = "applicants_report";
                $headers = ['ID', 'First Name', 'Last Name', 'Email', 'Contact Number', 'Preferred Course', 'Status', 'Application Date'];
                break;

            case 'exams':
                $query = "SELECT 
                            er.id,
                            CONCAT(a.first_name, ' ', a.last_name) as applicant_name,
                            er.score,
                            e.title as exam_title,
                            e.part as exam_part,
                            er.status as result,
                            er.completed_at as exam_date
                         FROM exam_results er
                         JOIN applicants a ON er.applicant_id = a.id
                         JOIN exams e ON er.exam_id = e.id
                         WHERE DATE(er.completed_at) BETWEEN ? AND ?
                         ORDER BY er.completed_at DESC";
                $filename = "exam_results_report";
                $headers = ['ID', 'Applicant Name', 'Score', 'Exam Title', 'Part', 'Result', 'Date Taken'];
                break;

            case 'interviews':
                $query = "SELECT 
                            i.id,
                            CONCAT(a.first_name, ' ', a.last_name) as applicant_name,
                            CONCAT(u.first_name, ' ', u.last_name) as interviewer_name,
                            i.schedule_date,
                            i.start_time,
                            i.end_time,
                            i.status,
                            i.interview_status,
                            i.total_score
                         FROM interview_schedules i
                         JOIN applicants a ON i.applicant_id = a.id
                         JOIN users u ON i.interviewer_id = u.id
                         WHERE DATE(i.schedule_date) BETWEEN ? AND ?
                         ORDER BY i.schedule_date DESC";
                $filename = "interview_results_report";
                $headers = ['ID', 'Applicant Name', 'Interviewer', 'Date', 'Start Time', 'End Time', 'Status', 'Result', 'Score'];
                break;

            default:
                throw new Exception('Invalid report type selected.');
        }

        // Execute query
        $stmt = $conn->prepare($query);
        $stmt->execute([$start_date, $end_date]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($results)) {
            throw new Exception('No data found for the selected criteria.');
        }

        // Generate filename with date range
        $filename = sprintf("%s_%s_to_%s.%s", 
            $filename, 
            str_replace('-', '', $start_date),
            str_replace('-', '', $end_date),
            $format
        );

        // Set headers for download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Create output stream
        $output = fopen('php://output', 'w');

        // Add BOM for Excel UTF-8 compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Write headers
        fputcsv($output, $headers);

        // Write data rows
        foreach ($results as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit();

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

admin_header('Export Reports');
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">Export Reports</h1>
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
            <form method="post" class="row g-3">
                <!-- Report Type -->
                <div class="col-md-4">
                    <label for="report_type" class="form-label">Report Type</label>
                    <select class="form-select" id="report_type" name="report_type" required>
                        <option value="">Select Report Type</option>
                        <option value="applicants">Applicants Report</option>
                        <option value="exams">Exam Results Report</option>
                        <option value="interviews">Interview Results Report</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                </div>

                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" required>
                </div>

                <!-- Export Format -->
                <div class="col-md-2">
                    <label for="format" class="form-label">Format</label>
                    <select class="form-select" id="format" name="format">
                        <option value="csv">CSV</option>
                    </select>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-download"></i> Export Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Descriptions -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Applicants Report</h5>
                    <p class="card-text">
                        Includes detailed information about applicants including personal details,
                        preferred course, and application status.
                    </p>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-check-circle text-success"></i> Personal Information</li>
                        <li><i class="bi bi-check-circle text-success"></i> Contact Details</li>
                        <li><i class="bi bi-check-circle text-success"></i> Application Status</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Exam Results Report</h5>
                    <p class="card-text">
                        Contains comprehensive exam results including scores, completion times,
                        and exam dates for all applicants.
                    </p>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-check-circle text-success"></i> Exam Scores</li>
                        <li><i class="bi bi-check-circle text-success"></i> Completion Times</li>
                        <li><i class="bi bi-check-circle text-success"></i> Date and Time Details</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Interview Results Report</h5>
                    <p class="card-text">
                        Provides detailed interview information including schedules, interviewers,
                        scores, and final results.
                    </p>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-check-circle text-success"></i> Interview Details</li>
                        <li><i class="bi bi-check-circle text-success"></i> Scores and Results</li>
                        <li><i class="bi bi-check-circle text-success"></i> Interviewer Information</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set default dates
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    
    document.getElementById('start_date').value = firstDayOfMonth.toISOString().split('T')[0];
    document.getElementById('end_date').value = today.toISOString().split('T')[0];

    // Validate date range
    document.querySelector('form').addEventListener('submit', function(e) {
        const startDate = new Date(document.getElementById('start_date').value);
        const endDate = new Date(document.getElementById('end_date').value);
        
        if (startDate > endDate) {
            e.preventDefault();
            alert('Start date cannot be later than end date.');
        }
    });
});
</script>

<?php
admin_footer();
?>
