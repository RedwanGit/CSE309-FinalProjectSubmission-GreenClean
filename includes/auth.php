<?php
// includes/auth.php

function loginAdmin($username, $password) {
    global $pdo;
    
    try {
        // First check if user exists with given username
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Username not found'];
        }
        
        if ($user['password'] !== $password) {
            return ['success' => false, 'message' => 'Incorrect password'];
        }
        
        if ($user['is_admin'] !== 1) {
            return ['success' => false, 'message' => 'This account does not have admin privileges'];
        }

        $_SESSION['admin'] = true;
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['last_active'] = time();
        return ['success' => true, 'message' => 'Admin login successful'];
    } catch(PDOException $e) {
        error_log("Admin login error: " . $e->getMessage());
        return ['success' => false, 'message' => 'System error during admin login'];
    }
}

function logoutAdmin() {
    return logoutUser(); // Use the same logout function for consistency
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true && validateSession();
}

function isEmailTaken($email) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    } catch(PDOException $e) {
        error_log("Email check error: " . $e->getMessage());
        return false;
    }
}

function logoutUser() {
    try {
        // Clear all session variables
        $_SESSION = array();
        
        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-3600, '/');
        }
        
        // Destroy the session
        session_destroy();
        
        return ['success' => true, 'message' => 'Logout successful'];
    } catch(Exception $e) {
        error_log("Logout error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error during logout'];
    }
}

function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && validateSession();
}

function getCurrentUser() {
    global $pdo;
    
    if (!isUserLoggedIn()) {
        return null;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, is_admin, created_at, updated_at FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        error_log("Get user error: " . $e->getMessage());
        return null;
    }
}

function validateSession() {
    if (!isset($_SESSION['last_active'])) {
        $_SESSION['last_active'] = time();
        return true;
    }
    
    if ((time() - $_SESSION['last_active']) > 3600) {
        logoutUser();
        return false;
    }
    
    $_SESSION['last_active'] = time();
    return true;
}