<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = (int)($_GET['order_id'] ?? $_SESSION['last_order_id'] ?? 0);

// Ensure reviews table has required columns
foreach ([
    "ALTER TABLE reviews ADD COLUMN is_approved TINYINT(1) DEFAULT 0 AFTER comment",
    "ALTER TABLE reviews ADD COLUMN order_id INT DEFAULT NULL AFTER product_id"
] as $sql) {
    try { $pdo->exec($sql); } catch (PDOException $e) {}
}

if (!$order_id) {
    header("Location: dashboard.php");
    exit;
}

// Verify this order belongs to the user
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: dashboard.php");
    exit;
}

// Get order items
$stmt = $pdo->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

// Get existing reviews for this order
$reviewed = [];
$stmt = $pdo->prepare("SELECT product_id FROM reviews WHERE user_id = ? AND order_id = ?");
$stmt->execute([$user_id, $order_id]);
foreach ($stmt->fetchAll() as $rv) {
    $reviewed[$rv['product_id']] = true;
}

// Handle submission
$submitted = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rate'])) {
    $allSet = true;
    foreach ($items as $item) {
        $pid = $item['product_id'];
        $rating = (int)($_POST["rating_$pid"] ?? 0);
        $comment = trim($_POST["comment_$pid"] ?? '');
        if ($rating >= 1 && $rating <= 5 && !isset($reviewed[$pid])) {
            $stmt = $pdo->prepare("INSERT INTO reviews (user_id, product_id, order_id, rating, comment, is_approved) VALUES (?, ?, ?, ?, ?, 0)");
            $stmt->execute([$user_id, $pid, $order_id, $rating, $comment]);
        }
    }
    unset($_SESSION['last_order_id']);
    $submitted = true;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Your Purchase | CraftyGifts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, var(--background) 0%, var(--pink-50) 100%);
            min-height: 100vh;
        }
        .rate-card {
            background: rgba(255,255,255,0.93);
            backdrop-filter: blur(20px);
            border: 1.5px solid rgba(232,98,140,0.12);
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(232,98,140,0.08);
            padding: 2rem;
            margin-bottom: 1.5rem;
        }
        .star-input {
            display: inline-flex;
            gap: 4px;
            font-size: 1.8rem;
            cursor: pointer;
        }
        .star-input i {
            color: var(--pink-200);
            transition: all 0.2s;
        }
        .star-input i.active {
            color: #f59e0b;
        }
        .item-row {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            padding: 1.25rem 0;
            border-bottom: 1px dashed var(--pink-100);
        }
        .item-row:last-child { border-bottom: none; }
        .item-img {
            width: 70px;
            height: 70px;
            border-radius: 14px;
            object-fit: cover;
            background: var(--pink-50);
            flex-shrink: 0;
        }
        .success-banner {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            border: 2px solid #6ee7b7;
            border-radius: 18px;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 2rem;
        }
        .skip-link {
            color: var(--text-light);
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        .skip-link:hover { color: var(--primary); }
        .celebration {
            font-size: 3rem;
            animation: bounce 1s ease infinite;
        }
        @keyframes bounce {
            0%,100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body>
    <div class="container py-5" style="max-width:720px;">
        <div class="text-center mb-4">
            <a href="../index.php" class="font-serif fs-2 fw-bold text-gradient text-decoration-none">CraftyGifts</a>
        </div>

        <?php if ($submitted): ?>
            <div class="text-center py-5 fade-up visible">
                <div class="celebration mb-3">🎉</div>
                <h2 class="font-serif fw-bold">Thank You!</h2>
                <p class="text-secondary mb-4">Your reviews help other craft lovers find the perfect gift ♡</p>
                <a href="dashboard.php" class="btn btn-primary-custom px-5 py-2 rounded-pill">
                    <i class="fas fa-arrow-right me-2"></i>Go to Dashboard
                </a>
            </div>
        <?php else: ?>
            <!-- Success Banner -->
            <div class="success-banner fade-up visible">
                <h2 class="font-serif fw-bold mb-1" style="color:#065f46;">Order Placed! ♡</h2>
                <p class="mb-0" style="color:#047857;">#ORD-<?= $order_id ?> &middot; ₹<?= number_format($order['total_amount'], 2) ?></p>
                <p class="mt-2 mb-0" style="color:#065f46;font-size:0.95rem;">Love what you got? Rate your ✨purchase✨</p>
            </div>

            <form method="POST" class="fade-up visible" style="transition-delay:0.15s;">
                <?php foreach ($items as $i => $item):
                    $pid = $item['product_id'];
                    $imgSrc = $item['image'];
                    if ($imgSrc && !preg_match('/^https?:\/\//i', $imgSrc)) $imgSrc = '../' . $imgSrc;
                    $alreadyReviewed = isset($reviewed[$pid]);
                ?>
                <div class="rate-card">
                    <div class="item-row">
                        <img src="<?= htmlspecialchars($imgSrc ?: '../assets/img/products/default.jpg') ?>" alt="" class="item-img" onerror="this.parentElement.innerHTML='<div class=\'item-img\' style=\'display:flex;align-items:center;justify-content:center;color:var(--pink-200);font-size:1.5rem;\'><i class=\'fa-solid fa-gift\'></i></div>'">
                        <div style="flex-grow:1;min-width:0;">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                            <div style="font-size:0.85rem;color:var(--text-light);">Qty: <?= $item['quantity'] ?> &middot; ₹<?= number_format($item['price'], 2) ?></div>

                            <?php if ($alreadyReviewed): ?>
                                <div style="color:#15803d;font-size:0.85rem;margin-top:0.5rem;">
                                    <i class="fa-solid fa-check-circle me-1"></i>Reviewed
                                </div>
                            <?php else: ?>
                                <div style="margin-top:0.6rem;">
                                    <div class="star-input" id="stars-<?= $pid ?>">
                                        <?php for ($s = 1; $s <= 5; $s++): ?>
                                            <i class="fa-regular fa-star" data-star="<?= $s ?>" onclick="setRating(<?= $pid ?>, <?= $s ?>)"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <input type="hidden" name="rating_<?= $pid ?>" id="rating-<?= $pid ?>" value="0">
                                    <div style="font-size:0.75rem;color:var(--text-light);margin-top:0.2rem;" id="label-<?= $pid ?>">Tap a star</div>
                                    <textarea name="comment_<?= $pid ?>" class="form-control mt-2" rows="2" placeholder="Optional: share your thoughts..." style="border-radius:10px;border-color:var(--pink-200);font-size:0.85rem;resize:none;"></textarea>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <div style="text-align:center;margin-top:1.5rem;">
                    <button type="submit" name="rate" class="btn btn-primary-custom px-5 py-2 rounded-pill fs-5">
                        <i class="fa-solid fa-paper-plane me-2"></i>Submit Reviews
                    </button>
                    <div style="margin-top:0.8rem;">
                        <a href="dashboard.php" class="skip-link"><i class="fas fa-angle-right me-1"></i>Skip & go to Dashboard</a>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        function setRating(pid, val) {
            document.getElementById('rating-' + pid).value = val;
            const container = document.getElementById('stars-' + pid);
            container.querySelectorAll('i').forEach(s => {
                const star = parseInt(s.dataset.star);
                if (star <= val) {
                    s.className = 'fa-solid fa-star active';
                } else {
                    s.className = 'fa-regular fa-star';
                }
            });
            const labels = ['', 'Terrible 😢', 'Bad 🙁', 'Okay 😐', 'Good 😊', 'Amazing 🥰'];
            document.getElementById('label-' + pid).textContent = labels[val];
        }

        // Star hover effects
        document.querySelectorAll('.star-input').forEach(container => {
            container.querySelectorAll('i').forEach(star => {
                const pid = container.id.replace('stars-', '');
                star.addEventListener('mouseenter', function() {
                    const val = parseInt(this.dataset.star);
                    container.querySelectorAll('i').forEach(s => {
                        if (parseInt(s.dataset.star) <= val) {
                            s.className = 'fa-solid fa-star active';
                        }
                    });
                });
                star.addEventListener('mouseleave', function() {
                    const current = parseInt(document.getElementById('rating-' + pid).value);
                    container.querySelectorAll('i').forEach(s => {
                        if (parseInt(s.dataset.star) <= current) {
                            s.className = 'fa-solid fa-star active';
                        } else {
                            s.className = 'fa-regular fa-star';
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>