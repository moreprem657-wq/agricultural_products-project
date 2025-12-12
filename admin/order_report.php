<?php
require_once 'includes/config.php';
require_once 'includes/admin_header.php';

$page_title = "Order Reports";

// Get date range filters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Get all orders with user information
$sql = "
    SELECT 
        o.order_id,
        o.user_id,
        u.username,
        u.email,
        o.total_amount,
        o.order_date,
        o.status,
        o.payment_status,
        o.shipping_address
    FROM 
        orders o
    LEFT JOIN 
        users u ON o.user_id = u.user_id
    WHERE 
        (o.order_date BETWEEN :start_date AND :end_date)
";

if (!empty($status_filter)) {
    $sql .= " AND o.status = :status";
}

$sql .= " ORDER BY o.order_date DESC";

$stmt = $db->prepare($sql);

$params = [
    ':start_date' => $start_date,
    ':end_date' => $end_date
];

if (!empty($status_filter)) {
    $params[':status'] = $status_filter;
}

$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$total_orders = count($orders);
$total_revenue = array_sum(array_column($orders, 'total_amount'));
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Order Reports</h5>
    </div>
    <div class="card-body">
        <form method="get" class="mb-4">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="status" class="form-label">Order Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo ($status_filter == 'processing') ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?php echo ($status_filter == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo ($status_filter == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo ($status_filter == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="order_report.php" class="btn btn-outline-secondary ms-2">Reset</a>
                </div>
            </div>
        </form>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title">Total Orders</h6>
                        <h3 class="mb-0"><?php echo $total_orders; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title">Total Revenue</h6>
                        <h3 class="mb-0">$<?php echo number_format($total_revenue, 2); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Order ID</th>
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
                        <td>
                            <div><?php echo htmlspecialchars($order['username']); ?></div>
                            <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <span class="badge 
                                <?php 
                                switch($order['status']) {
                                    case 'pending': echo 'bg-warning'; break;
                                    case 'processing': echo 'bg-info'; break;
                                    case 'shipped': echo 'bg-primary'; break;
                                    case 'delivered': echo 'bg-success'; break;
                                    case 'cancelled': echo 'bg-danger'; break;
                                    default: echo 'bg-secondary';
                                }
                                ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge 
                                <?php 
                                echo ($order['payment_status'] == 'paid') ? 'bg-success' : 'bg-danger'; 
                                ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary view-order" 
                                    data-id="<?php echo $order['order_id']; ?>"
                                    data-user="<?php echo htmlspecialchars($order['username']); ?>"
                                    data-date="<?php echo date('M d, Y', strtotime($order['order_date'])); ?>"
                                    data-amount="<?php echo number_format($order['total_amount'], 2); ?>"
                                    data-status="<?php echo $order['status']; ?>"
                                    data-payment="<?php echo $order['payment_status']; ?>"
                                    data-address="<?php echo htmlspecialchars($order['shipping_address']); ?>">
                                <i class="bi bi-eye"></i> View
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details - #<span id="modalOrderId"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6>Customer Information</h6>
                        <p class="mb-1"><strong>Name:</strong> <span id="modalCustomerName"></span></p>
                        <p class="mb-1"><strong>Order Date:</strong> <span id="modalOrderDate"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Order Summary</h6>
                        <p class="mb-1"><strong>Status:</strong> <span id="modalOrderStatus" class="badge"></span></p>
                        <p class="mb-1"><strong>Payment:</strong> <span id="modalPaymentStatus" class="badge"></span></p>
                        <p class="mb-1"><strong>Total Amount:</strong> $<span id="modalOrderAmount"></span></p>
                    </div>
                </div>
                <div class="mb-3">
                    <h6>Shipping Address</h6>
                    <p id="modalShippingAddress" class="text-muted"></p>
                </div>
                <div class="mb-3">
                    <h6>Order Items</h6>
                    <div class="table-responsive">
                        <table class="table table-sm" id="orderItemsTable">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Order items will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="printOrderBtn">
                    <i class="bi bi-printer"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Required JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('.datatable').DataTable({
        responsive: true,
        order: [[2, 'desc']] // Default sort by order date descending
    });

    // Handle view order button clicks
    $('.view-order').click(function() {
        const orderId = $(this).data('id');
        const customerName = $(this).data('user');
        const orderDate = $(this).data('date');
        const orderAmount = $(this).data('amount');
        const orderStatus = $(this).data('status');
        const paymentStatus = $(this).data('payment');
        const shippingAddress = $(this).data('address');

        // Set modal content
        $('#modalOrderId').text(orderId);
        $('#modalCustomerName').text(customerName);
        $('#modalOrderDate').text(orderDate);
        $('#modalOrderAmount').text(orderAmount);
        $('#modalShippingAddress').text(shippingAddress);

        // Set status badges
        $('#modalOrderStatus').text(orderStatus.charAt(0).toUpperCase() + orderStatus.slice(1));
        $('#modalPaymentStatus').text(paymentStatus.charAt(0).toUpperCase() + paymentStatus.slice(1));
        
        // Set badge colors
        $('#modalOrderStatus').removeClass().addClass('badge').addClass(
            orderStatus === 'pending' ? 'bg-warning' :
            orderStatus === 'processing' ? 'bg-info' :
            orderStatus === 'shipped' ? 'bg-primary' :
            orderStatus === 'delivered' ? 'bg-success' :
            orderStatus === 'cancelled' ? 'bg-danger' : 'bg-secondary'
        );
        
        $('#modalPaymentStatus').removeClass().addClass('badge').addClass(
            paymentStatus === 'paid' ? 'bg-success' : 'bg-danger'
        );

        // Load order items via AJAX
        $.get('ajax/get_order_items.php', { order_id: orderId }, function(data) {
            $('#orderItemsTable tbody').html(data);
        });

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
        modal.show();
    });

    // Print order
    $('#printOrderBtn').click(function() {
        window.print();
    });
});
</script>

<?php
require_once 'includes/admin_footer.php';
?>