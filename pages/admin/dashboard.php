<?php
include '../../backend/includes/session-manager.php';
include '../../backend/config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /pages/auth/login.php');
    exit();
}

$msg = '';

// ✅ CREATE user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $nu = trim($_POST['new_username'] ?? '');
    $ne = trim($_POST['new_email']    ?? '');
    $np = $_POST['new_password']      ?? '';
    $nr = $_POST['new_role']          ?? 'buyer';
    $nh = trim($_POST['new_phone']    ?? '');

    if ($nu && $ne && $np) {
        $hash = password_hash($np, PASSWORD_DEFAULT);
        $ins  = $conn->prepare("INSERT INTO users (username, email, password, phone, user_role, is_active) VALUES (?,?,?,?,?,1)");
        $ins->bind_param("sssss", $nu, $ne, $hash, $nh, $nr);
        if ($ins->execute()) {
            $new_uid = $conn->insert_id;
            if ($nr === 'seller') {
                $sp = $conn->prepare("INSERT INTO seller_profiles (user_id, business_name, location, rating, total_sales) VALUES (?,?,'' ,0,0)");
                $sp->bind_param("is", $new_uid, $nu);
                $sp->execute();
                $sp->close();
            }
            $msg = 'created';
        } else {
            $msg = 'create_error';
        }
        $ins->close();
    } else {
        $msg = 'create_error';
    }
}

// ✅ UPDATE user role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $uid_upd  = intval($_POST['user_id_update'] ?? 0);
    $new_role = $_POST['updated_role'] ?? '';
    if (in_array($new_role, ['buyer','seller','admin']) && $uid_upd !== intval($_SESSION['user_id'])) {
        $upd = $conn->prepare("UPDATE users SET user_role = ? WHERE user_id = ?");
        $upd->bind_param("si", $new_role, $uid_upd);
        $upd->execute();
        $upd->close();

        // Auto-create seller profile if changing to seller
        if ($new_role === 'seller') {
            $chk = $conn->prepare("SELECT seller_id FROM seller_profiles WHERE user_id = ? LIMIT 1");
            $chk->bind_param("i", $uid_upd);
            $chk->execute();
            $chk->store_result();
            if ($chk->num_rows === 0) {
                $sp = $conn->prepare("INSERT INTO seller_profiles (user_id, business_name, location, rating, total_sales) VALUES (?,'',' ',0,0)");
                $sp->bind_param("i", $uid_upd);
                $sp->execute();
                $sp->close();
            }
            $chk->close();
        }
        $msg = 'role_updated';
    }
}

// ✅ DELETE user
if (isset($_GET['delete_user'])) {
    $del_id = intval($_GET['delete_user']);
    if ($del_id !== intval($_SESSION['user_id'])) {
        $conn->query("DELETE FROM seller_profiles WHERE user_id = $del_id");
        $del = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $del->bind_param("i", $del_id);
        $del->execute();
        $del->close();
        $msg = 'deleted';
    } else {
        $msg = 'self_delete';
    }
    header("Location: /pages/admin/dashboard.php?msg=$msg");
    exit();
}

// Stats
$total_users    = $conn->query("SELECT COUNT(*) AS t FROM users")->fetch_assoc()['t'];
$total_products = $conn->query("SELECT COUNT(*) AS t FROM products")->fetch_assoc()['t'];
$total_orders   = $conn->query("SELECT COUNT(*) AS t FROM orders")->fetch_assoc()['t'];
$total_revenue  = $conn->query("SELECT COALESCE(SUM(total_price),0) AS r FROM orders WHERE payment_status='paid'")->fetch_assoc()['r'];

