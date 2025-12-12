<?php
require_once 'includes/config.php';
require_once 'includes/admin_header.php';
$page_title = "Users";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_user'])) {
        // Update user
        $user_id = intval($_POST['user_id']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $full_name = trim($_POST['full_name']);
        $address = trim($_POST['address']);
        $phone = trim($_POST['phone']);
        $role = trim($_POST['role']);
        
        // Validate input
        $errors = [];
        
        if (empty($username)) {
            $errors[] = "Username is required";
        }
        
        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
        
        if (empty($full_name)) {
            $errors[] = "Full name is required";
        }
        
        if (empty($address)) {
            $errors[] = "Address is required";
        }
        
        if (empty($phone)) {
            $errors[] = "Phone number is required";
        }
        
        // Check if username or email already exists (excluding current user)
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
        $stmt->execute([$username, $email, $user_id]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Username or email already exists";
        }
        
        if (empty($errors)) {
            $stmt = $db->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, address = ?, phone = ?, role = ? WHERE user_id = ?");
            if ($stmt->execute([$username, $email, $full_name, $address, $phone, $role, $user_id])) {
                $_SESSION['success'] = "User updated successfully";
                redirect('users.php');
            } else {
                $_SESSION['error'] = "Failed to update user";
            }
        } else {
            $_SESSION['error'] = implode("<br>", $errors);
        }
    } elseif (isset($_GET['delete'])) {
        // Delete user (can't delete self)
        $user_id = intval($_GET['delete']);
        
        if ($user_id == $_SESSION['user_id']) {
            $_SESSION['error'] = "You cannot delete your own account";
        } else {
            $stmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
            if ($stmt->execute([$user_id])) {
                $_SESSION['success'] = "User deleted successfully";
            } else {
                $_SESSION['error'] = "Failed to delete user";
            }
        }
        redirect('users.php');
    }
}

// Get all users
$users = $db->query("SELECT * FROM users ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2>Users</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Full Name</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['user_id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                    <td>
                        <span class="badge <?php echo $user['role'] == 'admin' ? 'bg-success' : 'bg-primary'; ?>">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary edit-user" 
                                data-id="<?php echo $user['user_id']; ?>"
                                data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                data-full-name="<?php echo htmlspecialchars($user['full_name']); ?>"
                                data-address="<?php echo htmlspecialchars($user['address']); ?>"
                                data-phone="<?php echo htmlspecialchars($user['phone']); ?>"
                                data-role="<?php echo $user['role']; ?>">
                            Edit
                        </button>
                        <a href="users.php?delete=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="users.php" method="post">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_address" class="form-label">Address</label>
                        <textarea class="form-control" id="edit_address" name="address" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="edit_phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Role</label>
                        <select class="form-select" id="edit_role" name="role" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_user" class="btn btn-success">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle edit button clicks
document.querySelectorAll('.edit-user').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const username = this.getAttribute('data-username');
        const email = this.getAttribute('data-email');
        const fullName = this.getAttribute('data-full-name');
        const address = this.getAttribute('data-address');
        const phone = this.getAttribute('data-phone');
        const role = this.getAttribute('data-role');
        
        document.getElementById('edit_user_id').value = id;
        document.getElementById('edit_username').value = username;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_full_name').value = fullName;
        document.getElementById('edit_address').value = address;
        document.getElementById('edit_phone').value = phone;
        document.getElementById('edit_role').value = role;
        
        var editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
        editModal.show();
    });
});
</script>

<?php
require_once '../includes/footer.php';
?>