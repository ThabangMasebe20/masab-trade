<?php
include '../../backend/includes/session-manager.php';
include '../../backend/config/database.php';

if (!isset($_SESSION['user_id']) ||
    ($_SESSION['user_role'] !== 'seller' && $_SESSION['user_role'] !== 'admin')) {
    header('Location: /pages/auth/login.php');
    exit();
}

$uid = $_SESSION['user_id'];

$sp = $conn->prepare("SELECT seller_id FROM seller_profiles WHERE user_id = ? LIMIT 1");
$sp->bind_param("i", $uid);
$sp->execute();
$seller = $sp->get_result()->fetch_assoc();
$sp->close();
$sid = $seller ? $seller['seller_id'] : 0;

// ✅ Handle status update — also updates payment + transaction
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && $sid) {
    $oid        = intval($_POST['order_id']);
    $new_status = $_POST['new_status'] ?? '';
    $allowed    = ['pending','confirmed','shipped','delivered','cancelled'];

    if (in_array($new_status, $allowed)) {
        // Update order status
        $upd = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ? AND seller_id = ?");
        $upd->bind_param("sii", $new_status, $oid, $sid);
        $upd->execute();
        $upd->close();

        // ✅ When delivered: mark payment paid + transaction completed
        if ($new_status === 'delivered') {
            // Update order payment status
            $pay = $conn->prepare("UPDATE orders SET payment_status = 'paid' WHERE order_id = ?");
            $pay->bind_param("i", $oid);
            $pay->execute();
            $pay->close();

            // Update transaction
            $tr = $conn->prepare("UPDATE transactions SET transaction_status = 'completed' WHERE order_id = ?");
            $tr->bind_param("i", $oid);
            $tr->execute();
            $tr->close();

            // ✅ Update seller total_sales count
            $ts = $conn->prepare("
                UPDATE seller_profiles
                SET total_sales = (
                    SELECT COUNT(*) FROM orders
                    WHERE seller_id = ? AND order_status = 'delivered'
                )
                WHERE seller_id = ?
            ");
            $ts->bind_param("ii", $sid, $sid);
            $ts->execute();
            $ts->close();
        }

        // ✅ When cancelled: if payment was COD (never paid), keep pending; otherwise refund
        if ($new_status === 'cancelled') {
            $conn->query("UPDATE transactions SET transaction_status = 'failed' WHERE order_id = $oid AND transaction_status = 'pending'");
        }
    }

    header('Location: /pages/seller/sales-history.php?updated=1');
    exit();
}

// Fetch all orders for this seller
$orders = [];
if ($sid) {
    $stmt = $conn->prepare("
        SELECT o.*, p.product_name, p.image_url,
               u.username AS buyer_name, u.phone AS buyer_phone
        FROM orders o
        JOIN products p ON o.product_id = p.product_id
        JOIN users u ON o.buyer_id = u.user_id
        WHERE o.seller_id = ?
        ORDER BY o.order_date DESC
    ");
    $stmt->bind_param("i", $sid);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales History — Masab Trade</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="dashboard-container">

    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo"><i class="fas fa-store"></i></div>
            <h2>Seller Panel</h2>
            <p><?php echo htmlspecialchars($_SESSION['username']); ?></p>
            <span class="user-badge badge-seller">Seller</span>
        </div>
        <nav class="sidebar-nav">
            <a href="/pages/seller/dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="/pages/add-product.php">
                <i class="fas fa-plus-circle"></i> Add Product
            </a>
            <a href="/pages/seller/manage-products.php">
                <i class="fas fa-boxes"></i> My Products
            </a>
            <a href="/pages/seller/sales-history.php" class="active">
                <i class="fas fa-history"></i> Sales History
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
            <div>
                <h1>Sales History</h1>
                <p>All orders for your products — update status below</p>
            </div>
        </div>

        <div class="dashboard-content">

            <?php if (isset($_GET['updated'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Order status updated. Payment and transaction records also updated automatically.
                </div>
            <?php endif; ?>

            <!-- ✅ Status guide -->
            <div class="dashboard-section" style="margin-bottom:18px;">
                <div class="section-header">
                    <h2><i class="fas fa-info-circle"></i> How Order Status Works</h2>
                </div>
                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:12px; font-size:.85rem;">
                    <div style="padding:12px; background:#fff3cd; border-radius:8px; border-left:3px solid #856404;">
                        <strong>Pending</strong><br>Order just placed
                    </div>
                    <div style="padding:12px; background:#cce5ff; border-radius:8px; border-left:3px solid #004085;">
                        <strong>Confirmed</strong><br>You accepted the order
                    </div>
                    <div style="padding:12px; background:#d1ecf1; border-radius:8px; border-left:3px solid #0c5460;">
                        <strong>Shipped</strong><br>Item sent to buyer
                    </div>
                    <div style="padding:12px; background:#d4edda; border-radius:8px; border-left:3px solid #155724;">
                        <strong>Delivered ✅</strong><br>Auto-marks payment as Paid
                    </div>
                    <div style="padding:12px; background:#f8d7da; border-radius:8px; border-left:3px solid #721c24;">
                        <strong>Cancelled</strong><br>Order cancelled
                    </div>
                </div>
            </div>

            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-history"></i>
                        All Sales (<?php echo count($orders); ?>)
                    </h2>
                </div>

                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <i class="fas fa-chart-line"></i>
                        <h3>No sales yet</h3>
                        <p>Your sales will appear here once buyers place orders.</p>
                    </div>
                <?php else: ?>
                    <div class="orders-list">
                        <?php foreach ($orders as $o):
                            $waNum = '27' . ltrim($o['buyer_phone'], '0');
                            $waMsg = urlencode('Hi ' . $o['buyer_name'] . ', regarding Order #' . str_pad($o['order_id'], 6, '0', STR_PAD_LEFT) . ' on Masab Trade.');
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
                                    <span class="badge badge-<?php echo $o['payment_status']; ?>">
                                        Payment: <?php echo ucfirst($o['payment_status']); ?>
                                    </span>
                                </div>

                                <div class="order-card-body">
                                    <img src="../../<?php echo htmlspecialchars($o['image_url'] ?: 'assets/images/products/placeholder.png'); ?>"
                                         alt="Product"
                                         onerror="this.src='../../assets/images/products/placeholder.png'">
                                    <div class="order-product-info">
                                        <h3><?php echo htmlspecialchars($o['product_name']); ?></h3>
                                        <p><i class="fas fa-user"></i> Buyer: <?php echo htmlspecialchars($o['buyer_name']); ?></p>
                                        <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($o['buyer_phone']); ?></p>
                                        <p><i class="fas fa-truck"></i> <?php echo ucwords(str_replace('_', ' ', $o['delivery_method'])); ?></p>
                                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($o['delivery_address']); ?></p>
                                        <p><i class="fas fa-credit-card"></i> <?php echo ucwords(str_replace('_', ' ', $o['payment_method'])); ?></p>
                                    </div>
                                    <div class="order-price-info">
                                        <p class="order-total">
                                            R <?php echo number_format($o['total_price'], 2); ?>
                                        </p>
                                        <span class="badge badge-<?php echo $o['payment_status']; ?>">
                                            <?php echo ucfirst($o['payment_status']); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="order-card-footer">
                                    <a href="https://wa.me/<?php echo $waNum; ?>?text=<?php echo $waMsg; ?>"
                                       target="_blank" class="btn-whatsapp">
                                        <i class="fab fa-whatsapp"></i> Contact Buyer
                                    </a>

                                    <!-- ✅ Update status form -->
                                    <form method="POST" action=""
                                          style="display:flex; gap:8px; align-items:center;">
                                        <input type="hidden" name="order_id"
                                               value="<?php echo $o['order_id']; ?>">
                                        <select name="new_status" class="status-select">
                                            <?php foreach (['pending','confirmed','shipped','delivered','cancelled'] as $s): ?>
                                                <option value="<?php echo $s; ?>"
                                                    <?php echo $o['order_status'] === $s ? 'selected' : ''; ?>>
                                                    <?php echo ucfirst($s); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn-view-order"
                                                style="border:none; cursor:pointer;">
                                            <i class="fas fa-save"></i> Update
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
<div id="sessionWarning"></div>
<script src="../../js/main.js"></script>
</body>
</html>