<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

$page_title = "Payment Processing";

if (!isset($_GET['order_id'])) {
    $_SESSION['error'] = "Order not specified";
    redirect('orders.php');
}

$order_id = intval($_GET['order_id']);
$order_details = getOrderDetails($order_id);

if (!$order_details || $order_details['order']['user_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = "Order not found";
    redirect('orders.php');
}

// Simulate payment processing
sleep(2);

// Update payment status
$stmt = $db->prepare("UPDATE orders SET payment_status = 'paid', status = 'processing' WHERE order_id = ?");
$stmt->execute([$order_id]);

$_SESSION['success'] = "Payment processed successfully! Your order is now being processed.";
redirect('order_details.php?id=' . $order_id);
?>