<?php
require_once '../classes/Auth.php';
require_once '../config/database.php';
require_once '../includes/layout.php';

$auth = new Auth();
$auth->requireRole('applicant');

$user = $auth->getCurrentUser();

// Get applicant's progress and exam results
$database = new Database();
$conn = $database->getConnection();

$query = "SELECT a.*, 
    (SELECT COUNT(*) FROM exam_results er WHERE er.applicant_id = a.id) as exams_completed,
    (SELECT COUNT(*) FROM exam_results er WHERE er.applicant_id = a.id AND er.status = 'passed') as exams_passed,
    (SELECT COUNT(*) FROM interview_schedules i WHERE i.applicant_id = a.id AND i.status = 'completed') as interview_completed,
    (SELECT schedule_date FROM interview_schedules i WHERE i.applicant_id = a.id ORDER BY schedule_date DESC LIMIT 1) as interview_date
    FROM applicants a 
    WHERE a.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$user['id']]);
$applicant = $stmt->fetch(PDO::FETCH_ASSOC);

// Get exam results
$query = "SELECT er.*, e.title, e.part 
          FROM exam_results er 
          JOIN exams e ON er.exam_id = e.id 
          WHERE er.applicant_id = ? 
          ORDER BY er.completed_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute([$applicant['id']]);
$exam_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get announcements
$query = "SELECT * FROM announcements WHERE target_role IN ('all', 'applicant') ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->execute();
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate completion percentage
$total_steps = 4; // Registration, Part 1, Part 2, Interview
$completed_steps = 1; // Registration is done
$completed_steps += $applicant['exams_completed'];
$completed_steps += $applicant['interview_completed'];
$progress_percentage = ($completed_steps / $total_steps) * 100;

get_header('Applicant Dashboard');
get_sidebar('applicant');
?>

