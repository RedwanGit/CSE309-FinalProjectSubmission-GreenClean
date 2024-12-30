<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isAdminLoggedIn()) {
    header("Location: index.php");
    exit();
}

// Get some basic statistics
try {
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE is_admin = 0");
    $totalUsers = $stmt->fetch()['total'];
    
    // Total products
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $totalProducts = $stmt->fetch()['total'];
    
    // Total orders
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $stmt->fetch()['total'];
    
    // Revenue
    $stmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'");
    $totalRevenue = $stmt->fetch()['total'] ?? 0;
} catch(PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - GreenClean</title>
    <link rel="stylesheet" href="../css/admin.css">
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
                    <li class="active"><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="products.php">Products</a></li>
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
                <h1>Dashboard</h1>
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</p>
            </div>

            <!-- Statistics Cards -->
            <div class="stat-cards">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <p class="stat-number"><?php echo $totalUsers; ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Total Products</h3>
                    <p class="stat-number"><?php echo $totalProducts; ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <p class="stat-number"><?php echo $totalOrders; ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Total Revenue</h3>
                    <p class="stat-number">$<?php echo number_format($totalRevenue, 2); ?></p>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="recent-activity">
                <h2>Recent Orders</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT o.*, u.username 
                                           FROM orders o 
                                           JOIN users u ON o.user_id = u.id 
                                           ORDER BY o.created_at DESC 
                                           LIMIT 5");
                        while ($order = $stmt->fetch()) {
                            echo "<tr>";
                            echo "<td>#" . $order['id'] . "</td>";
                            echo "<td>" . htmlspecialchars($order['username']) . "</td>";
                            echo "<td>$" . number_format($order['total_amount'], 2) . "</td>";
                            echo "<td>" . htmlspecialchars($order['status']) . "</td>";
                            echo "<td>" . date('Y-m-d H:i', strtotime($order['created_at'])) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>