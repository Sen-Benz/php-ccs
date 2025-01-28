<?php
require_once '../../classes/Auth.php';
require_once '../includes/layout.php';

$auth = new Auth();
$auth->requireRole('super_admin');

$user = $auth->getCurrentUser();

// Start the page
admin_header('Super Admin Dashboard');
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Super Admin Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-primary me-2">
            <i class="bi bi-download"></i> Export Report
        </button>
        <button type="button" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg"></i> Create Admin Account
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Applicants</h5>
                <h2 class="card-text">150</h2>
                <small>+12% from last week</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Passed Exams</h5>
                <h2 class="card-text">89</h2>
                <small>59.3% success rate</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-warning text-dark">
            <div class="card-body">
                <h5 class="card-title">Pending Interviews</h5>
                <h2 class="card-text">45</h2>
                <small>Next interview in 2 days</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Active Admins</h5>
                <h2 class="card-text">8</h2>
                <small>2 new this month</small>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Recent Activity</h5>
    </div>
    <div class="card-body">
        <div class="list-group">
            <a href="#" class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">New Applicant Registration</h6>
                    <small>3 minutes ago</small>
                </div>
                <p class="mb-1">John Doe completed the registration process.</p>
            </a>
            <a href="#" class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">Exam Results Updated</h6>
                    <small>1 hour ago</small>
                </div>
                <p class="mb-1">25 new exam results have been processed.</p>
            </a>
            <a href="#" class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">Interview Schedule Modified</h6>
                    <small>2 hours ago</small>
                </div>
                <p class="mb-1">Interview schedule for next week has been updated.</p>
            </a>
        </div>
    </div>
</div>

<?php
admin_footer();
?>
