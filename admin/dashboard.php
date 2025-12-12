<?php
require_once 'includes/config.php';
require_once 'includes/admin_header.php';

$page_title = "Dashboard";

// Get counts for dashboard
$users_count = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$products_count = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$orders_count = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$categories_count = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();

// Get recent orders
$recent_orders = $db->query("
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id 
    ORDER BY o.order_date DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Get low stock products
$low_stock = $db->query("
    SELECT * FROM products 
    WHERE quantity < 10 
    ORDER BY quantity ASC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="stat-label">Total Users</h6>
                        <h3 class="stat-value text-primary"><?php echo $users_count; ?></h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                        <i class="bi bi-people fs-4 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Other stat cards for products, orders, categories -->
    
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Orders</h5>
                <a href="orders.php" class="btn btn-sm btn-success">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <!-- Order table content -->
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Low Stock Products</h5>
                <a href="products.php" class="btn btn-sm btn-success">View All</a>
            </div>
            <div class="card-body">
                <!-- Low stock products list -->
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/admin_footer.php';
?>