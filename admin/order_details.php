<?php
require_once 'includes/config.php';
require_once 'includes/admin_header.php';

$page_title = "Order Details";

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Order not specified";
    redirect('orders.php');
}

$order_id = intval($_GET['id']);
$order_details = getOrderDetails($order_id);

if (!$order_details) {
    $_SESSION['error'] = "Order not found";
    redirect('orders.php');
}

// Get user info
$user = $db->query("SELECT * FROM users WHERE user_id = {$order_details['order']['user_id']}")->fetch(PDO::FETCH_ASSOC);

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
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge 
                                <?php 
                                switch($order_details['order']['status']) {
                                    case 'pending': echo 'bg-warning'; break;
                                    case 'processing': echo 'bg-info'; break;
                                    case 'shipped': echo 'bg-primary'; break;
                                    case 'delivered': echo 'bg-success'; break;
                                    default: echo 'bg-secondary';
                                }
                                ?>
                            ">
                                <?php echo ucfirst($order_details['order']['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Payment</th>
                        <td>
                            <span class="badge 
                                <?php 
                                switch($order_details['order']['payment_status']) {
                                    case 'pending': echo 'bg-warning'; break;
                                    case 'paid': echo 'bg-success'; break;
                                    case 'failed': echo 'bg-danger'; break;
                                    default: echo 'bg-secondary';
                                }
                                ?>
                            ">
                                <?php echo ucfirst($order_details['order']['payment_status']); ?>
                            </span>
                        </td>
                    </tr>
                </table>
                
                <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#statusModal">Update Status</button>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Customer Information</h5>
            </div>
            <div class="card-body">
                <h6><?php echo htmlspecialchars($user['full_name']); ?></h6>
                <p>
                    <?php echo htmlspecialchars($user['email']); ?><br>
                    <?php echo htmlspecialchars($user['phone']); ?>
                </p>
                
                <h6 class="mt-3">Shipping Address</h6>
                <p><?php echo nl2br(htmlspecialchars($order_details['order']['shipping_address'])); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Order Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="orders.php" method="post">
                <input type="hidden" name="order_id" value="<?php echo $order_details['order']['order_id']; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pending" <?php echo $order_details['order']['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $order_details['order']['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo $order_details['order']['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo $order_details['order']['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_status" class="btn btn-success">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once 'includes/admin_footer.php';
?>