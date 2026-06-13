<?php include '../backend/includes/session-manager.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Masab Trade</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/info-pages.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header>
    <div class="container">
        <div class="logo"><h1><a href="/index.php">Masab <span style="color:#667eea">Trade</span></a></h1></div>
        <nav class="navbar">
            <a href="/index.php"><i class="fas fa-home"></i> Home</a>
            <a href="/pages/browse.php"><i class="fas fa-shopping-bag"></i> Browse</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/backend/auth/logout.php" class="nav-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="/pages/auth/login.php" class="nav-login"><i class="fas fa-sign-in-alt"></i> Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<div class="info-container">
    <div class="info-hero">
        <i class="fas fa-shield-alt"></i>
        <h1>Privacy Policy</h1>
        <p>Last updated: January 2026</p>
    </div>

    <div class="info-content">

        <div class="info-section">
            <h2><i class="fas fa-info-circle"></i> 1. Introduction</h2>
            <p>Masab Trade ("we," "our," or "us") is committed to protecting the privacy and personal information of all users on our platform. This Privacy Policy explains how we collect, use, store, and protect your information when you use our C2C e-commerce platform, which serves South Africa's township economy.</p>
            <p>By using Masab Trade, you agree to the collection and use of information as described in this policy.</p>
        </div>

        <div class="info-section">
            <h2><i class="fas fa-database"></i> 2. Information We Collect</h2>
            <ul>
                <li><strong>Account Information:</strong> Username, email address, phone number, and password (stored encrypted)</li>
                <li><strong>Profile Information:</strong> Business name, location, and seller description (for sellers)</li>
                <li><strong>Transaction Data:</strong> Order details, payment method selections, and delivery addresses</li>
                <li><strong>Product Information:</strong> Listings, descriptions, prices, and product images you upload</li>
                <li><strong>Communication Data:</strong> Messages sent between buyers and sellers on the platform</li>
                <li><strong>Usage Data:</strong> How you interact with our platform, search terms, and browsing behavior</li>
            </ul>
        </div>

        <div class="info-section">
            <h2><i class="fas fa-cogs"></i> 3. How We Use Your Information</h2>
            <ul>
                <li>To create and manage your account on Masab Trade</li>
                <li>To facilitate transactions between buyers and sellers</li>
                <li>To send order confirmations and transaction updates</li>
                <li>To improve our platform and user experience</li>
                <li>To prevent fraud and ensure platform security</li>
                <li>To comply with South African legal requirements (POPIA)</li>
                <li>To support sellers in growing their businesses</li>
            </ul>
        </div>

        <div class="info-section">
            <h2><i class="fas fa-share-alt"></i> 4. Information Sharing</h2>
            <p>We do <strong>not</strong> sell your personal information to third parties. We may share limited information in the following circumstances:</p>
            <ul>
                <li><strong>Between Buyers and Sellers:</strong> Contact details shared only when a transaction is confirmed</li>
                <li><strong>Legal Requirements:</strong> When required by South African law or court order</li>
                <li><strong>Platform Safety:</strong> To prevent fraud, abuse, or illegal activities</li>
            </ul>
        </div>

        <div class="info-section">
            <h2><i class="fas fa-lock"></i> 5. Data Security</h2>
            <p>We implement industry-standard security measures including:</p>
            <ul>
                <li>Password encryption using bcrypt hashing</li>
                <li>Secure HTTPS connections for all data transmission</li>
                <li>Regular security audits of our platform</li>
                <li>Limited access to personal data by staff</li>
                <li>Session timeout after 5 minutes of inactivity</li>
            </ul>
        </div>

        <div class="info-section">
            <h2><i class="fas fa-user-check"></i> 6. Your Rights (POPIA)</h2>
            <p>Under the Protection of Personal Information Act (POPIA), you have the right to:</p>
            <ul>
                <li>Access your personal information held by us</li>
                <li>Correct inaccurate personal information</li>
                <li>Request deletion of your account and data</li>
                <li>Object to the processing of your personal information</li>
                <li>Lodge a complaint with the Information Regulator of South Africa</li>
            </ul>
        </div>

        <div class="info-section">
            <h2><i class="fas fa-envelope"></i> 7. Contact Us</h2>
            <p>For any privacy-related questions or requests, please contact us:</p>
            <div class="contact-info-box">
                <p><i class="fas fa-envelope"></i> privacy@masabtrade.co.za</p>
                <p><i class="fas fa-phone"></i> +27 123 456 789</p>
                <p><i class="fas fa-map-marker-alt"></i> Cape Town, South Africa</p>
            </div>
        </div>

    </div>
</div>

<footer>
    <div class="container">
        <div class="footer-content">
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
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 Masab Trade. All rights reserved.</p>
        </div>
    </div>
</footer>

</body>
</html>