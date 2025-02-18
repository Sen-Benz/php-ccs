<?php
require_once '../../config/config.php';
require_once '../../middleware/SessionManager.php';
require_once '../../classes/Auth.php';
require_once '../../config/Database.php';
require_once '../includes/layout.php';

// Start session
SessionManager::start();

// Initialize Auth
$auth = new Auth();

// Check authentication and role
try {
    if (!$auth->requireRole('super_admin')) {
        exit();
    }
    
    $user = $auth->getCurrentUser();
    if (!$user) {
        throw new Exception('User data not found');
    }

    $error = '';
    $success = '';
    $backupPath = __DIR__ . '/../../backups/';

    // Create backup directory if it doesn't exist
    if (!file_exists($backupPath)) {
        mkdir($backupPath, 0755, true);
    }

    // Handle backup creation
    if (isset($_POST['create_backup'])) {
        try {
            $conn = Database::getInstance()->getConnection();
            
            // Generate backup filename with timestamp
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "backup_{$timestamp}.sql";
            $filepath = $backupPath . $filename;

            // Get all tables
            $tables = [];
            $result = $conn->query("SHOW TABLES");
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            $backup = "-- CCS System Backup - Generated on " . date('Y-m-d H:i:s') . "\n\n";

            // Generate backup for each table
            foreach ($tables as $table) {
                $result = $conn->query("SHOW CREATE TABLE $table");
                $row = $result->fetch(PDO::FETCH_NUM);
                
                $backup .= "\n\n" . $row[1] . ";\n\n";
                
                $result = $conn->query("SELECT * FROM $table");
                while ($row = $result->fetch(PDO::FETCH_NUM)) {
                    $values = array_map(function ($value) use ($conn) {
                        if ($value === null) return 'NULL';
                        return "'" . $conn->quote($value) . "'";
                    }, $row);
                    $backup .= "INSERT INTO $table VALUES(" . implode(',', $values) . ");\n";
                }
            }

            // Save backup file
            if (file_put_contents($filepath, $backup)) {
                $success = "Backup created successfully: $filename";
                
                // Log the backup creation
                $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $user['id'],
                    'Created Backup',
                    "Created database backup: $filename",
                    $_SERVER['REMOTE_ADDR']
                ]);
            } else {
                throw new Exception("Failed to create backup file");
            }
        } catch (Exception $e) {
            $error = "Backup failed: " . $e->getMessage();
        }
    }

    // Handle backup restore
    if (isset($_POST['restore_backup']) && isset($_POST['backup_file'])) {
        try {
            $conn = Database::getInstance()->getConnection();
            $backupFile = $_POST['backup_file'];
            $filepath = $backupPath . basename($backupFile);

            if (!file_exists($filepath)) {
                throw new Exception("Backup file not found");
            }

            // Read and execute backup file
            $sql = file_get_contents($filepath);
            $conn->exec($sql);

            $success = "Database restored successfully from: " . basename($backupFile);
            
            // Log the restore operation
            $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $user['id'],
                'Restored Backup',
                "Restored database from backup: " . basename($backupFile),
                $_SERVER['REMOTE_ADDR']
            ]);
        } catch (Exception $e) {
            $error = "Restore failed: " . $e->getMessage();
        }
    }

    // Get list of existing backups
    $backups = [];
    if (is_dir($backupPath)) {
        $files = scandir($backupPath);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $backups[] = [
                    'filename' => $file,
                    'size' => filesize($backupPath . $file),
                    'created' => date("Y-m-d H:i:s", filemtime($backupPath . $file))
                ];
            }
        }
        // Sort backups by creation date (newest first)
        usort($backups, function($a, $b) {
            return strtotime($b['created']) - strtotime($a['created']);
        });
    }

} catch (Exception $e) {
    error_log("Backup/Restore error: " . $e->getMessage());
    $_SESSION['error'] = 'An error occurred. Please try logging in again.';
    $auth->logout();
    header('Location: /php-ccs/login.php');
    exit();
}

// Start the page
admin_header('Backup & Restore');
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">Backup & Restore</h1>
        <form method="post" class="mb-0">
            <button type="submit" name="create_backup" class="btn btn-primary">
                <i class="bi bi-download"></i> Create New Backup
            </button>
        </form>
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
        <div class="card-header">
            <h5 class="card-title mb-0">Available Backups</h5>
        </div>
        <div class="card-body">
            <?php if (empty($backups)): ?>
                <p class="text-center text-muted my-3">No backups found.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Backup File</th>
                                <th>Created</th>
                                <th>Size</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($backups as $backup): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($backup['filename']); ?></td>
                                    <td><?php echo $backup['created']; ?></td>
                                    <td><?php echo number_format($backup['size'] / 1024, 2); ?> KB</td>
                                    <td>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to restore this backup? This will overwrite the current database.');">
                                            <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($backup['filename']); ?>">
                                            <button type="submit" name="restore_backup" class="btn btn-sm btn-warning">
                                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                                            </button>
                                        </form>
                                        <a href="<?php echo '/php-ccs/backups/' . urlencode($backup['filename']); ?>" 
                                           class="btn btn-sm btn-info" 
                                           download>
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
admin_footer();
?>