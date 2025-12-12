<?php
require_once 'config.php';

// Check if user is logged in, if not redirect to login page
if (!isLoggedIn() && basename($_SERVER['PHP_SELF']) != 'login.php' && basename($_SERVER['PHP_SELF']) != 'register.php') {
    redirect('login.php');
}

// Check if admin is trying to access admin pages
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false && !isAdmin()) {
    $_SESSION['error'] = "You don't have permission to access this page";
    redirect('../index.php');
}
?>