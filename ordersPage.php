<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Redirect if not logged in
if (!isUserLoggedIn()) {
    header('Location: authPage.php');
    exit();
}

// Get user's orders with product details
$stmt = $pdo->prepare("
    SELECT 
        o.id as order_id,
        o.total_amount,
        o.status,
        o.created_at,
        o.shipping_address,
        o.shipping_city,
        o.shipping_state,
        o.shipping_zip,
        p.name as product_name,
        oi.quantity,
        oi.price as item_price
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");

$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group orders by order ID
$grouped_orders = [];
foreach ($orders as $order) {
    $order_id = $order['order_id'];
    if (!isset($grouped_orders[$order_id])) {
        $grouped_orders[$order_id] = [
            'order_id' => $order_id,
            'total_amount' => $order['total_amount'],
            'status' => $order['status'],
            'created_at' => $order['created_at'],
            'shipping_address' => $order['shipping_address'],
            'shipping_city' => $order['shipping_city'],
            'shipping_state' => $order['shipping_state'],
            'shipping_zip' => $order['shipping_zip'],
            'items' => []
        ];
    }
    $grouped_orders[$order_id]['items'][] = [
        'product_name' => $order['product_name'],
        'quantity' => $order['quantity'],
        'item_price' => $order['item_price']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenClean - My Orders</title>
    <link rel="stylesheet" href="css/themes.css?v=2">
    <link rel="stylesheet" href="css/ordersPage.css?v=2">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="main">
        <main class="orders-main">
            <div class="orders-container">
                <h1>My Orders</h1>
                
                <?php if (empty($grouped_orders)): ?>
                    <div class="no-orders">
                        <p>You haven't placed any orders yet.</p>
                        <a href="productPage.php" class="shop-now-btn">Shop Now</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($grouped_orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <h3>Order #<?php echo htmlspecialchars($order['order_id']); ?></h3>
                                    <p class="order-date">Placed on: <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                                </div>
                                <div class="order-status">
                                    <span class="status-badge <?php echo strtolower($order['status']); ?>">
                                        <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="order-items">
                                <?php foreach ($order['items'] as $item): ?>
                                    <div class="order-item">
                                        <span class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                        <span class="quantity">Qty: <?php echo htmlspecialchars($item['quantity']); ?></span>
                                        <span class="price">$<?php echo number_format($item['item_price'], 2); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="order-footer">
                                <div class="shipping-info">
                                    <h4>Shipping Address:</h4>
                                    <p>
                                        <?php echo htmlspecialchars($order['shipping_address']); ?><br>
                                        <?php echo htmlspecialchars($order['shipping_city']); ?>, 
                                        <?php echo htmlspecialchars($order['shipping_state']); ?> 
                                        <?php echo htmlspecialchars($order['shipping_zip']); ?>
                                    </p>
                                </div>
                                <div class="order-total">
                                    <span>Total:</span>
                                    <span class="total-amount">$<?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> GreenClean. All rights reserved.</p>
    </footer>

    <script src="js/themeManager.js"></script>
</body>

</html>