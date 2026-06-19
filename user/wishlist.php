<?php
session_start();
include '../includes/db.php';
include '../includes/cart-helper.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Remove item
if (isset($_GET['remove'])) {
    $pdo->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?")->execute([$user_id, (int)$_GET['remove']]);
    header("Location: wishlist.php");
    exit;
}

// Move single to cart
if (isset($_GET['move'])) {
    $pid = (int)$_GET['move'];
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ((int)$item['product_id'] === $pid) { $item['quantity']++; $found = true; break; }
    }
    unset($item);
    if (!$found) $_SESSION['cart'][] = ['product_id' => $pid, 'quantity' => 1, 'size' => 'Standard', 'color' => 'Default'];
    $pdo->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?")->execute([$user_id, $pid]);
    header("Location: wishlist.php?moved=1");
    exit;
}

// Add all to cart
if (isset($_GET['add_all'])) {
    $items = $pdo->prepare("SELECT product_id FROM wishlists WHERE user_id = ?");
    $items->execute([$user_id]);
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    foreach ($items->fetchAll(PDO::FETCH_COLUMN) as $pid) {
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ((int)$item['product_id'] === (int)$pid) { $item['quantity']++; $found = true; break; }
        }
        unset($item);
        if (!$found) $_SESSION['cart'][] = ['product_id' => $pid, 'quantity' => 1, 'size' => 'Standard', 'color' => 'Default'];
    }
    $pdo->prepare("DELETE FROM wishlists WHERE user_id = ?")->execute([$user_id]);
    header("Location: cart.php");
    exit;
}

