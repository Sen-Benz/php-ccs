<?php
require_once __DIR__ . '/../../classes/Auth.php';
require_once __DIR__ . '/../../config/database.php';

function get_current_page() {
    $current_file = basename($_SERVER['PHP_SELF']);
    return str_replace('.php', '', $current_file);
}

function is_active($page) {
    return get_current_page() === $page ? 'active' : '';
}

function is_menu_open($menu_items) {
    $current = get_current_page();
    return in_array($current, $menu_items) ? 'show' : '';
}

// Initialize Auth
$auth = new Auth();
$auth->requireRole('applicant');
$user = $auth->getCurrentUser();

// Calculate base path
$base_path = '/php-ccs/php-ccs';

// Get applicant progress
$database = new Database();
$conn = $database->getConnection();
$query = "SELECT progress_status, preferred_course FROM applicants WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$user['id']]);
$applicant_data = $stmt->fetch(PDO::FETCH_ASSOC);
$progress_status = $applicant_data['progress_status'] ?? 'registered';
?>

<!-- Sidebar -->
<div class="col-md-3 col-lg-2 px-0 sidebar" id="sidebar">
    <div class="d-flex flex-column min-vh-100">
        <!-- Sidebar Header -->
        <div class="d-flex align-items-center p-3">
            <a href="<?php echo $base_path; ?>/applicant/dashboard.php" class="text-white text-decoration-none flex-grow-1">
                <i class="bi bi-mortarboard-fill me-2 fs-4"></i>
                <span class="fs-4">CCS Screening</span>
            </a>
            <button class="btn btn-link text-white d-md-none" id="sidebarCollapseBtn">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <hr class="text-white mx-3 mt-0">
        
        <ul class="nav nav-pills flex-column mb-auto px-2">
            <!-- Dashboard -->
            <li class="nav-item mb-1">
                <a href="<?php echo $base_path; ?>/applicant/dashboard.php" 
                   class="nav-link <?php echo is_active('dashboard'); ?>">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </a>
            </li>

            <!-- Profile -->
            <li class="nav-item mb-1">
                <a href="<?php echo $base_path; ?>/applicant/profile.php" 
                   class="nav-link <?php echo is_active('profile'); ?>">
                    <i class="bi bi-person me-2"></i>
                    My Profile
                </a>
            </li>

            <!-- Exams -->
            <li class="nav-item mb-1">
                <a href="#examSubmenu" data-bs-toggle="collapse" 
                   class="nav-link d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-file-text me-2"></i>
                        Exams
                    </div>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse <?php echo is_menu_open(['exam', 'exam_results']); ?>" id="examSubmenu">
                    <ul class="nav flex-column ms-3">
                        <?php if (in_array($progress_status, ['registered', 'exam_scheduled', 'exam_completed'])): ?>
                        <li class="nav-item">
                            <a href="<?php echo $base_path; ?>/applicant/exam.php" 
                               class="nav-link <?php echo is_active('exam'); ?>">
                                <i class="bi bi-pencil me-2"></i>
                                Take Exam
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a href="<?php echo $base_path; ?>/applicant/exam_results.php" 
                               class="nav-link <?php echo is_active('exam_results'); ?>">
                                <i class="bi bi-card-checklist me-2"></i>
                                My Results
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Interview -->
            <?php if (in_array($progress_status, ['exam_completed', 'interview_scheduled', 'interview_completed'])): ?>
            <li class="nav-item mb-1">
                <a href="#interviewSubmenu" data-bs-toggle="collapse" 
                   class="nav-link d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-camera-video me-2"></i>
                        Interview
                    </div>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse <?php echo is_menu_open(['interview_schedule', 'interview_details']); ?>" id="interviewSubmenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a href="<?php echo $base_path; ?>/applicant/interview_schedule.php" 
                               class="nav-link <?php echo is_active('interview_schedule'); ?>">
                                <i class="bi bi-calendar-event me-2"></i>
                                Schedule
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_path; ?>/applicant/interview_details.php" 
                               class="nav-link <?php echo is_active('interview_details'); ?>">
                                <i class="bi bi-info-circle me-2"></i>
                                Details
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <?php endif; ?>

            <!-- Help -->
            <li class="nav-item mb-1">
                <a href="<?php echo $base_path; ?>/applicant/help.php" 
                   class="nav-link <?php echo is_active('help'); ?>">
                    <i class="bi bi-question-circle me-2"></i>
                    Help & Support
                </a>
            </li>
        </ul>

        <hr class="text-white mx-3">
        <div class="dropdown px-3 mb-3">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle me-2"></i>
                <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                <li><a class="dropdown-item" href="<?php echo $base_path; ?>/applicant/profile.php">Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?php echo $base_path; ?>/logout.php">Sign out</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Mobile Toggle Button -->
<button class="btn btn-primary position-fixed d-md-none" 
        id="sidebarToggleBtn"
        style="top: 10px; left: 10px; z-index: 1031;">
    <i class="bi bi-list"></i>
</button>

<style>
.sidebar {
    background: #0d6efd;
    transition: margin-left 0.3s ease-in-out;
}

@media (max-width: 767.98px) {
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        z-index: 1030;
        margin-left: -100%;
    }
    
    .sidebar.show {
        margin-left: 0;
    }
}

.sidebar .nav-link {
    color: rgba(255, 255, 255, 0.85);
    font-size: 0.95rem;
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
}

.sidebar .nav-link:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.1);
}

.sidebar .nav-link.active {
    color: #0d6efd;
    background: #fff;
}

.sidebar .collapse .nav-link {
    padding-left: 1rem;
    font-size: 0.9rem;
}

.sidebar hr {
    margin: 1rem 0;
    opacity: 0.25;
}

.dropdown-toggle { outline: 0; }

.btn-toggle {
    padding: .25rem .5rem;
    font-weight: 600;
    background-color: transparent;
    border: 0;
}

.btn-toggle:hover,
.btn-toggle:focus {
    background-color: rgba(255, 255, 255, .1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const collapseBtn = document.getElementById('sidebarCollapseBtn');
    
    // Toggle sidebar on mobile
    toggleBtn.addEventListener('click', function() {
        sidebar.classList.add('show');
    });
    
    // Close sidebar on mobile
    collapseBtn.addEventListener('click', function() {
        sidebar.classList.remove('show');
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth < 768) {
            const isClickInside = sidebar.contains(event.target) || toggleBtn.contains(event.target);
            if (!isClickInside && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        }
    });
});
</script>
