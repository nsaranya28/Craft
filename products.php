<?php
session_start();
include 'includes/db.php';
include 'includes/cart-helper.php';

$category_id = isset($_GET['category']) ? $_GET['category'] : null;
$query = "SELECT * FROM products";
$params = [];

if ($category_id) {
    $query .= " WHERE category_id = ?";
    $params[] = $category_id;
}

$stmt = $pdo->prepare($query);

// Recently viewed
$recentViews = [];
if (isset($_SESSION['user_id'])) {
    $recentViews = getRecentViews($_SESSION['user_id'], 4);
}

// Recommended: products in viewed categories or random
$recProducts = [];
if (isset($_SESSION['user_id']) && !empty($recentViews)) {
    $viewedIds = array_column($recentViews, 'id');
    $ph = implode(',', array_fill(0, count($viewedIds), '?'));
    $rc = $pdo->prepare("SELECT id, name, image, base_price FROM products WHERE id NOT IN ($ph) ORDER BY RAND() LIMIT 4");
    $rc->execute($viewedIds);
    $recProducts = $rc->fetchAll(PDO::FETCH_ASSOC);
} else {
    $recProducts = $pdo->query("SELECT id, name, image, base_price FROM products ORDER BY RAND() LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
}
$wishlisted = (isset($_SESSION['user_id'])) ? getWishlistIds($_SESSION['user_id']) : [];

// Categories for filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
$stmt->execute($params);
$products = $stmt->fetchAll();

$catstmt = $pdo->query("SELECT * FROM categories");
$categories = $catstmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop | CraftyGifts</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=2.0">
</head>
<body>
    <!-- Navbar brand with cute ribbon -->
    <nav class="navbar navbar-expand-lg glass-nav">
        <div class="container-fluid">
            <a class="navbar-brand font-serif fs-3 fw-bold text-gradient" href="index.php">
                CraftyGifts
                <svg class="brand-ribbon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="36" height="36" style="color: var(--primary); fill: none; stroke: currentColor; stroke-width: 3; stroke-linecap: round; stroke-linejoin: round; vertical-align: middle; margin-left: 2px;">
                    <path d="M32 32 C20 18, 10 24, 16 36 C20 44, 30 36, 32 32 Z" fill="rgba(226, 95, 132, 0.15)"/>
                    <path d="M32 32 C44 18, 54 24, 48 36 C44 44, 34 36, 32 32 Z" fill="rgba(226, 95, 132, 0.15)"/>
                    <path d="M28 34 C24 44, 18 50, 20 54 M20 54 C23 54, 25 50, 27 46"/>
                    <path d="M36 34 C40 44, 46 50, 44 54 M44 54 C41 54, 39 50, 37 46"/>
                    <circle cx="32" cy="32" r="5" fill="var(--primary)"/>
                </svg>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars" style="color: var(--primary);"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link fw-medium" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active fw-medium" href="products.php">Shop</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="custom-request.php">Custom Order</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="cart.php"><i class="fas fa-shopping-cart me-1"></i>Cart <span class="badge bg-primary text-white rounded-pill px-2" style="font-size:0.75rem;"><?= getCartCount() ?></span></a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link fw-medium" href="user/wishlist.php"><i class="fa-regular fa-heart me-1"></i>Wishlist</a></li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex gap-2 align-items-center">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="user/dashboard.php" class="btn btn-outline-custom">Dashboard</a>
                        <a href="auth/logout.php" class="btn btn-primary-custom">Logout</a>
                    <?php else: ?>
                        <a href="auth/login.php" class="btn btn-outline-custom">Login</a>
                        <a href="auth/register.php" class="btn btn-primary-custom">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <div class="navbar-scallop-divider"></div>

    <main class="py-5">
        <div class="container">
            <div class="section-title fade-up">
                <h2>Browse Our Collection</h2>
                <p>Find the perfect gift or customize it to make it unique.</p>
            </div>

            <!-- Category Filter Pills -->
            <div class="d-flex gap-3 mb-5 justify-content-center flex-wrap fade-up">
                <a href="products.php" class="btn <?php echo !$category_id ? 'btn-primary-custom' : 'btn-outline-custom'; ?>">All Items</a>
                <?php foreach($categories as $cat): ?>
                    <a href="products.php?category=<?php echo $cat['id']; ?>" class="btn <?php echo $category_id == $cat['id'] ? 'btn-primary-custom' : 'btn-outline-custom'; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Product Grid -->
            <div class="row g-4 fade-up">
                <?php if (count($products) > 0): ?>
                    <?php foreach($products as $product): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card product-card h-100 position-relative">
                            <?php if (isset($_SESSION['user_id'])): ?>
                            <button class="btn position-absolute top-0 end-0 m-2 p-1" style="background:rgba(255,255,255,0.85);border-radius:50%;width:34px;height:34px;display:flex;align-items:center;justify-content:center;border:none;z-index:2;color:var(--pink-200);font-size:0.95rem;transition:all 0.2s;" onclick="event.stopPropagation();toggleWishlist(<?= $product['id'] ?>, this)" title="Wishlist">
                                <i class="fa-regular fa-heart" id="wl-icon-<?= $product['id'] ?>"></i>
                            </button>
                            <?php endif; ?>
                            <div style="height:200px;overflow:hidden;background:var(--pink-50);" onclick="location.href='product-details.php?id=<?= $product['id'] ?>'">
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width:100%;height:100%;object-fit:cover;cursor:pointer;" onerror="this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;color:var(--pink-200);font-size:3rem;\'><i class=\'fa-solid fa-gift\'></i></div>'">
                            </div>
                            <div class="card-body d-flex flex-column p-3">
                                <h3 class="h6 font-serif fw-bold mb-1"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="text-secondary small mb-2 flex-grow-1" style="font-size:0.8rem;"><?php echo htmlspecialchars(substr($product['description'], 0, 70)) . '...'; ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-auto gap-1 flex-wrap">
                                    <span class="fw-bold" style="color:var(--primary);font-size:1rem;">₹<?php echo number_format($product['base_price'], 2); ?></span>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm" style="background:var(--pink-50);color:var(--primary);border:none;border-radius:10px;font-size:0.75rem;" onclick="quickView(<?= $product['id'] ?>)"><i class="fas fa-eye"></i></button>
                                        <button class="btn btn-sm" style="background:var(--primary);color:white;border:none;border-radius:10px;font-size:0.75rem;" onclick="addToCart(<?= $product['id'] ?>, '<?= htmlspecialchars(addslashes($product['name'])) ?>')"><i class="fas fa-cart-plus me-1"></i>Cart</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <p class="text-secondary">No products found in this category.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($recProducts)): ?>
        <div class="mt-5 fade-up">
            <h4 class="font-serif fw-bold mb-3">✨ You Might Love</h4>
            <div class="row g-3">
                <?php foreach ($recProducts as $r):
                    $rimg = $r['image'];
                    if ($rimg && !preg_match('/^https?:\/\//i', $rimg)) $rimg = '../' . $rimg;
                ?>
                <div class="col-6 col-md-3">
                    <div class="card product-card h-100" style="border-radius:16px;overflow:hidden;">
                        <a href="product-details.php?id=<?= $r['id'] ?>">
                            <div style="height:150px;overflow:hidden;background:var(--pink-50);">
                                <img src="<?= htmlspecialchars($rimg) ?>" alt="" style="width:100%;height:100%;object-fit:cover;" onerror="this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;color:var(--pink-200);font-size:2rem;\'><i class=\'fa-solid fa-gift\'></i></div>'">
                            </div>
                        </a>
                        <div class="card-body p-2 text-center">
                            <h6 class="small fw-bold mb-1"><?= htmlspecialchars($r['name']) ?></h6>
                            <span class="fw-bold" style="color:var(--primary);font-size:0.85rem;">₹<?= number_format($r['base_price'], 2) ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($recentViews)): ?>
        <div class="mt-4 fade-up">
            <h4 class="font-serif fw-bold mb-3">👀 Recently Viewed</h4>
            <div class="row g-3">
                <?php foreach ($recentViews as $r):
                    $rimg = $r['image'];
                    if ($rimg && !preg_match('/^https?:\/\//i', $rimg)) $rimg = '../' . $rimg;
                ?>
                <div class="col-6 col-md-3">
                    <div class="card product-card h-100" style="border-radius:16px;overflow:hidden;">
                        <a href="product-details.php?id=<?= $r['id'] ?>">
                            <div style="height:150px;overflow:hidden;background:var(--pink-50);">
                                <img src="<?= htmlspecialchars($rimg) ?>" alt="" style="width:100%;height:100%;object-fit:cover;" onerror="this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;color:var(--pink-200);font-size:2rem;\'><i class=\'fa-solid fa-gift\'></i></div>'">
                            </div>
                        </a>
                        <div class="card-body p-2 text-center">
                            <h6 class="small fw-bold mb-1"><?= htmlspecialchars($r['name']) ?></h6>
                            <span class="fw-bold" style="color:var(--primary);font-size:0.85rem;">₹<?= number_format($r['base_price'], 2) ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <!-- Quick View Modal -->
    <div class="modal fade" id="quickViewModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius:20px;border:none;">
                <div class="modal-body p-0" id="quickViewContent">
                    <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast-notif" id="toast"><i class="fa-solid fa-check-circle me-2" style="color:var(--primary);"></i><span id="toastMsg">Added to cart!</span></div>
    <style>
        .toast-notif {
            position:fixed;top:20px;right:20px;z-index:9999;
            background:white;border-radius:16px;padding:1rem 1.5rem;
            box-shadow:0 12px 40px rgba(0,0,0,0.12);border-left:4px solid var(--primary);
            transform:translateX(120%);transition:transform 0.4s cubic-bezier(0.175,0.885,0.32,1.275);max-width:360px;
        }
        .toast-notif.show { transform:translateX(0); }
    </style>

    <!-- Footer -->
    <footer class="mt-5">
        <div class="container text-center">
            <p class="mb-0 text-secondary">&copy; 2026 CraftyGifts. All rights reserved. Handcrafted with <i class="fas fa-heart text-danger mx-1"></i></p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Fade up animation script -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const fadeElements = document.querySelectorAll('.fade-up');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, { threshold: 0.1 });
            
            fadeElements.forEach(el => observer.observe(el));
        });

        <?php if (isset($_SESSION['user_id'])): ?>
        async function toggleWishlist(pid, btn) {
            const icon = btn.querySelector('i');
            const isHearted = icon.classList.contains('fa-solid');
            const form = new FormData();
            form.append('product_id', pid);
            form.append('action', isHearted ? 'remove' : 'add');
            try {
                const res = await fetch('user/wishlist-action.php', { method: 'POST', body: form });
                const data = await res.json();
                if (data.success) {
                    if (data.action === 'added') {
                        icon.className = 'fa-solid fa-heart';
                        btn.style.color = '#e55';
                        showToast('Added to wishlist ♡');
                    } else {
                        icon.className = 'fa-regular fa-heart';
                        btn.style.color = 'var(--pink-200)';
                        showToast('Removed from wishlist');
                    }
                }
            } catch(e) {}
        }
        <?php endif; ?>

        // ── Add to Cart ──
        async function addToCart(pid, name) {
            const form = new FormData();
            form.append('action', 'add');
            form.append('product_id', pid);
            form.append('quantity', 1);
            try {
                const res = await fetch('cart-action.php', { method: 'POST', body: form });
                const data = await res.json();
                if (data.success) {
                    showToast(data.message || name + ' added to cart!');
                    const badge = document.querySelector('#navbarNav .badge');
                    if (badge) badge.textContent = data.count;
                }
            } catch(e) { showToast('Could not add to cart'); }
        }

        // ── Quick View ──
        async function quickView(pid) {
            const modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
            document.getElementById('quickViewContent').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
            modal.show();
            try {
                const res = await fetch('product-details.php?ajax=1&id=' + pid);
                const html = await res.text();
                document.getElementById('quickViewContent').innerHTML = html || '<div class="p-4 text-center">Product not found</div>';
            } catch(e) {
                document.getElementById('quickViewContent').innerHTML = '<div class="p-4 text-center">Could not load product.</div>';
            }
        }

        // ── Toast ──
        function showToast(msg) {
            document.getElementById('toastMsg').textContent = msg;
            document.getElementById('toast').classList.add('show');
            setTimeout(() => document.getElementById('toast').classList.remove('show'), 2500);
        }
    </script>
</body>
</html>
