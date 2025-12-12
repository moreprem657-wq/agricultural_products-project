<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isAdmin()) {
    $_SESSION['error'] = "You don't have permission to access this page";
    redirect('../../index.php');
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Panel'; ?> | AgriChem Admin</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="../../assets/css/admin.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <style>
        :root {
            --sidebar-width: 250px;
            --topbar-height: 60px;
            --primary-color: #28a745;
            --dark-color: #343a40;
        }
        
        body {
            overflow-x: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Sidebar Styles */
        #sidebar-wrapper {
            min-height: 100vh;
            margin-left: calc(-1 * var(--sidebar-width));
            width: var(--sidebar-width);
            background: var(--dark-color);
            color: white;
            transition: all 0.3s ease;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
        }
        
        #sidebar-wrapper .sidebar-heading {
            padding: 1.25rem 1.5rem;
            font-size: 1.2rem;
            background: var(--primary-color);
            font-weight: 600;
        }
        
        #sidebar-wrapper .list-group {
            width: 100%;
        }
        
        #sidebar-wrapper .list-group-item {
            border: none;
            border-radius: 0;
            padding: 0.75rem 1.5rem;
            color: #adb5bd;
            background: transparent;
            transition: all 0.2s;
        }
        
        #sidebar-wrapper .list-group-item:hover,
        #sidebar-wrapper .list-group-item.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
        
        #sidebar-wrapper .list-group-item i {
            width: 20px;
            margin-right: 10px;
            text-align: center;
        }
        
        /* Page Content Styles */
        #page-content-wrapper {
            min-width: 100vw;
            margin-left: 0;
            transition: all 0.3s ease;
            padding-top: var(--topbar-height);
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
        /* Navbar Styles */
        .navbar-top {
            height: var(--topbar-height);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 900;
            background: white;
        }
        
        /* Main Content Styles */
        .main-content {
            padding: 20px;
            margin-left: 0;
        }
        
        /* Active State */
        #wrapper.toggled #sidebar-wrapper {
            margin-left: 0;
        }
        
        #wrapper.toggled #page-content-wrapper {
            margin-left: var(--sidebar-width);
        }
        
        /* Responsive Adjustments */
        @media (min-width: 768px) {
            #sidebar-wrapper {
                margin-left: 0;
            }
            
            #page-content-wrapper {
                min-width: 0;
                width: calc(100% - var(--sidebar-width));
                margin-left: var(--sidebar-width);
            }
            
            #wrapper.toggled #sidebar-wrapper {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            
            #wrapper.toggled #page-content-wrapper {
                margin-left: 0;
            }
        }
        
        /* Breadcrumb Styles */
        .breadcrumb-nav {
            background: transparent;
            padding: 0;
            margin-bottom: 1.5rem;
        }
        
        .page-title {
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        
        /* Card Styles */
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
            padding: 1rem 1.25rem;
        }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-dark border-right" id="sidebar-wrapper">
            <div class="sidebar-heading text-white">
                <i class="bi bi-shop me-2"></i> AgriChem Admin
            </div>
            <div class="list-group list-group-flush">
                <a href="dashboard.php" class="list-group-item list-group-item-action <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a href="products.php" class="list-group-item list-group-item-action <?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
                    <i class="bi bi-box-seam me-2"></i> Products
                </a>
                <a href="categories.php" class="list-group-item list-group-item-action <?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">
                    <i class="bi bi-tags me-2"></i> Categories
                </a>
                <a href="orders.php" class="list-group-item list-group-item-action <?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">
                    <i class="bi bi-cart-check me-2"></i> Orders
                </a>
                <a href="users.php" class="list-group-item list-group-item-action <?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
                    <i class="bi bi-people me-2"></i> Users
                </a>
                <!-- Reports Parent Link -->
                <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>" 
                   data-bs-toggle="collapse" href="#reportsSubmenu" role="button" aria-expanded="false" aria-controls="reportsSubmenu">
                    <span><i class="bi bi-graph-up me-2"></i> Reports</span>
                    <i class="bi bi-chevron-down"></i>
                </a>

                <!-- Dropdown Submenu -->
                <div class="collapse <?php echo in_array($current_page, ['user_report.php', 'product_report.php', 'order_report.php', 'contact_report.php']) ? 'show' : ''; ?>" id="reportsSubmenu">
                    <a href="user_report.php" class="list-group-item list-group-item-action ps-5 <?php echo $current_page == 'user_report.php' ? 'active' : ''; ?>">User Report</a>
                    <a href="product_report.php" class="list-group-item list-group-item-action ps-5 <?php echo $current_page == 'product_report.php' ? 'active' : ''; ?>">Product Report</a>
                    <a href="order_report.php" class="list-group-item list-group-item-action ps-5 <?php echo $current_page == 'order_report.php' ? 'active' : ''; ?>">Order Report</a>
                    <a href="contact_report.php" class="list-group-item list-group-item-action ps-5 <?php echo $current_page == 'contact_report.php' ? 'active' : ''; ?>">Contact Report</a>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-white navbar-top">
                <div class="container-fluid">
                    <button class="btn btn-sm btn-outline-success me-2" id="menu-toggle">
                        <i class="bi bi-list"></i>
                    </button>
                    
                    <div class="d-flex align-items-center ms-auto">
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-2" style="font-size: 1.5rem;"></i>
                                <span class="d-none d-sm-inline"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="container-fluid px-4 py-3 main-content">
                <nav aria-label="breadcrumb" class="mb-4 breadcrumb-nav">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Admin</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo $page_title ?? 'Dashboard'; ?></li>
                    </ol>
                    <h3 class="page-title"><?php echo $page_title ?? 'Dashboard'; ?></h3>
                </nav>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>