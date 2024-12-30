<?php
// admin/orders.php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

if (!isAdminLoggedIn()) {
    header("Location: index.php");
    exit();
}

$success_message = '';
$error_message = '';

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = sanitizeInput($_POST['order_id']);
    $status = sanitizeInput($_POST['status']);
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        $success_message = "Order status updated successfully!";
    } catch(PDOException $e) {
        $error_message = "Error updating order: " . $e->getMessage();
    }
}

// Handle order details view
$order_details = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT oi.*, p.name as product_name, p.price as unit_price 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$_GET['view']]);
        $order_details = $stmt->fetchAll();
    } catch(PDOException $e) {
        $error_message = "Error fetching order details: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Management - GreenClean</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin-orders.css">
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
                    <li><a href="products.php">Products</a></li>
                    <li><a href="users.php">Users</a></li>
                    <li class="active"><a href="orders.php">Orders</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="admin-content">
            <div class="content-header">
                <h1>Order Management</h1>
            </div>

            <?php if ($success_message): ?>
                <div class="message success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="message error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <?php if ($order_details): ?>
                <!-- Order Details View -->
                <div class="order-details">
                    <h2>Order #<?php echo htmlspecialchars($_GET['view']); ?> Details</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_details as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <a href="orders.php" class="btn-back">Back to Orders</a>
                </div>
            <?php else: ?>
                <!-- Order List -->
                <div class="table-container">
                    <h2>Order List</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Order Date</th>
                                <th>Shipping Information</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("
                                SELECT o.*, u.username, u.email 
                                FROM orders o 
                                JOIN users u ON o.user_id = u.id 
                                ORDER BY o.created_at DESC
                            ");
                            while ($order = $stmt->fetch()) {
                                echo "<tr>";
                                echo "<td>#" . htmlspecialchars($order['id']) . "</td>";
                                echo "<td>" . htmlspecialchars($order['username']) . "<br><small>" . htmlspecialchars($order['email']) . "</small></td>";
                                echo "<td>$" . number_format($order['total_amount'], 2) . "</td>";
                                echo "<td>";
                                ?>
                                <form method="POST" class="status-form">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                                <?php
                                echo "</td>";
                                echo "<td>" . date('Y-m-d H:i', strtotime($order['created_at'])) . "</td>";
                                echo "<td class='shipping-info'>";
                                echo "<div class='shipping-label'>Shipping Address:</div>";
                                echo "<address>";
                                echo htmlspecialchars($order['shipping_address']) . "<br>";
                                echo htmlspecialchars($order['shipping_city']) . ", " . 
                                     htmlspecialchars($order['shipping_state']) . " " . 
                                     htmlspecialchars($order['shipping_zip']);
                                echo "</address>";
                                echo "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-hide messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const messages = document.querySelectorAll('.message');
            messages.forEach(function(message) {
                setTimeout(function() {
                    message.style.animation = 'slideOut 0.5s ease-in forwards';
                    setTimeout(function() {
                        message.remove();
                    }, 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>