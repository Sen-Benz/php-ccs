<?php
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

// Get user data
$user = isset($user) ? $user : $auth->getCurrentUser();
$is_super_admin = $user['role'] === 'super_admin';

// Calculate base path
$base_path = $is_super_admin ? '/php-ccs/php-ccs' : '/php-ccs/php-ccs';
?>

<!-- Sidebar -->
<div class="col-md-3 col-lg-2 px-0 sidebar" id="sidebar">
    <div class="d-flex flex-column p-3 min-vh-100">
        <a href="<?php echo $base_path . ($is_super_admin ? '/admin/super/dashboard.php' : '/admin/dashboard.php'); ?>" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <i class="bi bi-mortarboard-fill me-2 fs-4"></i>
            <span class="fs-4">CCS Screening</span>
        </a>
        <hr class="text-white">
        
        <ul class="nav nav-pills flex-column mb-auto">
            <!-- Dashboard -->
            <li class="nav-item mb-1">
                <a href="<?php echo $base_path . ($is_super_admin ? '/admin/super/dashboard.php' : '/admin/dashboard.php'); ?>" 
                   class="nav-link <?php echo is_active('dashboard'); ?>">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </a>
            </li>

            <?php if($is_super_admin): ?>
            <!-- Admin Management (Super Admin Only) -->
            <li class="nav-item mb-1">
                <a href="#adminSubmenu" data-bs-toggle="collapse" 
                   class="nav-link d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-people me-2"></i>
                        Admin Management
                    </div>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse <?php echo is_menu_open(['create_admin', 'list_admins']); ?>" id="adminSubmenu">
                    <ul class="nav nav-pills flex-column ms-3 mt-1">
                        <li class="nav-item">
                            <a href="<?php echo $base_path; ?>/admin/super/create_admin.php" class="nav-link <?php echo is_active('create_admin'); ?>">
                                <i class="bi bi-person-plus me-2"></i>
                                Create Admin
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_path; ?>/admin/super/list_admins.php" class="nav-link <?php echo is_active('list_admins'); ?>">
                                <i class="bi bi-person-lines-fill me-2"></i>
                                View Admins
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <?php endif; ?>

            <!-- Applicant Management -->
            <li class="nav-item mb-1">
                <a href="#applicantSubmenu" data-bs-toggle="collapse" 
                   class="nav-link d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-person-lines-fill me-2"></i>
                        Applicants
                    </div>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse <?php echo is_menu_open(['list_applicants', 'applicant_status']); ?>" id="applicantSubmenu">
                    <ul class="nav nav-pills flex-column ms-3 mt-1">
                        <li class="nav-item">
                            <a href="<?php echo $base_path . ($is_super_admin ? '/admin/super/list_applicants.php' : '/admin/list_applicants.php'); ?>" 
                               class="nav-link <?php echo is_active('list_applicants'); ?>">
                                <i class="bi bi-list-ul me-2"></i>
                                View Applicants
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_path . ($is_super_admin ? '/admin/super/applicant_status.php' : '/admin/applicant_status.php'); ?>" 
                               class="nav-link <?php echo is_active('applicant_status'); ?>">
                                <i class="bi bi-graph-up me-2"></i>
                                Status Overview
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Exam Management -->
            <li class="nav-item mb-1">
                <a href="#examSubmenu" data-bs-toggle="collapse" 
                   class="nav-link d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-file-text me-2"></i>
                        Exam Management
                    </div>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse <?php echo is_menu_open(['create_exam', 'list_exams', 'exam_results']); ?>" id="examSubmenu">
                    <ul class="nav nav-pills flex-column ms-3 mt-1">
                        <li class="nav-item">
                            <a href="<?php echo $base_path . ($is_super_admin ? '/admin/super/create_exam.php' : '/admin/create_exam.php'); ?>" 
                               class="nav-link <?php echo is_active('create_exam'); ?>">
                                <i class="bi bi-plus-circle me-2"></i>
                                Create Exam
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_path . ($is_super_admin ? '/admin/super/list_exams.php' : '/admin/list_exams.php'); ?>" 
                               class="nav-link <?php echo is_active('list_exams'); ?>">
                                <i class="bi bi-list-check me-2"></i>
                                View Exams
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_path . ($is_super_admin ? '/admin/super/exam_results.php' : '/admin/exam_results.php'); ?>" 
                               class="nav-link <?php echo is_active('exam_results'); ?>">
                                <i class="bi bi-clipboard-data me-2"></i>
                                Exam Results
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Interview Management -->
            <li class="nav-item mb-1">
                <a href="#interviewSubmenu" data-bs-toggle="collapse" 
                   class="nav-link d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-calendar-event me-2"></i>
                        Interviews
                    </div>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse <?php echo is_menu_open(['schedule_interview', 'interview_list', 'interview_results']); ?>" id="interviewSubmenu">
                    <ul class="nav nav-pills flex-column ms-3 mt-1">
                        <li class="nav-item">
                            <a href="<?php echo $base_path . ($is_super_admin ? '/admin/super/schedule_interview.php' : '/admin/schedule_interview.php'); ?>" 
                               class="nav-link <?php echo is_active('schedule_interview'); ?>">
                                <i class="bi bi-calendar-plus me-2"></i>
                                Schedule Interview
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_path . ($is_super_admin ? '/admin/super/interview_list.php' : '/admin/interview_list.php'); ?>" 
                               class="nav-link <?php echo is_active('interview_list'); ?>">
                                <i class="bi bi-calendar-week me-2"></i>
                                Interview Schedule
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_path . ($is_super_admin ? '/admin/super/interview_results.php' : '/admin/interview_results.php'); ?>" 
                               class="nav-link <?php echo is_active('interview_results'); ?>">
                                <i class="bi bi-clipboard-check me-2"></i>
                                Interview Results
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Reports -->
            <li class="nav-item mb-1">
                <a href="#reportsSubmenu" data-bs-toggle="collapse" 
                   class="nav-link d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-graph-up me-2"></i>
                        Reports
                    </div>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse <?php echo is_menu_open(['analytics', 'export_reports']); ?>" id="reportsSubmenu">
                    <ul class="nav nav-pills flex-column ms-3 mt-1">
                        <li class="nav-item">
                            <a href="<?php echo $base_path . ($is_super_admin ? '/admin/super/analytics.php' : '/admin/analytics.php'); ?>" 
                               class="nav-link <?php echo is_active('analytics'); ?>">
                                <i class="bi bi-bar-chart me-2"></i>
                                Analytics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_path . ($is_super_admin ? '/admin/super/export_reports.php' : '/admin/export_reports.php'); ?>" 
                               class="nav-link <?php echo is_active('export_reports'); ?>">
                                <i class="bi bi-download me-2"></i>
                                Export Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Settings -->
            <li class="nav-item mb-1">
                <a href="<?php echo $base_path . ($is_super_admin ? '/admin/super/settings.php' : '/admin/settings.php'); ?>" 
                   class="nav-link <?php echo is_active('settings'); ?>">
                    <i class="bi bi-gear me-2"></i>
                    Settings
                </a>
            </li>
        </ul>

        <hr class="text-white">
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle me-2"></i>
                <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                <li><a class="dropdown-item" href="<?php echo $base_path . ($is_super_admin ? '/admin/super/profile.php' : '/admin/profile.php'); ?>">Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?php echo $base_path; ?>/logout.php">Sign out</a></li>
            </ul>
        </div>
    </div>
</div>

<style>
.sidebar {
    background: #0d6efd;
    min-height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 100;
    width: inherit;
    max-width: inherit;
}

.nav-link {
    color: rgba(255,255,255,.85);
    border-radius: 5px;
    margin: 2px 0;
}

.nav-link:hover {
    color: white;
    background: rgba(255,255,255,.1);
}

.nav-link.active {
    background: rgba(255,255,255,.2);
    color: white;
}

.collapse .nav-link {
    font-size: 0.95rem;
    padding: 0.5rem 1rem;
}

.sidebar hr {
    margin: 1rem 0;
    opacity: 0.25;
}

@media (max-width: 767.98px) {
    .sidebar {
        position: static;
        height: auto;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Keep submenus open for active items
    const activeLinks = document.querySelectorAll('.nav-link.active');
    activeLinks.forEach(link => {
        const submenu = link.closest('.collapse');
        if (submenu) {
            submenu.classList.add('show');
        }
    });
});
</script>
