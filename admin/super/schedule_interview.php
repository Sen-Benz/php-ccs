<?php
require_once '../../classes/Auth.php';
require_once '../../config/database.php';
require_once '../../classes/Email.php';
require_once '../includes/layout.php';

$auth = new Auth();
$auth->requireRole('super_admin');

$user = $auth->getCurrentUser();
$error = '';
$success = '';

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Get list of applicants who have completed Part 2
    $query = "SELECT 
                a.id,
                CONCAT(a.first_name, ' ', a.last_name) as applicant_name,
                a.contact_number,
                u.email,
                a.progress_status
              FROM applicants a
              JOIN users u ON a.user_id = u.id
              WHERE a.progress_status = 'part2_completed'
              AND NOT EXISTS (
                SELECT 1 FROM interview_schedules i 
                WHERE i.applicant_id = a.id 
                AND i.status != 'cancelled'
              )
              ORDER BY a.last_name, a.first_name";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $applicants = $stmt->fetchAll();

    // Get list of available interviewers (admins and super admins)
    $query = "SELECT 
                u.id,
                CONCAT(u.first_name, ' ', u.last_name) as interviewer_name,
                u.role
              FROM users u
              WHERE u.role IN ('admin', 'super_admin')
              AND u.status = 'active'
              ORDER BY u.role DESC, u.last_name, u.first_name";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $interviewers = $stmt->fetchAll();

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $applicant_id = $_POST['applicant_id'] ?? '';
        $interviewer_id = $_POST['interviewer_id'] ?? '';
        $schedule_date = $_POST['schedule_date'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $meeting_link = $_POST['meeting_link'] ?? '';

        if (!$applicant_id || !$interviewer_id || !$schedule_date || !$start_time || !$end_time || !$meeting_link) {
            throw new Exception('Please fill in all required fields.');
        }

        // Validate date and time
        $schedule_datetime = new DateTime($schedule_date . ' ' . $start_time);
        $end_datetime = new DateTime($schedule_date . ' ' . $end_time);
        $now = new DateTime();

        if ($schedule_datetime < $now) {
            throw new Exception('Interview date and time must be in the future.');
        }

        if ($end_datetime <= $schedule_datetime) {
            throw new Exception('End time must be after start time.');
        }

        // Validate meeting link
        if (!filter_var($meeting_link, FILTER_VALIDATE_URL)) {
            throw new Exception('Please enter a valid meeting link.');
        }

        // Check for interviewer availability
        $query = "SELECT COUNT(*) as count
                  FROM interview_schedules
                  WHERE interviewer_id = ?
                  AND schedule_date = ?
                  AND status != 'cancelled'
                  AND (
                      (start_time BETWEEN ? AND ?) OR
                      (end_time BETWEEN ? AND ?) OR
                      (start_time <= ? AND end_time >= ?)
                  )";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([
            $interviewer_id,
            $schedule_date,
            $start_time,
            $end_time,
            $start_time,
            $end_time,
            $start_time,
            $end_time
        ]);
        
        if ($stmt->fetch()['count'] > 0) {
            throw new Exception('The interviewer is not available at the selected time.');
        }

        // Create interview schedule
        $query = "INSERT INTO interview_schedules 
                  (applicant_id, interviewer_id, schedule_date, start_time, end_time, notes, meeting_link)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([
            $applicant_id,
            $interviewer_id,
            $schedule_date,
            $start_time,
            $end_time,
            $notes,
            $meeting_link
        ]);

        // Send email notification
        $email = new Email();
        $query = "SELECT 
                    a.id,
                    CONCAT(a.first_name, ' ', a.last_name) as applicant_name,
                    u.email
                  FROM applicants a
                  JOIN users u ON a.user_id = u.id
                  WHERE a.id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$applicant_id]);
        $applicant = $stmt->fetch();
        $query = "SELECT 
                    u.id,
                    CONCAT(u.first_name, ' ', u.last_name) as interviewer_name
                  FROM users u
                  WHERE u.id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$interviewer_id]);
        $interviewer = $stmt->fetch();
        $interview_data = [
            'schedule_date' => $schedule_date,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'interviewer_name' => $interviewer['interviewer_name'],
            'meeting_link' => $meeting_link,
            'notes' => $notes
        ];
        
        if (!$email->sendInterviewSchedule($applicant['email'], $applicant['applicant_name'], $interview_data)) {
            // Log email error but don't stop the process
            error_log("Failed to send interview schedule email: " . $email->getError());
        }

        // Update applicant status
        $query = "UPDATE applicants SET progress_status = 'interview_pending' WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$applicant_id]);

        // Send notification to applicant
        $query = "INSERT INTO notifications (user_id, title, message, type)
                  SELECT 
                    a.user_id,
                    'Interview Scheduled',
                    CONCAT('Your interview has been scheduled for ', 
                           DATE_FORMAT(?, '%M %d, %Y'), ' at ',
                           TIME_FORMAT(?, '%h:%i %p')),
                    'interview'
                  FROM applicants a
                  WHERE a.id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([
            $schedule_date,
            $start_time,
            $applicant_id
        ]);

        $success = 'Interview has been successfully scheduled.';
        
        // Refresh the applicants list
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $applicants = $stmt->fetchAll();
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}

