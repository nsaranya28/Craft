<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process order
    $user_id = $_SESSION['user_id'];
    $total = $_POST['total'];
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'demo';

    if (empty($shipping_address)) {
        $error = 'Please enter your shipping address.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, payment_status, shipping_address) VALUES (?, ?, 'ordered', 'paid', ?)");
        $stmt->execute([$user_id, $total, $shipping_address]);

        $_SESSION['cart'] = []; // Clear cart
        $success = true;
        $selected_payment = $payment_method;
    }
}

$total = 0;
foreach ($_SESSION['cart'] ?? [] as $item) {
    $stmt = $pdo->prepare("SELECT base_price FROM products WHERE id = ?");
    $stmt->execute([$item['product_id']]);
    $price = $stmt->fetchColumn();
    $total += $price * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | CraftyGifts</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=2.0">
    <style>
        .checkout-container {
            max-width: 600px;
            margin: 0 auto;
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
                    <li class="nav-item"><a class="nav-link fw-medium" href="products.php">Shop</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="custom-request.php">Custom Order</a></li>
                    <li class="nav-item">
                        <a class="nav-link active fw-medium" href="cart.php">
                            <i class="fas fa-shopping-cart me-1"></i>Cart 
                            <span class="badge bg-primary text-white rounded-pill px-2" style="font-size: 0.75rem; vertical-align: middle;">
                                <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
                            </span>
                        </a>
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
            <div class="card checkout-container p-4 p-md-5 shadow-sm border-pink rounded-cute bg-white text-center fade-up">
                <?php if (isset($success)): ?>
                    <div style="font-size: 4rem; margin-bottom: 1.5rem;">🎉</div>
                    <h2 class="font-serif mb-2 text-dark">Order Placed Successfully!</h2>
                    <p class="text-secondary mb-3">Your unique crafts are being prepared with love.</p>
                    <?php if (!empty($selected_payment) && $selected_payment !== 'demo'): ?>
                        <div class="alert alert-info rounded-cute mb-3" style="font-size:0.9rem;">
                            <i class="fas fa-info-circle me-1"></i>
                            Payment via <strong><?php echo htmlspecialchars(ucfirst($selected_payment)); ?></strong> is simulated in demo mode. No actual charge was made.
                        </div>
                    <?php endif; ?>
                    <p class="text-secondary mb-4 pb-3 border-bottom">You can track your order status in your dashboard.</p>
                    <a href="user/dashboard.php" class="btn btn-primary-custom btn-lg">Go to Dashboard</a>
                <?php else: ?>
                    <h2 class="font-serif mb-3 text-dark">Complete Your Order</h2>
                    <p class="text-secondary fs-5 mb-4">Total Amount: <span class="price-tag fs-4">₹<?php echo number_format($total, 2); ?></span></p>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger text-start rounded-cute mb-4"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" class="text-start">
                        <input type="hidden" name="total" value="<?php echo $total; ?>">

                        <!-- Shipping Address -->
                        <div class="mb-4">
                            <h5 class="font-serif mb-2 text-dark"><i class="fas fa-map-marker-alt me-2" style="color:var(--primary);"></i>Shipping Address</h5>
                            <textarea name="shipping_address" rows="3" class="form-control border-pink rounded-cute" placeholder="Enter your full delivery address..." required><?php echo htmlspecialchars($_POST['shipping_address'] ?? ''); ?></textarea>
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-4">
                            <h5 class="font-serif mb-3 text-dark"><i class="fas fa-wallet me-2" style="color:var(--primary);"></i>Payment Method</h5>
                            <div class="d-flex flex-column gap-3">

                                <!-- Google Pay -->
                                <label class="payment-option-card d-flex align-items-center gap-3 p-3 rounded-cute border-pink cursor-pointer" style="background:var(--cream); cursor:pointer;">
                                    <input type="radio" name="payment_method" value="gpay" class="form-check-input mt-0" style="accent-color:var(--primary);">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/f2/Google_Pay_Logo.svg/512px-Google_Pay_Logo.svg.png" alt="Google Pay" style="height:28px; object-fit:contain;">
                                    <span class="fw-semibold text-dark">Google Pay</span>
                                    <span class="ms-auto badge" style="background:var(--primary-light); color:var(--primary); font-size:0.7rem;">UPI</span>
                                </label>

                                <!-- PhonePe -->
                                <label class="payment-option-card d-flex align-items-center gap-3 p-3 rounded-cute border-pink cursor-pointer" style="background:var(--cream); cursor:pointer;">
                                    <input type="radio" name="payment_method" value="phonepe" class="form-check-input mt-0" style="accent-color:#5f259f;">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5f/PhonePe_Logo.svg/512px-PhonePe_Logo.svg.png" alt="PhonePe" style="height:28px; object-fit:contain;">
                                    <span class="fw-semibold text-dark">PhonePe</span>
                                    <span class="ms-auto badge" style="background:#ede7f6; color:#5f259f; font-size:0.7rem;">UPI</span>
                                </label>

                                <!-- Demo / COD -->
                                <label class="payment-option-card d-flex align-items-center gap-3 p-3 rounded-cute border-pink cursor-pointer" style="background:var(--cream); cursor:pointer;">
                                    <input type="radio" name="payment_method" value="demo" class="form-check-input mt-0" checked style="accent-color:#28a745;">
                                    <i class="fas fa-truck fa-lg" style="color:#28a745;"></i>
                                    <span class="fw-semibold text-dark">Cash on Delivery</span>
                                    <span class="ms-auto badge bg-success-subtle text-success" style="font-size:0.7rem;">COD</span>
                                </label>

                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-custom btn-lg w-100 py-3 d-flex align-items-center justify-content-center gap-2">
                            <i class="fas fa-lock me-1"></i> Confirm &amp; Place Order
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>

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
    </script>
</body>
</html>
