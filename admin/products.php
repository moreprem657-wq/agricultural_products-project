<?php
require_once 'includes/config.php';
require_once 'includes/admin_header.php';

$page_title = "Products";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_product'])) {
        $category_id = intval($_POST['category_id']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $quantity = intval($_POST['quantity']);
        $image = '';
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../assets/images/products/';
            $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_ext, $allowed_ext)) {
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $new_name = 'product_' . uniqid() . '.' . $file_ext;
                $target_file = $upload_dir . $new_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image = $new_name;
                } else {
                    $_SESSION['error'] = "Failed to upload image";
                }
            } else {
                $_SESSION['error'] = "Invalid file type. Only JPG, JPEG, PNG & GIF are allowed";
            }
        }
        
        if (empty($name)) {
            $_SESSION['error'] = "Product name is required";
        } elseif ($price <= 0) {
            $_SESSION['error'] = "Price must be greater than 0";
        } else {
            $stmt = $db->prepare("INSERT INTO products (category_id, name, description, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$category_id, $name, $description, $price, $quantity, $image])) {
                $_SESSION['success'] = "Product added successfully";
                redirect('products.php');
            } else {
                $_SESSION['error'] = "Failed to add product";
            }
        }
    }
    elseif (isset($_POST['update_product'])) {
        $product_id = intval($_POST['product_id']);
        $category_id = intval($_POST['category_id']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $quantity = intval($_POST['quantity']);
        
        // Get current image
        $current_image = $db->query("SELECT image FROM products WHERE product_id = $product_id")->fetchColumn();
        $image = $current_image;
        
        // Handle new image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../assets/images/products/';
            $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_ext, $allowed_ext)) {
                // Delete old image if exists
                if ($current_image && file_exists($upload_dir . $current_image)) {
                    unlink($upload_dir . $current_image);
                }
                
                $new_name = 'product_' . uniqid() . '.' . $file_ext;
                $target_file = $upload_dir . $new_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image = $new_name;
                } else {
                    $_SESSION['error'] = "Failed to upload new image";
                }
            } else {
                $_SESSION['error'] = "Invalid file type. Only JPG, JPEG, PNG & GIF are allowed";
            }
        }
        
        if (empty($name)) {
            $_SESSION['error'] = "Product name is required";
        } elseif ($price <= 0) {
            $_SESSION['error'] = "Price must be greater than 0";
        } else {
            $stmt = $db->prepare("UPDATE products SET category_id=?, name=?, description=?, price=?, quantity=?, image=? WHERE product_id=?");
            if ($stmt->execute([$category_id, $name, $description, $price, $quantity, $image, $product_id])) {
                $_SESSION['success'] = "Product updated successfully";
                redirect('products.php');
            } else {
                $_SESSION['error'] = "Failed to update product";
            }
        }
    }
}

// Handle product deletion
if (isset($_GET['delete'])) {
    $product_id = intval($_GET['delete']);
    
    // Get image path
    $image = $db->query("SELECT image FROM products WHERE product_id = $product_id")->fetchColumn();
    
    $stmt = $db->prepare("DELETE FROM products WHERE product_id = ?");
    if ($stmt->execute([$product_id])) {
        // Delete image file if exists
        if ($image && file_exists(__DIR__ . '/../../assets/images/products/' . $image)) {
            unlink(__DIR__ . '/../../assets/images/products/' . $image);
        }
        $_SESSION['success'] = "Product deleted successfully";
    } else {
        $_SESSION['error'] = "Failed to delete product";
    }
    redirect('products.php');
}

// Get all products with category names
$products = $db->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.category_id 
    ORDER BY p.name
")->fetchAll(PDO::FETCH_ASSOC);

// Get all categories for dropdown
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Products Management</h5>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="bi bi-plus-circle me-1"></i> Add Product
        </button>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo $product['product_id']; ?></td>
                        <td>
                            <?php if ($product['image']): ?>
                                <img src="/assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" width="50" class="rounded img-thumbnail">
                            <?php else: ?>
                                <span class="text-muted">No image</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                        <td>
                            <?php if ($product['quantity'] > 0): ?>
                                <span class="badge bg-success"><?php echo $product['quantity']; ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger">Out of stock</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-product" 
                                    data-id="<?php echo $product['product_id']; ?>"
                                    data-category-id="<?php echo $product['category_id']; ?>"
                                    data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                    data-description="<?php echo htmlspecialchars($product['description']); ?>"
                                    data-price="<?php echo $product['price']; ?>"
                                    data-quantity="<?php echo $product['quantity']; ?>"
                                    data-image="<?php echo htmlspecialchars($product['image']); ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
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

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="products.php" method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" min="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <small class="text-muted">Max size: 2MB (JPG, PNG, GIF)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_product" class="btn btn-success">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="products.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="product_id" id="edit_product_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_category_id" class="form-label">Category</label>
                            <select class="form-select" id="edit_category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_price" class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control" id="edit_price" name="price" min="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="edit_quantity" name="quantity" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_image" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                        <div class="mt-2" id="current_image_container">
                            <small>Current Image:</small>
                            <img id="current_image" src="" width="100" class="d-block mt-1 rounded img-thumbnail">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_product" class="btn btn-success">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Required JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Handle edit button clicks
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-product');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const categoryId = this.getAttribute('data-category-id');
            const name = this.getAttribute('data-name');
            const description = this.getAttribute('data-description');
            const price = this.getAttribute('data-price');
            const quantity = this.getAttribute('data-quantity');
            const image = this.getAttribute('data-image');
            
            document.getElementById('edit_product_id').value = productId;
            document.getElementById('edit_category_id').value = categoryId;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_price').value = price;
            document.getElementById('edit_quantity').value = quantity;
            
            // Handle image display
            const currentImageContainer = document.getElementById('current_image_container');
            const currentImage = document.getElementById('current_image');
            
            if (image) {
                currentImage.src = '/assets/images/products/' + image;
                currentImageContainer.style.display = 'block';
            } else {
                currentImageContainer.style.display = 'none';
            }
            
            // Show modal
            const editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
            editModal.show();
        });
    });
    
    // Initialize DataTable with proper check
    if ($.fn.DataTable.isDataTable('.datatable')) {
        $('.datatable').DataTable().destroy();
    }
    
    $('.datatable').DataTable({
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [1, 6] } // Disable sorting on image and actions columns
        ]
    });
});
</script>

<?php
require_once 'includes/admin_footer.php';
?>