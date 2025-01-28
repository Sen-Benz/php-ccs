<?php
require_once '../classes/Auth.php';
require_once '../includes/layout.php';

$auth = new Auth();
$auth->requireRole('applicant');

$user = $auth->getCurrentUser();

// Get applicant's progress
$database = new Database();
$conn = $database->getConnection();

$query = "SELECT a.*, 
    (SELECT COUNT(*) FROM exam_results er WHERE er.applicant_id = a.id) as exams_completed,
    (SELECT COUNT(*) FROM interview_schedules i WHERE i.applicant_id = a.id AND i.status = 'completed') as interview_completed
    FROM applicants a 
    WHERE a.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$user['id']]);
$applicant = $stmt->fetch(PDO::FETCH_ASSOC);

// Get announcements
$query = "SELECT * FROM announcements WHERE target_role IN ('all', 'applicant') ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->execute();
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

get_header('Applicant Dashboard');
get_sidebar('applicant');
?>

<div class="content">
    <div class="container-fluid">
        <h1 class="h2 mb-4">Welcome, <?php echo htmlspecialchars($applicant['first_name']); ?>!</h1>
        
        <!-- Progress Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Application Status</h5>
                        <p class="card-text">
                            <span class="h3 d-block mb-2"><?php echo ucfirst(str_replace('_', ' ', $applicant['progress_status'])); ?></span>
                            <small class="text-muted">Current stage of your application</small>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Exams Completed</h5>
                        <p class="card-text">
                            <span class="h3 d-block mb-2"><?php echo $applicant['exams_completed']; ?> / 2</span>
                            <small class="text-muted">Part 1 and Part 2 examinations</small>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Interview Status</h5>
                        <p class="card-text">
                            <span class="h3 d-block mb-2">
                                <?php echo $applicant['interview_completed'] ? 'Completed' : 'Pending'; ?>
                            </span>
                            <small class="text-muted">Final interview stage</small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Next Steps -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Next Steps</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        switch($applicant['progress_status']) {
                            case 'registered':
                                echo '<p>Welcome to the CCS Freshman Screening! Your next step is to take Part 1 of the examination.</p>';
                                echo '<a href="/applicant/exam.php" class="btn btn-primary">Start Part 1 Exam</a>';
                                break;
                            case 'part1_completed':
                                echo '<p>Congratulations on completing Part 1! You can now proceed with Part 2 of the examination.</p>';
                                echo '<a href="/applicant/exam.php" class="btn btn-primary">Start Part 2 Exam</a>';
                                break;
                            case 'part2_completed':
                                echo '<p>Great job completing both parts of the exam! Please wait for your interview schedule.</p>';
                                echo '<a href="/applicant/interview.php" class="btn btn-primary">Check Interview Schedule</a>';
                                break;
                            case 'interview_pending':
                                echo '<p>Your interview has been scheduled. Please prepare and attend on time.</p>';
                                echo '<a href="/applicant/interview.php" class="btn btn-primary">View Interview Details</a>';
                                break;
                            case 'interview_completed':
                                echo '<p>Your interview is complete. Please wait for the final results.</p>';
                                break;
                            case 'passed':
                                echo '<p class="text-success">Congratulations! You have passed the screening process.</p>';
                                break;
                            case 'failed':
                                echo '<p class="text-danger">We regret to inform you that you did not pass the screening process.</p>';
                                break;
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <!-- Announcements -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Announcements</h5>
                    </div>
                    <div class="card-body">
                        <?php if(empty($announcements)): ?>
                            <p class="text-muted">No announcements at this time.</p>
                        <?php else: ?>
                            <?php foreach($announcements as $announcement): ?>
                                <div class="mb-3">
                                    <h6><?php echo htmlspecialchars($announcement['title']); ?></h6>
                                    <p class="small text-muted mb-1">
                                        <?php echo date('M d, Y', strtotime($announcement['created_at'])); ?>
                                    </p>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
                                </div>
                                <hr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
