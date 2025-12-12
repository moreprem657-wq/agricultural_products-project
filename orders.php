<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

$page_title = "My Orders";

if (!isLoggedIn()) {
    $_SESSION['error'] = "Please login to view your orders";
    redirect('login.php');
}

$orders = getUserOrders($_SESSION['user_id']);
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2>My Orders</h2>
    </div>
</div>

<?php if (empty($orders)): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">You haven't placed any orders yet. <a href="products.php">Browse products</a></div>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-md-12">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo $order['order_id']; ?></td>
                        <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <span class="badge 
                                <?php 
                                switch($order['status']) {
                                    case 'pending': echo 'bg-warning'; break;
                                    case 'processing': echo 'bg-info'; break;
                                    case 'shipped': echo 'bg-primary'; break;
                                    case 'delivered': echo 'bg-success'; break;
                                    default: echo 'bg-secondary';
                                }
                                ?>
                            ">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge 
                                <?php 
                                switch($order['payment_status']) {
                                    case 'pending': echo 'bg-warning'; break;
                                    case 'paid': echo 'bg-success'; break;
                                    case 'failed': echo 'bg-danger'; break;
                                    default: echo 'bg-secondary';
                                }
                                ?>
                            ">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-success">View</a>
                            <?php if ($order['payment_status'] == 'pending'): ?>
                                <a href="payment.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-primary">Pay Now</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php
require_once 'includes/footer.php';
?>