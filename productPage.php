<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$base_url = "http://localhost/greenclean/";

// Check login status
$isLoggedIn = isUserLoggedIn();

try {
    // Fetch products from the database
    $stmt = $pdo->prepare("SELECT id, name, price, description, image_url, category, stock FROM products WHERE stock > 0");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching products: " . $e->getMessage());
    $products = [];
}

// Handle AJAX add to cart request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_add_to_cart'])) {
    header('Content-Type: application/json');
    
    if (!$isLoggedIn) {
        echo json_encode(['success' => false, 'message' => 'Please login first']);
        exit();
    }
    
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT) ?? 1;
    
    try {
        $order_id = getPendingOrder($_SESSION['user_id']);
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity) 
                              VALUES (?, ?, ?) 
                              ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)");
        $stmt->execute([$order_id, $product_id, $quantity]);
        
        updateOrderTotal($order_id);
        echo json_encode(['success' => true, 'message' => 'Product added to cart!']);
    } catch (Exception $e) {
        error_log("Add to cart error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to add product']);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Green Clean Products</title>
    <link rel="stylesheet" href="css/productPage.css?v=1.1">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="main">
        <div class="container">
            <div id="notification" class="notification"></div>
            
            <h1 class="page-title">Our Products</h1>
            
            <div class="category-selection">
                <label for="category-select">Filter by Category:</label>
                <select id="category-select" onchange="filterCategory(this.value)">
                    <option value="all">All</option>
                    <option value="household">Household</option>
                    <option value="personal">Personal</option>
                </select>
            </div>
            
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card" data-category="<?= htmlspecialchars($product['category']) ?>">
                        <div class="product-image-container">
                            <img src="<?= $base_url . htmlspecialchars($product['image_url']) ?>" 
                                alt="<?= htmlspecialchars($product['name']) ?>" 
                                class="product-image">
                            <div class="cart-status" id="status-<?= $product['id'] ?>">Added to cart!</div>
                        </div>
                        <div class="product-content">
                            <h2 class="product-title"><?= htmlspecialchars($product['name']) ?></h2>
                            <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                            <p class="product-stock">In Stock: <?= htmlspecialchars($product['stock']) ?></p>
                            <div class="product-footer">
                                <span class="product-price">$<?= number_format($product['price'], 2) ?></span>
                                <button onclick="addToCart(<?= $product['id'] ?>)" 
                                        class="add-to-cart-btn" 
                                        id="btn-<?= $product['id'] ?>">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <footer class="footer">© 2024 Green Clean. All rights reserved.</footer>
    
    <script src="js/productPage.js"></script>
</body>
</html>