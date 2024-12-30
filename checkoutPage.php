<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (!isUserLoggedIn()) {
    header('Location: authpage.php');
    exit;
}

$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
if (!$order_id) {
    header('Location: cartPage.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error = '';
$order_confirmed = false;

try {
    $stmt = $pdo->prepare("
        SELECT o.*, 
               oi.product_id,
               oi.quantity,
               oi.price as item_price,
               p.name as product_name
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.id = ? AND o.user_id = ? AND (o.status = 'processing' OR o.status = 'confirmed')
    ");
    $stmt->execute([$order_id, $user_id]);
    $order_items = $stmt->fetchAll();

    if (empty($order_items)) {
        header('Location: cartPage.php');
        exit;
    }

    $order_total = $order_items[0]['total_amount'];
    $order_confirmed = $order_items[0]['status'] === 'confirmed';
    
} catch (PDOException $e) {
    error_log("Checkout page error: " . $e->getMessage());
    header('Location: cartPage.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$order_confirmed) {
    try {
        $pdo->beginTransaction();

        // Modern input sanitization using htmlspecialchars instead of FILTER_SANITIZE_STRING
        $shipping_address = htmlspecialchars(trim($_POST['address']));
        $shipping_city = htmlspecialchars(trim($_POST['city']));
        $shipping_state = htmlspecialchars(trim($_POST['state']));
        $shipping_zip = htmlspecialchars(trim($_POST['zip']));

        // Validate inputs
        if (empty($shipping_address) || empty($shipping_city) || 
            empty($shipping_state) || !preg_match('/^\d{5}$/', $shipping_zip)) {
            throw new Exception("Invalid shipping information provided");
        }

        // Update order status and shipping information
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET status = 'confirmed',
                shipping_address = ?,
                shipping_city = ?,
                shipping_state = ?,
                shipping_zip = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND user_id = ? AND status = 'processing'
        ");
        
        $stmt->execute([
            $shipping_address,
            $shipping_city,
            $shipping_state,
            $shipping_zip,
            $order_id,
            $user_id
        ]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("Order not found or already processed");
        }

        $pdo->commit();
        $success_message = "Your order has been successfully confirmed! You will receive updates on your order status.";
        $order_confirmed = true;

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Order confirmation error: " . $e->getMessage());
        $error = "There was an error processing your order. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - GreenClean</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/checkout.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <main class="main">
        <div class="checkout-container">
            <h1><?= $order_confirmed ? 'Order Confirmed' : 'Checkout' ?></h1>
            
            <?php if ($success_message): ?>
                <div class="success-message">
                    <?= htmlspecialchars($success_message) ?>
                    <div class="mt-4">
                        <a href="productPage.php" class="continue-shopping-btn">Continue Shopping</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <div class="order-summary">
                <h2>Order Summary</h2>
                <div class="order-items">
                    <?php foreach ($order_items as $item): ?>
                        <div class="order-item">
                            <span class="item-name"><?= htmlspecialchars($item['product_name']) ?></span>
                            <span class="item-quantity">x<?= htmlspecialchars($item['quantity']) ?></span>
                            <span class="item-price">$<?= number_format($item['item_price'] * $item['quantity'], 2) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-total">
                    <span>Total (including shipping)</span>
                    <span class="total-amount">$<?= number_format($order_total, 2) ?></span>
                </div>
            </div>
            
            <?php if (!$order_confirmed): ?>
                <form id="checkout-form" method="POST">
                    <div class="form-section">
                        <h2>Shipping Information</h2>
                        <div class="form-group">
                            <label for="address">Street Address</label>
                            <input type="text" id="address" name="address" required maxlength="255">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" required maxlength="100">
                            </div>
                            <div class="form-group">
                                <label for="state">Shipping City</label>
                                <input type="text" id="state" name="state" required maxlength="50">
                            </div>
                            <div class="form-group">
                                <label for="zip">ZIP Code</label>
                                <input type="text" id="zip" name="zip" required pattern="[0-9]{5}" title="Five digit zip code">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="submit-btn">Confirm Order</button>
                </form>
            <?php else: ?>
                <div class="form-section">
                    <h2>Shipping Information</h2>
                    <div class="shipping-details">
                        <p><?= htmlspecialchars($shipping_address) ?></p>
                        <p><?= htmlspecialchars($shipping_city) . ', ' . htmlspecialchars($shipping_state) . ' ' . htmlspecialchars($shipping_zip) ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 GreenClean. All rights reserved.</p>
        </div>
    </footer>

    <script src="js/checkoutPage.js"></script>
</body>
</html>