admin_header('Schedule Interview');
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">Schedule Interview</h1>
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

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="post" id="scheduleForm">
                        <div class="mb-3">
                            <label for="applicant_id" class="form-label">Select Applicant</label>
                            <select class="form-select" id="applicant_id" name="applicant_id" required>
                                <option value="">Choose an applicant...</option>
                                <?php foreach ($applicants as $applicant): ?>
                                    <option value="<?php echo $applicant['id']; ?>">
                                        <?php echo htmlspecialchars($applicant['applicant_name']); ?> 
                                        (<?php echo htmlspecialchars($applicant['email']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="interviewer_id" class="form-label">Select Interviewer</label>
                            <select class="form-select" id="interviewer_id" name="interviewer_id" required>
                                <option value="">Choose an interviewer...</option>
                                <?php foreach ($interviewers as $interviewer): ?>
                                    <option value="<?php echo $interviewer['id']; ?>">
                                        <?php echo htmlspecialchars($interviewer['interviewer_name']); ?> 
                                        (<?php echo strtoupper($interviewer['role']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="schedule_date" class="form-label">Interview Date</label>
                                    <input type="date" class="form-control" id="schedule_date" name="schedule_date" 
                                           min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="meeting_link" class="form-label">Meeting Link (Zoom/Google Meet)</label>
                                    <input type="url" class="form-control" id="meeting_link" name="meeting_link" 
                                           placeholder="https://zoom.us/j/..." required>
                                    <div class="form-text">Enter the Zoom or Google Meet link for the interview</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Start Time</label>
                                    <input type="time" class="form-control" id="start_time" name="start_time" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">End Time</label>
                                    <input type="time" class="form-control" id="end_time" name="end_time" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Add any additional notes or instructions..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Schedule Interview</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Interview Guidelines</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            Schedule interviews during business hours (9 AM - 5 PM)
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            Allow at least 1 hour for each interview
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            Check interviewer availability before scheduling
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            Include specific instructions in notes if needed
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            Ensure applicant has completed Part 2 exam
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('scheduleForm');
    const startTime = document.getElementById('start_time');
    const endTime = document.getElementById('end_time');

    startTime.addEventListener('change', function() {
        // Set minimum end time to start time
        endTime.min = this.value;
        
        // If end time is before start time, update it
        if (endTime.value && endTime.value <= this.value) {
            // Add 1 hour to start time for default end time
            const startDate = new Date(`2000-01-01T${this.value}`);
            startDate.setHours(startDate.getHours() + 1);
            const newEndTime = startDate.toTimeString().slice(0, 5);
            endTime.value = newEndTime;
        }
    });

    form.addEventListener('submit', function(e) {
        const scheduleDate = document.getElementById('schedule_date').value;
        const startTimeVal = startTime.value;
        const endTimeVal = endTime.value;

        // Create Date objects for comparison
        const scheduleDateTime = new Date(`${scheduleDate}T${startTimeVal}`);
        const endDateTime = new Date(`${scheduleDate}T${endTimeVal}`);
        const now = new Date();

        if (scheduleDateTime < now) {
            e.preventDefault();
            alert('Interview date and time must be in the future.');
            return;
        }

        if (endDateTime <= scheduleDateTime) {
            e.preventDefault();
            alert('End time must be after start time.');
            return;
        }

        // Check if interview is during business hours (9 AM - 5 PM)
        const startHour = scheduleDateTime.getHours();
        const endHour = endDateTime.getHours();

        if (startHour < 9 || endHour > 17) {
            e.preventDefault();
            alert('Please schedule interviews between 9 AM and 5 PM.');
            return;
        }
    });
});
</script>

<?php
admin_footer();
?>
