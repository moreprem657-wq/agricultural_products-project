<?php
require_once 'includes/config.php';
require_once 'includes/admin_header.php';

$page_title = "Categories";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_category'])) {
        // Add new category logic
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        if (!empty($name)) {
            try {
                $stmt = $db->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                $stmt->execute([$name, $description]);
                $_SESSION['success'] = "Category added successfully!";
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error adding category: " . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = "Category name is required!";
        }
        redirect('categories.php');
        
    } elseif (isset($_POST['update_category'])) {
        // Update category logic
        $category_id = $_POST['category_id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        if (!empty($name)) {
            try {
                $stmt = $db->prepare("UPDATE categories SET name = ?, description = ? WHERE category_id = ?");
                $stmt->execute([$name, $description, $category_id]);
                $_SESSION['success'] = "Category updated successfully!";
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error updating category: " . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = "Category name is required!";
        }
        redirect('categories.php');
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    $category_id = $_GET['delete'];
    
    try {
        // First check if category is used in any products
        $check = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $check->execute([$category_id]);
        $count = $check->fetchColumn();
        
        if ($count > 0) {
            $_SESSION['error'] = "Cannot delete category - it is being used by products!";
        } else {
            $stmt = $db->prepare("DELETE FROM categories WHERE category_id = ?");
            $stmt->execute([$category_id]);
            $_SESSION['success'] = "Category deleted successfully!";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting category: " . $e->getMessage();
    }
    redirect('categories.php');
}

// Get all categories
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Categories Management</h5>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-plus-circle me-1"></i> Add Category
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
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo $category['category_id']; ?></td>
                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                        <td><?php echo htmlspecialchars($category['description']); ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-category" 
                                    data-id="<?php echo $category['category_id']; ?>"
                                    data-name="<?php echo htmlspecialchars($category['name']); ?>"
                                    data-description="<?php echo htmlspecialchars($category['description']); ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="categories.php?delete=<?php echo $category['category_id']; ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure you want to delete this category?')">
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

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="categories.php" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_category" class="btn btn-success">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="categories.php" method="post">
                <input type="hidden" name="category_id" id="edit_category_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_category" class="btn btn-success">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle edit button clicks
document.querySelectorAll('.edit-category').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const name = this.getAttribute('data-name');
        const description = this.getAttribute('data-description');
        
        document.getElementById('edit_category_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_description').value = description;
        
        var editModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
        editModal.show();
    });
});
</script>

<?php
require_once 'includes/admin_footer.php';
?>