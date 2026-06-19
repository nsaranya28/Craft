<?php
session_start();
include 'includes/db.php';
include 'includes/cart-helper.php';

$items = getCartItems();
$subtotal = getCartTotal();
$shipping = getShipping();
$grand = $subtotal + $shipping;
$count = getCartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart | CraftyGifts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2.0">
    <style>
        .cart-item {
            display: flex; gap: 1.2rem; padding: 1.2rem;
            border-bottom: 1px dashed var(--pink-100); align-items: center;
            transition: background 0.3s;
        }
        .cart-item:last-child { border-bottom: none; }
        .cart-item:hover { background: var(--pink-50); }
        .cart-item img {
            width: 90px; height: 90px; border-radius: 14px;
            object-fit: cover; border: 2px solid var(--pink-50); flex-shrink: 0;
        }
        .qty-ctl {
            display: inline-flex; align-items: center; gap: 0; border-radius: 10px;
            overflow: hidden; border: 1.5px solid var(--pink-200);
        }
        .qty-ctl button {
            width: 32px; height: 32px; border: none; background: var(--cream);
            font-size: 0.85rem; cursor: pointer; transition: all 0.15s;
            display: flex; align-items: center; justify-content: center;
        }
        .qty-ctl button:hover { background: var(--pink-100); }
        .qty-ctl input {
            width: 40px; height: 32px; border: none; text-align: center;
            font-weight: 600; font-size: 0.85rem; background: white;
        }
        .qty-ctl input:focus { outline: none; }
        .summary-card { position: sticky; top: 30px; }
        .empty-state { text-align: center; padding: 4rem 2rem; }
        .empty-state .icon { font-size: 4rem; opacity: 0.3; margin-bottom: 1rem; }
        .toast-notif {
            position: fixed; top: 20px; right: 20px; z-index: 9999;
            background: white; border-radius: 16px; padding: 1rem 1.5rem;
            box-shadow: 0 12px 40px rgba(0,0,0,0.12); border-left: 4px solid var(--primary);
            transform: translateX(120%); transition: transform 0.4s cubic-bezier(0.175,0.885,0.32,1.275);
            max-width: 360px;
        }
        .toast-notif.show { transform: translateX(0); }
        .remove-btn {
            width: 34px; height: 34px; border-radius: 50%; border: none;
            background: #fee2e2; color: #b91c1c; font-size: 0.85rem;
            cursor: pointer; transition: all 0.2s; display: flex;
            align-items: center; justify-content: center;
        }
        .remove-btn:hover { background: #fecaca; transform: scale(1.1); }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg glass-nav">
        <div class="container-fluid">
            <a class="navbar-brand font-serif fs-3 fw-bold text-gradient" href="index.php">CraftyGifts</a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars" style="color:var(--primary);"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link fw-medium" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="products.php">Shop</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="custom-request.php">Custom Order</a></li>
                    <li class="nav-item"><a class="nav-link active fw-medium" href="cart.php"><i class="fas fa-shopping-cart me-1"></i>Cart <span class="badge bg-primary text-white rounded-pill px-2" id="navCartCount" style="font-size:0.75rem;"><?= $count ?></span></a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="user/wishlist.php"><i class="fa-regular fa-heart me-1"></i>Wishlist</a></li>
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

    <!-- Toast notification -->
    <div class="toast-notif" id="toast"><i class="fa-solid fa-check-circle me-2" style="color:var(--primary);"></i><span id="toastMsg">Item removed!</span></div>

    <main class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h1 class="font-serif fw-bold mb-0">♡ Shopping Cart</h1>
                <?php if (!empty($items)): ?>
                    <button class="btn btn-outline-custom btn-sm" onclick="clearCart()"><i class="fa-regular fa-trash-can me-1"></i>Clear All</button>
                <?php endif; ?>
            </div>

            <?php if (empty($items)): ?>
                <div class="card border-pink rounded-cute bg-white text-center shadow-sm empty-state">
                    <div class="icon">🛒</div>
                    <h4 class="font-serif mb-2">Your cart is empty</h4>
                    <p class="text-secondary mb-3">Add some beautiful handcrafted gifts to get started!</p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="products.php" class="btn btn-primary-custom rounded-pill px-4"><i class="fas fa-gift me-2"></i>Browse Products</a>
                        <a href="custom-request.php" class="btn btn-outline-custom rounded-pill px-4"><i class="fas fa-paint-brush me-2"></i>Custom Order</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card border-pink rounded-cute bg-white shadow-sm overflow-hidden" id="cartItemsContainer">
                            <?php foreach ($items as $i): ?>
                            <div class="cart-item" data-pid="<?= $i['product_id'] ?>">
                                <img src="<?= htmlspecialchars($i['image']) ?>" alt="<?= htmlspecialchars($i['name']) ?>" onerror="this.src='assets/img/products/default.jpg'">
                                <div style="flex:1;min-width:0;">
                                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($i['name']) ?></h6>
                                    <div class="text-secondary small mb-1">
                                        <?= htmlspecialchars($i['size']) ?> · <?= htmlspecialchars($i['color']) ?>
                                        <?php if ($i['custom_text']): ?> · "<?= htmlspecialchars($i['custom_text']) ?>"<?php endif; ?>
                                    </div>
                                    <div class="fw-bold" style="color:var(--primary);">₹<?= number_format($i['price'], 2) ?></div>
                                </div>
                                <div class="text-center" style="flex-shrink:0;">
                                    <div class="qty-ctl">
                                        <button onclick="updateQty(<?= $i['product_id'] ?>, <?= $i['quantity'] - 1 ?>)">−</button>
                                        <input type="text" value="<?= $i['quantity'] ?>" readonly>
                                        <button onclick="updateQty(<?= $i['product_id'] ?>, <?= $i['quantity'] + 1 ?>)">+</button>
                                    </div>
                                    <div class="mt-1 fw-semibold" id="subtotal-<?= $i['product_id'] ?>">₹<?= number_format($i['subtotal'], 2) ?></div>
                                </div>
                                <button class="remove-btn" onclick="removeItem(<?= $i['product_id'] ?>)" title="Remove"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="col-lg-4">
                        <div class="card summary-card p-4 shadow-sm border-pink rounded-cute bg-white">
                            <h5 class="font-serif fw-bold mb-3 border-bottom pb-2" style="border-color:var(--pink-100)!important;">Order Summary</h5>
                            <div class="d-flex justify-content-between mb-2 text-secondary small">
                                <span>Items (<?= $count ?>)</span>
                                <span class="fw-medium text-dark" id="sumSubtotal">₹<?= number_format($subtotal, 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 text-secondary small">
                                <span>Shipping</span>
                                <span class="fw-medium" id="sumShipping" style="color:<?= $shipping === 0 ? '#15803d' : 'var(--text)' ?>;">
                                    <?= $shipping === 0 ? 'FREE' : '₹' . number_format($shipping, 2) ?>
                                </span>
                            </div>
                            <?php if ($subtotal < 500): ?>
                                <div class="small text-center py-1 px-2 mb-2" style="background:var(--pink-50);border-radius:8px;color:var(--primary);">
                                    <i class="fa-solid fa-truck me-1"></i>Add ₹<?= number_format(500 - $subtotal, 2) ?> more for FREE shipping!
                                </div>
                            <?php endif; ?>
                            <hr class="my-2" style="border-color:var(--pink-100);">
                            <div class="d-flex justify-content-between font-serif fw-bold fs-5 mb-3">
                                <span>Total</span>
                                <span class="text-gradient" id="sumGrand">₹<?= number_format($grand, 2) ?></span>
                            </div>
                            <a href="checkout.php" class="btn btn-primary-custom w-100 py-2 rounded-pill"><i class="fas fa-lock me-2"></i>Proceed to Checkout</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="mt-5"><div class="container text-center py-3"><p class="mb-0 text-secondary small">&copy; 2026 CraftyGifts. Made with <i class="fas fa-heart" style="color:var(--primary);"></i></p></div></footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showToast(msg) {
            document.getElementById('toastMsg').textContent = msg;
            const t = document.getElementById('toast');
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 2500);
        }

        async function updateQty(pid, qty) {
            if (qty < 1) return;
            const form = new FormData();
            form.append('action', 'update');
            form.append('product_id', pid);
            form.append('quantity', qty);
            const res = await fetch('cart-action.php', { method: 'POST', body: form });
            const d = await res.json();
            if (d.success) refreshCart(d);
        }

        async function removeItem(pid) {
            const form = new FormData();
            form.append('action', 'remove');
            form.append('product_id', pid);
            const res = await fetch('cart-action.php', { method: 'POST', body: form });
            const d = await res.json();
            if (d.success) {
                document.querySelector(`.cart-item[data-pid="${pid}"]`).remove();
                refreshCart(d);
                showToast('Item removed from cart');
                if (d.count === 0) setTimeout(() => location.reload(), 400);
            }
        }

        async function clearCart() {
            if (!confirm('Clear your entire cart?')) return;
            const form = new FormData();
            form.append('action', 'clear');
            await fetch('cart-action.php', { method: 'POST', body: form });
            location.reload();
        }

        function refreshCart(d) {
            document.getElementById('navCartCount').textContent = d.count;
            const subtotalEl = document.getElementById('sumSubtotal');
            const shippingEl = document.getElementById('sumShipping');
            const grandEl = document.getElementById('sumGrand');
            if (subtotalEl) subtotalEl.textContent = '₹' + d.subtotal.toFixed(2);
            if (shippingEl) {
                shippingEl.textContent = d.shipping === 0 ? 'FREE' : '₹' + d.shipping.toFixed(2);
                shippingEl.style.color = d.shipping === 0 ? '#15803d' : 'var(--text)';
            }
            if (grandEl) grandEl.textContent = '₹' + d.grand.toFixed(2);
        }
    </script>
</body>
</html>
