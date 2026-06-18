<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$pageTitle = 'My Wishlist';

// Remove item
if (isset($_GET['remove'])) {
    $pid = (int)$_GET['remove'];
    $pdo->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?")->execute([$user_id, $pid]);
    header("Location: wishlist.php");
    exit;
}

// Get wishlist items
$wishlist = $pdo->prepare("
    SELECT w.*, p.name, p.image, p.base_price, p.stock_quantity
    FROM wishlists w
    JOIN products p ON w.product_id = p.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");
$wishlist->execute([$user_id]);
$items = $wishlist->fetchAll();
$count = count($items);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist | CraftyGifts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, var(--background) 0%, var(--pink-50) 100%);
            min-height: 100vh;
        }
        .wl-card {
            background: rgba(255,255,255,0.93);
            backdrop-filter: blur(20px);
            border: 1.5px solid rgba(232,98,140,0.12);
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(232,98,140,0.08);
            overflow: hidden;
            transition: all 0.3s;
        }
        .wl-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(232,98,140,0.15);
        }
        .wl-card .img-wrap {
            height: 200px;
            overflow: hidden;
            background: var(--pink-50);
        }
        .wl-card .img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .wl-card .heart-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255,255,255,0.9);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e55;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .wl-card .heart-btn:hover {
            background: #fee2e2;
            transform: scale(1.1);
        }
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-light);
        }
        .empty-state .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.4;
        }
    </style>
</head>
<body>
    <div class="container py-5" style="max-width: 1000px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="../index.php" class="font-serif fs-2 fw-bold text-gradient text-decoration-none">CraftyGifts</a>
                <h4 class="fw-bold mt-3" style="font-family: 'Playfair Display', serif;">
                    ♡ My Wishlist
                    <?php if ($count > 0): ?>
                        <span class="badge rounded-pill" style="background:var(--primary);font-size:0.7rem;vertical-align:middle;"><?= $count ?></span>
                    <?php endif; ?>
                </h4>
            </div>
            <a href="dashboard.php" class="btn btn-outline-custom btn-sm"><i class="fas fa-arrow-left me-1"></i>Dashboard</a>
        </div>

        <?php if (empty($items)): ?>
            <div class="wl-card empty-state">
                <div class="icon">♡</div>
                <h5>Your wishlist is empty</h5>
                <p class="mb-3">Save your favorite items to find them easily later!</p>
                <a href="../products.php" class="btn btn-primary-custom rounded-pill px-4">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($items as $item):
                    $imgSrc = $item['image'];
                    if ($imgSrc && !preg_match('/^https?:\/\//i', $imgSrc)) $imgSrc = '../' . $imgSrc;
                ?>
                <div class="col-lg-4 col-md-6">
                    <div class="wl-card position-relative">
                        <button class="heart-btn" onclick="removeWish(<?= $item['product_id'] ?>)" title="Remove">
                            <i class="fa-solid fa-heart"></i>
                        </button>
                        <a href="../product-details.php?id=<?= $item['product_id'] ?>">
                            <div class="img-wrap">
                                <img src="<?= htmlspecialchars($imgSrc ?: '../assets/img/products/default.jpg') ?>" alt="<?= htmlspecialchars($item['name']) ?>" onerror="this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;color:var(--pink-200);font-size:3rem;\'><i class=\'fa-solid fa-gift\'></i></div>'">
                            </div>
                        </a>
                        <div class="p-3">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="fw-bold" style="color:var(--primary);">₹<?= number_format($item['base_price'], 2) ?></span>
                                <?php if ($item['stock_quantity'] > 0): ?>
                                    <a href="../product-details.php?id=<?= $item['product_id'] ?>" class="btn btn-primary-custom btn-sm rounded-pill px-3"><i class="fas fa-shopping-cart me-1"></i>Buy</a>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size:0.7rem;color:var(--text-light);margin-top:0.4rem;">Saved <?= date('M d, Y', strtotime($item['created_at'])) ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        async function removeWish(pid) {
            const form = new FormData();
            form.append('product_id', pid);
            form.append('action', 'remove');
            await fetch('wishlist-action.php', { method: 'POST', body: form });
            location.reload();
        }
    </script>
</body>
</html>