<div class="content">
    <div class="container-fluid px-4 py-4" style="margin-top: 60px;">
        <div class="row align-items-center mb-4">
            <div class="col">
                <h1 class="h3 mb-0">Welcome, <?php echo htmlspecialchars($applicant['first_name']); ?>!</h1>
                <p class="text-muted">Track your CCS screening progress</p>
            </div>
            <div class="col-auto">
                <span class="badge bg-primary"><?php echo ucfirst($applicant['preferred_course']); ?> Applicant</span>
            </div>
        </div>

        <!-- Progress Overview -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Overall Progress</h5>
                        <div class="progress mb-3" style="height: 20px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: <?php echo $progress_percentage; ?>%;"
                                 aria-valuenow="<?php echo $progress_percentage; ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <?php echo round($progress_percentage); ?>%
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col">
                                <div class="d-inline-block px-3">
                                    <i class="bi bi-check-circle text-success me-1"></i>
                                    Registration
                                </div>
                            </div>
                            <div class="col">
                                <div class="d-inline-block px-3">
                                    <?php if ($applicant['exams_completed'] > 0): ?>
                                        <i class="bi bi-check-circle text-success me-1"></i>
                                    <?php else: ?>
                                        <i class="bi bi-circle text-muted me-1"></i>
                                    <?php endif; ?>
                                    Exam Part 1
                                </div>
                            </div>
                            <div class="col">
                                <div class="d-inline-block px-3">
                                    <?php if ($applicant['exams_completed'] > 1): ?>
                                        <i class="bi bi-check-circle text-success me-1"></i>
                                    <?php else: ?>
                                        <i class="bi bi-circle text-muted me-1"></i>
                                    <?php endif; ?>
                                    Exam Part 2
                                </div>
                            </div>
                            <div class="col">
                                <div class="d-inline-block px-3">
                                    <?php if ($applicant['interview_completed']): ?>
                                        <i class="bi bi-check-circle text-success me-1"></i>
                                    <?php else: ?>
                                        <i class="bi bi-circle text-muted me-1"></i>
                                    <?php endif; ?>
                                    Interview
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Status Cards -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Application Status</h5>
                        
                        <!-- Exam Status -->
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <i class="bi bi-file-text fs-4 text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Entrance Exam</h6>
                                <small class="text-muted">
                                    <?php
                                    if ($applicant['exams_completed'] == 0) {
                                        echo "Not started";
                                    } elseif ($applicant['exams_completed'] == 1) {
                                        echo "Part 1 completed";
                                    } elseif ($applicant['exams_completed'] == 2) {
                                        echo "All parts completed";
                                    }
                                    ?>
                                </small>
                            </div>
                        </div>
                        
                        <!-- Interview Status -->
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-camera-video fs-4 text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Interview</h6>
                                <small class="text-muted">
                                    <?php
                                    if ($applicant['interview_completed']) {
                                        echo "Completed";
                                    } elseif ($applicant['interview_date']) {
                                        echo "Scheduled for " . date('F j, Y', strtotime($applicant['interview_date']));
                                    } else {
                                        echo "Not scheduled";
                                    }
                                    ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Next Steps</h5>
                        
                        <?php if ($applicant['exams_completed'] == 0): ?>
                            <div class="alert alert-info mb-0">
                                <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Take Your Entrance Exam</h6>
                                <p class="mb-0">You can now take Part 1 of the entrance examination. Click the button below to start.</p>
                                <hr>
                                <a href="exam.php" class="btn btn-primary">Start Exam</a>
                            </div>
                        <?php elseif ($applicant['exams_completed'] == 1): ?>
                            <div class="alert alert-info mb-0">
                                <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Complete Part 2</h6>
                                <p class="mb-0">You've completed Part 1. Proceed to take Part 2 of the entrance examination.</p>
                                <hr>
                                <a href="exam.php" class="btn btn-primary">Take Part 2</a>
                            </div>
                        <?php elseif (!$applicant['interview_date'] && $applicant['exams_passed'] == 2): ?>
                            <div class="alert alert-success mb-0">
                                <h6 class="alert-heading"><i class="bi bi-check-circle me-2"></i>Schedule Your Interview</h6>
                                <p class="mb-0">Congratulations on passing the entrance exam! Please schedule your interview.</p>
                                <hr>
                                <a href="interview_schedule.php" class="btn btn-success">Schedule Now</a>
                            </div>
                        <?php elseif ($applicant['interview_date'] && !$applicant['interview_completed']): ?>
                            <div class="alert alert-success" role="alert">
                                <h6 class="alert-heading"><i class="bi bi-calendar-check me-2"></i>Interview Scheduled</h6>
                                <p class="mb-0">Your interview is scheduled for: 
                                    <strong><?php echo date('F j, Y', strtotime($applicant['interview_date'])); ?></strong>
                                </p>
                            </div>
                            <a href="interview_details.php" class="btn btn-primary">View Details</a>
                        <?php elseif ($applicant['interview_completed']): ?>
                            <div class="alert alert-success mb-0">
                                <h6 class="alert-heading"><i class="bi bi-check-circle me-2"></i>All Steps Completed</h6>
                                <p class="mb-0">You have completed all the required steps. We will notify you of the results soon.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Exam Results -->
            <div class="col-md-7 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Recent Exam Results</h5>
                        <?php if (empty($exam_results)): ?>
                            <p class="text-muted mb-0">No exam results yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Exam</th>
                                            <th>Score</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($exam_results, 0, 3) as $result): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($result['title']); ?></td>
                                                <td><?php echo $result['score']; ?>%</td>
                                                <td>
                                                    <?php if ($result['status'] === 'passed'): ?>
                                                        <span class="badge bg-success">Passed</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Failed</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($result['completed_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if (count($exam_results) > 3): ?>
                                <a href="exam_results.php" class="btn btn-link">View all results</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Announcements -->
            <div class="col-md-5 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Recent Announcements</h5>
                        <?php if (empty($announcements)): ?>
                            <p class="text-muted mb-0">No announcements at this time.</p>
                        <?php else: ?>
                            <?php foreach ($announcements as $announcement): ?>
                                <div class="border-bottom mb-3 pb-3">
                                    <h6><?php echo htmlspecialchars($announcement['title']); ?></h6>
                                    <p class="mb-1"><?php echo htmlspecialchars($announcement['content']); ?></p>
                                    <small class="text-muted">
                                        <?php echo date('F j, Y', strtotime($announcement['created_at'])); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
