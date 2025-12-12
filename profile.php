<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

$page_title = "My Profile";

if (!isLoggedIn()) {
    $_SESSION['error'] = "Please login to view your profile";
    redirect('login.php');
}

// Get user details
$user = $db->query("SELECT * FROM users WHERE user_id = {$_SESSION['user_id']}")->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    
    // Validate input
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($address)) {
        $errors[] = "Address is required";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    }
    
    // Check if email is already taken by another user
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND user_id != ?");
    $stmt->execute([$email, $_SESSION['user_id']]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Email already in use by another account";
    }
    
    // Update password if provided
    $password_changed = false;
    if (!empty($_POST['new_password'])) {
        if (empty($_POST['current_password'])) {
            $errors[] = "Current password is required to change password";
        } elseif (!password_verify($_POST['current_password'], $user['password'])) {
            $errors[] = "Current password is incorrect";
        } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
            $errors[] = "New passwords do not match";
        } elseif (strlen($_POST['new_password']) < 6) {
            $errors[] = "New password must be at least 6 characters";
        } else {
            $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $password_changed = true;
        }
    }
    
    if (empty($errors)) {
        // Update user details
        if ($password_changed) {
            $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, address = ?, phone = ?, password = ? WHERE user_id = ?");
            $success = $stmt->execute([$full_name, $email, $address, $phone, $new_password, $_SESSION['user_id']]);
        } else {
            $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, address = ?, phone = ? WHERE user_id = ?");
            $success = $stmt->execute([$full_name, $email, $address, $phone, $_SESSION['user_id']]);
        }
        
        if ($success) {
            $_SESSION['success'] = "Profile updated successfully";
            redirect('profile.php');
        } else {
            $_SESSION['error'] = "Failed to update profile";
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>My Profile</h4>
            </div>
            <div class="card-body">
                <form action="profile.php" method="post">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                    </div>
                    
                    <h5 class="mt-4">Change Password</h5>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <button type="submit" class="btn btn-success">Update Profile</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>