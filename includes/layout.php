<?php
function get_header($title = '') {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../classes/Auth.php';
    
    $auth = new Auth();
    $user = $auth->getCurrentUser();
    
    // Get unread notifications
    $db = Database::getInstance();
    try {
        $stmt = $db->getConnection()->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? AND is_read = 0 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $notifications = $stmt->fetchAll();
        $unread_count = count($notifications);
    } catch (Exception $e) {
        error_log("Header Notifications Error: " . $e->getMessage());
        $notifications = [];
        $unread_count = 0;
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $title ? $title . ' - ' : ''; ?>CCS Screening</title>
        
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Bootstrap Icons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
        
        <style>
            body {
                min-height: 100vh;
                background-color: #f8f9fa;
            }
            .content {
                margin-left: 0;
                transition: margin-left 0.3s ease-in-out;
            }
            @media (min-width: 768px) {
                .content {
                    margin-left: 16.666667%;
                }
            }
            .navbar {
                transition: margin-left 0.3s ease-in-out;
            }
            @media (min-width: 768px) {
                .navbar {
                    margin-left: 16.666667%;
                }
            }
            .notification-dropdown {
                min-width: 300px;
                padding: 0;
            }
            .notification-header {
                padding: 0.5rem 1rem;
                background-color: #f8f9fa;
                border-bottom: 1px solid #dee2e6;
            }
            .notification-body {
                max-height: 300px;
                overflow-y: auto;
            }
            .notification-item {
                padding: 0.75rem 1rem;
                border-bottom: 1px solid #dee2e6;
            }
            .notification-item:last-child {
                border-bottom: none;
            }
            .notification-item.unread {
                background-color: #f0f7ff;
            }
            .notification-title {
                font-weight: 600;
                margin-bottom: 0.25rem;
            }
            .notification-message {
                font-size: 0.875rem;
                color: #6c757d;
            }
            .notification-time {
                font-size: 0.75rem;
                color: #adb5bd;
            }
            .notification-footer {
                padding: 0.5rem 1rem;
                background-color: #f8f9fa;
                border-top: 1px solid #dee2e6;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <!-- Fixed Navbar -->
        <nav class="navbar navbar-expand-md navbar-dark bg-primary fixed-top">
            <div class="container-fluid">
                <span class="navbar-brand"><?php echo $title; ?></span>
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <a href="#" class="text-white text-decoration-none dropdown-toggle" id="navbarDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell me-2"></i>
                            <?php if ($unread_count > 0): ?>
                                <span class="badge bg-danger"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="navbarDropdown">
                            <div class="notification-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">Notifications</span>
                                    <?php if ($unread_count > 0): ?>
                                        <span class="badge bg-primary"><?php echo $unread_count; ?> new</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="notification-body">
                                <?php if (empty($notifications)): ?>
                                    <div class="notification-item">
                                        <div class="text-muted text-center">No new notifications</div>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($notifications as $notification): ?>
                                        <div class="notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>">
                                            <div class="notification-title">
                                                <?php echo safe_string($notification['title']); ?>
                                            </div>
                                            <div class="notification-message">
                                                <?php echo safe_string($notification['message']); ?>
                                            </div>
                                            <div class="notification-time">
                                                <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <div class="notification-footer">
                                <a href="<?php echo BASE_URL; ?>applicant/notifications.php" class="text-decoration-none">
                                    View All Notifications
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown ms-3">
                        <a href="#" class="text-white text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo $user['first_name']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>applicant/profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <div class="d-flex">
    <?php
}

function get_footer() {
    ?>
        </div>
        <!-- Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
        // Mark notification as read when clicked
        document.querySelectorAll('.notification-item.unread').forEach(item => {
            item.addEventListener('click', function() {
                const notificationId = this.dataset.id;
                fetch('<?php echo BASE_URL; ?>api/mark_notification_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        notification_id: notificationId
                    })
                }).then(response => {
                    if (response.ok) {
                        this.classList.remove('unread');
                    }
                });
            });
        });
        </script>
    </body>
    </html>
    <?php
}

function get_sidebar($type = 'applicant') {
    if ($type === 'applicant') {
        require_once __DIR__ . '/../applicant/includes/sidebar.php';
    } else {
        require_once __DIR__ . '/../admin/includes/sidebar.php';
    }
}
?>
