<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (isAdminLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = loginAdmin($username, $password);
    if ($result['success']) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="admin-login-container">
        <h1>Admin Login</h1>
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="login-form">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>
            
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit">Login</button>
        </form>
    </div>
</body>
<script>
        // Auto-hide error messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                const errorMessage = document.querySelector('.error-message');
                if (errorMessage) {
                    errorMessage.style.display = 'none';
                }
            }, 5000); // Hide after 5 seconds
        });
    </script>
</body>
</html>