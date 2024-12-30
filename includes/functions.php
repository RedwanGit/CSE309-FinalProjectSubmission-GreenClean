<?php
// includes/functions.php
function checkAdmin() {
    if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        header("Location: index.php");
        exit();
    }
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags($data));
}

function getCartItems($user_id) {
    global $pdo;
    
    try {
        $query = "SELECT p.*, oi.quantity 
                  FROM order_items oi 
                  JOIN products p ON oi.product_id = p.id 
                  JOIN orders o ON oi.order_id = o.id 
                  WHERE o.user_id = ? AND o.status = 'pending'";
                  
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        error_log("Get cart items error: " . $e->getMessage());
        return [];
    }
}

function updateCartQuantity($user_id, $product_id, $quantity) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Check stock availability
        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if (!$product || $product['stock'] < $quantity) {
            throw new Exception('Not enough stock available');
        }
        
        // Get pending order
        $order_id = getPendingOrder($user_id);
        
        // Update quantity
        $stmt = $pdo->prepare("UPDATE order_items 
                              SET quantity = ? 
                              WHERE order_id = ? AND product_id = ?");
        $stmt->execute([$quantity, $order_id, $product_id]);
        
        // Update order total
        updateOrderTotal($order_id);
        
        $pdo->commit();
        return true;
    } catch(Exception $e) {
        $pdo->rollBack();
        error_log("Update cart quantity error: " . $e->getMessage());
        throw $e;
    }
}

function removeCartItem($user_id, $product_id) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Get pending order
        $order_id = getPendingOrder($user_id);
        
        // Remove item
        $stmt = $pdo->prepare("DELETE FROM order_items 
                              WHERE order_id = ? AND product_id = ?");
        $stmt->execute([$order_id, $product_id]);
        
        // Update order total
        updateOrderTotal($order_id);
        
        $pdo->commit();
        return true;
    } catch(PDOException $e) {
        $pdo->rollBack();
        error_log("Remove cart item error: " . $e->getMessage());
        return false;
    }
}

function getPendingOrder($user_id) {
    global $pdo;
    
    try {
        // Check for existing pending order
        $stmt = $pdo->prepare("SELECT id FROM orders 
                              WHERE user_id = ? AND status = 'pending'");
        $stmt->execute([$user_id]);
        $order = $stmt->fetch();
        
        if ($order) {
            return $order['id'];
        }
        
        // Create new order if none exists
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, status) 
                              VALUES (?, 'pending')");
        $stmt->execute([$user_id]);
        return $pdo->lastInsertId();
    } catch(PDOException $e) {
        error_log("Get pending order error: " . $e->getMessage());
        throw $e;
    }
}

function updateOrderTotal($order_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE orders o 
                              SET total_amount = (
                                  SELECT SUM(oi.quantity * p.price)
                                  FROM order_items oi
                                  JOIN products p ON oi.product_id = p.id
                                  WHERE oi.order_id = ?
                              )
                              WHERE o.id = ?");
        $stmt->execute([$order_id, $order_id]);
        return true;
    } catch(PDOException $e) {
        error_log("Update order total error: " . $e->getMessage());
        throw $e;
    }
}