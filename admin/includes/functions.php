<?php
// Redirect function with output buffering
function redirect($url) {
    if (!headers_sent()) {
        header("Location: $url");
        exit();
    } else {
        echo "<script>window.location.href='$url';</script>";
        exit();
    }
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Get all categories
function getCategories() {
    global $db;
    $stmt = $db->query("SELECT * FROM categories ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get products by category
function getProductsByCategory($category_id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM products WHERE category_id = ?");
    $stmt->execute([$category_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get product by ID
function getProductById($product_id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Add to cart
function addToCart($user_id, $product_id, $quantity = 1) {
    global $db;
    
    // Check if product already in cart
    $stmt = $db->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // Update quantity
        $new_quantity = $existing['quantity'] + $quantity;
        $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
        return $stmt->execute([$new_quantity, $existing['cart_id']]);
    } else {
        // Add new item
        $stmt = $db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        return $stmt->execute([$user_id, $product_id, $quantity]);
    }
}

// Get cart items
function getCartItems($user_id) {
    global $db;
    $stmt = $db->prepare("SELECT c.*, p.name, p.price, p.image FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Calculate cart total
function getCartTotal($user_id) {
    $items = getCartItems($user_id);
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

// Remove from cart
function removeFromCart($cart_id, $user_id) {
    global $db;
    $stmt = $db->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    return $stmt->execute([$cart_id, $user_id]);
}

// Place order
function placeOrder($user_id, $shipping_address) {
    global $db;
    
    try {
        $db->beginTransaction();
        
        // Get cart items
        $cart_items = getCartItems($user_id);
        if (empty($cart_items)) {
            throw new Exception("Your cart is empty");
        }
        
        // Calculate total
        $total = getCartTotal($user_id);
        
        // Create order
        $stmt = $db->prepare("INSERT INTO orders (user_id, total_amount, shipping_address) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $total, $shipping_address]);
        $order_id = $db->lastInsertId();
        
        // Add order items and update product quantities
        foreach ($cart_items as $item) {
            // Add to order items
            $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
            
            // Update product quantity
            $stmt = $db->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ?");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        // Clear cart
        $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        $db->commit();
        return $order_id;
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}

// Get user orders
function getUserOrders($user_id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get order details
function getOrderDetails($order_id) {
    global $db;
    
    // Order info
    $stmt = $db->prepare("SELECT * FROM orders WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) return false;
    
    // Order items
    $stmt = $db->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'order' => $order,
        'items' => $items
    ];
}