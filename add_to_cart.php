<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = "Please login to add items to cart";
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if ($product_id <= 0) {
        $_SESSION['error'] = "Invalid product";
        redirect('products.php');
    }
    
    // Check product exists and has enough quantity
    $product = getProductById($product_id);
    if (!$product) {
        $_SESSION['error'] = "Product not found";
        redirect('products.php');
    }
    
    if ($quantity <= 0) {
        $_SESSION['error'] = "Invalid quantity";
        redirect('product_detail.php?id=' . $product_id);
    }
    
    if ($product['quantity'] < $quantity) {
        $_SESSION['error'] = "Not enough stock available";
        redirect('product_detail.php?id=' . $product_id);
    }
    
    // Add to cart
    if (addToCart($_SESSION['user_id'], $product_id, $quantity)) {
        $_SESSION['success'] = "Product added to cart";
        redirect('product_detail.php?id=' . $product_id);
    } else {
        $_SESSION['error'] = "Failed to add product to cart";
        redirect('product_detail.php?id=' . $product_id);
    }
} else {
    redirect('products.php');
}
?>