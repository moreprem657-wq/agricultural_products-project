<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Product not specified";
    redirect('products.php');
}

$product_id = intval($_GET['id']);
$product = getProductById($product_id);

if (!$product) {
    $_SESSION['error'] = "Product not found";
    redirect('products.php');
}

$page_title = $product['name'];

// Get category name
$category = $db->query("SELECT name FROM categories WHERE category_id = {$product['category_id']}")->fetchColumn();
?>

<div class="row mb-4">
    <div class="col-md-6">
        <?php if ($product['image']): ?>
            <?php
            $image_path = '/assets/images/products/' . htmlspecialchars($product['image']);
            $default_image = '/assets/images/products/default_product.jpg';
            ?>
            <img src="<?php echo $image_path; ?>" 
                 class="img-fluid rounded" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                 onerror="this.onerror=null;this.src='<?php echo $default_image; ?>';">
        <?php else: ?>
            <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 400px;">
                <div class="text-center">
                    <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No image available</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="col-md-6">
        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
        <p class="text-muted">Category: <?php echo htmlspecialchars($category); ?></p>
        <h4 class="text-success">$<?php echo number_format($product['price'], 2); ?></h4>
        <p>
            <?php if ($product['quantity'] > 0): ?>
                <span class="badge bg-success">In Stock (<?php echo $product['quantity']; ?>)</span>
            <?php else: ?>
                <span class="badge bg-danger">Out of Stock</span>
            <?php endif; ?>
        </p>
        
        <div class="product-description mb-4">
            <h5>Description</h5>
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        </div>
        
        <?php if (isLoggedIn() && $product['quantity'] > 0): ?>
            <form action="add_to_cart.php" method="post" class="mt-4">
                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label for="quantity" class="col-form-label">Quantity</label>
                    </div>
                    <div class="col-auto">
                        <input type="number" class="form-control" id="quantity" name="quantity" 
                               value="1" min="1" max="<?php echo $product['quantity']; ?>" style="width: 80px;">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </form>
        <?php elseif (!isLoggedIn()): ?>
            <div class="alert alert-info mt-4">
                Please <a href="login.php" class="alert-link">login</a> to add products to your cart.
            </div>
        <?php else: ?>
            <div class="alert alert-warning mt-4">
                This product is currently out of stock.
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Product Details</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th>Product Name</th>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                    </tr>
                    <tr>
                        <th>Category</th>
                        <td><?php echo htmlspecialchars($category); ?></td>
                    </tr>
                    <tr>
                        <th>Price</th>
                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                    </tr>
                    <tr>
                        <th>Availability</th>
                        <td>
                            <?php if ($product['quantity'] > 0): ?>
                                <span class="badge bg-success">In Stock (<?php echo $product['quantity']; ?>)</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Out of Stock</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if ($product['image']): ?>
                    <!-- <tr>
                        <th>Image File</th>
                        <td></td>
                    </tr> -->
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>