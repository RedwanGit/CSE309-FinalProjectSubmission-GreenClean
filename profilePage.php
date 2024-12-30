<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isUserLoggedIn()) {
    header('Location: authPage.php');
    exit();
}

$currentUser = getCurrentUser();
if (!$currentUser) {
    header('Location: authPage.php');
    exit();
}

$response = ['success' => false, 'message' => ''];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    $email = $data['email'] ?? '';
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    
    try {
        $updates = [];
        $params = [];
        
        // Check email change
        if ($email !== $currentUser['email']) {
            if (isEmailTaken($email)) {
                $response['message'] = 'Email already in use';
                echo json_encode($response);
                exit();
            }
            $updates[] = 'email = ?';
            $params[] = $email;
        }
        
        // Check username change
        if ($username !== $currentUser['username']) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $currentUser['id']]);
            if ($stmt->fetchColumn() > 0) {
                $response['message'] = 'Username already taken';
                echo json_encode($response);
                exit();
            }
            $updates[] = 'username = ?';
            $params[] = $username;
        }
        
        // Check password change
        if (!empty($password)) {
            $updates[] = 'password = ?';
            $params[] = $password;
        }
        
        // Only proceed if there are changes
        if (!empty($updates)) {
            $updates[] = 'updated_at = ?';
            $params[] = date('Y-m-d H:i:s');
            $params[] = $currentUser['id'];
            
            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            // Update session if username changed
            if ($username !== $currentUser['username']) {
                $_SESSION['username'] = $username;
            }
            
            $response = [
                'success' => true,
                'message' => 'Profile updated successfully'
            ];
        } else {
            $response = [
                'success' => true,
                'message' => 'No changes to update'
            ];
        }
    } catch (Exception $e) {
        error_log("Profile update error: " . $e->getMessage());
        $response['message'] = 'An error occurred while updating profile';
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/profilePage.css">
    <title>GreenClean - Profile</title>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main>
        <div class="auth-container">
            <div class="auth-box">
                <h2>Profile Information</h2>
                <form id="profileForm">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" value="<?php echo htmlspecialchars($currentUser['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" placeholder="Enter new password to change">
                    </div>
                    <button type="submit" class="auth-button">Update Profile</button>
                </form>
                <div class="notification" id="notification"></div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <p>© 2024 GreenClean. All rights reserved.</p>
    </footer>

    <script src="js/navbar.js"></script>
    <script src="js/profilePage.js"></script>
</body>
</html>