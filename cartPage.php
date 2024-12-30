<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Add base URL definition
$base_url = "http://localhost/greenclean/";

// Check login status and show login popup instead of redirect
$isLoggedIn = isUserLoggedIn();
$cartItems = $isLoggedIn ? getCartItems($_SESSION['user_id']) : [];
$cartTotal = 0;
$shippingCost = 5.00;

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isLoggedIn) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';
    
    if ($product_id) {
        try {
            switch ($action) {
                case 'update':
                    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
                    if ($quantity && $quantity > 0) {
                        updateCartQuantity($_SESSION['user_id'], $product_id, $quantity);
                    }
                    break;
                    
                case 'remove':
                    removeCartItem($_SESSION['user_id'], $product_id);
                    break;
            }
        } catch (Exception $e) {
            error_log("Cart action error: " . $e->getMessage());
        }
    }
    
    header("Location: cartPage.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Your shopping cart at GreenClean - Review and checkout your eco-friendly cleaning products">
    <title>GreenClean - Shopping Cart</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/cartPage.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <!-- Login Popup -->
    <?php if (!$isLoggedIn): ?>
    <div class="login-popup" id="loginPopup">
        <div class="popup-content">
            <h2>Please Sign In</h2>
            <p>You need to be signed in to view your cart</p>
            <a href="authpage.php" class="checkout-btn">Sign In/Sign Up</a>
        </div>
    </div>
    <?php endif; ?>

    <?php 
        error_log('Checking for success message');
        if (isset($_SESSION['success_message'])): 
            error_log('Success message found: ' . $_SESSION['success_message']);
        ?>
        <div class="success-message">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
        </div>
            <?php 
            error_log('Unsetting success message');
            unset($_SESSION['success_message']); 
            ?>
        <?php else: 
            error_log('No success message found in session');
        endif; 
    ?>

    <main class="main" role="main">
        <div class="cart-section">
            <h1 class="page-title">Your Shopping Cart</h1>
            
            <?php if (!empty($cartItems)): ?>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Stock</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): 
                            $cartTotal += $item['price'] * $item['quantity'];
                        ?>
                        <tr data-product-id="<?= htmlspecialchars($item['id']) ?>">
                            <td>
                                <div class="product-info">
                                    <img src="<?= $base_url . htmlspecialchars($item['image_url']) ?>" 
                                         alt="<?= htmlspecialchars($item['name']) ?>" 
                                         class="product-image">
                                    <span class="product-name"><?= htmlspecialchars($item['name']) ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="quantity-controls">
                                    <button class="quantity-btn" onclick="updateQuantity(<?= $item['id'] ?>, 'decrease')">−</button>
                                    <input type="number" class="quantity-input" 
                                           value="<?= htmlspecialchars($item['quantity']) ?>" 
                                           min="1" max="<?= htmlspecialchars($item['stock']) ?>" 
                                           readonly>
                                    <button class="quantity-btn" onclick="updateQuantity(<?= $item['id'] ?>, 'increase')">+</button>
                                </div>
                            </td>
                            <td><span class="stock-amount"><?= htmlspecialchars($item['stock']) ?></span></td>
                            <td><span class="current-price">$<?= number_format($item['price'], 2) ?></span></td>
                            <td>
                                <button class="remove-btn" onclick="removeItem(<?= $item['id'] ?>)">×</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="cart-summary">
                    <div class="summary-item">
                        <span>Subtotal</span>
                        <span class="subtotal-amount">$<?= number_format($cartTotal, 2) ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Shipping</span>
                        <span class="shipping-amount">$<?= number_format($shippingCost, 2) ?></span>
                    </div>
                    <div class="summary-item total">
                        <span>Total</span>
                        <span class="total-amount">$<?= number_format($cartTotal + $shippingCost, 2) ?></span>
                    </div>
                    <button class="checkout-btn" onclick="proceedToCheckout()">Proceed to Checkout</button>
                </div>
            <?php else: ?>
                <div class="empty-cart-container">
                    <div class="empty-cart-message">
                        <h2 class="empty-cart-text">Your Cart is Empty</h2>
                        <p class="text-gray-300">Add some eco-friendly products to get started!</p>
                    </div>
                    <a href="productPage.php" class="continue-shopping-btn">
                        Continue Shopping
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer" role="contentinfo">
        <div class="container">
            <p class="footer-text">&copy; 2024 GreenClean. All rights reserved.</p>
        </div>
    </footer>

    <script src="js/cartPage.js"></script>
</body>
</html>