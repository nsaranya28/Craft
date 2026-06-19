<?php
session_start();
include 'includes/db.php';
include 'includes/cart-helper.php';

$id = isset($_GET['id']) ? $_GET['id'] : die('Product not found');
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if(!$product) die('Product not found');

// Track recently viewed (only for logged-in users, not for AJAX quick-view)
if (!isset($_GET['ajax']) && isset($_SESSION['user_id'])) {
    trackRecentView($_SESSION['user_id'], $product['id']);
}
$recentViews = isset($_SESSION['user_id']) ? getRecentViews($_SESSION['user_id'], 4) : [];

// Get recommended products (same category)
$recStmt = $pdo->prepare("SELECT id, name, image, base_price, stock_quantity FROM products WHERE category_id = ? AND id != ? ORDER BY RAND() LIMIT 4");
$recStmt->execute([$product['category_id'], $product['id']]);
$recommended = $recStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> | Customize | CraftyGifts</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=2.0">
    <style>
        /* Custom Size Selector */
        .size-selector input[type="radio"] {
            display: none;
        }
        .size-selector label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 52px;
            height: 52px;
            border-radius: 16px;
            border: 2px solid var(--pink-200);
            color: var(--text);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            background: var(--white);
        }
        .size-selector input[type="radio"]:checked + label {
            background-color: var(--primary);
            border-color: var(--primary);
            color: var(--white);
            transform: scale(1.08);
            box-shadow: 0 6px 16px rgba(226, 95, 132, 0.25);
        }

        /* Custom Color Swatches */
        .color-selector input[type="radio"] {
            display: none;
        }
        .color-selector .swatch-container {
            text-align: center;
            cursor: pointer;
        }
        .color-selector .swatch {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            border-radius: 50%;
            border: 2px solid var(--pink-200);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.06);
            cursor: pointer;
        }
        .color-selector .swatch-white {
            background-color: #FFFFFF;
            border: 2.5px solid #FBC5D2;
        }
        .color-selector .swatch-black {
            background-color: #2D2D2D;
            border-color: #2D2D2D;
        }
        .color-selector .swatch-natural {
            background-color: #E6C29E;
            border-color: #E6C29E;
        }
        .color-selector input[type="radio"]:checked + .swatch {
            transform: scale(1.18);
            box-shadow: 0 0 0 3px var(--white), 0 0 0 6px var(--primary);
        }
        .color-selector .swatch-label-text {
            font-size: 0.8rem;
            margin-top: 6px;
            color: var(--text-light);
            font-weight: 600;
            transition: color 0.2s;
        }
        .color-selector input[type="radio"]:checked ~ .swatch-label-text {
            color: var(--primary);
        }

        /* Custom File Upload */
        .custom-file-upload {
            position: relative;
        }
        .file-upload-label {
            display: block;
            border: 2px dashed var(--pink-300);
            padding: 1.5rem;
            border-radius: 16px;
            background: var(--cream);
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--text);
        }
        .file-upload-label:hover {
            background: var(--pink-50);
            border-color: var(--primary);
            transform: translateY(-2px);
        }
        .file-upload-label i {
            color: var(--primary);
        }

        /* Custom Quantity Selector */
        .quantity-selector {
            border: 2px solid var(--pink-200);
            border-radius: 12px;
            overflow: hidden;
            background: var(--cream);
            width: fit-content;
            display: flex;
            align-items: center;
        }
        .qty-btn {
            border: none;
            background: transparent;
            width: 44px;
            height: 44px;
            color: var(--primary);
            cursor: pointer;
            transition: background 0.2s;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .qty-btn:hover {
            background: var(--pink-100);
        }
        .qty-input {
            border: none;
            background: transparent;
            width: 50px;
            text-align: center;
            font-weight: 600;
            color: var(--text);
            outline: none;
        }
        .qty-input::-webkit-outer-spin-button,
        .qty-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
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
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="cart.php">
                            <i class="fas fa-shopping-cart me-1"></i>Cart 
                            <span class="badge bg-primary text-white rounded-pill px-2" style="font-size: 0.75rem; vertical-align: middle;">
                                <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="user/wishlist.php"><i class="fa-regular fa-heart me-1"></i>Wishlist</a>
                    </li>
                </ul>
                <div class="d-flex gap-2">
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
            <div class="row g-5">
                <!-- Product Preview Image -->
                <div class="col-lg-6">
                    <div class="card p-4 border-pink rounded-cute bg-white text-center shadow-sm">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid rounded-cute shadow-sm mb-4" style="max-height: 480px; object-fit: cover; width: 100%;">
                        <div class="text-start px-2">
                            <h2 class="font-serif mb-2 text-dark fs-1 fw-bold"><?php echo htmlspecialchars($product['name']); ?></h2>
                            <p class="price-tag fs-2 mb-3">$<?php echo htmlspecialchars($product['base_price']); ?></p>
                            <p class="text-secondary" style="font-size: 1.05rem; line-height: 1.7;"><?php echo htmlspecialchars($product['description']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Customization Form -->
                <div class="col-lg-6">
                    <div class="card p-5 shadow-sm border-pink rounded-cute bg-white">
                        <h2 class="font-serif mb-4 text-dark border-bottom pb-3 border-pink-dashed">Customize Your Item</h2>
                        
                        <form action="cart-action.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            
                            <!-- Size Options -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark mb-2">Choose Size</label>
                                <div class="size-selector d-flex gap-3">
                                    <div>
                                        <input type="radio" name="size" id="size-s" value="Small" checked>
                                        <label for="size-s">S</label>
                                    </div>
                                    <div>
                                        <input type="radio" name="size" id="size-m" value="Medium">
                                        <label for="size-m">M</label>
                                    </div>
                                    <div>
                                        <input type="radio" name="size" id="size-l" value="Large">
                                        <label for="size-l">L</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Color Options -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark mb-2">Select Color</label>
                                <div class="color-selector d-flex gap-4">
                                    <div class="swatch-container">
                                        <input type="radio" name="color" id="color-white" value="White" checked>
                                        <label for="color-white" class="swatch swatch-white" title="White"></label>
                                        <div class="swatch-label-text">White</div>
                                    </div>
                                    <div class="swatch-container">
                                        <input type="radio" name="color" id="color-black" value="Black">
                                        <label for="color-black" class="swatch swatch-black" title="Black"></label>
                                        <div class="swatch-label-text">Black</div>
                                    </div>
                                    <div class="swatch-container">
                                        <input type="radio" name="color" id="color-natural" value="Natural">
                                        <label for="color-natural" class="swatch swatch-natural" title="Natural"></label>
                                        <div class="swatch-label-text">Natural</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Personalization Text -->
                            <div class="mb-4 mt-2">
                                <label class="form-label fw-bold text-dark mb-1">Custom Text / Personalization</label>
                                <p class="text-secondary small mb-2">Enter the text you want engraved or printed on the item.</p>
                                <textarea class="form-control" name="custom_text" rows="3" placeholder="e.g. 'Happy Birthday Sarah!', 'Established 2024'" style="background: var(--cream); resize: none;"></textarea>
                            </div>

                            <!-- File Upload -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark mb-1">Upload Reference Image (Optional)</label>
                                <p class="text-secondary small mb-2">Attach a photo or sketch for reference or custom printing.</p>
                                <div class="custom-file-upload">
                                    <input type="file" name="custom_image" id="custom_image" class="d-none">
                                    <label for="custom_image" class="file-upload-label">
                                        <i class="fas fa-cloud-upload-alt mb-2 fs-3"></i>
                                        <span class="d-block fw-medium">Choose a file or drag it here</span>
                                        <span class="file-name text-secondary small mt-1 d-block">No file selected</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Quantity & Action -->
                            <div class="d-flex align-items-end justify-content-between gap-4 mt-5">
                                <div style="flex-shrink: 0;">
                                    <label class="form-label fw-bold text-dark mb-2">Quantity</label>
                                    <div class="quantity-selector">
                                        <button type="button" class="qty-btn" id="qty-minus"><i class="fas fa-minus"></i></button>
                                        <input type="number" name="quantity" id="quantity-input" value="1" min="1" class="qty-input">
                                        <button type="button" class="qty-btn" id="qty-plus"><i class="fas fa-plus"></i></button>
                                    </div>
                                </div>
                                <div style="flex-grow: 1;display:flex;gap:8px;">
                                    <button type="button" id="wishlistBtn" class="btn py-3 d-flex align-items-center justify-content-center gap-2" style="height:52px;width:52px;border-radius:14px;border:2px solid var(--pink-200);background:white;flex-shrink:0;font-size:1.2rem;color:var(--pink-200);transition:all 0.2s;" onclick="toggleWishlist(<?php echo $product['id']; ?>)" title="Add to Wishlist">
                                        <i id="wishlistIcon" class="fa-regular fa-heart"></i>
                                    </button>
                                    <button type="submit" class="btn btn-primary-custom w-100 py-3 d-flex align-items-center justify-content-center gap-2" style="height: 52px; font-size: 1.05rem;">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($recommended)): ?>
        <div class="mt-5 fade-up">
            <h4 class="font-serif fw-bold mb-3">✨ You May Also Like</h4>
            <div class="row g-3">
                <?php foreach ($recommended as $r):
                    $rimg = $r['image'];
                    if ($rimg && !preg_match('/^https?:\/\//i', $rimg)) $rimg = '../' . $rimg;
                ?>
                <div class="col-6 col-md-3">
                    <div class="card product-card h-100 border-pink" style="border-radius:16px;overflow:hidden;">
                        <a href="product-details.php?id=<?= $r['id'] ?>">
                            <div style="height:160px;overflow:hidden;background:var(--pink-50);">
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

        <?php if (!empty($recentViews) && !isset($_GET['ajax'])): ?>
        <div class="mt-4 fade-up">
            <h4 class="font-serif fw-bold mb-3">👀 Recently Viewed</h4>
            <div class="row g-3">
                <?php foreach ($recentViews as $r):
                    $rimg = $r['image'];
                    if ($rimg && !preg_match('/^https?:\/\//i', $rimg)) $rimg = '../' . $rimg;
                ?>
                <div class="col-6 col-md-3">
                    <div class="card product-card h-100 border-pink" style="border-radius:16px;overflow:hidden;">
                        <a href="product-details.php?id=<?= $r['id'] ?>">
                            <div style="height:160px;overflow:hidden;background:var(--pink-50);">
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

    <!-- Footer -->
    <footer class="mt-5">
        <div class="container text-center">
            <p class="mb-0 text-secondary">&copy; 2026 CraftyGifts. All rights reserved. Handcrafted with <i class="fas fa-heart text-danger mx-1"></i></p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // File upload custom presentation
        document.getElementById('custom_image').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : "No file selected";
            this.parentElement.querySelector('.file-name').textContent = fileName;
        });

        // Quantity controls
        document.getElementById('qty-minus').addEventListener('click', () => {
            const input = document.getElementById('quantity-input');
            if (input.value > 1) input.value = parseInt(input.value) - 1;
        });
        document.getElementById('qty-plus').addEventListener('click', () => {
            const input = document.getElementById('quantity-input');
            input.value = parseInt(input.value) + 1;
        });

        // ── Wishlist Toggle ──
        <?php if (isset($_SESSION['user_id'])): ?>
        checkWishlist(<?= $product['id'] ?>);
        <?php endif; ?>
        async function checkWishlist(pid) {
            const form = new FormData();
            form.append('product_id', pid);
            form.append('action', 'check');
            try {
                const res = await fetch('user/wishlist-action.php', { method: 'POST', body: form });
                const data = await res.json();
                if (data.wishlisted) {
                    document.getElementById('wishlistIcon').className = 'fa-solid fa-heart';
                    document.getElementById('wishlistBtn').style.color = '#e55';
                    document.getElementById('wishlistBtn').style.borderColor = '#e55';
                }
            } catch(e) {}
        }
        async function toggleWishlist(pid) {
            const icon = document.getElementById('wishlistIcon');
            const btn = document.getElementById('wishlistBtn');
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
                        btn.style.borderColor = '#e55';
                    } else {
                        icon.className = 'fa-regular fa-heart';
                        btn.style.color = 'var(--pink-200)';
                        btn.style.borderColor = 'var(--pink-200)';
                    }
                }
            } catch(e) {}
        }
    </script>
</body>
</html>
