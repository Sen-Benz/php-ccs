<?php
class Auth {
    private $conn;
    private $database;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        require_once __DIR__ . '/../config/database.php';
        $this->database = Database::getInstance();
        $this->conn = $this->database->getConnection();
    }

    public function login($email, $password) {
        try {
            // First check if user exists with given email
            $stmt = $this->conn->prepare("
                SELECT u.*, 
                       CASE 
                           WHEN sa.user_id IS NOT NULL THEN 'super_admin'
                           WHEN a.user_id IS NOT NULL THEN 'admin'
                           ELSE u.role 
                       END as actual_role
                FROM users u
                LEFT JOIN super_admins sa ON u.id = sa.user_id
                LEFT JOIN admins a ON u.id = a.user_id
                WHERE u.email = ? 
                LIMIT 1
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }

            if (!password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }

            if ($user['status'] !== 'active') {
                return ['success' => false, 'message' => 'Account is not active. Please wait for admin approval.'];
            }

            // Generate new session ID to prevent session fixation
            session_regenerate_id(true);
            
            // Set session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['actual_role'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['LAST_ACTIVITY'] = time();
            
            // Log successful login
            $this->logActivity($user['id'], 'login', 'User logged in successfully');
            
            return [
                'success' => true,
                'role' => $user['actual_role']
            ];
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'System error occurred. Please try again later.'];
        }
    }

    public function register($email, $password, $role = 'applicant') {
        try {
            // Check if email already exists
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if($stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Email already exists'];
            }

            // Start transaction
            $this->conn->beginTransaction();

            // Insert new user
            $stmt = $this->conn->prepare("
                INSERT INTO users (email, password, role, status) 
                VALUES (?, ?, ?, ?)
            ");
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $status = ($role === 'applicant') ? 'pending' : 'active';
            
            $stmt->execute([$email, $hashed_password, $role, $status]);
            $userId = $this->conn->lastInsertId();

            // Log the registration
            $this->logActivity($userId, 'register', 'New user registration');

            $this->conn->commit();
            return ['success' => true, 'user_id' => $userId];

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed. Please try again later.'];
        }
    }

    public function requireRole($roles) {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            header('Location: /php-ccs/login.php');
            exit();
        }

        try {
            $stmt = $this->conn->prepare("
                SELECT u.*, 
                       CASE 
                           WHEN sa.user_id IS NOT NULL THEN 'super_admin'
                           WHEN a.user_id IS NOT NULL THEN 'admin'
                           ELSE u.role 
                       END as actual_role
                FROM users u
                LEFT JOIN super_admins sa ON u.id = sa.user_id
                LEFT JOIN admins a ON u.id = a.user_id
                WHERE u.id = ? AND u.status = 'active'
                LIMIT 1
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if (!$user || !in_array($user['actual_role'], $roles)) {
                $this->logout();
                $_SESSION['error'] = 'Access denied. Please log in with appropriate credentials.';
                header('Location: /php-ccs/login.php');
                exit();
            }

            return true;
        } catch (Exception $e) {
            error_log("Role verification error: " . $e->getMessage());
            $this->logout();
            header('Location: /php-ccs/login.php?error=system');
            exit();
        }
    }

    public function logout() {
        if(isset($_SESSION['user_id'])) {
            $this->logActivity($_SESSION['user_id'], 'logout', 'User logged out');
        }
        
        $_SESSION = array();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-3600, '/');
        }
        session_destroy();
        session_start();
    }

    private function logActivity($user_id, $action, $details) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO activity_logs (user_id, action, details, ip_address) 
                VALUES (?, ?, ?, ?)
            ");
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $stmt->execute([$user_id, $action, $details, $ip]);
        } catch (Exception $e) {
            error_log("Activity logging error: " . $e->getMessage());
        }
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        try {
            $stmt = $this->conn->prepare("
                SELECT u.*, 
                       CASE 
                           WHEN sa.user_id IS NOT NULL THEN 'super_admin'
                           WHEN a.user_id IS NOT NULL THEN 'admin'
                           ELSE u.role 
                       END as actual_role
                FROM users u
                LEFT JOIN super_admins sa ON u.id = sa.user_id
                LEFT JOIN admins a ON u.id = a.user_id
                WHERE u.id = ? AND u.status = 'active'
                LIMIT 1
            ");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Get current user error: " . $e->getMessage());
            return null;
        }
    }
}
?>
