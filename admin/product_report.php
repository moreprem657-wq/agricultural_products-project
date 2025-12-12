<?php
require_once 'includes/config.php';
require_once 'includes/admin_header.php';

$page_title = "Product Reports";

// Get date range filters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Get all products with sales data
$stmt = $db->prepare("
    SELECT 
        p.product_id,
        p.name,
        p.price,
        c.name as category_name,
        COALESCE(SUM(oi.quantity), 0) as total_sold,
        COALESCE(SUM(oi.quantity * oi.price), 0) as total_revenue,
        p.quantity as current_stock
    FROM 
        products p
    LEFT JOIN 
        categories c ON p.category_id = c.category_id
    LEFT JOIN 
        order_items oi ON p.product_id = oi.product_id
    LEFT JOIN 
        orders o ON oi.order_id = o.order_id
    WHERE 
        (o.order_date BETWEEN :start_date AND :end_date OR o.order_date IS NULL)
    GROUP BY 
        p.product_id
    ORDER BY 
        total_sold DESC
");

$stmt->execute([
    ':start_date' => $start_date,
    ':end_date' => $end_date
]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$total_products = count($products);
$total_sold = array_sum(array_column($products, 'total_sold'));
$total_revenue = array_sum(array_column($products, 'total_revenue'));
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Product Sales Report</h5>
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
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="product_report.php" class="btn btn-outline-secondary ms-2">Reset</a>
                </div>
            </div>
        </form>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title">Total Products</h6>
                        <h3 class="mb-0"><?php echo $total_products; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title">Total Sold</h6>
                        <h3 class="mb-0"><?php echo $total_sold; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
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
                        <th>ID</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Sold</th>
                        <th>Revenue</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo $product['product_id']; ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                        <td>
                            <?php if ($product['current_stock'] > 0): ?>
                                <span class="badge bg-success"><?php echo $product['current_stock']; ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger">Out of stock</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $product['total_sold']; ?></td>
                        <td>$<?php echo number_format($product['total_revenue'], 2); ?></td>
                        <td>
                            <a href="products.php?delete=<?php echo $product['product_id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this product?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart Section -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Sales Overview</h5>
    </div>
    <div class="card-body">
        <canvas id="salesChart" height="300"></canvas>
    </div>
</div>

<!-- Required JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Initialize DataTable
$(document).ready(function() {
    $('.datatable').DataTable({
        responsive: true,
        order: [[5, 'desc']] // Default sort by total sold descending
    });

    // Prepare data for chart
    const productNames = <?php echo json_encode(array_column($products, 'name')); ?>;
    const productSales = <?php echo json_encode(array_column($products, 'total_sold')); ?>;
    const productRevenue = <?php echo json_encode(array_column($products, 'total_revenue')); ?>;

    // Create chart
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: productNames,
            datasets: [
                {
                    label: 'Units Sold',
                    data: productSales,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Revenue ($)',
                    data: productRevenue,
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    type: 'line',
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Units Sold'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Revenue ($)'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
});
</script>

<?php
require_once 'includes/admin_footer.php';
?>