<?php
include '../../backend/includes/session-manager.php';
include '../../backend/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/auth/login.php');
    exit();
}

$order_id = intval($_GET['order_id'] ?? 0);
if (!$order_id) {
    header('Location: /pages/browse.php');
    exit();
}

$stmt = $conn->prepare("
    SELECT o.*, p.product_name, p.image_url, p.product_id,
           u.username AS seller_name, u.phone AS seller_phone,
           sp.business_name, sp.seller_id
    FROM orders o
    JOIN products p  ON o.product_id  = p.product_id
    JOIN seller_profiles sp ON o.seller_id = sp.seller_id
    JOIN users u ON sp.user_id = u.user_id
    WHERE o.order_id = ? AND o.buyer_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: /pages/browse.php');
    exit();
}

$review_success = false;
$review_error   = '';

// Check if this specific order already has a review
$chk_rev = $conn->prepare("SELECT review_id FROM reviews WHERE order_id = ? LIMIT 1");
$chk_rev->bind_param("i", $order_id);
$chk_rev->execute();
$chk_rev->store_result();
$already_reviewed = $chk_rev->num_rows > 0;
$chk_rev->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $rating       = intval($_POST['rating']       ?? 0);
    $review_text  = trim($_POST['review_text']    ?? '');
    $review_title = trim($_POST['review_title']   ?? '');
    $product_id   = intval($_POST['product_id']   ?? 0);
    $seller_id    = intval($_POST['seller_id']    ?? 0);
    $buyer_id     = $_SESSION['user_id'];
    $oid          = intval($_POST['order_id']     ?? 0);

    if ($already_reviewed) {
        $review_error = 'You have already submitted a review for this order.';
    } elseif ($rating < 1 || $rating > 5) {
        $review_error = 'Please select a star rating between 1 and 5.';
    } elseif (strlen($review_text) < 10) {
        $review_error = 'Review must be at least 10 characters.';
    } else {
        $ins = $conn->prepare("
            INSERT INTO reviews
                (order_id, buyer_id, seller_id, product_id, review_title, rating, review_text)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $ins->bind_param("iiiisis",
            $oid, $buyer_id, $seller_id,
            $product_id, $review_title, $rating, $review_text
        );

        if ($ins->execute()) {
            $review_success   = true;
            $already_reviewed = true;

            // Update seller average rating and total sales
            $avg = $conn->prepare("
                SELECT AVG(rating) AS avg_rating, COUNT(*) AS total
                FROM reviews WHERE seller_id = ?
            ");
            $avg->bind_param("i", $seller_id);
            $avg->execute();
            $avg_data = $avg->get_result()->fetch_assoc();
            $avg->close();

            $upd = $conn->prepare("
                UPDATE seller_profiles
                SET rating = ?
                WHERE seller_id = ?
            ");
            $upd->bind_param("di", $avg_data['avg_rating'], $seller_id);
            $upd->execute();
            $upd->close();
        } else {
            $review_error = 'Could not submit review. Please try again.';
        }
        $ins->close();
    }
}

$conn->close();
$order_num = str_pad($order_id, 6, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed — Masab Trade</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/checkout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { display:block; min-height:100vh; background:#f0f2f5; }

        .success-popup {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg,#27ae60,#229954);
            color: white;
            padding: 18px 24px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(39,174,96,.4);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 12px;
            max-width: 340px;
            animation: slideInRight .4s ease;
        }
        .success-popup i  { font-size:1.6rem; }
        .success-popup h4 { margin:0 0 3px; font-size:1rem; }
        .success-popup p  { margin:0; font-size:.85rem; opacity:.9; }

        @keyframes slideInRight {
            from { transform:translateX(120px); opacity:0; }
            to   { transform:translateX(0);     opacity:1; }
        }

        .review-card {
            background: white;
            border-radius: 14px;
            padding: 26px;
            box-shadow: 0 4px 18px rgba(0,0,0,.08);
            margin-top: 22px;
        }
        .review-card h2 {
            font-size: 1.15rem;
            color: #2c3e50;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 9px;
        }
        .review-card h2 i { color:#f39c12; }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            gap: 6px;
            margin-bottom: 16px;
        }
        .star-rating input { display:none; }
        .star-rating label {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
            transition: color .2s;
        }
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label { color:#f39c12; }

        .review-input {
            width: 100%;
            padding: 11px 14px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: .95rem;
            font-family: inherit;
            margin-bottom: 12px;
            box-sizing: border-box;
            transition: border-color .3s;
        }
        .review-input:focus { outline:none; border-color:#667eea; }
        textarea.review-input { resize:vertical; min-height:90px; }

        .submit-review-btn {
            padding: 12px 28px;
            background: linear-gradient(135deg,#667eea,#764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: .95rem;
            font-weight: bold;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all .3s;
            font-family: inherit;
        }
        .submit-review-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(102,126,234,.4);
        }
        .review-done {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 16px 18px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: .92rem;
        }
        .review-error-box {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 12px;
            font-size: .88rem;
        }
    </style>
</head>
<body>

<!-- Success popup -->
<?php if (!isset($_POST['submit_review'])): ?>
<div class="success-popup" id="successPopup">
    <i class="fas fa-check-circle"></i>
    <div>
        <h4>Order Placed Successfully!</h4>
        <p>Order #<?php echo $order_num; ?> is confirmed.</p>
    </div>
</div>
<?php endif; ?>

<header>
    <div class="container">
        <div class="logo">
            <h1><a href="/index.php">Masab <span style="color:#667eea">Trade</span></a></h1>
        </div>
        <nav class="navbar">
            <a href="/pages/buyer/dashboard.php">
                <i class="fas fa-user"></i> My Account
            </a>
            <a href="/pages/browse.php">
                <i class="fas fa-shopping-bag"></i> Browse
            </a>
            <a href="/backend/auth/logout.php" class="nav-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>
</header>

<div class="confirmation-container">

    <div class="confirmation-banner">
        <div class="success-icon"><i class="fas fa-check-circle"></i></div>
        <h1>Order Confirmed!</h1>
        <p>Thank you, <?php echo htmlspecialchars($_SESSION['username']); ?>!
           Your order has been received and is being processed.</p>
        <span class="order-number">Order #<?php echo $order_num; ?></span>
    </div>

    <div class="confirmation-grid">

        <div class="confirmation-card">
            <h2><i class="fas fa-box"></i> Order Details</h2>
            <div class="order-product">
                <img src="../../<?php echo htmlspecialchars($order['image_url'] ?: 'assets/images/products/placeholder.png'); ?>"
                     alt="Product"
                     onerror="this.src='../../assets/images/products/placeholder.png'">
                <div>
                    <h3><?php echo htmlspecialchars($order['product_name']); ?></h3>
                    <p>Qty: <?php echo $order['quantity']; ?></p>
                    <p class="total-price">
                        R <?php echo number_format($order['total_price'], 2); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="confirmation-card">
            <h2><i class="fas fa-truck"></i> Delivery Information</h2>
            <div class="info-row">
                <span>Method:</span>
                <span><?php echo ucwords(str_replace('_',' ',$order['delivery_method'])); ?></span>
            </div>
            <div class="info-row">
                <span>Address:</span>
                <span><?php echo htmlspecialchars($order['delivery_address']); ?></span>
            </div>
            <div class="info-row">
                <span>Status:</span>
                <span class="badge badge-<?php echo $order['order_status']; ?>">
                    <?php echo ucfirst($order['order_status']); ?>
                </span>
            </div>
        </div>

        <div class="confirmation-card">
            <h2><i class="fas fa-credit-card"></i> Payment Information</h2>
            <div class="info-row">
                <span>Method:</span>
                <span><?php echo ucwords(str_replace('_',' ',$order['payment_method'])); ?></span>
            </div>
            <div class="info-row">
                <span>Status:</span>
                <span class="badge badge-<?php echo $order['payment_status']; ?>">
                    <?php echo ucfirst($order['payment_status']); ?>
                </span>
            </div>
            <div class="info-row">
                <span>Amount:</span>
                <span><strong>R <?php echo number_format($order['total_price'],2); ?></strong></span>
            </div>

            <?php if ($order['payment_method'] === 'cash_on_delivery'): ?>
                <div class="payment-instruction cod">
                    <i class="fas fa-info-circle"></i>
                    <span>Pay <strong>R <?php echo number_format($order['total_price'],2); ?></strong>
                          cash on delivery/collection.</span>
                </div>
            <?php elseif ($order['payment_method'] === 'mobile_money'): ?>
                <div class="payment-instruction mobile">
                    <i class="fas fa-mobile-alt"></i>
                    <span>Send <strong>R <?php echo number_format($order['total_price'],2); ?></strong>
                          to <strong><?php echo htmlspecialchars($order['seller_phone']); ?></strong>
                          via Mobile Money. Ref: <strong>#<?php echo $order_num; ?></strong></span>
                </div>
            <?php elseif ($order['payment_method'] === 'eft'): ?>
                <div class="payment-instruction eft">
                    <i class="fas fa-university"></i>
                    <span>EFT to seller. Contact for banking details.
                          Ref: <strong>#<?php echo $order_num; ?></strong></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="confirmation-card">
            <h2><i class="fas fa-store"></i> Seller Information</h2>
            <div class="info-row">
                <span>Seller:</span>
                <span><?php echo htmlspecialchars($order['seller_name']); ?></span>
            </div>
            <div class="info-row">
                <span>Business:</span>
                <span><?php echo htmlspecialchars($order['business_name'] ?: 'N/A'); ?></span>
            </div>
            <div class="info-row">
                <span>Phone:</span>
                <span><?php echo htmlspecialchars($order['seller_phone']); ?></span>
            </div>
            <?php
            $waNum = '27' . ltrim($order['seller_phone'], '0');
            $waMsg = urlencode("Hi, I placed Order #$order_num on Masab Trade for " . $order['product_name']);
            ?>
            <a href="https://wa.me/<?php echo $waNum; ?>?text=<?php echo $waMsg; ?>"
               target="_blank" class="whatsapp-btn">
                <i class="fab fa-whatsapp"></i> Contact Seller on WhatsApp
            </a>
        </div>

    </div>

    
    <div class="review-card" id="review">
        <h2><i class="fas fa-star"></i> Rate Your Experience</h2>

        <?php if ($already_reviewed || $review_success): ?>
            <div class="review-done">
                <i class="fas fa-check-circle"></i>
                Your review has been submitted. Thank you for your feedback!
            </div>
        <?php else: ?>

            <?php if ($review_error): ?>
                <div class="review-error-box">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($review_error); ?>
                </div>
            <?php endif; ?>

            <p style="color:#7f8c8d; font-size:.92rem; margin-bottom:16px;">
                Help other buyers by sharing your experience.
            </p>

            <form method="POST" action="">
                <input type="hidden" name="submit_review" value="1">
                <input type="hidden" name="order_id"   value="<?php echo $order_id; ?>">
                <input type="hidden" name="product_id" value="<?php echo $order['product_id']; ?>">
                <input type="hidden" name="seller_id"  value="<?php echo $order['seller_id']; ?>">

                <div class="star-rating">
                    <input type="radio" id="s5" name="rating" value="5">
                    <label for="s5" title="Excellent"><i class="fas fa-star"></i></label>
                    <input type="radio" id="s4" name="rating" value="4">
                    <label for="s4" title="Good"><i class="fas fa-star"></i></label>
                    <input type="radio" id="s3" name="rating" value="3">
                    <label for="s3" title="Average"><i class="fas fa-star"></i></label>
                    <input type="radio" id="s2" name="rating" value="2">
                    <label for="s2" title="Poor"><i class="fas fa-star"></i></label>
                    <input type="radio" id="s1" name="rating" value="1">
                    <label for="s1" title="Terrible"><i class="fas fa-star"></i></label>
                </div>

                <input type="text" name="review_title" class="review-input"
                       placeholder="Review title (e.g. Great product!)">

                <textarea name="review_text" class="review-input"
                          placeholder="Write your review... (min 10 characters)"
                          required></textarea>

                <button type="submit" class="submit-review-btn">
                    <i class="fas fa-paper-plane"></i> Submit Review
                </button>
            </form>
        <?php endif; ?>
    </div>

    <div class="confirmation-actions" style="margin-top:22px;">
        <a href="/pages/browse.php" class="btn btn-primary">
            <i class="fas fa-shopping-bag"></i> Continue Shopping
        </a>
        <a href="/pages/buyer/orders.php" class="btn btn-secondary" style="color:#667eea;">
            <i class="fas fa-list"></i> View My Orders
        </a>
    </div>

</div>

<div id="sessionWarning"></div>
<script src="../../js/main.js"></script>
<script>
    setTimeout(function () {
        var p = document.getElementById('successPopup');
        if (p) {
            p.style.transition = 'opacity 0.5s';
            p.style.opacity    = '0';
            setTimeout(function () { p.style.display = 'none'; }, 500);
        }
    }, 5000);
</script>
</body>
</html>
