<?php
class Auth {
    private $conn;
    private $database;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        require_once __DIR__ . '/../config/database.php';
        $this->database = new Database();
        $this->conn = $this->database->getConnection();
    }

    public function login($email, $password) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Clear any existing session data
                session_unset();
                
                // Set essential session data only
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['LAST_ACTIVITY'] = time();
                
                return [
                    'success' => true,
                    'role' => $user['role']
                ];
            }
            
            return ['success' => false, 'message' => 'Invalid credentials'];
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'System error'];
        }
    }

    public function register($email, $password, $role = 'applicant') {
        // Check if email already exists
        $query = "SELECT id FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        
        if($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        // Insert new user
        $query = "INSERT INTO users (email, password, role, status) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $status = ($role === 'applicant') ? 'pending' : 'active';
        
        if($stmt->execute([$email, $hashed_password, $role, $status])) {
            return ['success' => true, 'user_id' => $this->conn->lastInsertId()];
        }
        
        return ['success' => false, 'message' => 'Registration failed'];
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ? AND status = 'active' LIMIT 1");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $this->logout();
                return null;
            }
            
            return $user;
        } catch (Exception $e) {
            error_log("Auth error: " . $e->getMessage());
            return null;
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

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            header('Location: /php-ccs/login.php');
            exit();
        }
    }

    public function requireRole($role) {
        // Debug information
        error_log("Checking role: " . $role);
        error_log("Session data: " . print_r($_SESSION, true));
        
        // Check if user is logged in first
        if (!isset($_SESSION['user_id'])) {
            error_log("No user_id in session");
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            header('Location: /php-ccs/login.php');
            exit();
        }

        // Get user data directly from database
        try {
            $stmt = $this->conn->prepare("SELECT role, status FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || $user['status'] !== 'active' || $user['role'] !== $role) {
                error_log("User validation failed: " . print_r($user, true));
                $this->logout();
                $_SESSION['error'] = 'Access denied. Please log in with appropriate credentials.';
                header('Location: /php-ccs/login.php');
                exit();
            }

            return true;
        } catch (Exception $e) {
            error_log("Database error: " . $e->getMessage());
            $this->logout();
            header('Location: /php-ccs/login.php?error=system');
            exit();
        }
    }

    private function redirectToDashboard($role) {
        switch($role) {
            case 'super_admin':
                header('Location: /php-ccs/admin/super/dashboard.php');
                break;
            case 'admin':
                header('Location: /php-ccs/admin/dashboard.php');
                break;
            case 'applicant':
                header('Location: /php-ccs/applicant/dashboard.php');
                break;
            default:
                header('Location: /php-ccs/login.php');
        }
        exit();
    }

    public function logActivity($user_id, $action, $details) {
        $query = "INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $stmt->execute([$user_id, $action, $details, $ip]);
    }
}
?>
