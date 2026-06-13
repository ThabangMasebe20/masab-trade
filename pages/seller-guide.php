<?php include '../backend/includes/session-manager.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Guide - Masab Trade</title>
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
            <a href="/pages/add-product.php"><i class="fas fa-tags"></i> Sell</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/pages/seller/dashboard.php"><i class="fas fa-store"></i> Dashboard</a>
                <a href="/backend/auth/logout.php" class="nav-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="/pages/auth/register.php?from=seller&role=seller" class="nav-register"><i class="fas fa-user-plus"></i> Start Selling</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<div class="info-container">
    <div class="info-hero seller-hero">
        <i class="fas fa-store"></i>
        <h1>Seller Guide</h1>
        <p>Everything you need to know to succeed on Masab Trade</p>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="/pages/auth/register.php?from=seller&role=seller" class="hero-cta-btn">
                <i class="fas fa-rocket"></i> Start Selling Today - It's Free!
            </a>
        <?php endif; ?>
    </div>

    <div class="info-content">

        <!-- STEP BY STEP -->
        <div class="info-section">
            <h2><i class="fas fa-list-ol"></i> How to Start Selling in 4 Easy Steps</h2>

            <div class="steps-visual">
                <div class="step-visual">
                    <div class="step-num">1</div>
                    <div class="step-detail">
                        <h3>Create Your Seller Account</h3>
                        <p>Register for free at Masab Trade. Select "Sell Products" as your account type. Your seller profile is created automatically — no extra steps needed.</p>
                    </div>
                </div>
                <div class="step-visual">
                    <div class="step-num">2</div>
                    <div class="step-detail">
                        <h3>List Your First Product</h3>
                        <p>Click "Sell" in the navigation or "Add Product" in your dashboard. Add a clear title, description, price, condition, your location, and a photo.</p>
                    </div>
                </div>
                <div class="step-visual">
                    <div class="step-num">3</div>
                    <div class="step-detail">
                        <h3>Receive Orders</h3>
                        <p>When a buyer purchases your product, it appears in your Sales History. You'll see the buyer's contact details, delivery address, and payment method.</p>
                    </div>
                </div>
                <div class="step-visual">
                    <div class="step-num">4</div>
                    <div class="step-detail">
                        <h3>Complete the Sale</h3>
                        <p>Contact the buyer via WhatsApp to arrange delivery or collection. Update the order status in your dashboard as it progresses.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- TIPS -->
        <div class="info-section">
            <h2><i class="fas fa-lightbulb"></i> Tips for Success</h2>

            <div class="tips-grid">
                <div class="tip-card">
                    <i class="fas fa-camera"></i>
                    <h3>Take Great Photos</h3>
                    <p>Use good lighting and take photos from multiple angles. Listings with clear, high-quality photos get up to 3x more views than those without.</p>
                </div>
                <div class="tip-card">
                    <i class="fas fa-pen"></i>
                    <h3>Write Clear Descriptions</h3>
                    <p>Include the brand, model, size, colour, and any defects. The more detail you provide, the more confident buyers feel about purchasing.</p>
                </div>
                <div class="tip-card">
                    <i class="fas fa-tag"></i>
                    <h3>Price Competitively</h3>
                    <p>Browse similar items on the platform before setting your price. Fair pricing leads to faster sales and better buyer reviews.</p>
                </div>
                <div class="tip-card">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Add Your Location</h3>
                    <p>Buyers prefer sellers nearby for easier collection. Adding your township or area increases trust and local sales.</p>
                </div>
                <div class="tip-card">
                    <i class="fas fa-reply"></i>
                    <h3>Respond Quickly</h3>
                    <p>Fast responses via WhatsApp show buyers you are reliable. Aim to respond within 2 hours during the day.</p>
                </div>
                <div class="tip-card">
                    <i class="fas fa-star"></i>
                    <h3>Build Your Reputation</h3>
                    <p>Deliver quality items as described and follow through on commitments. Good buyer reviews will bring more sales over time.</p>
                </div>
            </div>
        </div>

        <!-- WHAT TO SELL -->
        <div class="info-section">
            <h2><i class="fas fa-boxes"></i> What Can You Sell?</h2>
            <p>Masab Trade is designed for everyday South Africans to sell items from their homes or small businesses. Popular categories include:</p>

            <div class="categories-grid">
                <div class="cat-item"><i class="fas fa-mobile-alt"></i><span>Electronics</span></div>
                <div class="cat-item"><i class="fas fa-tshirt"></i><span>Clothing & Fashion</span></div>
                <div class="cat-item"><i class="fas fa-couch"></i><span>Home & Kitchen</span></div>
                <div class="cat-item"><i class="fas fa-book"></i><span>Books & Media</span></div>
                <div class="cat-item"><i class="fas fa-futbol"></i><span>Sports & Outdoor</span></div>
                <div class="cat-item"><i class="fas fa-gamepad"></i><span>Toys & Games</span></div>
                <div class="cat-item"><i class="fas fa-spa"></i><span>Beauty & Health</span></div>
                <div class="cat-item"><i class="fas fa-ellipsis-h"></i><span>Other Items</span></div>
            </div>
        </div>

        <!-- PROHIBITED -->
        <div class="info-section">
            <h2><i class="fas fa-ban"></i> What Cannot Be Sold</h2>
            <div class="prohibited-list">
                <div class="prohibited-item"><i class="fas fa-times-circle"></i><span>Counterfeit or fake branded goods</span></div>
                <div class="prohibited-item"><i class="fas fa-times-circle"></i><span>Stolen property</span></div>
                <div class="prohibited-item"><i class="fas fa-times-circle"></i><span>Illegal weapons or dangerous items</span></div>
                <div class="prohibited-item"><i class="fas fa-times-circle"></i><span>Drugs or controlled substances</span></div>
                <div class="prohibited-item"><i class="fas fa-times-circle"></i><span>Adult content</span></div>
                <div class="prohibited-item"><i class="fas fa-times-circle"></i><span>Items prohibited by South African law</span></div>
            </div>
        </div>

        <!-- CTA -->
        <div class="info-section cta-section">
            <h2>Ready to Start Selling?</h2>
            <p>Join thousands of South African sellers who are growing their income on Masab Trade.</p>
            <div style="display:flex; gap:15px; flex-wrap:wrap; margin-top:20px;">
                <a href="/pages/auth/register.php?from=seller&role=seller" class="hero-cta-btn">
                    <i class="fas fa-user-plus"></i> Register as Seller
                </a>
                <a href="/pages/add-product.php" class="hero-cta-btn" style="background:white; color:#667eea; border:2px solid #667eea;">
                    <i class="fas fa-plus"></i> List a Product
                </a>
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
        <div class="footer-bottom"><p>&copy; 2026 Masab Trade. All rights reserved.</p></div>
    </div>
</footer>

</body>
</html>