<?php include '../backend/includes/session-manager.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - Masab Trade</title>
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
        <i class="fas fa-file-contract"></i>
        <h1>Terms of Service</h1>
        <p>Last updated: January 2026</p>
    </div>

    <div class="info-content">

        <div class="info-section">
            <h2><i class="fas fa-handshake"></i> 1. Agreement to Terms</h2>
            <p>By accessing and using Masab Trade, you agree to be bound by these Terms of Service. Masab Trade is a Consumer-to-Consumer (C2C) e-commerce platform designed to support South Africa's township economy and informal traders.</p>
            <p>If you do not agree with any part of these terms, please do not use our platform.</p>
        </div>

        <div class="info-section">
            <h2><i class="fas fa-users"></i> 2. User Accounts</h2>
            <ul>
                <li>You must be 18 years or older to create an account on Masab Trade</li>
                <li>You are responsible for maintaining the security of your account and password</li>
                <li>You agree to provide accurate and truthful information during registration</li>
                <li>One person may only maintain one active account on the platform</li>
                <li>Masab Trade reserves the right to suspend accounts that violate these terms</li>
            </ul>
        </div>

        <div class="info-section">
            <h2><i class="fas fa-store"></i> 3. Seller Responsibilities</h2>
            <ul>
                <li><strong>Accurate Listings:</strong> All product descriptions, prices, and images must be truthful and accurate</li>
                <li><strong>Legal Products:</strong> Sellers may only list items that are legal to sell in South Africa</li>
                <li><strong>Product Condition:</strong> Condition descriptions must accurately reflect the item's state</li>
                <li><strong>Timely Communication:</strong> Sellers must respond to buyer inquiries within 24 hours</li>
                <li><strong>Fulfillment:</strong> Sellers are obligated to complete transactions they agree to</li>
                <li><strong>Prohibited Items:</strong> No counterfeit goods, stolen property, weapons, drugs, or illegal items</li>
            </ul>
        </div>

        <div class="info-section">
            <h2><i class="fas fa-shopping-cart"></i> 4. Buyer Responsibilities</h2>
            <ul>
                <li>Buyers must complete payments as agreed with the seller</li>
                <li>Buyers must provide accurate delivery addresses</li>
                <li>Buyers agree to communicate respectfully with sellers</li>
                <li>Buyers must report fraudulent listings promptly</li>
                <li>Buyers acknowledge that Masab Trade facilitates but does not guarantee transactions</li>
            </ul>
        </div>

        <div class="info-section">
            <h2><i class="fas fa-credit-card"></i> 5. Payments and Transactions</h2>
            <p>Masab Trade supports the following payment methods:</p>
            <ul>
                <li><strong>Cash on Delivery (COD):</strong> Payment in cash upon receipt of goods</li>
                <li><strong>Mobile Money:</strong> Via MTN Mobile Money or Vodacom M-Pesa</li>
                <li><strong>EFT/Bank Transfer:</strong> Direct electronic funds transfer</li>
            </ul>
            <p>Masab Trade is not a payment processor and does not hold funds. All payment disputes are between the buyer and seller directly.</p>
        </div>

        <div class="info-section">
            <h2><i class="fas fa-exclamation-triangle"></i> 6. Prohibited Activities</h2>
            <ul>
                <li>Listing stolen, counterfeit, or illegal goods</li>
                <li>Harassment or threatening behavior toward other users</li>
                <li>Creating fake reviews or manipulating ratings</li>
                <li>Using the platform for money laundering or fraud</li>
                <li>Attempting to bypass platform security measures</li>
                <li>Spamming other users with unsolicited messages</li>
            </ul>
        </div>

        <div class="info-section">
            <h2><i class="fas fa-gavel"></i> 7. Dispute Resolution</h2>
            <p>In the event of a dispute between buyers and sellers:</p>
            <ul>
                <li>Both parties should first attempt to resolve the issue directly</li>
                <li>If unresolved, contact Masab Trade support at support@masabtrade.co.za</li>
                <li>Masab Trade will investigate and mediate disputes fairly</li>
                <li>These terms are governed by the laws of South Africa</li>
            </ul>
        </div>

        <div class="info-section">
            <h2><i class="fas fa-envelope"></i> 8. Contact</h2>
            <div class="contact-info-box">
                <p><i class="fas fa-envelope"></i> legal@masabtrade.co.za</p>
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
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 Masab Trade. All rights reserved.</p>
        </div>
    </div>
</footer>

</body>
</html>