<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

function get_header($title = 'CCS Freshman Screening') {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #f8f9fa;
        }
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .navbar-brand {
            padding-top: .75rem;
            padding-bottom: .75rem;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .content {
            margin-left: 240px;
            padding: 20px;
        }
        .nav-link {
            color: #333;
            padding: 8px 16px;
            margin: 4px 0;
        }
        .nav-link:hover {
            background-color: #e9ecef;
        }
        .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">CCS Freshman Screening</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['email']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/profile.php">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
<?php
}

function get_sidebar($role) {
    $menu_items = [
        'super_admin' => [
            ['icon' => 'speedometer2', 'text' => 'Dashboard', 'link' => '/admin/super/dashboard.php'],
            ['icon' => 'people', 'text' => 'Manage Admins', 'link' => '/admin/super/admins.php'],
            ['icon' => 'person-lines-fill', 'text' => 'Manage Applicants', 'link' => '/admin/super/applicants.php'],
            ['icon' => 'file-text', 'text' => 'Manage Exams', 'link' => '/admin/super/exams.php'],
            ['icon' => 'graph-up', 'text' => 'Reports', 'link' => '/admin/super/reports.php'],
            ['icon' => 'gear', 'text' => 'Settings', 'link' => '/admin/super/settings.php']
        ],
        'admin' => [
            ['icon' => 'speedometer2', 'text' => 'Dashboard', 'link' => '/admin/dashboard.php'],
            ['icon' => 'person-lines-fill', 'text' => 'Applicants', 'link' => '/admin/applicants.php'],
            ['icon' => 'file-text', 'text' => 'Exams', 'link' => '/admin/exams.php'],
            ['icon' => 'calendar-event', 'text' => 'Interviews', 'link' => '/admin/interviews.php'],
            ['icon' => 'megaphone', 'text' => 'Announcements', 'link' => '/admin/announcements.php']
        ],
        'applicant' => [
            ['icon' => 'speedometer2', 'text' => 'Dashboard', 'link' => '/applicant/dashboard.php'],
            ['icon' => 'file-text', 'text' => 'Take Exam', 'link' => '/applicant/exam.php'],
            ['icon' => 'calendar-event', 'text' => 'Interview Schedule', 'link' => '/applicant/interview.php'],
            ['icon' => 'bell', 'text' => 'Notifications', 'link' => '/applicant/notifications.php']
        ]
    ];

    $current_items = $menu_items[$role] ?? [];
    if(empty($current_items)) return;
?>
    <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
        <div class="sidebar-sticky">
            <ul class="nav flex-column">
                <?php foreach($current_items as $item): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($_SERVER['PHP_SELF'] === $item['link']) ? 'active' : ''; ?>" 
                           href="<?php echo $item['link']; ?>">
                            <i class="bi bi-<?php echo $item['icon']; ?> me-2"></i>
                            <?php echo htmlspecialchars($item['text']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>
<?php
}

function get_footer() {
?>
    <footer class="footer mt-auto py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">© <?php echo date('Y'); ?> Eulogio "Amang" Rodriguez Institute of Science and Technology. All rights reserved.</span>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
}
?>
