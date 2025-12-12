<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = "Please login to update your cart";
    redirect('login.php');
}

if (isset($_GET['id'])) {
    $cart_id = intval($_GET['id']);
    
    if ($cart_id <= 0) {
        $_SESSION['error'] = "Invalid cart item";
        redirect('cart.php');
    }
    
    // Verify cart item belongs to user
    $stmt = $db->prepare("SELECT * FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $_SESSION['user_id']]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        $_SESSION['error'] = "Cart item not found";
        redirect('cart.php');
    }
    
    // Remove from cart
    if (removeFromCart($cart_id, $_SESSION['user_id'])) {
        $_SESSION['success'] = "Item removed from cart";
        redirect('cart.php');
    } else {
        $_SESSION['error'] = "Failed to remove item from cart";
        redirect('cart.php');
    }
} else {
    redirect('cart.php');
}
?>