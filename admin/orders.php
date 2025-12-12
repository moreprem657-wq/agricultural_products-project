<?php
require_once 'includes/config.php';
require_once 'includes/admin_header.php';

$page_title = "Orders";

// Handle status updates
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = trim($_POST['status']);
    
    $stmt = $db->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    if ($stmt->execute([$status, $order_id])) {
        $_SESSION['success'] = "Order status updated successfully";
    } else {
        $_SESSION['error'] = "Failed to update order status";
    }
    redirect('orders.php');
}

// Get all orders with user information
$orders = $db->query("
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id 
    ORDER BY o.order_date DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Order Management</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['order_id']; ?></td>
                        <td><?php echo htmlspecialchars($order['username']); ?></td>
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
                            <a href="order_details.php?id=<?php echo $order['order_id']; ?>" 
                               class="btn btn-sm btn-success" 
                               data-bs-toggle="tooltip" 
                               title="View Details">
                                <i class="bi bi-eye"></i>
                            </a>
                            <button class="btn btn-sm btn-primary" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#statusModal<?php echo $order['order_id']; ?>"
                                    data-bs-toggle="tooltip"
                                    title="Update Status">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- Status Update Modal -->
                    <div class="modal fade" id="statusModal<?php echo $order['order_id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Update Order Status</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="orders.php" method="post">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
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
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once 'includes/admin_footer.php';
?>