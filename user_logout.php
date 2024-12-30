<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Call the logout function
$result = logoutUser();

// Redirect to home page after logout
header('Location: homePage.php');
exit();