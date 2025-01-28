<?php
require_once 'classes/Auth.php';

$auth = new Auth();
$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $contact_number = $_POST['contact_number'] ?? '';
    $preferred_course = $_POST['preferred_course'] ?? '';
    
    if($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Register the user
        $result = $auth->register($email, $password);
        
        if($result['success']) {
            // Add applicant details
            $query = "INSERT INTO applicants (user_id, first_name, last_name, contact_number, preferred_course) VALUES (?, ?, ?, ?, ?)";
            $database = new Database();
            $conn = $database->getConnection();
            $stmt = $conn->prepare($query);
            
            if($stmt->execute([$result['user_id'], $first_name, $last_name, $contact_number, $preferred_course])) {
                $success = 'Registration successful! Please wait for admin approval before logging in.';
            } else {
                $error = 'Failed to save applicant details';
            }
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
    <title>Register - CCS Freshman Screening</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .register-container {
            max-width: 600px;
            margin: 50px auto;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #fff;
            border-bottom: 2px solid #f8f9fa;
            border-top-left-radius: 10px !important;
            border-top-right-radius: 10px !important;
        }
    </style>
</head>
<body>
    <div class="container register-container">
        <div class="card">
            <div class="card-header text-center">
                <h4 class="mb-0">Register for CCS Freshman Screening</h4>
            </div>
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo htmlspecialchars($success); ?>
                        <br>
                        <a href="login.php" class="alert-link">Click here to login</a>
                    </div>
                <?php endif; ?>

                <form method="post" class="needs-validation" novalidate>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input type="tel" class="form-control" id="contact_number" name="contact_number" required>
                    </div>

                    <div class="mb-3">
                        <label for="preferred_course" class="form-label">Preferred Course</label>
                        <select class="form-select" id="preferred_course" name="preferred_course" required>
                            <option value="">Select a course</option>
                            <option value="BSIT">Bachelor of Science in Information Technology (BSIT)</option>
                            <option value="BSCS">Bachelor of Science in Computer Science (BSCS)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>
