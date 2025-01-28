<?php
function admin_header($title = '') {
    global $auth;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ? $title . ' - ' : ''; ?>CCS Freshman Screening</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-size: .875rem;
        }

        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #e9ecef;
            overflow-y: auto;
            max-height: 100vh;
        }

        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }

        .sidebar .nav-link {
            font-weight: 500;
            color: #212529;
            padding: 0.5rem 1rem;
        }

        .sidebar .nav-link:hover {
            color: #0d6efd;
            background-color: rgba(255, 255, 255, 0.5);
        }

        .sidebar .nav-link.active {
            color: #0d6efd;
            background-color: rgba(255, 255, 255, 0.7);
        }

        .sidebar .nav-link .bi {
            margin-right: 4px;
            color: #212529;
        }

        .sidebar .nav-link:hover .bi {
            color: #0d6efd;
        }

        .sidebar .nav-link.active .bi {
            color: #0d6efd;
        }

        .sidebar-heading {
            font-size: .75rem;
            text-transform: uppercase;
            padding: 1rem;
            color: #495057;
        }

        .navbar-brand {
            padding-top: .75rem;
            padding-bottom: .75rem;
            font-size: 1rem;
            background-color: rgba(0, 0, 0, .25);
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25);
        }

        .navbar .navbar-toggler {
            top: .25rem;
            right: 1rem;
        }

        .dropdown-menu {
            max-height: 200px;
            overflow-y: auto;
        }

        .nav-item .collapse, 
        .nav-item .collapsing {
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 0.25rem;
            margin: 0 1rem;
        }

        .nav-item .collapse .nav-link, 
        .nav-item .collapsing .nav-link {
            padding-left: 2rem;
            font-size: 0.85rem;
        }

        main {
            margin-left: 240px; /* Sidebar width */
            padding: 20px;
        }

        @media (max-width: 767.98px) {
            .sidebar {
                top: 5rem;
            }
            main {
                margin-left: 0;
            }
        }
        
        .content-wrapper {
            margin-left: 16.66667%;
            width: 83.33333%;
            padding: 20px;
        }
        .stat-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        @media (max-width: 767.98px) {
            .content-wrapper {
                margin-left: 0;
                width: 100%;
            }
        }

        :root {
            --ccs-gray: #4a4a4a;
            --ccs-gray-dark: #333333;
            --ccs-gray-light: #666666;
            --ccs-gray-lighter: #808080;
            --ccs-hover: #5a5a5a;
        }

        /* Custom Scrollbar Styling */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
            transition: background-color 0.3s ease;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }

        /* Firefox scrollbar */
        .sidebar {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
        }

        /* For the dropdown menus */
        .dropdown-menu::-webkit-scrollbar {
            width: 6px;
        }

        .dropdown-menu::-webkit-scrollbar-track {
            background: transparent;
        }

        .dropdown-menu::-webkit-scrollbar-thumb {
            background-color: rgba(74, 74, 74, 0.2);
            border-radius: 3px;
        }

        .dropdown-menu::-webkit-scrollbar-thumb:hover {
            background-color: rgba(74, 74, 74, 0.3);
        }

        /* Main content scrollbar */
        body::-webkit-scrollbar {
            width: 8px;
        }

        body::-webkit-scrollbar-track {
            background: transparent;
        }

        body::-webkit-scrollbar-thumb {
            background-color: rgba(74, 74, 74, 0.2);
            border-radius: 4px;
        }

        body::-webkit-scrollbar-thumb:hover {
            background-color: rgba(74, 74, 74, 0.3);
        }

        body {
            scrollbar-width: thin;
            scrollbar-color: rgba(74, 74, 74, 0.2) transparent;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            
            <!-- Main Content -->
            <div class="content-wrapper">
<?php
}

function admin_footer() {
?>
            </main>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Add active class to current nav item
    document.addEventListener('DOMContentLoaded', function() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            if (currentPath.includes(link.getAttribute('href'))) {
                link.classList.add('active');
                // If it's in a submenu, expand the parent
                const submenu = link.closest('.collapse');
                if (submenu) {
                    submenu.classList.add('show');
                }
            }
        });
    });

    // Keep sidebar scroll position when navigating
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        const scrollPos = sessionStorage.getItem('sidebarScrollPos');
        if (scrollPos) {
            sidebar.scrollTop = parseInt(scrollPos);
        }

        sidebar.addEventListener('scroll', function() {
            sessionStorage.setItem('sidebarScrollPos', sidebar.scrollTop);
        });
    }
    </script>
</body>
</html>
<?php
}
?>