// Get wishlist items
$wishlist = $pdo->prepare("
    SELECT w.*, p.name, p.image, p.base_price, p.stock_quantity, p.description
    FROM wishlists w JOIN products p ON w.product_id = p.id
    WHERE w.user_id = ? ORDER BY w.created_at DESC
");
$wishlist->execute([$user_id]);
$items = $wishlist->fetchAll();
$count = count($items);
$moved = isset($_GET['moved']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist | CraftyGifts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=2.0">
    <style>
        body { background: linear-gradient(135deg,var(--background)0%,var(--pink-50)100%); min-height:100vh; }
        .wl-card {
            background: rgba(255,255,255,0.93); backdrop-filter: blur(20px);
            border: 1.5px solid rgba(232,98,140,0.12); border-radius: 20px;
            box-shadow: 0 8px 32px rgba(232,98,140,0.08); overflow: hidden;
            transition: all 0.3s; position: relative;
        }
        .wl-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(232,98,140,0.15); }
        .wl-card .img-wrap { height: 200px; overflow: hidden; background: var(--pink-50); }
        .wl-card .img-wrap img { width:100%; height:100%; object-fit:cover; }
        .wl-card .badge-stock { position:absolute; top:10px; left:10px; font-size:0.7rem; }
        .heart-remove {
            position:absolute; top:10px; right:10px; width:34px; height:34px;
            border-radius:50%; background:rgba(255,255,255,0.9); border:none;
            display:flex; align-items:center; justify-content:center;
            color:#e55; cursor:pointer; transition:all 0.2s; z-index:2;
        }
        .heart-remove:hover { background:#fee2e2; transform:scale(1.1); }
        .empty-state { text-align:center; padding:4rem 2rem; }
        .toast-notif {
            position:fixed; top:20px; right:20px; z-index:9999;
            background:white; border-radius:16px; padding:1rem 1.5rem;
            box-shadow:0 12px 40px rgba(0,0,0,0.12); border-left:4px solid #15803d;
            transform:translateX(120%); transition:transform 0.4s cubic-bezier(0.175,0.885,0.32,1.275);
        }
        .toast-notif.show { transform:translateX(0); }
    </style>
</head>
<body>
    <div class="toast-notif" id="toast"><i class="fa-solid fa-check-circle me-2" style="color:#15803d;"></i><span id="toastMsg">Moved to cart!</span></div>

    <div class="container py-5" style="max-width:1100px;">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <a href="../index.php" class="font-serif fs-2 fw-bold text-gradient text-decoration-none">CraftyGifts</a>
                <h4 class="fw-bold mt-2 mb-0" style="font-family:'Playfair Display',serif;">
                    ♡ My Wishlist
                    <?php if ($count > 0): ?><span class="badge rounded-pill" style="background:var(--primary);font-size:0.7rem;vertical-align:middle;"><?= $count ?></span><?php endif; ?>
                </h4>
            </div>
            <div class="d-flex gap-2">
                <?php if ($count > 0): ?>
                    <a href="?add_all=1" class="btn btn-primary-custom btn-sm rounded-pill px-3" onclick="return confirm('Add all wishlist items to cart?')"><i class="fas fa-cart-plus me-1"></i>Add All to Cart</a>
                <?php endif; ?>
                <a href="dashboard.php" class="btn btn-outline-custom btn-sm"><i class="fas fa-arrow-left me-1"></i>Dashboard</a>
            </div>
        </div>

        <?php if ($moved): ?>
            <div class="alert alert-success py-2 rounded-pill text-center" style="border:none;">♡ Item moved to cart! <a href="../cart.php" class="fw-bold">View Cart</a></div>
        <?php endif; ?>

        <?php if (empty($items)): ?>
            <div class="wl-card empty-state">
                <div style="font-size:4rem;opacity:0.3;margin-bottom:1rem;">♡</div>
                <h5 class="font-serif">Your wishlist is empty</h5>
                <p class="text-secondary mb-3">Save your favorite items to find them easily later!</p>
                <a href="../products.php" class="btn btn-primary-custom rounded-pill px-4"><i class="fas fa-gift me-2"></i>Browse Products</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($items as $item):
                    $inStock = $item['stock_quantity'] > 0;
                    $img = $item['image'];
                    if ($img && !preg_match('/^https?:\/\//i', $img)) $img = '../' . $img;
                ?>
                <div class="col-lg-4 col-md-6">
                    <div class="wl-card">
                        <button class="heart-remove" onclick="removeWish(<?= $item['product_id'] ?>)" title="Remove"><i class="fa-solid fa-heart"></i></button>
                        <?php if (!$inStock): ?><span class="badge bg-secondary badge-stock">Out of Stock</span><?php endif; ?>
                        <?php if ($inStock && $item['stock_quantity'] <= 5): ?><span class="badge bg-warning text-dark badge-stock">Only <?= $item['stock_quantity'] ?> left</span><?php endif; ?>
                        <a href="../product-details.php?id=<?= $item['product_id'] ?>">
                            <div class="img-wrap">
                                <img src="<?= htmlspecialchars($img ?: '../assets/img/products/default.jpg') ?>" alt="<?= htmlspecialchars($item['name']) ?>" onerror="this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;color:var(--pink-200);font-size:3rem;\'><i class=\'fa-solid fa-gift\'></i></div>'">
                            </div>
                        </a>
                        <div class="p-3">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                            <p class="small text-secondary mb-2"><?= htmlspecialchars(substr($item['description'], 0, 60)) ?>...</p>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="fw-bold" style="color:var(--primary);">₹<?= number_format($item['base_price'], 2) ?></span>
                                <div class="d-flex gap-1">
                                    <?php if ($inStock): ?>
                                        <a href="?move=<?= $item['product_id'] ?>" class="btn btn-primary-custom btn-sm rounded-pill px-2" style="font-size:0.75rem;"><i class="fas fa-cart-plus me-1"></i>Move to Cart</a>
                                    <?php endif; ?>
                                    <button class="btn btn-outline-custom btn-sm rounded-pill px-2" style="font-size:0.75rem;" onclick="quickView(<?= $item['product_id'] ?>)"><i class="fas fa-eye me-1"></i>View</button>
                                </div>
                            </div>
                            <div style="font-size:0.7rem;color:var(--text-light);margin-top:0.4rem;">Saved <?= date('M d, Y', strtotime($item['created_at'])) ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        async function removeWish(pid) {
            const form = new FormData();
            form.append('product_id', pid);
            form.append('action', 'remove');
            await fetch('wishlist-action.php', { method: 'POST', body: form });
            location.reload();
        }

        function showToast(msg) {
            document.getElementById('toastMsg').textContent = msg;
            document.getElementById('toast').classList.add('show');
            setTimeout(() => document.getElementById('toast').classList.remove('show'), 2500);
        }

        async function quickView(pid) {
            const modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
            document.getElementById('quickViewContent').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
            modal.show();
            try {
                const res = await fetch('../product-details.php?ajax=1&id=' + pid);
                const html = await res.text();
                document.getElementById('quickViewContent').innerHTML = html || '<div class="p-4 text-center">Product not found</div>';
            } catch(e) {
                document.getElementById('quickViewContent').innerHTML = '<div class="p-4 text-center">Could not load product.</div>';
            }
        }
    </script>
</body>
</html>
