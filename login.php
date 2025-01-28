<?php
require_once 'classes/Auth.php';
require_once 'config/database.php';

$auth = new Auth();
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $selected_role = $_POST['role'] ?? '';
    
    // Verify that the user exists with the selected role
    $database = new Database();
    $conn = $database->getConnection();
    
    $query = "SELECT id FROM users WHERE email = ? AND role = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$email, $selected_role]);
    
    if($stmt->rowCount() === 0) {
        $error = 'Invalid credentials for selected role';
    } else {
        $result = $auth->login($email, $password);
        
        if($result['success']) {
            // Redirect based on role
            switch($result['role']) {
                case 'super_admin':
                    header('Location: admin/super/dashboard.php');
                    break;
                case 'admin':
                    header('Location: admin/dashboard.php');
                    break;
                case 'applicant':
                    header('Location: applicant/dashboard.php');
                    break;
            }
            exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CCS Freshman Screening</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: #0d6efd;
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
            text-align: center;
        }
        .btn-primary {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
        }
        .form-control {
            padding: 12px;
            border-radius: 8px;
        }
        .school-logo {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
        }
        .role-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            margin-bottom: 20px;
        }
        .role-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .role-card.selected {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
        .role-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #0d6efd;
        }
        .login-form {
            display: none;
        }
        .login-form.active {
            display: block;
        }
        #roleSelection {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container login-container">
        <div class="card">
            <div class="card-header">
                <!-- Add your school logo here -->
                <img src="assets/images/logo.png" alt="School Logo" class="school-logo">
                <h4 class="mb-0">CCS Freshman Screening</h4>
                <p class="mb-0">Login to your Account</p>
            </div>
            <div class="card-body p-4">
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <!-- Role Selection Cards -->
                <div id="roleSelection">
                    <h5 class="text-center mb-4">Select Your Role</h5>
                    <div class="row justify-content-center">
                        <div class="col-md-4">
                            <div class="card role-card text-center p-4" data-role="applicant">
                                <div class="role-icon">
                                    <i class="bi bi-person-circle"></i>
                                </div>
                                <h5>Applicant</h5>
                                <p class="text-muted small">Login as a CCS applicant</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card role-card text-center p-4" data-role="admin">
                                <div class="role-icon">
                                    <i class="bi bi-person-badge"></i>
                                </div>
                                <h5>Admin</h5>
                                <p class="text-muted small">Login as an administrator</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card role-card text-center p-4" data-role="super_admin">
                                <div class="role-icon">
                                    <i class="bi bi-person-badge-fill"></i>
                                </div>
                                <h5>Super Admin</h5>
                                <p class="text-muted small">Login as a super administrator</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Login Form (Initially Hidden) -->
                <form method="POST" action="" class="login-form needs-validation" novalidate>
                    <input type="hidden" name="role" id="selectedRole">
                    
                    <div class="mb-4">
                        <label for="email" class="form-label">Email address</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login
                    </button>
                    
                    <button type="button" class="btn btn-link d-block w-100 mt-2" id="backToRoles">
                        <i class="bi bi-arrow-left me-2"></i>Back to Role Selection
                    </button>
                </form>
                
                <div class="text-center mt-4">
                    <p class="mb-0">Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-3 text-muted">
            <small>&copy; <?php echo date('Y'); ?> Eulogio "Amang" Rodriguez Institute of Science and Technology</small>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleCards = document.querySelectorAll('.role-card');
        const loginForm = document.querySelector('.login-form');
        const roleSelection = document.getElementById('roleSelection');
        const selectedRoleInput = document.getElementById('selectedRole');
        const backToRoles = document.getElementById('backToRoles');
        
        roleCards.forEach(card => {
            card.addEventListener('click', function() {
                const role = this.dataset.role;
                selectedRoleInput.value = role;
                
                // Hide role selection and show login form
                roleSelection.style.display = 'none';
                loginForm.classList.add('active');
                
                // Update form title based on selected role
                const roleTitle = this.querySelector('h5').textContent;
                document.querySelector('.card-header p').textContent = `Login as ${roleTitle}`;
            });
        });
        
        backToRoles.addEventListener('click', function() {
            // Show role selection and hide login form
            roleSelection.style.display = 'block';
            loginForm.classList.remove('active');
            document.querySelector('.card-header p').textContent = 'Login to your Account';
        });
        
        // Form validation
        const forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    });
    </script>
</body>
</html>
