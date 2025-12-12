<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

$page_title = "Checkout";

if (!isLoggedIn()) {
    $_SESSION['error'] = "Please login to checkout";
    redirect('login.php');
}

$cart_items = getCartItems($_SESSION['user_id']);
if (empty($cart_items)) {
    $_SESSION['error'] = "Your cart is empty";
    redirect('products.php');
}

// Get user details
$user = $db->query("SELECT * FROM users WHERE user_id = {$_SESSION['user_id']}")->fetch(PDO::FETCH_ASSOC);
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2>Checkout</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Shipping Information</h5>
            </div>
            <div class="card-body">
                <form action="place_order.php" method="post">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="shipping_address" class="form-label">Shipping Address</label>
                        <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                    
                    <h5 class="mt-4">Payment Method</h5>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="credit_card" checked>
                        <label class="form-check-label" for="credit_card">
                            Credit Card
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal">
                        <label class="form-check-label" for="paypal">
                            PayPal
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-success mt-4">Place Order</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Order Summary</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Subtotal</th>
                            <td>$<?php echo number_format(getCartTotal($_SESSION['user_id']), 2); ?></td>
                        </tr>
                        <tr>
                            <th colspan="2">Shipping</th>
                            <td>Free</td>
                        </tr>
                        <tr>
                            <th colspan="2">Total</th>
                            <td><strong>$<?php echo number_format(getCartTotal($_SESSION['user_id']), 2); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>