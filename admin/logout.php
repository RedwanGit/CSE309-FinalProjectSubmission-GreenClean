<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Check if user is logged in before attempting logout
if (isAdminLoggedIn()) {
    // Perform logout
    logoutAdmin();
    
    // Clear any output buffers to ensure proper redirection
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Ensure no output has been sent before redirect
    if (!headers_sent()) {
        header("Location: index.php");
        exit();
    } else {
        echo '<script>window.location.href = "index.php";</script>';
        exit();
    }
} else {
    // If not logged in, redirect to admin index
    header("Location: index.php");
    exit();
}
?>
