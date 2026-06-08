<?php
session_start();
include 'includes/db.php';

$cart_items = $_SESSION['cart'] ?? [];
$total = 0;

// Fetch product details for cart items
$display_items = [];
foreach ($cart_items as $item) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$item['product_id']]);
    $product = $stmt->fetch();
    if ($product) {
        $display_items[] = [
            'name' => $product['name'],
            'price' => $product['base_price'],
            'quantity' => $item['quantity'],
            'subtotal' => $product['base_price'] * $item['quantity'],
            'image' => $product['image'],
            'options' => "Size: {$item['size']}, Color: {$item['color']}"
        ];
        $total += $product['base_price'] * $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart | CraftyGifts</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=2.0">
    <style>
        .cart-item {
            display: flex;
            gap: 1.5rem;
            padding: 1.5rem;
            border-bottom: 1px solid var(--pink-100);
            align-items: center;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .cart-item img {
            width: 100px;
            height: 100px;
            border-radius: 12px;
            object-fit: cover;
            border: 1px solid var(--pink-100);
        }
        .summary-card {
            position: sticky;
            top: 40px;
            height: fit-content;
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
                                <?php echo count($display_items); ?>
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
            <h1 class="font-serif mb-4 text-dark">Shopping Cart</h1>

            <?php if (empty($display_items)): ?>
                <div class="card p-5 border-pink rounded-cute bg-white text-center shadow-sm fade-up">
                    <h2 class="font-serif text-secondary mb-3">Your cart is empty</h2>
                    <p class="text-secondary mb-4">Add some beautiful handcrafted gifts to your cart to get started.</p>
                    <div>
                        <a href="products.php" class="btn btn-primary-custom btn-lg">Continue Shopping</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4 fade-up">
                    <!-- Cart List -->
                    <div class="col-lg-8">
                        <div class="card border-pink rounded-cute bg-white shadow-sm overflow-hidden">
                            <?php foreach($display_items as $di): ?>
                            <div class="cart-item">
                                <img src="<?php echo htmlspecialchars($di['image']); ?>" alt="<?php echo htmlspecialchars($di['name']); ?>">
                                <div style="flex: 1;">
                                    <h3 class="h5 font-serif text-dark mb-1"><?php echo htmlspecialchars($di['name']); ?></h3>
                                    <p class="text-secondary small mb-2"><?php echo htmlspecialchars($di['options']); ?></p>
                                    <span class="price-tag fs-6">$<?php echo htmlspecialchars($di['price']); ?> x <?php echo htmlspecialchars($di['quantity']); ?></span>
                                </div>
                                <div class="font-serif fw-bold text-dark fs-5">$<?php echo number_format($di['subtotal'], 2); ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="col-lg-4">
                        <div class="card summary-card p-4 shadow-sm border-pink rounded-cute bg-white">
                            <h3 class="font-serif mb-4 text-dark border-bottom pb-2 border-pink-dashed">Order Summary</h3>
                            
                            <div class="d-flex justify-content-between mb-3 text-secondary">
                                <span>Subtotal</span>
                                <span class="fw-medium text-dark">$<?php echo number_format($total, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 text-secondary">
                                <span>Shipping</span>
                                <span class="fw-medium text-success">FREE</span>
                            </div>
                            
                            <hr class="border-pink-dashed my-3">
                            
                            <div class="d-flex justify-content-between mb-4 font-serif fw-bold fs-4 text-dark">
                                <span>Total</span>
                                <span class="text-gradient">$<?php echo number_format($total, 2); ?></span>
                            </div>
                            
                            <a href="checkout.php" class="btn btn-primary-custom btn-lg w-100 py-3 text-center d-block">
                                Proceed to Checkout <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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
</body>
</html>
