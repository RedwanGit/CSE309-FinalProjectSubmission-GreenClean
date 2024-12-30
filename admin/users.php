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

// Handle AJAX update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    header('Content-Type: application/json');
    
    try {
        $user_id = sanitizeInput($_POST['id']);
        $field = sanitizeInput($_POST['field']);
        $value = sanitizeInput($_POST['value']);
        
        // Validate field name to prevent SQL injection
        $allowed_fields = ['username', 'email', 'is_admin', 'password'];
        if (!in_array($field, $allowed_fields)) {
            throw new Exception('Invalid field');
        }

        // Special handling for email updates
        if ($field === 'email' && $value !== '') {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$value, $user_id]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Email already taken']);
                exit;
            }
        }

        // Special handling for is_admin field
        if ($field === 'is_admin') {
            $value = $value === 'true' || $value === '1' ? 1 : 0;
        }

        $stmt = $pdo->prepare("UPDATE users SET $field = ?, updated_at = NOW() WHERE id = ? AND id != ?");
        $stmt->execute([$value, $user_id, $_SESSION['admin_id']]);
        
        echo json_encode(['status' => 'success']);
        exit;
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

// Handle user addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    
    try {
        if (isEmailTaken($email)) {
            $error_message = "Email already taken. Please use a different email.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_admin, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$username, $email, $password, $is_admin]);
            $success_message = "User added successfully!";
        }
    } catch (PDOException $e) {
        $error_message = "Error adding user: " . $e->getMessage();
    }
}

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND id != ?");
        $stmt->execute([$_GET['delete'], $_SESSION['admin_id']]);
        $success_message = "User deleted successfully!";
    } catch (PDOException $e) {
        $error_message = "Error deleting user: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Management - GreenClean</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin-users.css">
    <script src="../js/admin-users.js" defer></script>
</head>
<body>
    <div class="admin-wrapper">
        <div class="admin-sidebar">
            <div class="sidebar-header">
                <h2>GreenClean Admin</h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li class="active"><a href="users.php">Users</a></li>
                    <li><a href="orders.php">Orders</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>

        <div class="admin-content">
            <div class="content-header">
                <h1>User Management</h1>
            </div>

            <?php if ($success_message || $error_message): ?>
                <div id="popupMessage" class="popup-message <?php echo $success_message ? '' : 'error'; ?>">
                    <?php echo $success_message ? $success_message : $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <h2>Add New User</h2>
                <form method="POST" class="admin-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Username:</label>
                            <input type="text" name="username" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Email:</label>
                            <input type="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Password:</label>
                            <input type="password" name="password" required>
                        </div>
                        
                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" name="is_admin">
                                Make Admin
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" name="add_user">Add User</button>
                </form>
            </div>

            <div class="table-container">
                <h2>User List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Password</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM users ORDER BY is_admin DESC, created_at DESC");
                        while ($user = $stmt->fetch()) {
                            $isCurrentUser = $user['id'] == $_SESSION['admin_id'];
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                            echo "<td class='editable' data-field='username' data-id='{$user['id']}'>" . htmlspecialchars($user['username']) . "</td>";
                            echo "<td class='editable' data-field='email' data-id='{$user['id']}'>" . htmlspecialchars($user['email']) . "</td>";
                            echo "<td class='editable' data-field='password' data-id='{$user['id']}'>" . htmlspecialchars($user['password']) . "</td>";
                            echo "<td class='editable' data-field='is_admin' data-id='{$user['id']}'>" . 
                                 ($user['is_admin'] ? 'Admin' : 'User') . 
                                 "</td>";
                            echo "<td>" . date('Y-m-d', strtotime($user['created_at'])) . "</td>";
                            echo "<td>";
                            if (!$isCurrentUser) {
                                echo "<button class='btn-delete' data-user-id='" . $user['id'] . "'>Delete</button>";                            } else {
                                echo "<span class='text-muted'>Current User</span>";
                            }
                            echo "</td>";
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
            <p>Are you sure you want to delete this user?</p>
            <div class="confirm-buttons">
                <button id="confirmYes" class="btn-confirm">Yes, Delete</button>
                <button id="confirmNo" class="btn-cancel">Cancel</button>
            </div>
        </div>
    </div>

</body>
</html>