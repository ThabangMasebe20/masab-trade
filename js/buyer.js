let currentProductId    = 0;
let currentProductName  = '';
let currentProductPrice = 0;

document.addEventListener('DOMContentLoaded', function () {
    var si = document.getElementById('searchInput');
    if (si) {
        si.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') searchProducts();
        });
    }
});


function viewProduct(id, name, price, desc, condition, location, imgSrc, qty, reviews) {
    currentProductId    = id;
    currentProductName  = name;
    currentProductPrice = price;

    document.getElementById('modalTitle').textContent       = name;
    document.getElementById('modalPrice').textContent       = 'R ' + parseFloat(price).toFixed(2);
    document.getElementById('modalDescription').textContent = desc;
    document.getElementById('modalLocation').textContent    = location;

    var condMap = {
        'new':         'Brand New',
        'like_new':    'Like New',
        'used':        'Used - Good',
        'refurbished': 'Refurbished'
    };
    var condDisplay = condMap[condition] ||
        condition.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
    document.getElementById('modalCondition').textContent = condDisplay;

    document.getElementById('modalImage').src = imgSrc;
    document.getElementById('modalImage').alt = name;

    // Stock count
    var stockRow = document.getElementById('modalStockRow');
    var stockEl  = document.getElementById('modalStock');
    if (qty === null || qty === undefined) {
        if (stockRow) stockRow.style.display = 'none';
    } else {
        if (stockRow) stockRow.style.display = 'flex';
        if (stockEl) {
            var txt   = '';
            var color = '#27ae60';
            if (qty >= 10)     { txt = qty + ' in stock';       color = '#27ae60'; }
            else if (qty >= 4) { txt = 'Only ' + qty + ' left'; color = '#f39c12'; }
            else if (qty > 1)  { txt = 'Only ' + qty + ' left!';color = '#e67e22'; }
            else if (qty === 1){ txt = 'Last one available!';    color = '#e74c3c'; }
            else               { txt = 'Out of stock';           color = '#e74c3c'; }
            stockEl.textContent = txt;
            stockEl.style.color = color;
        }
    }

    
    var revList  = document.getElementById('modalReviewsList');
    var revTitle = document.getElementById('modalReviewTitle');

    if (revList) {
        if (!reviews || reviews.length === 0) {
            if (revTitle) revTitle.innerHTML = '<i class="fas fa-star" style="color:#f39c12;"></i> Customer Reviews';
            revList.innerHTML = '<p style="color:#7f8c8d; font-size:.88rem;">No reviews yet. Be the first to review this product!</p>';
        } else {
            if (revTitle) revTitle.innerHTML = '<i class="fas fa-star" style="color:#f39c12;"></i> Customer Reviews (' + reviews.length + ')';
            var html = '';
            reviews.forEach(function (r) {
                var stars = '';
                for (var i = 1; i <= 5; i++) {
                    stars += '<i class="' + (i <= r.rating ? 'fas' : 'far') + ' fa-star" style="color:#f39c12; font-size:.85rem;"></i>';
                }
                html += '<div style="border-bottom:1px solid #f0f2f5; padding:12px 0;">' +
                    '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">' +
                        '<div style="display:flex; align-items:center; gap:8px;">' +
                            '<strong style="font-size:.88rem; color:#2c3e50;">' + r.buyer + '</strong>' +
                            '<span>' + stars + '</span>' +
                        '</div>' +
                        '<span style="font-size:.78rem; color:#95a5a6;">' + r.date + '</span>' +
                    '</div>' +
                    (r.title ? '<p style="font-size:.88rem; font-weight:600; color:#2c3e50; margin:0 0 4px;">' + r.title + '</p>' : '') +
                    '<p style="font-size:.85rem; color:#7f8c8d; margin:0; line-height:1.5;">' + r.text + '</p>' +
                '</div>';
            });
            revList.innerHTML = html;
        }
    }

    document.getElementById('productModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function buyNow() {
    var loggedIn = document.getElementById('userLoggedIn');
    var isLogged = loggedIn && loggedIn.value === '1';

    if (currentProductId === 0) {
        alert('This is a sample display product.\nPlease browse real listings from sellers!');
        return;
    }
    if (!isLogged) {
        window.location.href = '/pages/auth/login.php?redirect=checkout&pid=' + currentProductId;
        return;
    }
    window.location.href = '/pages/buyer/checkout.php?product_id=' + currentProductId;
}

function closeModalBtn() {
    document.getElementById('productModal').classList.remove('active');
    document.body.style.overflow = '';
}

function closeModal(e) {
    if (e.target === document.getElementById('productModal')) closeModalBtn();
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeModalBtn();
});

function searchProducts() {
    var term  = document.getElementById('searchInput').value.toLowerCase().trim();
    var count = 0;
    document.querySelectorAll('.product-card').forEach(function (card) {
        var title = card.querySelector('.product-title').textContent.toLowerCase();
        var info  = card.querySelector('.product-info').textContent.toLowerCase();
        var show  = title.includes(term) || info.includes(term);
        card.style.display = show ? '' : 'none';
        if (show) count++;
    });
    showNoResults(count);
}

function filterProducts() {
    var cat   = document.getElementById('categoryFilter').value;
    var price = document.getElementById('priceFilter').value;
    var cond  = document.getElementById('conditionFilter').value;
    var count = 0;

    document.querySelectorAll('.product-card').forEach(function (card) {
        var show = true;
        if (cat  !== 'all' && card.dataset.category  !== cat)  show = false;
        if (cond !== 'all' && card.dataset.condition  !== cond) show = false;
        if (price !== 'all' && show) {
            var p = parseFloat(card.dataset.price);
            if (price === '0-100'    && p > 100)               show = false;
            if (price === '100-500'  && (p < 100  || p > 500)) show = false;
            if (price === '500-1000' && (p < 500  || p > 1000))show = false;
            if (price === '1000+'    && p < 1000)               show = false;
        }
        card.style.display = show ? '' : 'none';
        if (show) count++;
    });
    showNoResults(count);
}

function showNoResults(count) {
    var div = document.getElementById('noResults');
    if (count === 0) {
        if (!div) {
            div = document.createElement('div');
            div.id        = 'noResults';
            div.className = 'no-results';
            div.innerHTML = '<i class="fas fa-search"></i><h3>No products found</h3><p>Try adjusting your search or filters</p>';
            document.querySelector('.products-grid').appendChild(div);
        }
        div.style.display = 'block';
    } else if (div) {
        div.style.display = 'none';
    }
}

function clearFilters() {
    ['searchInput','categoryFilter','priceFilter','conditionFilter'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.value = el.tagName === 'SELECT' ? 'all' : '';
    });
    document.querySelectorAll('.product-card').forEach(function (c) { c.style.display = ''; });
    var div = document.getElementById('noResults');
    if (div) div.style.display = 'none';
}
