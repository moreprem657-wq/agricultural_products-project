<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = "Please login to place an order";
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $shipping_address = trim($_POST['shipping_address']);
    $payment_method = trim($_POST['payment_method']);
    
    // Validate input
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    }
    
    if (empty($shipping_address)) {
        $errors[] = "Shipping address is required";
    }
    
    if (empty($payment_method)) {
        $errors[] = "Payment method is required";
    }
    
    // Check cart has items
    $cart_items = getCartItems($_SESSION['user_id']);
    if (empty($cart_items)) {
        $errors[] = "Your cart is empty";
    }
    
    if (empty($errors)) {
        // Place order
        $order_id = placeOrder($_SESSION['user_id'], $shipping_address);
        
        if ($order_id) {
            $_SESSION['success'] = "Order placed successfully!";
            redirect('payment.php?order_id=' . $order_id);
        } else {
            $_SESSION['error'] = "Failed to place order";
            redirect('checkout.php');
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
        redirect('checkout.php');
    }
} else {
    redirect('checkout.php');
}
?>