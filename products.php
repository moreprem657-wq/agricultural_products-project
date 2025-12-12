<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

$page_title = "Products";

// Get all categories first to ensure they exist
try {
    $categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching categories: " . $e->getMessage());
}

// Get category filter if set
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Get products
if ($category_id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM products WHERE category_id = ? ORDER BY name");
        $stmt->execute([$category_id]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $db->prepare("SELECT name FROM categories WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $category_name = $stmt->fetchColumn();
        
        if ($category_name) {
            $page_title = "Products - " . htmlspecialchars($category_name);
        }
    } catch (PDOException $e) {
        die("Error fetching products: " . $e->getMessage());
    }
} else {
    try {
        $products = $db->query("SELECT * FROM products ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error fetching products: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - AgriChem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --agri-primary: #2e7d32;
            --agri-secondary: #689f38;
            --agri-light: #f1f8e9;
        }
        
        .product-section {
            padding: 60px 0;
        }
        
        .product-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .product-img-container {
            height: 220px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }
        
        .product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .product-card:hover .product-img {
            transform: scale(1.05);
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .card-title {
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--agri-primary);
        }
        
        .card-text {
            color: #6c757d;
            margin-bottom: 1rem;
        }
        
        .price-tag {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--agri-primary);
        }
        
        .card-footer {
            background-color: white;
            border-top: none;
            padding: 1rem 1.5rem;
        }
        
        .filter-dropdown .dropdown-toggle {
            border: 2px solid var(--agri-primary);
            color: var(--agri-primary);
            font-weight: 500;
            padding: 8px 16px;
        }
        
        .filter-dropdown .dropdown-toggle:hover {
            background-color: var(--agri-primary);
            color: white;
        }
        
        .dropdown-item.active, .dropdown-item:active {
            background-color: var(--agri-primary);
        }
        
        .no-products {
            padding: 40px;
            text-align: center;
            border-radius: 10px;
            background-color: #f8f9fa;
        }
        
        .stock-badge {
            font-size: 0.85rem;
            padding: 0.35em 0.65em;
        }
        
        .section-title {
            position: relative;
            padding-bottom: 10px;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background: var(--agri-primary);
        }
    </style>
</head>
<body>
    <section class="product-section">
        <div class="container">
            <div class="row mb-5">
                <div class="col-md-12">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <h2 class="display-5 fw-bold mb-3 mb-md-0 section-title"><?php echo $page_title; ?></h2>
                        <?php if (!empty($categories)): ?>
                        <div class="filter-dropdown">
                            <button class="btn btn-outline-success dropdown-toggle" type="button" id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-filter me-2"></i>
                                <?php echo $category_id > 0 ? htmlspecialchars($category_name) : 'All Categories'; ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="categoryDropdown">
                                <li>
                                    <a class="dropdown-item <?php echo $category_id == 0 ? 'active' : ''; ?>" 
                                       href="products.php">
                                       <i class="fas fa-list me-2"></i> All Categories
                                    </a>
                                </li>
                                <?php foreach ($categories as $category): ?>
                                <li>
                                    <a class="dropdown-item <?php echo $category_id == $category['category_id'] ? 'active' : ''; ?>" 
                                       href="products.php?category=<?php echo $category['category_id']; ?>">
                                       <i class="fas fa-tag me-2"></i> <?php echo htmlspecialchars($category['name']); ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                    <hr class="mt-3">
                </div>
            </div>

            <div class="row g-4">
                <?php if (empty($products)): ?>
                    <div class="col-md-12">
                        <div class="alert alert-info no-products">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <h4>No products found</h4>
                            <p class="mb-0">We couldn't find any products matching your criteria.</p>
                            <a href="products.php" class="btn btn-success mt-3">View All Products</a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card product-card">
                            <div class="product-img-container">
                                <?php if (!empty($product['image'])): ?>
                                    <?php
                                    $image_path = '/assets/images/products/' . htmlspecialchars($product['image']);
                                    $default_image = '/assets/images/products/default_product.jpg';
                                    ?>
                                    <img src="<?php echo $image_path; ?>" 
                                         class="product-img" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         onerror="this.onerror=null;this.src='<?php echo $default_image; ?>';">
                                <?php else: ?>
                                    <div class="d-flex flex-column align-items-center justify-content-center p-4 text-center">
                                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                        <span class="text-muted">No image available</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="price-tag">$<?php echo number_format($product['price'], 2); ?></span>
                                    <?php if ($product['quantity'] > 0): ?>
                                        <span class="badge bg-success stock-badge">
                                            <i class="fas fa-check-circle me-1"></i> In Stock
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger stock-badge">
                                            <i class="fas fa-times-circle me-1"></i> Out of Stock
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="d-flex justify-content-between">
                                    <a href="product_detail.php?id=<?php echo $product['product_id']; ?>" 
                                       class="btn btn-outline-success">
                                        <i class="fas fa-eye me-2"></i> View
                                    </a>
                                    <?php if (isLoggedIn() && $product['quantity'] > 0): ?>
                                        <form action="add_to_cart.php" method="post" class="d-inline">
                                            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-cart-plus me-2"></i> Add to Cart
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php
    require_once 'includes/footer.php';
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>