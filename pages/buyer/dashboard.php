<?php
include '../../backend/includes/session-manager.php';
include '../../backend/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/auth/login.php');
    exit();
}

$product_id = intval($_GET['product_id'] ?? 0);
if (!$product_id) { header('Location: /pages/browse.php'); exit(); }


$stmt = $conn->prepare("
    SELECT p.*, sp.seller_id, sp.business_name, u.username AS seller_name, u.phone AS seller_phone
    FROM products p
    JOIN seller_profiles sp ON p.seller_id = sp.seller_id
    JOIN users u ON sp.user_id = u.user_id
    WHERE p.product_id = ? AND p.is_available = 1
    LIMIT 1
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) { header('Location: /pages/browse.php'); exit(); }

// Prevent seller from buying their own product
if ($_SESSION['user_role'] === 'seller') {
    $chk = $conn->prepare("SELECT seller_id FROM seller_profiles WHERE user_id = ? LIMIT 1");
    $chk->bind_param("i", $_SESSION['user_id']);
    $chk->execute();
    $mySeller = $chk->get_result()->fetch_assoc();
    $chk->close();
    if ($mySeller && $mySeller['seller_id'] == $product['seller_id']) {
        header('Location: /pages/browse.php?error=own_product');
        exit();
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delivery_method  = $_POST['delivery_method']  ?? 'collection';
    $payment_method   = $_POST['payment_method']   ?? 'cash_on_delivery';
    $delivery_address = trim($_POST['delivery_address'] ?? '');
    $buyer_id         = $_SESSION['user_id'];
    $seller_id        = $product['seller_id'];
    $quantity         = 1;

    $delivery_fee = match($delivery_method) {
        'courier' => 80.00,
        'postal'  => 50.00,
        default   => 0.00,
    };

    $total = $product['price'] + $delivery_fee;

    // Insert order
    $ins = $conn->prepare("INSERT INTO orders (buyer_id, product_id, seller_id, quantity, total_price, payment_method, payment_status, delivery_method, delivery_address, order_status) VALUES (?,?,?,?,?,?,'pending',?,?,'pending')");
    $ins->bind_param("iiidssss", $buyer_id, $product_id, $seller_id, $quantity, $total, $payment_method, $delivery_method, $delivery_address);

    if ($ins->execute()) {
        $order_id = $conn->insert_id;
        $ins->close();

        // Insert transaction
        $tr = $conn->prepare("INSERT INTO transactions (order_id, payment_method, amount, transaction_status) VALUES (?,?,?,'pending')");
        $tr->bind_param("isd", $order_id, $payment_method, $total);
        $tr->execute();
        $tr->close();

        // Mark product sold
        $upd = $conn->prepare("UPDATE products SET is_available = 0 WHERE product_id = ?");
        $upd->bind_param("i", $product_id);
        $upd->execute();
        $upd->close();

        $conn->close();
        header("Location: /pages/buyer/order-confirmation.php?order_id=$order_id");
        exit();
    } else {
        $error = 'Order failed: ' . $ins->error;
        $ins->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Masab Trade</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/checkout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body style="background:#f0f2f5; display:block; min-height:100vh;">

<header>
    <div class="container">
        <div class="logo"><h1><a href="/index.php">Masab <span>Trade</span></a></h1></div>
        <nav class="navbar">
            <a href="/pages/browse.php"><i class="fas fa-arrow-left"></i> Back to Browse</a>
            <a href="/backend/auth/logout.php" class="nav-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </div>
</header>

<div class="checkout-container">
    <h1><i class="fas fa-shopping-cart"></i> Checkout</h1>

    <?php if ($error): ?>
        <div style="padding:14px; background:#f8d7da; color:#721c24; border-radius:8px; margin-bottom:20px;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="checkout-grid">

        <!-- ORDER SUMMARY -->
        <div class="order-summary">
            <h2>Order Summary</h2>
            <div class="product-summary-card">
                <img src="../../<?php echo htmlspecialchars($product['image_url'] ?: 'assets/images/products/placeholder.png'); ?>"
                     alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                     onerror="this.src='../../assets/images/products/placeholder.png'">
                <div class="product-summary-info">
                    <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                    <p><i class="fas fa-check-circle"></i> <?php echo ucwords(str_replace('_',' ',$product['condition_status'])); ?></p>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($product['location']); ?></p>
                    <p><i class="fas fa-store"></i> <?php echo htmlspecialchars($product['seller_name']); ?></p>
                </div>
            </div>
            <div class="price-breakdown">
                <div class="price-row"><span>Product Price:</span><span>R <?php echo number_format($product['price'],2); ?></span></div>
                <div class="price-row"><span>Delivery Fee:</span><span id="deliveryFee">R 0.00</span></div>
                <div class="price-row total-row"><span><strong>Total:</strong></span><span id="totalPrice"><strong>R <?php echo number_format($product['price'],2); ?></strong></span></div>
            </div>
        </div>

        <!-- ORDER FORM -->
        <div class="order-form-section">
            <h2>Complete Your Order</h2>
            <form method="POST">

                <!-- DELIVERY METHOD -->
                <div class="form-section">
                    <h3><i class="fas fa-truck"></i> Delivery Method</h3>

                    <div class="radio-option selected" id="opt_collection" onclick="selectDelivery('collection')">
                        <input type="radio" name="delivery_method" id="collection" value="collection" checked>
                        <label for="collection">
                            <span class="option-icon">🏪</span>
                            <div class="option-info">
                                <strong>Community Collection Point</strong>
                                <p>Pick up from nearest collection point</p>
                            </div>
                            <span class="option-price free">FREE</span>
                        </label>
                    </div>

                    <div class="radio-option" id="opt_courier" onclick="selectDelivery('courier')">
                        <input type="radio" name="delivery_method" id="courier" value="courier">
                        <label for="courier">
                            <span class="option-icon">🚚</span>
                            <div class="option-info">
                                <strong>Local Courier</strong>
                                <p>Delivered to your door within 3-5 days</p>
                            </div>
                            <span class="option-price">R 80.00</span>
                        </label>
                    </div>

                    <div class="radio-option" id="opt_postal" onclick="selectDelivery('postal')">
                        <input type="radio" name="delivery_method" id="postal" value="postal">
                        <label for="postal">
                            <span class="option-icon">📦</span>
                            <div class="option-info">
                                <strong>SA Post Office</strong>
                                <p>Postal service within 5-7 days</p>
                            </div>
                            <span class="option-price">R 50.00</span>
                        </label>
                    </div>
                </div>

                <!-- DELIVERY ADDRESS -->
                <div class="form-section">
                    <h3><i class="fas fa-map-marker-alt"></i> Delivery / Collection Address</h3>
                    <div class="form-group">
                        <textarea name="delivery_address" placeholder="Enter your full address e.g. 112 Ford Street, Hammanskraal, Pretoria, 0400" required rows="3"></textarea>
                    </div>
                </div>

                <!-- PAYMENT METHOD -->
                <div class="form-section">
                    <h3><i class="fas fa-credit-card"></i> Payment Method</h3>

                    <div class="radio-option selected" id="opt_cod" onclick="selectPayment('cod')">
                        <input type="radio" name="payment_method" id="cod" value="cash_on_delivery" checked>
                        <label for="cod">
                            <span class="option-icon">💵</span>
                            <div class="option-info">
                                <strong>Cash on Delivery</strong>
                                <p>Pay cash when your item arrives</p>
                            </div>
                            <span class="option-badge safe">Most Popular</span>
                        </label>
                    </div>

                    <div class="radio-option" id="opt_mobile" onclick="selectPayment('mobile')">
                        <input type="radio" name="payment_method" id="mobile_money" value="mobile_money">
                        <label for="mobile_money">
                            <span class="option-icon">📱</span>
                            <div class="option-info">
                                <strong>Mobile Money</strong>
                                <p>MTN Mobile Money or Vodacom M-Pesa</p>
                            </div>
                            <span class="option-badge instant">Instant</span>
                        </label>
                    </div>

                    <div class="radio-option" id="opt_eft" onclick="selectPayment('eft')">
                        <input type="radio" name="payment_method" id="eft" value="eft">
                        <label for="eft">
                            <span class="option-icon">🏦</span>
                            <div class="option-info">
                                <strong>EFT / Bank Transfer</strong>
                                <p>Direct bank transfer</p>
                            </div>
                            <span class="option-badge">1-2 Days</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="place-order-btn">
                    <i class="fas fa-lock"></i> Place Order Securely
                </button>
                <p class="secure-note"><i class="fas fa-shield-alt"></i> Protected by Masab Trade Buyer Protection</p>
            </form>
        </div>
    </div>
</div>

<div id="sessionWarning"></div>

<script>
const basePrice = <?php echo $product['price']; ?>;

function selectDelivery(type) {
    ['collection','courier','postal'].forEach(t => {
        document.getElementById('opt_' + t).classList.remove('selected');
    });
    document.getElementById('opt_' + type).classList.add('selected');
    document.getElementById(type === 'collection' ? 'collection' : type).checked = true;

    const fees = { collection: 0, courier: 80, postal: 50 };
    const fee  = fees[type] || 0;
    const total = basePrice + fee;

    document.getElementById('deliveryFee').textContent  = 'R ' + fee.toFixed(2);
    document.getElementById('totalPrice').innerHTML     = '<strong>R ' + total.toFixed(2) + '</strong>';
}

function selectPayment(type) {
    ['cod','mobile','eft'].forEach(t => {
        document.getElementById('opt_' + t).classList.remove('selected');
    });
    document.getElementById('opt_' + type).classList.add('selected');
    const map = { cod: 'cod', mobile: 'mobile_money', eft: 'eft' };
    document.getElementById(map[type]).checked = true;
}
</script>

<script src="../../js/main.js"></script>
</body>
</html>