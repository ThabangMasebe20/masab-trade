<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include 'backend/includes/session-manager.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masab Trade - Buy & Sell in Your Community</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header>
    <div class="container">
        <div class="logo">
            <h1><a href="/index.php">Masab <span>Trade</span></a></h1>            
        </div>

        <nav class="navbar">
            <a href="/index.php" class="active"><i class="fas fa-home"></i> <span data-translate="header.home">Home</span></a>
            <a href="/pages/browse.php"><i class="fas fa-shopping-bag"></i> <span data-translate="header.buy">Buy</span></a>
            <a href="/pages/add-product.php"><i class="fas fa-tags"></i> <span data-translate="header.sell">Sell</span></a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="nav-username"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></span>

                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a href="/pages/admin/dashboard.php"><i class="fas fa-user-shield"></i> Admin</a>
                <?php elseif ($_SESSION['user_role'] === 'seller'): ?>
                    <a href="/pages/seller/dashboard.php"><i class="fas fa-store"></i> Dashboard</a>
                <?php else: ?>
                    <a href="/pages/buyer/dashboard.php"><i class="fas fa-user"></i> My Account</a>
                <?php endif; ?>

                <a href="/backend/auth/logout.php" class="nav-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="/pages/auth/login.php" class="nav-login"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="/pages/auth/register.php" class="nav-register"><i class="fas fa-user-plus"></i> Register</a>
            <?php endif; ?>
        </nav>

        <div class="language-switcher">
            <select id="languageSelect" onchange="changeLanguage(this.value)">
                <option value="en">English</option>
                <option value="zu">isiZulu</option>
                <option value="xh">isiXhosa</option>
                <option value="st">Sepedi</option>
                <option value="af">Afrikaans</option>
            </select>
        </div>
    </div>
</header>

<section class="hero">
    <div class="hero-content">
        <h2 data-translate="hero.title">Buy and Sell Safely in Your Community</h2>
        <p data-translate="hero.subtitle">Connect with local sellers and buyers. Trade with trust.</p>
        <div class="hero-buttons">
            <a href="/pages/browse.php" class="btn btn-primary">
                <i class="fas fa-search"></i>
                <span data-translate="hero.start_shopping">Start Shopping</span>
            </a>
            <a href="/pages/add-product.php" class="btn btn-secondary">
                <i class="fas fa-upload"></i>
                <span data-translate="hero.sell_items">Sell Your Items</span>
            </a>
        </div>
    </div>
</section>

<section class="features">
    <div class="container">
        <h2 class="section-title" data-translate="features.title">Why Choose Masab Trade?</h2>
        <div class="feature-grid">
            <div class="feature-card">
                <i class="fas fa-shield-alt"></i>
                <h3 data-translate="features.secure_payments">Secure Payments</h3>
                <p data-translate="features.secure_payments_desc">Cash-on-delivery and mobile money options</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-star"></i>
                <h3 data-translate="features.trusted_sellers">Trusted Sellers</h3>
                <p data-translate="features.trusted_sellers_desc">Rate and review sellers to build community trust</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-truck"></i>
                <h3 data-translate="features.local_delivery">Local Delivery</h3>
                <p data-translate="features.local_delivery_desc">Community collection points and local couriers</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-language"></i>
                <h3 data-translate="features.your_language">Your Language</h3>
                <p data-translate="features.your_language_desc">Available in 5 South African languages</p>
            </div>
        </div>
    </div>
</section>

<section class="how-it-works">
    <div class="container">
        <h2 class="section-title">How It Works</h2>
        <div class="steps-grid">
            <div class="step">
                <div class="step-number">1</div>
                <h3>Create Account</h3>
                <p>Sign up for free in under 2 minutes</p>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <h3>List or Browse</h3>
                <p>Upload items to sell or search for what you need</p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <h3>Connect & Trade</h3>
                <p>Message sellers, agree on terms, complete transaction</p>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <h3>Rate & Review</h3>
                <p>Share your experience to help the community</p>
            </div>
        </div>
    </div>
</section>

<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-column">
                <h3>About Masab Trade</h3>
                <p>Empowering South Africa's informal economy through secure digital commerce, aligned with the Township Economy vision.</p>
            </div>
            <div class="footer-column">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="/pages/privacy-policy.php"><i class="fas fa-chevron-right"></i> Privacy Policy</a></li>
                    <li><a href="/pages/terms-of-service.php"><i class="fas fa-chevron-right"></i> Terms of Service</a></li>
                    <li><a href="/pages/help-center.php"><i class="fas fa-chevron-right"></i> Help Center</a></li>
                    <li><a href="/pages/seller-guide.php"><i class="fas fa-chevron-right"></i> Seller Guide</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Contact Us</h3>
                <p><i class="fas fa-envelope"></i> support@masabtrade.co.za</p>
                <p><i class="fas fa-phone"></i> +27 123 456 789</p>
                <p><i class="fas fa-map-marker-alt"></i> Cape Town, South Africa</p>
            </div>
            <div class="footer-column">
                <h3>Follow Us</h3>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 Masab Trade. All rights reserved. Built for South Africa's Township Economy.</p>
        </div>
    </div>
</footer>

<div id="sessionWarning"></div>

<script src="js/main.js"></script>
<script src="js/language.js"></script>
</body>
</html>