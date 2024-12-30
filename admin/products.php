<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

if (!isAdminLoggedIn()) {
    header("Location: index.php");
    exit();
}

$success_message = '';
$error_message = '';

// Handle AJAX product updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_product'])) {
        $id = intval($_POST['id']);
        $field = sanitizeInput($_POST['field']);
        $value = sanitizeInput($_POST['value']);
        
        $allowed_fields = ['name', 'price', 'category', 'stock'];
        if (in_array($field, $allowed_fields)) {
            try {
                $stmt = $pdo->prepare("UPDATE products SET $field = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$value, $id]);
                echo json_encode(['success' => true]);
                exit;
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                exit;
            }
        }
    }
    // Handle product deletion
    else if (isset($_POST['delete_product'])) {
        $id = intval($_POST['id']);
        try {
            // First get the image URL to delete the file
            $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch();
            
            if ($product && $product['image_url']) {
                $image_path = $_SERVER['DOCUMENT_ROOT'] . $product['image_url'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            // Then delete the product from database
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
            exit;
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
    // Handle product addition
    else if (isset($_POST['add_product'])) {
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $price = floatval($_POST['price']);
        $category = sanitizeInput($_POST['category']);
        $stock = intval($_POST['stock']);
        
        // Handle image upload
        $image_url = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $target_dir = "../images/products/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            $unique_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $unique_filename;
            $image_url = "/images/products/" . $unique_filename;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image_url, category, stock, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
                    $stmt->execute([$name, $description, $price, $image_url, $category, $stock]);
                    $success_message = "Product added successfully!";
                } catch(PDOException $e) {
                    $error_message = "Error adding product: " . $e->getMessage();
                    if (file_exists($target_file)) {
                        unlink($target_file);
                    }
                }
            } else {
                $error_message = "Error uploading image.";
            }
        } else {
            $error_message = "Please select an image.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Product Management - GreenClean</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin-products.css">
    <script src="../js/admin-products.js" defer></script>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar Navigation -->
        <div class="admin-sidebar">
            <div class="sidebar-header">
                <h2>GreenClean Admin</h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li class="active"><a href="products.php">Products</a></li>
                    <li><a href="users.php">Users</a></li>
                    <li><a href="orders.php">Orders</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="admin-content">
            <div class="content-header">
                <h1>Product Management</h1>
            </div>

            <?php if ($success_message): ?>
                <div class="notification success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="notification error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <!-- Add Product Form -->
            <div class="form-container">
                <h2>Add New Product</h2>
                <form method="POST" enctype="multipart/form-data" class="admin-form" id="addProductForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Product Name:</label>
                            <input type="text" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Price:</label>
                            <input type="number" step="0.01" name="price" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Category:</label>
                            <select name="category" required>
                                <option value="household">Household</option>
                                <option value="personal">Personal Care</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Stock:</label>
                            <input type="number" name="stock" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="description" required rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Image:</label>
                        <input type="file" name="image" accept="image/*" required>
                    </div>
                    
                    <button type="submit" name="add_product" value="1">Add Product</button>
                </form>
            </div>

            <!-- Product List -->
            <div class="table-container">
                <h2>Current Products</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM products ORDER BY category, name");
                        while ($row = $stmt->fetch()) {
                            echo "<tr data-id='" . $row['id'] . "'>";
                            echo "<td>" . ($row['image_url'] ? "<img src='/greenclean" . htmlspecialchars($row['image_url']) . "' alt='" . htmlspecialchars($row['name']) . "' class='product-thumbnail'>" : "No image") . "</td>";
                            echo "<td><div class='editable' data-field='name'>" . htmlspecialchars($row['name']) . "</div></td>";
                            echo "<td><div class='editable' data-field='price'>$" . number_format($row['price'], 2) . "</div></td>";
                            echo "<td><div class='editable' data-field='category' data-type='select'>" . htmlspecialchars($row['category']) . "</div></td>";
                            echo "<td><div class='editable' data-field='stock'>" . $row['stock'] . "</div></td>";
                            echo "<td class='action-buttons'>
                                    <button class='btn-delete' onclick='deleteProduct(" . $row['id'] . ")'>Delete</button>
                                  </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="confirmDialog" class="confirm-dialog">
        <div class="confirm-content">
            <p>Are you sure you want to delete this product?</p>
            <div class="confirm-buttons">
                <button id="confirmYes" class="btn-confirm">Yes, Delete</button>
                <button id="confirmNo" class="btn-cancel">Cancel</button>
            </div>
        </div>
    </div>
</body>
</html>