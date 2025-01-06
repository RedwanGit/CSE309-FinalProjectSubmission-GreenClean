<?php
header('Content-Type: text/html; charset=UTF-8');
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenClean - Contact Us</title>
    <link rel="stylesheet" href="css/themes.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/contactPage.css">
</head>
<body>
    <!-- Placeholder for Navbar -->
    <?php include 'navbar.php'; ?>

    <main class="main" role="main">
        <section class="contact-section">
            <div class="container">
                <h1 class="page-title">Contact Us</h1>
                <p class="contact-description">
                    We'd love to hear from you! Whether you have a question about our products, need assistance, or just want to say hello, feel free to reach out.
                </p>

                <div class="contact-container">
                    <form id="contact-form" class="contact-form" novalidate>
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" required>
                            <span class="error-message">Please enter your name</span>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                            <span class="error-message">Please enter a valid email address</span>
                        </div>

                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="6" required></textarea>
                            <span class="error-message">Please enter your message</span>
                        </div>

                        <button type="submit" class="submit-btn">Send Message</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 GreenClean. All rights reserved.</p>
        </div>
    </footer>

    <script src="js/navbar.js"></script>
    <script src="js/contactPage.js"></script>
    <script src="js/themeManager.js"></script>
</body>
</html>
