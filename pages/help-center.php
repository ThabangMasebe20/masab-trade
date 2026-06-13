<?php include '../backend/includes/session-manager.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center - Masab Trade</title>
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
        <i class="fas fa-life-ring"></i>
        <h1>Help Center</h1>
        <p>Find answers to your questions about Masab Trade</p>
    </div>

    <!-- QUICK HELP CARDS -->
    <div class="help-cards">
        <div class="help-card">
            <i class="fas fa-user-plus"></i>
            <h3>Getting Started</h3>
            <p>Learn how to create an account and start buying or selling</p>
            <a href="#getting-started">Learn More</a>
        </div>
        <div class="help-card">
            <i class="fas fa-shopping-cart"></i>
            <h3>Buying on Masab Trade</h3>
            <p>How to browse, buy, and track your orders</p>
            <a href="#buying">Learn More</a>
        </div>
        <div class="help-card">
            <i class="fas fa-store"></i>
            <h3>Selling on Masab Trade</h3>
            <p>How to list products and manage your sales</p>
            <a href="#selling">Learn More</a>
        </div>
        <div class="help-card">
            <i class="fas fa-credit-card"></i>
            <h3>Payments & Delivery</h3>
            <p>Understanding payment options and delivery methods</p>
            <a href="#payments">Learn More</a>
        </div>
    </div>

    <div class="info-content">

        <div class="info-section" id="getting-started">
            <h2><i class="fas fa-user-plus"></i> Getting Started</h2>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>How do I create an account?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Click the <strong>Register</strong> button in the top navigation bar. Fill in your username, email address, phone number, and choose whether you want to buy or sell. Create a password of at least 6 characters and you're ready to go!</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Can I both buy and sell on Masab Trade?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Currently you register as either a buyer or a seller. If you want to do both, you can register two separate accounts with different email addresses.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Is it free to use Masab Trade?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Yes! Creating an account and listing products is completely free. Masab Trade is committed to empowering South Africa's township economy with no barriers to entry.</p>
                </div>
            </div>
        </div>

        <div class="info-section" id="buying">
            <h2><i class="fas fa-shopping-cart"></i> Buying on Masab Trade</h2>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>How do I purchase a product?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <ol>
                        <li>Browse products on the <strong>Buy</strong> page</li>
                        <li>Click <strong>View Details</strong> on any product you like</li>
                        <li>Click the <strong>Buy Now</strong> button</li>
                        <li>Log in or register if not already signed in</li>
                        <li>Choose your delivery method and address</li>
                        <li>Select your payment method</li>
                        <li>Click <strong>Place Order Securely</strong></li>
                    </ol>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>How do I track my order?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>After placing an order, go to <strong>My Account → My Orders</strong> to see the status of all your orders. You can also contact the seller directly via the WhatsApp button on your order.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>What if I have a problem with my order?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>First, contact the seller directly using the WhatsApp button on your order. If the issue is not resolved, email us at <strong>support@masabtrade.co.za</strong> with your order number and we will assist.</p>
                </div>
            </div>
        </div>

        <div class="info-section" id="selling">
            <h2><i class="fas fa-store"></i> Selling on Masab Trade</h2>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>How do I list a product?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <ol>
                        <li>Register as a <strong>Seller</strong> or log in to your seller account</li>
                        <li>Click <strong>Sell</strong> in the navigation or <strong>Add Product</strong> in your dashboard</li>
                        <li>Fill in the product name, category, price, condition, and description</li>
                        <li>Add your location and upload a clear product photo</li>
                        <li>Click <strong>List My Product</strong></li>
                    </ol>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>How do I know when someone buys my product?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Go to your <strong>Seller Dashboard → Sales History</strong> to see all orders. The buyer's contact details will be available so you can coordinate delivery or collection.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>How do I update an order status?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>In <strong>Sales History</strong>, each order has a status dropdown. Update it from Pending → Confirmed → Shipped → Delivered as the order progresses. Marking as Delivered will automatically mark Cash on Delivery orders as paid.</p>
                </div>
            </div>
        </div>

        <div class="info-section" id="payments">
            <h2><i class="fas fa-credit-card"></i> Payments & Delivery</h2>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>What payment methods are available?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <ul>
                        <li><strong>Cash on Delivery (COD):</strong> Pay cash when item arrives - most popular</li>
                        <li><strong>Mobile Money:</strong> MTN Mobile Money or Vodacom M-Pesa</li>
                        <li><strong>EFT/Bank Transfer:</strong> Direct bank transfer to seller</li>
                    </ul>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>What delivery options are available?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <ul>
                        <li><strong>Community Collection Point (FREE):</strong> Pick up from a nearby local collection point</li>
                        <li><strong>Local Courier (R80):</strong> Delivered to your door in 3-5 business days</li>
                        <li><strong>SA Post Office (R50):</strong> Standard postal service in 5-7 days</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- CONTACT SUPPORT -->
        <div class="info-section support-section">
            <h2><i class="fas fa-headset"></i> Still Need Help?</h2>
            <p>Our support team is here to help you. Reach out to us:</p>
            <div class="contact-info-box">
                <p><i class="fas fa-envelope"></i> support@masabtrade.co.za</p>
                <p><i class="fas fa-phone"></i> +27 123 456 789</p>
                <p><i class="fab fa-whatsapp"></i> WhatsApp: +27 123 456 789</p>
                <p><i class="fas fa-clock"></i> Support Hours: Mon-Fri, 8am - 6pm</p>
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

<script>
function toggleFaq(el) {
    const answer  = el.nextElementSibling;
    const icon    = el.querySelector('i');
    const isOpen  = answer.style.display === 'block';
    answer.style.display = isOpen ? 'none' : 'block';
    icon.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
}
</script>

</body>
</html>