$all_users      = $conn->query("SELECT * FROM users ORDER BY date_registered DESC")->fetch_all(MYSQLI_ASSOC);
$recent_orders  = $conn->query("
    SELECT o.*, p.product_name, u.username AS buyer_name
    FROM orders o
    JOIN products p ON o.product_id = p.product_id
    JOIN users u ON o.buyer_id = u.user_id
    ORDER BY o.order_date DESC LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

if (isset($_GET['msg'])) $msg = $_GET['msg'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — Masab Trade</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Create-user modal */
        .modal-admin {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.6);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-admin.open { display: flex; }
        .modal-admin-box {
            background: white;
            border-radius: 14px;
            padding: 30px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 20px 60px rgba(0,0,0,.2);
        }
        .modal-admin-box h3 {
            color: #2c3e50;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 9px;
        }
        .modal-admin-box .form-row {
            margin-bottom: 14px;
        }
        .modal-admin-box label {
            display: block;
            font-size: .85rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .modal-admin-box input,
        .modal-admin-box select {
            width: 100%;
            padding: 10px 13px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: .92rem;
            font-family: inherit;
            box-sizing: border-box;
        }
        .modal-admin-box input:focus,
        .modal-admin-box select:focus {
            outline: none;
            border-color: #667eea;
        }
        .modal-admin-btns {
            display: flex;
            gap: 10px;
            margin-top: 18px;
        }
        .btn-create-submit {
            flex: 1;
            padding: 11px;
            background: linear-gradient(135deg,#667eea,#764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-family: inherit;
            font-size: .92rem;
        }
        .btn-cancel-modal {
            padding: 11px 18px;
            background: #f0f2f5;
            color: #2c3e50;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-family: inherit;
            font-size: .92rem;
        }
    </style>
</head>
<body>
<div class="dashboard-container">

    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo"><i class="fas fa-user-shield"></i></div>
            <h2>Admin Panel</h2>
            <p><?php echo htmlspecialchars($_SESSION['username']); ?></p>
            <span class="user-badge badge-admin">Admin</span>
        </div>
        <nav class="sidebar-nav">
            <a href="/pages/admin/dashboard.php" class="active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="/pages/browse.php">
                <i class="fas fa-store"></i> View Store
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
            <div><h1>Admin Dashboard</h1><p>Full platform oversight and user management</p></div>
            <!-- ✅ CREATE user button -->
            <button class="topbar-btn" onclick="document.getElementById('createModal').classList.add('open')">
                <i class="fas fa-user-plus"></i> Add New User
            </button>
        </div>

        <div class="dashboard-content">

            <!-- ✅ Alert messages -->
            <?php if ($msg === 'created'): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> New user created successfully.</div>
            <?php elseif ($msg === 'create_error'): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Could not create user. Email may already exist.</div>
            <?php elseif ($msg === 'deleted'): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> User removed successfully.</div>
            <?php elseif ($msg === 'self_delete'): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> You cannot delete your own account.</div>
            <?php elseif ($msg === 'role_updated'): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> User role updated successfully.</div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                    <div class="stat-details"><h3><?php echo number_format($total_users); ?></h3><p>Total Users</p></div>
                </div>
                <div class="stat-card green">
                    <div class="stat-icon green"><i class="fas fa-box"></i></div>
                    <div class="stat-details"><h3><?php echo number_format($total_products); ?></h3><p>Products</p></div>
                </div>
                <div class="stat-card orange">
                    <div class="stat-icon orange"><i class="fas fa-shopping-cart"></i></div>
                    <div class="stat-details"><h3><?php echo number_format($total_orders); ?></h3><p>Orders</p></div>
                </div>
                <div class="stat-card red">
                    <div class="stat-icon red"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="stat-details">
                        <h3>R <?php echo number_format($total_revenue, 2); ?></h3>
                        <p>Revenue (Paid)</p>
                    </div>
                </div>
            </div>

            <!-- ✅ USERS TABLE — full CRUD -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-users"></i> User Management (<?php echo count($all_users); ?>)</h2>
                    <span style="font-size:.82rem; color:#7f8c8d;">RBAC: Create | Read | Update Role | Delete</span>
                </div>

                <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Current Role</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <!-- ✅ UPDATE + DELETE columns -->
                                <th>Update Role</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_users as $u): ?>
                                <tr>
                                    <td><?php echo $u['user_id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><?php echo htmlspecialchars($u['phone'] ?: '—'); ?></td>
                                    <td>
                                        <span class="badge <?php
                                            echo $u['user_role'] === 'admin'  ? 'badge-cancelled' :
                                                ($u['user_role'] === 'seller' ? 'badge-available' : 'badge-confirmed');
                                        ?>">
                                            <?php echo ucfirst($u['user_role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $u['is_active'] ? 'badge-available' : 'badge-cancelled'; ?>">
                                            <?php echo $u['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($u['date_registered'])); ?></td>

                                    <!-- ✅ UPDATE: change role inline -->
                                    <td>
                                        <?php if ($u['user_id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" style="display:flex; gap:5px; align-items:center;">
                                                <input type="hidden" name="user_id_update" value="<?php echo $u['user_id']; ?>">
                                                <select name="updated_role"
                                                        style="padding:5px 8px; border:2px solid #ecf0f1; border-radius:6px; font-size:.8rem;">
                                                    <option value="buyer"  <?php echo $u['user_role']==='buyer'  ?'selected':''; ?>>Buyer</option>
                                                    <option value="seller" <?php echo $u['user_role']==='seller' ?'selected':''; ?>>Seller</option>
                                                    <option value="admin"  <?php echo $u['user_role']==='admin'  ?'selected':''; ?>>Admin</option>
                                                </select>
                                                <button type="submit" name="update_role"
                                                        class="tbl-btn tbl-btn-edit"
                                                        style="padding:5px 10px;">
                                                    <i class="fas fa-save"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color:#95a5a6; font-size:.8rem;">You</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- ✅ DELETE -->
                                    <td>
                                        <?php if ($u['user_id'] != $_SESSION['user_id']): ?>
                                            <a href="/pages/admin/dashboard.php?delete_user=<?php echo $u['user_id']; ?>"
                                               class="tbl-btn tbl-btn-delete"
                                               onclick="return confirm('Remove user &quot;<?php echo htmlspecialchars(addslashes($u['username'])); ?>&quot;? This cannot be undone.')">
                                                <i class="fas fa-user-times"></i> Remove
                                            </a>
                                        <?php else: ?>
                                            <span style="color:#bbb; font-size:.8rem;">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-shopping-cart"></i> Recent Orders</h2>
                </div>

                <?php if (empty($recent_orders)): ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>No orders yet</h3>
                    </div>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Buyer</th>
                                    <th>Product</th>
                                    <th>Total</th>
                                    <th>Payment</th>
                                    <th>Order Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $o): ?>
                                    <tr>
                                        <td><strong>#<?php echo str_pad($o['order_id'],6,'0',STR_PAD_LEFT); ?></strong></td>
                                        <td><?php echo htmlspecialchars($o['buyer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($o['product_name']); ?></td>
                                        <td><strong style="color:#27ae60;">R <?php echo number_format($o['total_price'],2); ?></strong></td>
                                        <td>
                                            <span class="badge badge-<?php echo $o['payment_status']; ?>">
                                                <?php echo ucfirst($o['payment_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $o['order_status']; ?>">
                                                <?php echo ucfirst($o['order_status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($o['order_date'])); ?></td>
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

<!-- ✅ CREATE USER MODAL -->
<div class="modal-admin" id="createModal">
    <div class="modal-admin-box">
        <h3><i class="fas fa-user-plus" style="color:#667eea;"></i> Create New User</h3>

        <form method="POST" action="">
            <div class="form-row">
                <label>Username *</label>
                <input type="text" name="new_username" placeholder="e.g. john_doe" required>
            </div>
            <div class="form-row">
                <label>Email *</label>
                <input type="email" name="new_email" placeholder="john@email.com" required>
            </div>
            <div class="form-row">
                <label>Phone</label>
                <input type="tel" name="new_phone" placeholder="0712345678">
            </div>
            <div class="form-row">
                <label>Role *</label>
                <select name="new_role">
                    <option value="buyer">Buyer</option>
                    <option value="seller">Seller</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-row">
                <label>Password *</label>
                <input type="password" name="new_password" placeholder="Min 6 characters" required>
            </div>
            <div class="modal-admin-btns">
                <button type="button" class="btn-cancel-modal"
                        onclick="document.getElementById('createModal').classList.remove('open')">
                    Cancel
                </button>
                <button type="submit" name="create_user" class="btn-create-submit">
                    <i class="fas fa-check"></i> Create User
                </button>
            </div>
        </form>
    </div>
</div>

<div id="sessionWarning"></div>
<script src="../../js/main.js"></script>
<script>
// Close modal if clicking outside
document.getElementById('createModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});
</script>
</body>
</html>