<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

$page_title = "Order Details";

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Order not specified";
    redirect('orders.php');
}

$order_id = intval($_GET['id']);
$order_details = getOrderDetails($order_id);

if (!$order_details || $order_details['order']['user_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = "Order not found";
    redirect('orders.php');
}

$page_title = "Order #" . $order_details['order']['order_id'];
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2>Order #<?php echo $order_details['order']['order_id']; ?></h2>
        <p class="text-muted">Placed on <?php echo date('F j, Y, g:i a', strtotime($order_details['order']['order_date'])); ?></p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Order Items</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_details['items'] as $item): ?>
                        <tr>
                            <td>
                                <?php if ($item['image']): ?>
                                    <img src="/assets/images/products/<?php echo htmlspecialchars($item['image']); ?>" width="50" class="me-2">
                                <?php endif; ?>
                                <?php echo htmlspecialchars($item['name']); ?>
                            </td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Order Summary</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th>Subtotal</th>
                        <td>$<?php echo number_format($order_details['order']['total_amount'], 2); ?></td>
                    </tr>
                    <tr>
                        <th>Shipping</th>
                        <td>Free</td>
                    </tr>
                    <tr>
                        <th>Total</th>
                        <td><strong>$<?php echo number_format($order_details['order']['total_amount'], 2); ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Shipping Information</h5>
            </div>
            <div class="card-body">
                <address>
                    <?php echo nl2br(htmlspecialchars($order_details['order']['shipping_address'])); ?>
                </address>
            </div>
        </div>
        
        <?php if ($order_details['order']['payment_status'] == 'pending'): ?>
            <div class="mt-3">
                <a href="payment.php?order_id=<?php echo $order_details['order']['order_id']; ?>" class="btn btn-success w-100">Complete Payment</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>