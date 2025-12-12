<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agricultural Products - <?php echo $page_title ?? 'Home'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --agri-primary: #2e7d32;
            --agri-secondary: #689f38;
            --agri-light: #f1f8e9;
        }
        
        .navbar {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 0.8rem 0;
            background: linear-gradient(135deg, var(--agri-primary), var(--agri-secondary)) !important;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .navbar-brand i {
            margin-right: 10px;
            font-size: 1.3em;
        }
        
        .nav-link {
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            margin: 0 0.2rem;
            border-radius: 4px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .nav-link i {
            margin-right: 6px;
            font-size: 0.9em;
        }
        
        .nav-link:hover, .nav-link:focus {
            background-color: rgba(255,255,255,0.15);
        }
        
        .nav-link.active {
            background-color: rgba(255,255,255,0.2);
        }
        
        .navbar-toggler {
            border: none;
            padding: 0.5rem;
        }
        
        .navbar-toggler:focus {
            box-shadow: none;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 8px;
            margin-top: 5px;
        }
        
        .dropdown-item {
            padding: 0.5rem 1.5rem;
        }
        
        .user-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 8px;
            background-color: var(--agri-light);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .badge-notification {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.6rem;
            padding: 0.25em 0.4em;
        }
        
        /* User Dropdown Specific Styles */
        .user-dropdown {
            position: relative;
        }
        
        .user-dropdown-toggle {
            cursor: pointer;
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            color: white;
            text-decoration: none;
        }
        
        .user-dropdown-menu {
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 200px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-radius: 8px;
            z-index: 1000;
            display: none;
        }
        
        .user-dropdown-menu.show {
            display: block;
        }
        
        .user-dropdown-item {
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            color: #333;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        
        .user-dropdown-item i {
            width: 20px;
            margin-right: 10px;
            text-align: center;
        }
        
        .user-dropdown-item:hover {
            background-color: #f8f9fa;
            color: var(--agri-primary);
        }
        
        @media (max-width: 991.98px) {
            .user-dropdown-menu {
                position: static;
                box-shadow: none;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-leaf"></i>
                <span>AgriChem</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                            <i class="fas fa-home"></i>
                            <span>Home</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" href="products.php">
                            <i class="fas fa-seedling"></i>
                            <span>Products</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" href="contact.php">
                            <i class="fas fa-envelope"></i>
                            <span>Contact</span>
                        </a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : ''; ?>" href="cart.php">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Cart</span>
                                <?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                                    <span class="position-relative">
                                        <span class="badge bg-danger badge-notification"><?php echo $_SESSION['cart_count']; ?></span>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>" href="orders.php">
                                <i class="fas fa-clipboard-list"></i>
                                <span>My Orders</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-cog"></i>
                                <span>Admin</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="admin/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                <li><a class="dropdown-item" href="admin/products.php"><i class="fas fa-boxes me-2"></i>Manage Products</a></li>
                                <li><a class="dropdown-item" href="admin/orders.php"><i class="fas fa-clipboard-check me-2"></i>Manage Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="admin/users.php"><i class="fas fa-users me-2"></i>Manage Users</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <div class="user-dropdown">
                                <div class="user-dropdown-toggle nav-link" onclick="toggleUserDropdown()">
                                    <span class="user-avatar">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                                    <i class="fas fa-caret-down ms-2"></i>
                                </div>
                                <div class="user-dropdown-menu" id="userDropdown">
                                    <a href="profile.php" class="user-dropdown-item">
                                        <i class="fas fa-user-circle"></i> Profile
                                    </a>
                                    <a href="logout.php" class="user-dropdown-item">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </div>
                            </div>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>" href="login.php">
                                <i class="fas fa-sign-in-alt"></i>
                                <span>Login</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : ''; ?>" href="register.php">
                                <i class="fas fa-user-plus"></i>
                                <span>Register</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
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

    <script>
        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }
        
        // Close the dropdown if clicked outside
        window.onclick = function(event) {
            if (!event.target.matches('.user-dropdown-toggle') && !event.target.closest('.user-dropdown-toggle')) {
                const dropdowns = document.getElementsByClassName("user-dropdown-menu");
                for (let i = 0; i < dropdowns.length; i++) {
                    const openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
        
        // Close dropdown when clicking on a dropdown item
        document.querySelectorAll('.user-dropdown-item').forEach(item => {
            item.addEventListener('click', () => {
                document.getElementById('userDropdown').classList.remove('show');
            });
        });
    </script>