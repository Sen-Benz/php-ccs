<?php
require_once '../config/config.php';
require_once '../classes/Auth.php';
require_once '../includes/layout.php';
require_once '../includes/utilities.php';
require_once '../config/Database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$db = Database::getInstance();
$error_message = '';
$success_message = '';

try {
    // Mark all as read if requested
    if (isset($_POST['mark_all_read'])) {
        $stmt = $db->getConnection()->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $success_message = "All notifications marked as read.";
    }

    // Mark single notification as read
    if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
        $stmt = $db->getConnection()->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$_POST['notification_id'], $user_id]);
    }

    // Delete notification if requested
    if (isset($_POST['delete']) && isset($_POST['notification_id'])) {
        $stmt = $db->getConnection()->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
        $stmt->execute([$_POST['notification_id'], $user_id]);
        $success_message = "Notification deleted successfully.";
    }

    // Get all notifications for the user
    $stmt = $db->getConnection()->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll();

} catch (Exception $e) {
    error_log("Notifications Error: " . $e->getMessage());
    $error_message = "An error occurred. Please try again later.";
    $notifications = [];
}

get_header('Notifications');
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php get_sidebar('applicant'); ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Notifications</h1>
                <?php if (!empty($notifications)): ?>
                    <form method="POST" class="mb-0">
                        <button type="submit" name="mark_all_read" class="btn btn-secondary">
                            Mark All as Read
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo safe_string($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo safe_string($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (empty($notifications)): ?>
                <div class="alert alert-info">
                    No notifications to display.
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-12">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="card mb-3 <?php echo $notification['is_read'] ? 'bg-light' : 'border-primary'; ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-1">
                                            <?php if (!$notification['is_read']): ?>
                                                <span class="badge bg-primary me-2">New</span>
                                            <?php endif; ?>
                                            <?php echo safe_string($notification['title']); ?>
                                        </h5>
                                        <small class="text-muted">
                                            <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?>
                                        </small>
                                    </div>
                                    <p class="card-text mt-2 mb-2">
                                        <?php echo safe_string($notification['message']); ?>
                                    </p>
                                    <div class="d-flex justify-content-end">
                                        <?php if (!$notification['is_read']): ?>
                                            <form method="POST" class="me-2">
                                                <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                <button type="submit" name="mark_read" class="btn btn-sm btn-outline-primary">
                                                    Mark as Read
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this notification?');">
                                            <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                            <button type="submit" name="delete" class="btn btn-sm btn-outline-danger">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<style>
.card {
    transition: all 0.3s ease;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
.badge {
    font-size: 0.8em;
}
</style>

<?php get_footer(); ?>
