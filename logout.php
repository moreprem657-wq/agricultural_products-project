<?php
require_once 'admin/includes/config.php';

session_destroy();
redirect('login.php');
?>