<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

$page_title = "Shopping Cart";

if (!isLoggedIn()) {
    $_SESSION['error'] = "Please login to view your cart";
    redirect('login.php');
}

$cart_items = getCartItems($_SESSION['user_id']);
$cart_total = getCartTotal($_SESSION['user_id']);
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2>Your Shopping Cart</h2>
    </div>
</div>

<?php if (empty($cart_items)): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">Your cart is empty. <a href="products.php">Browse products</a></div>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-md-8">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td>
                            <img src="assets/images/products/<?php echo htmlspecialchars($item['image'] ?? 'default.jpg'); ?>" width="50" class="me-2">
                            <?php echo htmlspecialchars($item['name']); ?>
                        </td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td>
                            <form action="update_cart.php" method="post" class="d-inline">
                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" style="width: 60px;">
                                <button type="submit" class="btn btn-sm btn-success">Update</button>
                            </form>
                        </td>
                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        <td>
                            <a href="remove_from_cart.php?id=<?php echo $item['cart_id']; ?>" class="btn btn-sm btn-danger">Remove</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Order Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>Subtotal</th>
                            <td>$<?php echo number_format($cart_total, 2); ?></td>
                        </tr>
                        <tr>
                            <th>Shipping</th>
                            <td>Free</td>
                        </tr>
                        <tr>
                            <th>Total</th>
                            <td><strong>$<?php echo number_format($cart_total, 2); ?></strong></td>
                        </tr>
                    </table>
                    <a href="checkout.php" class="btn btn-success w-100">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
require_once 'includes/footer.php';
?>