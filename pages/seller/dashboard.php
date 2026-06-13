<?php
include '../../backend/includes/session-manager.php';
include '../../backend/config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'seller' && $_SESSION['user_role'] !== 'admin')) {
    header('Location: /pages/auth/login.php'); exit();
}

$uid = $_SESSION['user_id'];

$sp = $conn->prepare("SELECT * FROM seller_profiles WHERE user_id = ? LIMIT 1");
$sp->bind_param("i",$uid); $sp->execute();
$seller = $sp->get_result()->fetch_assoc(); $sp->close();
$sid = $seller ? $seller['seller_id'] : 0;

$total_products = $total_sales = $revenue = 0;

if ($sid) {
    $s1 = $conn->prepare("SELECT COUNT(*) AS t FROM products WHERE seller_id = ?"); $s1->bind_param("i",$sid); $s1->execute(); $total_products = $s1->get_result()->fetch_assoc()['t']; $s1->close();
    $s2 = $conn->prepare("SELECT COUNT(*) AS t FROM orders WHERE seller_id = ?");   $s2->bind_param("i",$sid); $s2->execute(); $total_sales    = $s2->get_result()->fetch_assoc()['t']; $s2->close();
    $s3 = $conn->prepare("SELECT COALESCE(SUM(total_price),0) AS r FROM orders WHERE seller_id = ? AND payment_status='paid'"); $s3->bind_param("i",$sid); $s3->execute(); $revenue = $s3->get_result()->fetch_assoc()['r']; $s3->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard - Masab Trade</title>
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
            <a href="/pages/seller/dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="/pages/add-product.php"><i class="fas fa-plus-circle"></i> Add Product</a>
            <a href="/pages/seller/manage-products.php"><i class="fas fa-boxes"></i> My Products</a>
            <a href="/pages/seller/sales-history.php"><i class="fas fa-history"></i> Sales History</a>
            <div class="sidebar-divider"></div>
            <a href="/index.php"><i class="fas fa-home"></i> Back to Home</a>
            <a href="/backend/auth/logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>

    <main class="dashboard-main">
        <div class="dashboard-topbar">
            <div><h1>Seller Dashboard</h1><p>Manage your products and track sales</p></div>
            <a href="/pages/add-product.php" class="topbar-btn"><i class="fas fa-plus"></i> Add Product</a>
        </div>

        <div class="dashboard-content">
            <?php if (!$seller): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Seller profile not found. Please contact support.</div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card blue"><div class="stat-icon blue"><i class="fas fa-box"></i></div><div class="stat-details"><h3><?php echo $total_products; ?></h3><p>My Products</p></div></div>
                <div class="stat-card green"><div class="stat-icon green"><i class="fas fa-shopping-cart"></i></div><div class="stat-details"><h3><?php echo $total_sales; ?></h3><p>Total Sales</p></div></div>
                <div class="stat-card orange"><div class="stat-icon orange"><i class="fas fa-money-bill-wave"></i></div><div class="stat-details"><h3>R <?php echo number_format($revenue,2); ?></h3><p>Revenue (Paid)</p></div></div>
            </div>

            <div class="dashboard-section">
                <div class="section-header"><h2><i class="fas fa-bolt"></i> Quick Actions</h2></div>
                <div class="quick-actions">
                    <a href="/pages/add-product.php" class="action-btn"><i class="fas fa-plus-circle"></i><span>Add Product</span></a>
                    <a href="/pages/seller/manage-products.php" class="action-btn"><i class="fas fa-boxes"></i><span>My Products</span></a>
                    <a href="/pages/seller/sales-history.php" class="action-btn"><i class="fas fa-history"></i><span>Sales History</span></a>
                    <a href="/pages/browse.php" class="action-btn"><i class="fas fa-store"></i><span>View Store</span></a>
                </div>
            </div>

            <div class="dashboard-section">
                <div class="section-header"><h2><i class="fas fa-lightbulb"></i> Seller Tips</h2></div>
                <div class="activity-list">
                    <div class="activity-item"><div class="act-icon"><i class="fas fa-camera"></i></div><p><strong>Add clear photos</strong> - Listings with good images get 3x more views</p></div>
                    <div class="activity-item"><div class="act-icon"><i class="fas fa-tag"></i></div><p><strong>Price competitively</strong> - Check similar listings to price well</p></div>
                    <div class="activity-item"><div class="act-icon"><i class="fas fa-reply"></i></div><p><strong>Respond quickly</strong> - Fast responses build trust and increase sales</p></div>
                </div>
            </div>
        </div>
    </main>
</div>
<div id="sessionWarning"></div>
<script src="../../js/main.js"></script>
</body>
</html>