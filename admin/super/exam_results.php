<?php
require_once '../../classes/Auth.php';
require_once '../../config/database.php';
require_once '../includes/layout.php';

$auth = new Auth();
$auth->requireRole('super_admin');

$user = $auth->getCurrentUser();
$error = '';
$success = '';
$resultsByPart = [];

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Get filter parameters
    $exam_part = $_GET['part'] ?? 'all';
    $status = $_GET['status'] ?? 'all';
    $search = $_GET['search'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';

    // Build the WHERE clause
    $where_conditions = [];
    $params = [];

    if ($exam_part !== 'all') {
        $where_conditions[] = "e.part = ?";
        $params[] = $exam_part;
    }

    if ($status !== 'all') {
        if ($status === 'passed') {
            $where_conditions[] = "(SELECT COUNT(*) FROM applicant_answers aa WHERE aa.exam_id = e.id AND aa.applicant_id = a.id AND aa.is_correct = 1) / (SELECT COUNT(*) FROM questions q WHERE q.exam_id = e.id) * 100 >= e.passing_score";
        } else {
            $where_conditions[] = "(SELECT COUNT(*) FROM applicant_answers aa WHERE aa.exam_id = e.id AND aa.applicant_id = a.id AND aa.is_correct = 1) / (SELECT COUNT(*) FROM questions q WHERE q.exam_id = e.id) * 100 < e.passing_score";
        }
    }

    if ($search) {
        $where_conditions[] = "(CONCAT(a.first_name, ' ', a.last_name) LIKE ? OR e.title LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if ($date_from) {
        $where_conditions[] = "er.completed_at >= ?";
        $params[] = $date_from;
    }

    if ($date_to) {
        $where_conditions[] = "er.completed_at <= ?";
        $params[] = $date_to . ' 23:59:59';
    }

    $where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

    // Get all exam results grouped by part
    $query = "SELECT 
                er.*,
                e.title as exam_title,
                e.type as exam_type,
                e.part as exam_part,
                e.passing_score,
                CONCAT(a.first_name, ' ', a.last_name) as applicant_name,
                (SELECT COUNT(*) FROM questions q WHERE q.exam_id = e.id) as total_questions,
                (SELECT COUNT(*) FROM applicant_answers aa 
                 WHERE aa.exam_id = e.id 
                 AND aa.applicant_id = a.id 
                 AND aa.is_correct = 1) as correct_answers
              FROM exam_results er
              JOIN exams e ON er.exam_id = e.id
              JOIN applicants a ON er.applicant_id = a.id
              $where_clause
              ORDER BY e.part ASC, er.completed_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group results by exam part
    foreach ($results as $result) {
        $resultsByPart[$result['exam_part']][] = $result;
    }

} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
    $results = [];
}

admin_header('Exam Results');
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Exam Results</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="exportToExcel()">
            <i class="bi bi-file-earmark-excel"></i> Export to Excel
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer"></i> Print View
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Search by name or exam...">
            </div>
            <div class="col-md-2">
                <label for="part" class="form-label">Exam Part</label>
                <select class="form-select" id="part" name="part">
                    <option value="all" <?php echo $exam_part === 'all' ? 'selected' : ''; ?>>All Parts</option>
                    <option value="1" <?php echo $exam_part === '1' ? 'selected' : ''; ?>>Part 1</option>
                    <option value="2" <?php echo $exam_part === '2' ? 'selected' : ''; ?>>Part 2</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="passed" <?php echo $status === 'passed' ? 'selected' : ''; ?>>Passed</option>
                    <option value="failed" <?php echo $status === 'failed' ? 'selected' : ''; ?>>Failed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label">Date From</label>
                <input type="date" class="form-control" id="date_from" name="date_from" 
                       value="<?php echo $date_from; ?>">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label">Date To</label>
                <input type="date" class="form-control" id="date_to" name="date_to" 
                       value="<?php echo $date_to; ?>">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<?php if ($error): ?>
<div class="alert alert-danger" role="alert">
    <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<?php foreach ($resultsByPart as $part => $partResults): ?>
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h3 class="h5 mb-0">Part <?php echo $part; ?> Results</h3>
        </div>
        <div class="card-body">
            <?php if (empty($partResults)): ?>
                <p class="text-center text-muted my-5">No results found for Part <?php echo $part; ?></p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="resultsTable<?php echo $part; ?>">
                        <thead>
                            <tr>
                                <th>Applicant</th>
                                <th>Exam</th>
                                <th>Score</th>
                                <th>Correct/Total</th>
                                <th>Status</th>
                                <th>Completion Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($partResults as $result): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($result['applicant_name']); ?></td>
                                <td><?php echo htmlspecialchars($result['exam_title']); ?></td>
                                <td>
                                    <?php 
                                        $score = ($result['correct_answers'] / $result['total_questions']) * 100;
                                        echo number_format($score, 1) . '%';
                                    ?>
                                </td>
                                <td>
                                    <?php echo $result['correct_answers'] . '/' . $result['total_questions']; ?>
                                </td>
                                <td>
                                    <?php 
                                        $passed = $score >= $result['passing_score'];
                                        echo '<span class="badge ' . ($passed ? 'bg-success' : 'bg-danger') . '">';
                                        echo $passed ? 'Passed' : 'Failed';
                                        echo '</span>';
                                    ?>
                                </td>
                                <td><?php echo date('M d, Y h:i A', strtotime($result['completed_at'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="view_result.php?result_id=<?php echo $result['id']; ?>">
                                                    <i class="bi bi-eye"></i> View Details
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="print_result.php?result_id=<?php echo $result['id']; ?>" target="_blank">
                                                    <i class="bi bi-printer"></i> Print Result
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

<script>
function exportToExcel() {
    // Create a workbook with multiple sheets (one for each part)
    let workbook = XLSX.utils.book_new();
    
    <?php foreach ($resultsByPart as $part => $partResults): ?>
    // Convert the table to worksheet
    let table<?php echo $part; ?> = document.getElementById('resultsTable<?php echo $part; ?>');
    let worksheet<?php echo $part; ?> = XLSX.utils.table_to_sheet(table<?php echo $part; ?>);
    
    // Add the worksheet to workbook
    XLSX.utils.book_append_sheet(workbook, worksheet<?php echo $part; ?>, 'Part <?php echo $part; ?> Results');
    <?php endforeach; ?>
    
    // Generate Excel file
    XLSX.writeFile(workbook, 'exam_results.xlsx');
}
</script>

<!-- Add SheetJS for Excel export -->
<script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>

<?php
admin_footer();
?>
