<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = "Please login to update your cart";
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cart_id = isset($_POST['cart_id']) ? intval($_POST['cart_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if ($cart_id <= 0) {
        $_SESSION['error'] = "Invalid cart item";
        redirect('cart.php');
    }
    
    if ($quantity <= 0) {
        $_SESSION['error'] = "Invalid quantity";
        redirect('cart.php');
    }
    
    // Verify cart item belongs to user
    $stmt = $db->prepare("SELECT c.*, p.quantity as product_quantity FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.cart_id = ? AND c.user_id = ?");
    $stmt->execute([$cart_id, $_SESSION['user_id']]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        $_SESSION['error'] = "Cart item not found";
        redirect('cart.php');
    }
    
    if ($item['product_quantity'] < $quantity) {
        $_SESSION['error'] = "Not enough stock available";
        redirect('cart.php');
    }
    
    // Update cart
    $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
    if ($stmt->execute([$quantity, $cart_id, $_SESSION['user_id']])) {
        $_SESSION['success'] = "Cart updated";
        redirect('cart.php');
    } else {
        $_SESSION['error'] = "Failed to update cart";
        redirect('cart.php');
    }
} else {
    redirect('cart.php');
}
?>