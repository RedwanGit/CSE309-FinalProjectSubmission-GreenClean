<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

if (!isUserLoggedIn()) {
    exit(json_encode(['success' => false, 'message' => 'Please login to continue']));
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

if (!$product_id && $action !== 'clear') {
    exit(json_encode(['success' => false, 'message' => 'Invalid product']));
}

try {
    switch ($action) {
        case 'add':
            $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT) ?? 1;
            
            $pdo->beginTransaction();
            
            // Check product stock
            $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if (!$product || $product['stock'] < $quantity) {
                throw new Exception('Insufficient stock');
            }
            
            $order_id = getPendingOrder($user_id);
            
            // Add/Update item
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity) 
                                  VALUES (?, ?, ?) 
                                  ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)");
            $stmt->execute([$order_id, $product_id, $quantity]);
            
            // Verify final quantity
            $stmt = $pdo->prepare("SELECT quantity FROM order_items WHERE order_id = ? AND product_id = ?");
            $stmt->execute([$order_id, $product_id]);
            
            if ($stmt->fetchColumn() > $product['stock']) {
                throw new Exception('Cannot exceed available stock');
            }
            
            updateOrderTotal($order_id);
            $pdo->commit();
            $message = 'Product added to cart';
            break;
            
        case 'update':
            $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
            if (!$quantity) {
                throw new Exception('Invalid quantity');
            }
            
            // Check stock
            $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if (!$product || $product['stock'] < $quantity) {
                throw new Exception('Not enough stock available');
            }
            
            if (!updateCartQuantity($user_id, $product_id, $quantity)) {
                throw new Exception('Failed to update cart');
            }
            $message = 'Cart updated';
            break;
            
        case 'remove':
            if (!removeCartItem($user_id, $product_id)) {
                throw new Exception('Failed to remove item');
            }
            $message = 'Item removed from cart';
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
    $response = ['success' => true, 'message' => $message];
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response = ['success' => false, 'message' => $e->getMessage()];
}

exit(json_encode($response));