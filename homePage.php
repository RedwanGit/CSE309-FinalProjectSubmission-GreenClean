<?php
header('Content-Type: text/html; charset=UTF-8');
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenClean - Home</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/homePage.css?v=2">
    <link rel="stylesheet" href="css/themes.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js"></script>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <main class="main" role="main">
        <section class="hero">
            <div class="container">
                <h1>Welcome to GreenClean</h1>
                <p class="hero-subtitle">Eco-friendly cleaning solutions for a healthier home and planet. <br>Along with personal care items for you!</p>
            </div>
        </section>
        <section class="features">
            <div class="container">
                <div class="feature-grid">
                    <div class="feature-card">
                        <div class="feature-image">
                            <img src="images/homePage/eco-friendly.jpg" alt="Eco-friendly">
                        </div>
                        <div class="feature-content">
                            <h2>Eco-friendly</h2>
                            <p>Our products are made from natural, biodegradable ingredients that are safe for the environment.</p>
                        </div>
                    </div>
                    <div class="feature-card">
                        <div class="feature-image">
                            <img src="images/homePage/effective.webp" alt="Effective">
                        </div>
                        <div class="feature-content">
                            <h2>Effective</h2>
                            <p>Powerful cleaning action that rivals traditional chemical cleaners, without the harmful side effects.</p>
                        </div>
                    </div>
                    <div class="feature-card">
                        <div class="feature-image">
                            <img src="images/homePage/affordable.jpg" alt="Affordable">
                        </div>
                        <div class="feature-content">
                            <h2>Affordable</h2>
                            <p>High-quality, eco-friendly cleaning doesn't have to break the bank. Our products are competitively priced.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> GreenClean. All rights reserved.</p>
        </div>
    </footer>
    <script src="js/navbar.js"></script>
    <script src="js/themeManager.js" defer></script>
</body>
</html>