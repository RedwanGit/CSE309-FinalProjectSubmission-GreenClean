<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isUserLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue']);
    exit;
}

try {
    global $pdo;
    $pdo->beginTransaction();
    
    // Get current pending order
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE user_id = ? AND status = 'pending' LIMIT 1");
    $stmt->execute([$user_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception('No pending order found');
    }
    
    $order_id = $order['id'];
    
    // Get all items in the order
    $stmt = $pdo->prepare("
        SELECT oi.product_id, oi.quantity, p.price, p.stock 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();
    
    if (empty($items)) {
        throw new Exception('No items in order');
    }
    
    $total_amount = 0;
    
    // Verify stock and calculate total
    foreach ($items as $item) {
        if ($item['stock'] < $item['quantity']) {
            throw new Exception("Insufficient stock for some items");
        }
        
        // Update product stock
        $new_stock = $item['stock'] - $item['quantity'];
        $stmt = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
        $stmt->execute([$new_stock, $item['product_id']]);
        
        // Update order_items with current price
        $stmt = $pdo->prepare("UPDATE order_items SET price = ? WHERE order_id = ? AND product_id = ?");
        $stmt->execute([$item['price'], $order_id, $item['product_id']]);
        
        $total_amount += $item['price'] * $item['quantity'];
    }
    
    // Add shipping cost
    $shipping_cost = 5.00;
    $total_amount += $shipping_cost;
    
    // Update order status and total
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET status = 'processing', 
            total_amount = ?, 
            updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    $stmt->execute([$total_amount, $order_id]);
    
    $pdo->commit();
    echo json_encode([
        'success' => true,
        'orderId' => $order_id
    ]);
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    error_log("Checkout error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}