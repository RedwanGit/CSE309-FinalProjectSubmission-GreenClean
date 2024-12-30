<?php
require_once 'includes/config.php';

header('Content-Type: application/json');

$product_id = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_GET, 'quantity', FILTER_VALIDATE_INT) ?? 1;

try {
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode([
            'available' => false,
            'message' => 'Product not found'
        ]);
        exit;
    }

    if ($product['stock'] < $quantity) {
        echo json_encode([
            'available' => false,
            'message' => 'Not enough stock available'
        ]);
        exit;
    }

    echo json_encode([
        'available' => true,
        'message' => 'Stock available'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'available' => false,
        'message' => 'Error checking stock'
    ]);
}