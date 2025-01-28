<?php
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $conn;
    private $table_name = "users";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function login($email, $password) {
        $query = "SELECT id, email, password, role, status, first_name, last_name FROM " . $this->table_name . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);

        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if(password_verify($password, $row['password'])) {
                if($row['status'] !== 'active') {
                    return ['success' => false, 'message' => 'Account is not active'];
                }
                
                session_start();
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['first_name'] = $row['first_name'];
                $_SESSION['last_name'] = $row['last_name'];
                
                // Log activity
                $this->logActivity($row['id'], 'login', 'User logged in');
                
                return ['success' => true, 'role' => $row['role']];
            }
        }
        
        return ['success' => false, 'message' => 'Invalid credentials'];
    }

    public function register($email, $password, $role = 'applicant') {
        // Check if email already exists
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        
        if($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        // Insert new user
        $query = "INSERT INTO " . $this->table_name . " (email, password, role, status) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $status = ($role === 'applicant') ? 'pending' : 'active';
        
        if($stmt->execute([$email, $hashed_password, $role, $status])) {
            return ['success' => true, 'user_id' => $this->conn->lastInsertId()];
        }
        
        return ['success' => false, 'message' => 'Registration failed'];
    }

    public function logout() {
        if(session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if(isset($_SESSION['user_id'])) {
            $this->logActivity($_SESSION['user_id'], 'logout', 'User logged out');
        }
        
        session_destroy();
        return true;
    }

    public function isLoggedIn() {
        if(session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }

    public function getCurrentUser() {
        if(!$this->isLoggedIn()) {
            return null;
        }

        // If first_name and last_name are not in session, fetch them from database
        if (!isset($_SESSION['first_name']) || !isset($_SESSION['last_name'])) {
            $query = "SELECT first_name, last_name FROM " . $this->table_name . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$_SESSION['user_id']]);
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $_SESSION['first_name'] = $row['first_name'];
                $_SESSION['last_name'] = $row['last_name'];
            }
        }

        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role'],
            'first_name' => $_SESSION['first_name'] ?? '',
            'last_name' => $_SESSION['last_name'] ?? ''
        ];
    }

    public function logActivity($user_id, $action, $details) {
        $query = "INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $stmt->execute([$user_id, $action, $details, $ip]);
    }

    public function requireRole($allowed_roles) {
        if(!$this->isLoggedIn()) {
            header('Location: /php-ccs/php-ccs/login.php');
            exit();
        }

        // Convert single role to array
        if (!is_array($allowed_roles)) {
            $allowed_roles = [$allowed_roles];
        }

        $user = $this->getCurrentUser();
        if (!in_array($user['role'], $allowed_roles)) {
            // Redirect based on user's actual role
            switch($user['role']) {
                case 'super_admin':
                    header('Location: /php-ccs/php-ccs/admin/super/dashboard.php');
                    break;
                case 'admin':
                    header('Location: /php-ccs/php-ccs/admin/dashboard.php');
                    break;
                case 'applicant':
                    header('Location: /php-ccs/php-ccs/applicant/dashboard.php');
                    break;
                default:
                    header('Location: /php-ccs/php-ccs/login.php');
            }
            exit();
        }
    }
}
?>
