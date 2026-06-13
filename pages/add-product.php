<?php
include '../backend/includes/session-manager.php';
include '../backend/config/database.php';

$error   = '';
$success = '';

if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/auth/register.php?from=seller&role=seller');
    exit();
}

if ($_SESSION['user_role'] !== 'seller' && $_SESSION['user_role'] !== 'admin') {
    $error = 'Only sellers can list products. <a href="/pages/auth/register.php?from=seller&role=seller">Register as a seller.</a>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $name      = mysqli_real_escape_string($conn, $_POST['productName'] ?? '');
    $category  = mysqli_real_escape_string($conn, $_POST['category']    ?? '');
    $price     = floatval($_POST['price']    ?? 0);
    $quantity  = intval($_POST['quantity']   ?? 1);
    $condition = mysqli_real_escape_string($conn, $_POST['condition']   ?? '');
    $desc      = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $location  = mysqli_real_escape_string($conn, $_POST['location']    ?? '');
    $uid       = $_SESSION['user_id'];

    if ($quantity < 1) $quantity = 1;

    // Get seller_id
    $sp = $conn->prepare("SELECT seller_id FROM seller_profiles WHERE user_id = ? LIMIT 1");
    $sp->bind_param("i", $uid);
    $sp->execute();
    $seller = $sp->get_result()->fetch_assoc();
    $sp->close();

    if (!$seller) {
        $error = 'Seller profile not found. Please contact support.';
    } else {
        $seller_id = $seller['seller_id'];
        $image_url = '';

        if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] === UPLOAD_ERR_OK) {
            $ext     = strtolower(pathinfo($_FILES['productImage']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $size    = $_FILES['productImage']['size'];

            if (!in_array($ext, $allowed)) {
                $error = 'Invalid image type. JPG, PNG, GIF only.';
            } elseif ($size > 5 * 1024 * 1024) {
                $error = 'Image too large. Max 5MB.';
            } else {
                $dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/products/';
                if (!is_dir($dir)) mkdir($dir, 0777, true);

                $fname = uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['productImage']['tmp_name'], $dir . $fname)) {
                    $image_url = 'uploads/products/' . $fname;
                } else {
                    $error = 'Failed to upload image. Check uploads folder permissions.';
                }
            }
        }

        if (!$error) {
            // ✅ quantity is now properly saved and is_available stays 1
            $ins = $conn->prepare("
                INSERT INTO products
                    (seller_id, product_name, description, category, price,
                     quantity, condition_status, image_url, location, is_available)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");
            $ins->bind_param("isssdiiss",
                $seller_id, $name, $desc, $category, $price,
                $quantity, $condition, $image_url, $location
            );

            if ($ins->execute()) {
                $success = 'Product listed successfully! <a href="/pages/seller/dashboard.php">View your dashboard</a>';
                // Clear POST data after success
                $_POST = [];
            } else {
                $error = 'Database error: ' . $ins->error;
            }
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
    <title>Sell Product - Masab Trade</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/seller.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header>
    <div class="container">
        <div class="logo">
            <h1><a href="/index.php">Masab <span style="color:#667eea">Trade</span></a></h1>
        </div>
        <nav class="navbar">
            <a href="/index.php"><i class="fas fa-home"></i> Home</a>
            <a href="/pages/browse.php"><i class="fas fa-shopping-bag"></i> Buy</a>
            <a href="/pages/add-product.php" class="active"><i class="fas fa-tags"></i> Sell</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/pages/seller/dashboard.php"><i class="fas fa-store"></i> Dashboard</a>
                <a href="/backend/auth/logout.php" class="nav-logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            <?php else: ?>
                <a href="/pages/auth/login.php" class="nav-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="/pages/auth/register.php?from=seller&role=seller" class="nav-register">
                    <i class="fas fa-user-plus"></i> Register to Sell
                </a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<div class="sell-page-wrapper">
    <a href="/index.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Back Home
    </a>

    <div class="sell-container">
        <h2><i class="fas fa-tags"></i> List Your Product</h2>
        <p>Fill in the details below to start selling on Masab Trade</p>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form id="productForm" method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label><i class="fas fa-box"></i> Product Name *</label>
                <input type="text" id="productName" name="productName"
                       placeholder="e.g. Samsung Galaxy Phone" required
                       value="<?php echo htmlspecialchars($_POST['productName'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label><i class="fas fa-list"></i> Category *</label>
                <select name="category" id="category" required>
                    <option value="">-- Select Category --</option>
                    <?php
                    $cats = [
                        'electronics' => 'Electronics',
                        'clothing'    => 'Clothing & Fashion',
                        'home'        => 'Home & Kitchen',
                        'books'       => 'Books & Media',
                        'sports'      => 'Sports & Outdoor',
                        'toys'        => 'Toys & Games',
                        'beauty'      => 'Beauty & Health',
                        'other'       => 'Other'
                    ];
                    foreach ($cats as $val => $label) {
                        $sel = (($_POST['category'] ?? '') === $val) ? 'selected' : '';
                        echo "<option value='$val' $sel>" . htmlspecialchars($label) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- ✅ Price and Quantity side by side -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Price (ZAR) *</label>
                    <input type="number" name="price" id="price"
                           placeholder="e.g. 500" min="1" step="0.01" required
                           value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-sort-amount-up"></i> Quantity *</label>
                    <input type="number" name="quantity" id="quantity"
                           placeholder="e.g. 5" min="1" step="1" required
                           value="<?php echo htmlspecialchars($_POST['quantity'] ?? '1'); ?>">
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-check-circle"></i> Condition *</label>
                <select name="condition" id="condition" required>
                    <option value="">-- Select Condition --</option>
                    <?php
                    $conds = [
                        'new'         => 'Brand New',
                        'like_new'    => 'Like New',
                        'used'        => 'Used - Good',
                        'refurbished' => 'Refurbished'
                    ];
                    foreach ($conds as $val => $label) {
                        $sel = (($_POST['condition'] ?? '') === $val) ? 'selected' : '';
                        echo "<option value='$val' $sel>" . htmlspecialchars($label) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label><i class="fas fa-align-left"></i> Description *</label>
                <textarea name="description" id="description"
                    placeholder="Describe your item in detail. Include brand, model, features and condition..."
                    required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label><i class="fas fa-map-marker-alt"></i> Location *</label>
                <input type="text" name="location" id="location"
                       placeholder="e.g. Soweto, Johannesburg" required
                       value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label><i class="fas fa-camera"></i> Product Image</label>
                <div class="image-upload" onclick="document.getElementById('imageInput').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p><strong>Click to upload image</strong></p>
                    <small>PNG, JPG, GIF up to 5MB</small>
                </div>
                <input type="file" id="imageInput" name="productImage"
                       accept="image/*" onchange="previewImages(event)">
                <div class="image-preview" id="imagePreview">
                    <img id="previewImg" src="" alt="Preview">
                </div>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-check"></i> List My Product
            </button>
        </form>
    </div>
</div>

<div id="sessionWarning"></div>
<script src="../js/main.js"></script>
<script src="../js/seller.js"></script>
</body>
</html>