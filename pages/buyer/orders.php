<?php
include '../../backend/includes/session-manager.php';
include '../../backend/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/auth/login.php');
    exit();
}

$uid  = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT o.*,
           p.product_name, p.image_url, p.product_id,
           u.username AS seller_name, u.phone AS seller_phone,
           sp.seller_id
    FROM orders o
    JOIN products p  ON o.product_id  = p.product_id
    JOIN seller_profiles sp ON o.seller_id = sp.seller_id
    JOIN users u ON sp.user_id = u.user_id
    WHERE o.buyer_id = ?
    ORDER BY o.order_date DESC
");
$stmt->bind_param("i", $uid);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Find which orders already have a review
$reviewed = [];
if (!empty($orders)) {
    $res = $conn->prepare("SELECT order_id FROM reviews WHERE buyer_id = ?");
    $res->bind_param("i", $uid);
    $res->execute();
    $rev_rows = $res->get_result()->fetch_all(MYSQLI_ASSOC);
    $res->close();
    foreach ($rev_rows as $r) {
        $reviewed[$r['order_id']] = true;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders — Masab Trade</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="dashboard-container">

    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo"><i class="fas fa-user"></i></div>
            <h2>My Account</h2>
            <p><?php echo htmlspecialchars($_SESSION['username']); ?></p>
            <span class="user-badge badge-buyer">Buyer</span>
        </div>
        <nav class="sidebar-nav">
            <a href="/pages/buyer/dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="/pages/browse.php">
                <i class="fas fa-shopping-bag"></i> Browse
            </a>
            <a href="/pages/buyer/orders.php" class="active">
                <i class="fas fa-shopping-cart"></i> My Orders
            </a>
            <div class="sidebar-divider"></div>
            <a href="/index.php">
                <i class="fas fa-home"></i> Back to Home
            </a>
            <a href="/backend/auth/logout.php" class="logout-link">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </aside>

    <main class="dashboard-main">
        <div class="dashboard-topbar">
            <div><h1>My Orders</h1><p>Track all your purchases</p></div>
        </div>

        <div class="dashboard-content">
            <?php if (empty($orders)): ?>
                <div class="dashboard-section">
                    <div class="empty-state">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>No orders yet</h3>
                        <p>You have not placed any orders.</p>
                        <a href="/pages/browse.php" class="topbar-btn">Start Shopping</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($orders as $o):
                        $waNum  = '27' . ltrim($o['seller_phone'], '0');
                        $waMsg  = urlencode('Hi, regarding Order #' . str_pad($o['order_id'], 6, '0', STR_PAD_LEFT) . ' on Masab Trade.');
                        $hasRev = isset($reviewed[$o['order_id']]);
                    ?>
                        <div class="order-card">
                            <div class="order-card-header">
                                <span class="order-id">
                                    Order #<?php echo str_pad($o['order_id'], 6, '0', STR_PAD_LEFT); ?>
                                </span>
                                <span class="order-date">
                                    <?php echo date('d M Y, H:i', strtotime($o['order_date'])); ?>
                                </span>
                                <span class="badge badge-<?php echo $o['order_status']; ?>">
                                    <?php echo ucfirst($o['order_status']); ?>
                                </span>
                            </div>

                            <div class="order-card-body">
                                <img src="../../<?php echo htmlspecialchars($o['image_url'] ?: 'assets/images/products/placeholder.png'); ?>"
                                     alt="Product"
                                     onerror="this.src='../../assets/images/products/placeholder.png'">
                                <div class="order-product-info">
                                    <h3><?php echo htmlspecialchars($o['product_name']); ?></h3>
                                    <p><i class="fas fa-user"></i> Seller: <?php echo htmlspecialchars($o['seller_name']); ?></p>
                                    <p><i class="fas fa-truck"></i> <?php echo ucwords(str_replace('_', ' ', $o['delivery_method'])); ?></p>
                                    <p><i class="fas fa-credit-card"></i> <?php echo ucwords(str_replace('_', ' ', $o['payment_method'])); ?></p>
                                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($o['delivery_address']); ?></p>
                                </div>
                                <div class="order-price-info">
                                    <p class="order-total">R <?php echo number_format($o['total_price'], 2); ?></p>
                                    <span class="badge badge-<?php echo $o['payment_status']; ?>">
                                        <?php echo ucfirst($o['payment_status']); ?>
                                    </span>
                                    <?php if ($o['payment_method'] !== 'cash_on_delivery' && $o['payment_status'] === 'pending'): ?>
                                        <p class="payment-reminder">
                                            <i class="fas fa-exclamation-circle"></i>
                                            Please complete payment to: <?php echo htmlspecialchars($o['seller_phone']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="order-card-footer">
                                <a href="https://wa.me/<?php echo $waNum; ?>?text=<?php echo $waMsg; ?>"
                                   target="_blank" class="btn-whatsapp">
                                    <i class="fab fa-whatsapp"></i> Contact Seller
                                </a>
                                <a href="/pages/buyer/order-confirmation.php?order_id=<?php echo $o['order_id']; ?>"
                                   class="btn-view-order">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <!-- ✅ Leave Review button — shows only if not yet reviewed -->
                                <?php if (!$hasRev): ?>
                                    <a href="/pages/buyer/order-confirmation.php?order_id=<?php echo $o['order_id']; ?>#review"
                                       class="btn-view-order"
                                       style="background:linear-gradient(135deg,#f39c12,#d68910);">
                                        <i class="fas fa-star"></i> Leave Review
                                    </a>
                                <?php else: ?>
                                    <span style="padding:10px 14px; background:#d4edda; color:#155724;
                                                 border-radius:25px; font-size:0.82rem; font-weight:bold;
                                                 display:inline-flex; align-items:center; gap:5px;">
                                        <i class="fas fa-check"></i> Reviewed
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<div id="sessionWarning"></div>
<script src="../../js/main.js"></script>
</body>
</html>