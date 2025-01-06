<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

$error = '';
$success = '';

// If user is already logged in, redirect to profile
if (isUserLoggedIn()) {
    header('Location: profilePage.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    try {
        switch($_POST['action']) {
            case 'login':
                $identifier = trim($_POST['identifier']);
                $password = trim($_POST['password']);
                
                $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
                $stmt->execute([$identifier, $identifier]);
                $user = $stmt->fetch();
                
                if (!$user || $user['password'] !== $password) {
                    $error = !$user ? 'No account found with this username/email' : 'Incorrect password';
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['is_admin'] = $user['is_admin'];
                    $_SESSION['last_active'] = time();
                    
                    $stmt = $pdo->prepare("UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    
                    header('Location: profilePage.php');
                    exit();
                }
                break;
                
            case 'register':
                $email = trim($_POST['email']);
                $username = trim($_POST['username']);
                $password = trim($_POST['password']);
                
                // Input validation
                if (empty($username) || empty($email) || empty($password)) {
                    $error = 'All fields are required';
                } elseif (strlen($username) < 3 || strlen($username) > 50) {
                    $error = 'Username must be between 3 and 50 characters';
                } elseif (strlen($password) < 4) {
                    $error = 'Password must be at least 4 characters long';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Invalid email format';
                } elseif (isEmailTaken($email)) {
                    $error = 'Email already registered';
                } else {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    
                    if ($stmt->fetchColumn() > 0) {
                        $error = 'Username already taken';
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at, updated_at) 
                                             VALUES (?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
                        if ($stmt->execute([$username, $email, $password])) {
                            $success = 'Registration successful! Please login.';
                        } else {
                            $error = 'Registration failed';
                        }
                    }
                }
                break;
        }
    } catch(PDOException $e) {
        error_log("Authentication error: " . $e->getMessage());
        $error = 'An error occurred during ' . $_POST['action'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenClean - Authentication</title>
    <link rel="stylesheet" href="css/themes.css?v=2">
    <link rel="stylesheet" href="css/authPage.css?v=2">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="auth-main">
        <div class="auth-box">
            <?php if ($error || $success): ?>
                <div class="notification <?php echo $error ? 'error' : 'success'; ?>">
                    <?php echo htmlspecialchars($error ?: $success); ?>
                </div>
            <?php endif; ?>

            <div id="loginForm">
                <h2>Login</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="login">
                    <div class="form-group">
                        <label for="identifier">Username or Email</label>
                        <input type="text" id="identifier" name="identifier" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" class="auth-button">Login</button>
                </form>
                <div class="toggle-text">
                    <a href="#" onclick="toggleForms()">Need to register?</a>
                </div>
            </div>

            <div id="registerForm" style="display: none;">
                <h2>Register</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="register">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="reg-password">Password</label>
                        <input type="password" id="reg-password" name="password" required>
                    </div>
                    <button type="submit" class="auth-button">Register</button>
                </form>
                <div class="toggle-text">
                    <a href="#" onclick="toggleForms()">Already have an account?</a>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> GreenClean. All rights reserved.</p>
    </footer>

    <script src="js/authPage.js"></script>
    <script src="js/themeManager.js"></script>
</body>
</html>