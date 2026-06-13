<?php
include '../../backend/includes/session-manager.php';
include '../../backend/config/database.php';

if (!isset($_SESSION['user_id']) ||
    ($_SESSION['user_role'] !== 'seller' && $_SESSION['user_role'] !== 'admin')) {
    header('Location: /pages/auth/login.php');
    exit();
}

$uid = $_SESSION['user_id'];

// Get seller_id
$sp = $conn->prepare("SELECT seller_id FROM seller_profiles WHERE user_id = ? LIMIT 1");
$sp->bind_param("i", $uid);
$sp->execute();
$seller = $sp->get_result()->fetch_assoc();
$sp->close();
$sid = $seller ? $seller['seller_id'] : 0;

// Handle delete
if (isset($_GET['delete']) && $sid) {
    $did = intval($_GET['delete']);
    $del = $conn->prepare("DELETE FROM products WHERE product_id = ? AND seller_id = ?");
    $del->bind_param("ii", $did, $sid);
    $del->execute();
    $del->close();
    header('Location: /pages/seller/manage-products.php?msg=deleted');
    exit();
}

// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty']) && $sid) {
    $pid     = intval($_POST['product_id']);
    $new_qty = intval($_POST['new_quantity']);

    if ($new_qty < 0) $new_qty = 0;

    // If qty > 0 mark available, else mark out of stock
    $available = $new_qty > 0 ? 1 : 0;

    $upd = $conn->prepare("UPDATE products SET quantity = ?, is_available = ? WHERE product_id = ? AND seller_id = ?");
    $upd->bind_param("iiii", $new_qty, $available, $pid, $sid);
    $upd->execute();
    $upd->close();

    header('Location: /pages/seller/manage-products.php?msg=updated');
    exit();
}

// Fetch products - explicitly list all columns to avoid confusion
$products = [];
if ($sid) {
    $p = $conn->prepare("
        SELECT
            product_id,
            seller_id,
            product_name,
            description,
            category,
            price,
            quantity,
            condition_status,
            image_url,
            location,
            is_available,
            date_listed
        FROM products
        WHERE seller_id = ?
        ORDER BY date_listed DESC
    ");
    $p->bind_param("i", $sid);
    $p->execute();
    $products = $p->get_result()->fetch_all(MYSQLI_ASSOC);
    $p->close();
}

$conn->close();

// Helper: make condition readable
function formatCondition($val) {
    $map = [
        'new'         => 'Brand New',
        'like_new'    => 'Like New',
        'used'        => 'Used',
        'refurbished' => 'Refurbished',
    ];
    return $map[$val] ?? ucfirst(str_replace('_', ' ', $val));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Products - Masab Trade</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="dashboard-container">

    <!-- SIDEBAR -->
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
            <a href="/pages/seller/manage-products.php" class="active">
                <i class="fas fa-boxes"></i> My Products
            </a>
            <a href="/pages/seller/sales-history.php">
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

    <!-- MAIN -->
    <main class="dashboard-main">
        <div class="dashboard-topbar">
            <div>
                <h1>My Products</h1>
                <p>Manage all your listings and update stock quantities</p>
            </div>
            <a href="/pages/add-product.php" class="topbar-btn">
                <i class="fas fa-plus"></i> Add New Product
            </a>
        </div>

        <div class="dashboard-content">

            <!-- Alert messages -->
            <?php if (isset($_GET['msg'])): ?>
                <?php if ($_GET['msg'] === 'deleted'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Product deleted successfully.
                    </div>
                <?php elseif ($_GET['msg'] === 'updated'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Quantity updated successfully.
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="dashboard-section">
                <div class="section-header">
                    <h2>
                        <i class="fas fa-boxes"></i>
                        All Products (<?php echo count($products); ?>)
                    </h2>
                </div>

                <?php if (empty($products)): ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <h3>No products yet</h3>
                        <p>Start selling by listing your first product!</p>
                        <a href="/pages/add-product.php" class="topbar-btn"
                           style="display:inline-flex; margin-top:10px;">
                            Add First Product
                        </a>
                    </div>

                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Qty in Stock</th>
                                    <th>Condition</th>
                                    <th>Status</th>
                                    <th>Listed</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $p): ?>
                                    <tr>
                                        <!-- Image -->
                                        <td>
                                            <img src="../../<?php echo htmlspecialchars($p['image_url'] ?: 'assets/images/products/placeholder.png'); ?>"
                                                 alt="product"
                                                 class="product-thumb"
                                                 onerror="this.src='../../assets/images/products/placeholder.png'">
                                        </td>

                                        <!-- Product Name -->
                                        <td>
                                            <strong><?php echo htmlspecialchars($p['product_name']); ?></strong>
                                        </td>

                                        <!-- Category -->
                                        <td><?php echo ucfirst(htmlspecialchars($p['category'])); ?></td>

                                        <!-- Price -->
                                        <td>
                                            <strong style="color:#27ae60;">
                                                R <?php echo number_format($p['price'], 2); ?>
                                            </strong>
                                        </td>

                                        <!-- Quantity update form -->
                                        <td>
                                            <form method="POST" action=""
                                                  style="display:flex; align-items:center; gap:6px;">
                                                <input type="hidden"
                                                       name="product_id"
                                                       value="<?php echo $p['product_id']; ?>">
                                                <input type="number"
                                                       name="new_quantity"
                                                       value="<?php echo intval($p['quantity']); ?>"
                                                       min="0"
                                                       step="1"
                                                       style="width:65px; padding:6px 8px;
                                                              border:2px solid #ecf0f1;
                                                              border-radius:6px;
                                                              font-size:0.88rem;
                                                              text-align:center;">
                                                <button type="submit"
                                                        name="update_qty"
                                                        class="tbl-btn tbl-btn-edit"
                                                        style="padding:6px 10px; white-space:nowrap;"
                                                        title="Save quantity">
                                                    <i class="fas fa-save"></i>
                                                </button>
                                            </form>
                                        </td>

                                        <!-- ✅ FIXED: Condition now reads condition_status properly -->
                                        <td>
                                            <?php echo formatCondition($p['condition_status']); ?>
                                        </td>

                                        <!-- Status badge -->
                                        <td>
                                            <span class="badge <?php echo $p['is_available'] ? 'badge-available' : 'badge-sold'; ?>">
                                                <?php echo $p['is_available'] ? 'Available' : 'Out of Stock'; ?>
                                            </span>
                                        </td>

                                        <!-- Date listed -->
                                        <td>
                                            <?php echo date('d M Y', strtotime($p['date_listed'])); ?>
                                        </td>

                                        <!-- Delete action -->
                                        <td>
                                            <a href="/pages/seller/manage-products.php?delete=<?php echo $p['product_id']; ?>"
                                               class="tbl-btn tbl-btn-delete"
                                               onclick="return confirm('Are you sure you want to delete \'<?php echo htmlspecialchars(addslashes($p['product_name'])); ?>\'?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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