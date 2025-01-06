<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenClean - Navbar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/themes.css?v=3">
    <link rel="stylesheet" href="css/navbar.css?v=3">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="nav-links">
                <a href="homePage.php" class="nav-link" data-page="home">
                    <i class="fas fa-home icon"></i>
                    <span>Home</span>
                </a>
                <a href="productPage.php" class="nav-link" data-page="products">
                    <i class="fas fa-box icon"></i>
                    <span>Products</span>
                </a>
                <a href="contactPage.php" class="nav-link" data-page="contact">
                    <i class="fas fa-envelope icon"></i>
                    <span>Contact</span>
                </a>
                <div class="account-dropdown">
                    <div class="nav-link">
                        <i class="fas fa-user icon"></i>
                        <span>Account</span>
                    </div>
                    <div class="dropdown-content">
                        <?php if (!isUserLoggedIn()): ?>
                            <a href="authPage.php" class="dropdown-item" data-page="auth">
                                Login/Signup
                            </a>
                            <?php else: ?>
                                <a href="profilePage.php" class="dropdown-item" data-page="profile">
                                    Profile
                                </a>
                                <a href="ordersPage.php" class="dropdown-item" data-page="orders">
                                    My Orders
                                </a>
                                <a href="user_logout.php" class="dropdown-item" data-page="logout">
                                    Sign Out
                                </a>
                            <?php endif; ?>
                    </div>
                </div>
                <button class="theme-toggle">
                    <i class="fas fa-moon icon"></i>
                </button>
                <a href="cartPage.php" class="cart-link">
                    <i class="fas fa-shopping-cart icon"></i>
                </a>
            </div>
        </div>
    </nav>
    <script src="js/navbar.js"></script>
</body>
</html>