<?php
include '../../backend/includes/session-manager.php';
include '../../backend/config/database.php';

if (!isset($_SESSION['user_id'])) {
    $pid = intval($_GET['product_id'] ?? 0);
    header("Location: /pages/auth/login.php?redirect=checkout&pid=$pid");
    exit();
}

$product_id = intval($_GET['product_id'] ?? 0);
if (!$product_id) {
    header('Location: /pages/browse.php');
    exit();
}

$stmt = $conn->prepare("
    SELECT p.*, sp.seller_id, sp.business_name,
           u.username AS seller_name, u.phone AS seller_phone
    FROM products p
    JOIN seller_profiles sp ON p.seller_id = sp.seller_id
    JOIN users u ON sp.user_id = u.user_id
    WHERE p.product_id = ? AND p.is_available = 1 AND p.quantity > 0
    LIMIT 1
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: /pages/browse.php?msg=unavailable');
    exit();
}

// Prevent seller buying their own products
if ($_SESSION['user_role'] === 'seller' || $_SESSION['user_role'] === 'admin') {
    $chk = $conn->prepare("SELECT seller_id FROM seller_profiles WHERE user_id = ? LIMIT 1");
    $chk->bind_param("i", $_SESSION['user_id']);
    $chk->execute();
    $my_seller = $chk->get_result()->fetch_assoc();
    $chk->close();
    if ($my_seller && $my_seller['seller_id'] == $product['seller_id']) {
        header('Location: /pages/browse.php?msg=own');
        exit();
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delivery_method  = $_POST['delivery_method']  ?? 'collection';
    $payment_method   = $_POST['payment_method']   ?? 'cash_on_delivery';
    $delivery_address = trim($_POST['delivery_address'] ?? '');
    $qty_ordered      = 1;
    $buyer_id         = $_SESSION['user_id'];
    $seller_id        = $product['seller_id'];

    if (!$delivery_address) {
        $error = 'Please enter your delivery or collection address.';
    } else {
        $fees         = ['collection' => 0.00, 'courier' => 80.00, 'postal' => 50.00];
        $delivery_fee = $fees[$delivery_method] ?? 0.00;
        $total        = $product['price'] + $delivery_fee;

        $ins = $conn->prepare("
            INSERT INTO orders
                (buyer_id, product_id, seller_id, quantity, total_price,
                 payment_method, payment_status, delivery_method, delivery_address, order_status)
            VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, 'pending')
        ");
        $ins->bind_param("iiidssss",
            $buyer_id, $product_id, $seller_id, $qty_ordered,
            $total, $payment_method, $delivery_method, $delivery_address
        );

        if ($ins->execute()) {
            $order_id = $conn->insert_id;
            $ins->close();

            // Insert transaction record
            $tr = $conn->prepare("INSERT INTO transactions (order_id, payment_method, amount, transaction_status) VALUES (?, ?, ?, 'pending')");
            $tr->bind_param("isd", $order_id, $payment_method, $total);
            $tr->execute();
            $tr->close();

            
            $new_qty   = intval($product['quantity']) - $qty_ordered;
            $available = ($new_qty > 0) ? 1 : 0;

            $upd = $conn->prepare("UPDATE products SET quantity = ?, is_available = ? WHERE product_id = ?");
            $upd->bind_param("iii", $new_qty, $available, $product_id);
            $upd->execute();
            $upd->close();

            $conn->close();
            header("Location: /pages/buyer/order-confirmation.php?order_id=$order_id");
            exit();
        } else {
            $error = 'Order could not be placed. Please try again. Error: ' . $ins->error;
            $ins->close();
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — Masab Trade</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/checkout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>body { display:block; min-height:100vh; background:#f0f2f5; }</style>
</head>
<body>

<header>
    <div class="container">
        <div class="logo">
            <h1><a href="/index.php">Masab <span style="color:#667eea">Trade</span></a></h1>
        </div>
        <nav class="navbar">
            <a href="/pages/browse.php">
                <i class="fas fa-arrow-left"></i> Back to Browse
            </a>
            <span class="nav-username">
                <i class="fas fa-user-circle"></i>
                <?php echo htmlspecialchars($_SESSION['username']); ?>
            </span>
            <a href="/backend/auth/logout.php" class="nav-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>
</header>

<div class="checkout-container">
    <h1><i class="fas fa-shopping-cart"></i> Checkout</h1>

    <?php if ($error): ?>
        <div style="padding:14px 18px; background:#f8d7da; color:#721c24; border-radius:10px; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
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
                    <p><i class="fas fa-cubes"></i> <?php echo intval($product['quantity']); ?> available</p>
                </div>
            </div>
            <div class="price-breakdown">
                <div class="price-row">
                    <span>Product Price:</span>
                    <span>R <?php echo number_format($product['price'],2); ?></span>
                </div>
                <div class="price-row">
                    <span>Delivery Fee:</span>
                    <span id="deliveryFee">R 0.00</span>
                </div>
                <div class="price-row total-row">
                    <span><strong>Total:</strong></span>
                    <span id="totalPrice"><strong>R <?php echo number_format($product['price'],2); ?></strong></span>
                </div>
            </div>
        </div>

        <!-- ORDER FORM -->
        <div class="order-form-section">
            <h2>Complete Your Order</h2>
            <form method="POST" action="">

                <div class="form-section">
                    <h3><i class="fas fa-truck"></i> Delivery Method</h3>

                    <div class="radio-option selected" id="opt_collection" onclick="selectDelivery('collection')">
                        <input type="radio" name="delivery_method" id="delivery_collection" value="collection" checked>
                        <label for="delivery_collection">
                            <span class="option-icon">🏪</span>
                            <div class="option-info">
                                <strong>Community Collection Point</strong>
                                <p>Pick up from a nearby local collection point</p>
                            </div>
                            <span class="option-price free">FREE</span>
                        </label>
                    </div>

                    <div class="radio-option" id="opt_courier" onclick="selectDelivery('courier')">
                        <input type="radio" name="delivery_method" id="delivery_courier" value="courier">
                        <label for="delivery_courier">
                            <span class="option-icon">🚚</span>
                            <div class="option-info">
                                <strong>Local Courier</strong>
                                <p>Delivered to your door within 3-5 business days</p>
                            </div>
                            <span class="option-price">R 80.00</span>
                        </label>
                    </div>

                    <div class="radio-option" id="opt_postal" onclick="selectDelivery('postal')">
                        <input type="radio" name="delivery_method" id="delivery_postal" value="postal">
                        <label for="delivery_postal">
                            <span class="option-icon">📦</span>
                            <div class="option-info">
                                <strong>SA Post Office</strong>
                                <p>Standard postal service within 5-7 days</p>
                            </div>
                            <span class="option-price">R 50.00</span>
                        </label>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-map-marker-alt"></i> Delivery / Collection Address</h3>
                    <div class="form-group">
                        <textarea name="delivery_address"
                                  placeholder="Enter your full address e.g. 112 Ford Street, Hammanskraal, Pretoria, 0400"
                                  required rows="3"><?php echo htmlspecialchars($_POST['delivery_address'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-credit-card"></i> Payment Method</h3>

                    <div class="radio-option selected" id="opt_cod" onclick="selectPayment('cod')">
                        <input type="radio" name="payment_method" id="pay_cod" value="cash_on_delivery" checked>
                        <label for="pay_cod">
                            <span class="option-icon">💵</span>
                            <div class="option-info">
                                <strong>Cash on Delivery</strong>
                                <p>Pay in cash when your item arrives or at collection</p>
                            </div>
                            <span class="option-badge safe">Most Popular</span>
                        </label>
                    </div>

                    <div class="radio-option" id="opt_mobile" onclick="selectPayment('mobile')">
                        <input type="radio" name="payment_method" id="pay_mobile" value="mobile_money">
                        <label for="pay_mobile">
                            <span class="option-icon">📱</span>
                            <div class="option-info">
                                <strong>Mobile Money</strong>
                                <p>MTN Mobile Money or Vodacom M-Pesa</p>
                            </div>
                            <span class="option-badge instant">Instant</span>
                        </label>
                    </div>

                    <div class="radio-option" id="opt_eft" onclick="selectPayment('eft')">
                        <input type="radio" name="payment_method" id="pay_eft" value="eft">
                        <label for="pay_eft">
                            <span class="option-icon">🏦</span>
                            <div class="option-info">
                                <strong>EFT / Bank Transfer</strong>
                                <p>Direct bank transfer to seller</p>
                            </div>
                            <span class="option-badge">1-2 Days</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="place-order-btn">
                    <i class="fas fa-lock"></i> Place Order Securely
                </button>

                <p class="secure-note">
                    <i class="fas fa-shield-alt"></i>
                    Protected by Masab Trade Buyer Protection Policy
                </p>
            </form>
        </div>

    </div>
</div>

<div id="sessionWarning"></div>
<script src="../../js/main.js"></script>
<script>
const basePrice = <?php echo floatval($product['price']); ?>;

function selectDelivery(type) {
    ['collection','courier','postal'].forEach(function(t) {
        var el = document.getElementById('opt_' + t);
        if (el) el.classList.remove('selected');
    });
    var chosen = document.getElementById('opt_' + type);
    if (chosen) chosen.classList.add('selected');

    var radioMap = { collection:'delivery_collection', courier:'delivery_courier', postal:'delivery_postal' };
    var radio = document.getElementById(radioMap[type]);
    if (radio) radio.checked = true;

    var fees  = { collection:0, courier:80, postal:50 };
    var fee   = fees[type] || 0;
    var total = basePrice + fee;

    document.getElementById('deliveryFee').textContent = 'R ' + fee.toFixed(2);
    document.getElementById('totalPrice').innerHTML    = '<strong>R ' + total.toFixed(2) + '</strong>';
}

function selectPayment(type) {
    ['cod','mobile','eft'].forEach(function(t) {
        var el = document.getElementById('opt_' + t);
        if (el) el.classList.remove('selected');
    });
    var chosen = document.getElementById('opt_' + type);
    if (chosen) chosen.classList.add('selected');

    var radioMap = { cod:'pay_cod', mobile:'pay_mobile', eft:'pay_eft' };
    var radio = document.getElementById(radioMap[type]);
    if (radio) radio.checked = true;
}
</script>
</body>
</html>
