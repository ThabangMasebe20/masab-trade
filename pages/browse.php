<?php
include '../backend/includes/session-manager.php';
include '../backend/config/database.php';

$sql = "SELECT p.*, sp.seller_id, u.username AS seller_name
        FROM products p
        JOIN seller_profiles sp ON p.seller_id = sp.seller_id
        JOIN users u ON sp.user_id = u.user_id
        WHERE p.is_available = 1 AND p.quantity > 0
        ORDER BY p.date_listed DESC";

$result      = $conn->query($sql);
$db_products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];


$product_reviews = [];
if (!empty($db_products)) {
    $pids = implode(',', array_map(fn($p) => intval($p['product_id']), $db_products));
    $rev_sql = "SELECT r.*, u.username AS buyer_name
                FROM reviews r
                JOIN users u ON r.buyer_id = u.user_id
                WHERE r.product_id IN ($pids)
                ORDER BY r.review_date DESC";
    $rev_res = $conn->query($rev_sql);
    if ($rev_res) {
        while ($row = $rev_res->fetch_assoc()) {
            $product_reviews[$row['product_id']][] = $row;
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
    <title>Browse Products — Masab Trade</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/buyer.css">
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
            <a href="/pages/browse.php" class="active">
                <i class="fas fa-shopping-bag"></i> Buy
            </a>
            <a href="/pages/add-product.php"><i class="fas fa-tags"></i> Sell</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="nav-username">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                </span>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a href="/pages/admin/dashboard.php">
                        <i class="fas fa-user-shield"></i> Admin
                    </a>
                <?php elseif ($_SESSION['user_role'] === 'seller'): ?>
                    <a href="/pages/seller/dashboard.php">
                        <i class="fas fa-store"></i> Dashboard
                    </a>
                <?php else: ?>
                    <a href="/pages/buyer/dashboard.php">
                        <i class="fas fa-user"></i> My Account
                    </a>
                <?php endif; ?>
                <a href="/backend/auth/logout.php" class="nav-logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            <?php else: ?>
                <a href="/pages/auth/login.php" class="nav-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="/pages/auth/register.php?from=browse&role=buyer" class="nav-register">
                    <i class="fas fa-user-plus"></i> Register to Buy
                </a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<input type="hidden" id="userLoggedIn"
       value="<?php echo isset($_SESSION['user_id']) ? '1' : '0'; ?>">

<div class="browse-container">
    <a href="/index.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Home
    </a>

    <div class="browse-header">
        <h2><i class="fas fa-shopping-bag"></i> Browse Products</h2>
        <p>Discover great deals from local sellers in your community</p>
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Search for products...">
            <button onclick="searchProducts()">
                <i class="fas fa-search"></i> Search
            </button>
        </div>
    </div>

    <div class="filters">
        <h3><i class="fas fa-filter"></i> Filter:</h3>
        <div class="filter-group">
            <select id="categoryFilter" onchange="filterProducts()">
                <option value="all">All Categories</option>
                <option value="electronics">Electronics</option>
                <option value="clothing">Clothing</option>
                <option value="home">Home & Kitchen</option>
                <option value="books">Books</option>
                <option value="sports">Sports</option>
                <option value="toys">Toys</option>
                <option value="beauty">Beauty</option>
                <option value="other">Other</option>
            </select>
            <select id="priceFilter" onchange="filterProducts()">
                <option value="all">All Prices</option>
                <option value="0-100">Under R100</option>
                <option value="100-500">R100 – R500</option>
                <option value="500-1000">R500 – R1000</option>
                <option value="1000+">Above R1000</option>
            </select>
            <select id="conditionFilter" onchange="filterProducts()">
                <option value="all">All Conditions</option>
                <option value="new">Brand New</option>
                <option value="like_new">Like New</option>
                <option value="used">Used</option>
                <option value="refurbished">Refurbished</option>
            </select>
        </div>
    </div>

    <div class="products-grid" id="productsGrid">

        <?php if (empty($db_products)): ?>
            <!-- SAMPLE products shown only when DB has none -->
            <div class="product-card" data-category="electronics" data-price="2500" data-condition="used">
                <img src="../assets/images/products/a52.png" alt="Samsung Galaxy A52" class="product-image"
                     onerror="this.src='../assets/images/products/placeholder.png'">
                <div class="product-info">
                    <h3 class="product-title">Samsung Galaxy A52</h3>
                    <div class="product-price">R 2,500</div>
                    <div class="product-location"><i class="fas fa-map-marker-alt"></i> Soweto, Johannesburg</div>
                    <div class="product-rating">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        <i class="fas fa-star"></i><i class="far fa-star"></i> <span>(12)</span>
                    </div>
                    <div class="sample-badge"><i class="fas fa-info-circle"></i> Sample product</div>
                    <button class="view-btn" onclick="viewProduct(0,'Samsung Galaxy A52',2500,'Samsung Galaxy A52 in excellent condition. 128GB storage, 6GB RAM.','used','Soweto, Johannesburg','../assets/images/products/a52.png',null,[])">
                        <i class="fas fa-eye"></i> View Details
                    </button>
                </div>
            </div>

            <div class="product-card" data-category="clothing" data-price="150" data-condition="new">
                <img src="../assets/images/products/airforce.png" alt="Nike Air Force 1" class="product-image"
                     onerror="this.src='../assets/images/products/placeholder.png'">
                <div class="product-info">
                    <h3 class="product-title">Nike Air Force 1 Mid</h3>
                    <div class="product-price">R 150</div>
                    <div class="product-location"><i class="fas fa-map-marker-alt"></i> Khayelitsha, Cape Town</div>
                    <div class="product-rating">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        <i class="fas fa-star"></i><i class="fas fa-star"></i> <span>(8)</span>
                    </div>
                    <div class="sample-badge"><i class="fas fa-info-circle"></i> Sample product</div>
                    <button class="view-btn" onclick="viewProduct(0,'Nike Air Force 1 Mid',150,'Brand new Nike Air Force 1 Mid. White colorway. Size 10.','new','Khayelitsha, Cape Town','../assets/images/products/airforce.png',null,[])">
                        <i class="fas fa-eye"></i> View Details
                    </button>
                </div>
            </div>

            <div class="product-card" data-category="home" data-price="800" data-condition="used">
                <img src="../assets/images/products/microwave.png" alt="Samsung Microwave" class="product-image"
                     onerror="this.src='../assets/images/products/placeholder.png'">
                <div class="product-info">
                    <h3 class="product-title">Samsung Microwave</h3>
                    <div class="product-price">R 800</div>
                    <div class="product-location"><i class="fas fa-map-marker-alt"></i> Umlazi, Durban</div>
                    <div class="product-rating">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        <i class="fas fa-star"></i><i class="far fa-star"></i> <span>(5)</span>
                    </div>
                    <div class="sample-badge"><i class="fas fa-info-circle"></i> Sample product</div>
                    <button class="view-btn" onclick="viewProduct(0,'Samsung Microwave',800,'Samsung mirror finish microwave. 1000W power.','used','Umlazi, Durban','../assets/images/products/microwave.png',null,[])">
                        <i class="fas fa-eye"></i> View Details
                    </button>
                </div>
            </div>

            <div class="product-card" data-category="books" data-price="50" data-condition="used">
                <img src="../assets/images/products/textbook.png" alt="Textbooks" class="product-image"
                     onerror="this.src='../assets/images/products/placeholder.png'">
                <div class="product-info">
                    <h3 class="product-title">University Textbooks</h3>
                    <div class="product-price">R 50</div>
                    <div class="product-location"><i class="fas fa-map-marker-alt"></i> Tembisa, Johannesburg</div>
                    <div class="product-rating">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        <i class="fas fa-star"></i><i class="fas fa-star"></i> <span>(15)</span>
                    </div>
                    <div class="sample-badge"><i class="fas fa-info-circle"></i> Sample product</div>
                    <button class="view-btn" onclick="viewProduct(0,'University Textbooks',50,'Bundle of university textbooks.','used','Tembisa, Johannesburg','../assets/images/products/textbook.png',null,[])">
                        <i class="fas fa-eye"></i> View Details
                    </button>
                </div>
            </div>

        <?php else: ?>
            <?php foreach ($db_products as $p):
                $img    = !empty($p['image_url']) ? '../' . $p['image_url'] : '../assets/images/products/placeholder.png';
                $name   = addslashes(htmlspecialchars($p['product_name']));
                $desc   = addslashes(htmlspecialchars($p['description']));
                $loc    = addslashes(htmlspecialchars($p['location']));
                $qty    = intval($p['quantity']);
                $pid    = $p['product_id'];
                $revs   = $product_reviews[$pid] ?? [];
                $rev_count = count($revs);
                $avg_rating = $rev_count > 0
                    ? array_sum(array_column($revs, 'rating')) / $rev_count
                    : 0;

                // Stock badge
                if ($qty >= 10)      { $sc = 'stock-high';   $sl = $qty . ' in stock'; }
                elseif ($qty >= 4)   { $sc = 'stock-medium'; $sl = 'Only ' . $qty . ' left'; }
                elseif ($qty > 1)    { $sc = 'stock-low';    $sl = 'Only ' . $qty . ' left!'; }
                else                 { $sc = 'stock-last';   $sl = 'Last one!'; }

                // Build reviews JSON for JS
                $revs_js = json_encode(array_map(fn($r) => [
                    'buyer'  => $r['buyer_name'],
                    'rating' => $r['rating'],
                    'title'  => $r['review_title'] ?? '',
                    'text'   => $r['review_text'],
                    'date'   => date('d M Y', strtotime($r['review_date']))
                ], $revs));
            ?>
                <div class="product-card"
                     data-category="<?php echo htmlspecialchars($p['category']); ?>"
                     data-price="<?php echo $p['price']; ?>"
                     data-condition="<?php echo htmlspecialchars($p['condition_status']); ?>">

                    <div style="position:relative; overflow:hidden;">
                        <img src="<?php echo htmlspecialchars($img); ?>"
                             alt="<?php echo htmlspecialchars($p['product_name']); ?>"
                             class="product-image"
                             onerror="this.src='../assets/images/products/placeholder.png'">
                        <div class="stock-badge <?php echo $sc; ?>">
                            <i class="fas fa-cubes"></i>
                            <?php echo htmlspecialchars($sl); ?>
                        </div>
                    </div>

                    <div class="product-info">
                        <h3 class="product-title"><?php echo htmlspecialchars($p['product_name']); ?></h3>
                        <div class="product-price">R <?php echo number_format($p['price'], 2); ?></div>
                        <div class="product-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($p['location']); ?>
                        </div>
                        <div class="product-location" style="color:#667eea; margin-top:4px;">
                            <i class="fas fa-store"></i>
                            <?php echo htmlspecialchars($p['seller_name']); ?>
                        </div>
                        
                        <div class="product-rating">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <i class="<?php echo $s <= round($avg_rating) ? 'fas' : 'far'; ?> fa-star"></i>
                            <?php endfor; ?>
                            <span>(<?php echo $rev_count; ?> review<?php echo $rev_count !== 1 ? 's' : ''; ?>)</span>
                        </div>

                        <button class="view-btn" onclick="viewProduct(
                            <?php echo $pid; ?>,
                            '<?php echo $name; ?>',
                            <?php echo floatval($p['price']); ?>,
                            '<?php echo $desc; ?>',
                            '<?php echo htmlspecialchars($p['condition_status']); ?>',
                            '<?php echo $loc; ?>',
                            '<?php echo addslashes($img); ?>',
                            <?php echo $qty; ?>,
                            <?php echo $revs_js; ?>)">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</div>

<!-- PRODUCT MODAL -->
<div id="productModal" class="modal-overlay" onclick="closeModal(event)">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModalBtn()">
            <i class="fas fa-times"></i>
        </button>

        <div class="modal-content">
            <div class="modal-image-side">
                <img id="modalImage" src="" alt="Product">
            </div>

            <div class="modal-info-side">
                <h2 id="modalTitle"></h2>
                <div class="modal-price" id="modalPrice"></div>

                <div class="modal-detail">
                    <i class="fas fa-map-marker-alt"></i>
                    <span id="modalLocation"></span>
                </div>
                <div class="modal-detail">
                    <i class="fas fa-check-circle"></i>
                    <span id="modalCondition"></span>
                </div>
                <div class="modal-detail" id="modalStockRow">
                    <i class="fas fa-cubes"></i>
                    <span id="modalStock"></span>
                </div>

                <div class="modal-description">
                    <h4>Description:</h4>
                    <p id="modalDescription"></p>
                </div>

                <div class="modal-actions">
                    <button class="btn-buy-now" onclick="buyNow()">
                        <i class="fas fa-shopping-cart"></i> Buy Now
                    </button>
                    <button class="btn-save" onclick="closeModalBtn()">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>

                <p id="sampleNotice"
                   style="display:none; text-align:center; color:#7f8c8d; font-size:.82rem; margin-top:10px;">
                    <i class="fas fa-info-circle"></i> This is a sample display product.
                </p>
            </div>
        </div>

        
        <div id="modalReviews" style="padding:20px 30px; border-top:1px solid #f0f2f5;">
            <h3 id="modalReviewTitle"
                style="font-size:1rem; color:#2c3e50; margin-bottom:14px;">
                <i class="fas fa-star" style="color:#f39c12;"></i> Customer Reviews
            </h3>
            <div id="modalReviewsList"></div>
        </div>

    </div>
</div>

<div id="sessionWarning"></div>
<script src="../js/main.js"></script>
<script src="../js/buyer.js"></script>

<script>
const _orig = viewProduct;
viewProduct = function(id, name, price, desc, condition, location, imgSrc, qty, reviews) {
    _orig(id, name, price, desc, condition, location, imgSrc, qty);

    // sample notice
    var notice = document.getElementById('sampleNotice');
    if (notice) notice.style.display = (id === 0) ? 'block' : 'none';

    // Render reviews
    var revList  = document.getElementById('modalReviewsList');
    var revTitle = document.getElementById('modalReviewTitle');
    if (!revList) return;

    if (!reviews || reviews.length === 0) {
        revTitle.innerHTML = '<i class="fas fa-star" style="color:#f39c12;"></i> Customer Reviews';
        revList.innerHTML  = '<p style="color:#7f8c8d; font-size:.88rem;">No reviews yet. Be the first to review this product!</p>';
        return;
    }

    revTitle.innerHTML = '<i class="fas fa-star" style="color:#f39c12;"></i> Customer Reviews (' + reviews.length + ')';

    var html = '';
    reviews.forEach(function(r) {
        var stars = '';
        for (var i = 1; i <= 5; i++) {
            stars += '<i class="' + (i <= r.rating ? 'fas' : 'far') + ' fa-star" style="color:#f39c12; font-size:.85rem;"></i>';
        }
        html += '<div style="border-bottom:1px solid #f0f2f5; padding:12px 0;">' +
                  '<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:5px;">' +
                    '<div style="display:flex; align-items:center; gap:8px;">' +
                      '<span style="font-weight:700; font-size:.88rem; color:#2c3e50;">' + r.buyer + '</span>' +
                      '<span>' + stars + '</span>' +
                    '</div>' +
                    '<span style="font-size:.78rem; color:#95a5a6;">' + r.date + '</span>' +
                  '</div>' +
                  (r.title ? '<p style="font-size:.88rem; font-weight:600; color:#2c3e50; margin:0 0 4px;">' + r.title + '</p>' : '') +
                  '<p style="font-size:.85rem; color:#7f8c8d; margin:0; line-height:1.5;">' + r.text + '</p>' +
                '</div>';
    });
    revList.innerHTML = html;
};
</script>

</body>
</html>
