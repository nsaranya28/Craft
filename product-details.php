<?php
session_start();
include 'includes/db.php';

$id = isset($_GET['id']) ? $_GET['id'] : die('Product not found');
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if(!$product) die('Product not found');
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
        .btn-outline-custom.active {
            background-color: var(--pink-100) !important;
            border-color: var(--primary) !important;
            color: var(--primary) !important;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(226, 95, 132, 0.1);
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
                    <div class="card p-3 border-pink rounded-cute bg-white text-center shadow-sm">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid rounded-cute shadow-sm mb-4" style="max-height: 480px; object-fit: cover; width: 100%;">
                        <div class="text-start px-2">
                            <h2 class="font-serif mb-2 text-dark"><?php echo htmlspecialchars($product['name']); ?></h2>
                            <p class="price-tag fs-3 mb-3">$<?php echo htmlspecialchars($product['base_price']); ?></p>
                            <p class="text-secondary"><?php echo htmlspecialchars($product['description']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Customization Form -->
                <div class="col-lg-6">
                    <div class="card p-4 shadow-sm border-pink rounded-cute bg-white">
                        <h2 class="font-serif mb-4 text-dark border-bottom pb-3 border-pink-dashed">Customize Your Item</h2>
                        
                        <form action="cart-action.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            
                            <!-- Size Options -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark mb-2">Choose Size</label>
                                <div class="d-flex gap-2 flex-wrap">
                                    <label class="btn btn-outline-custom active option-item">
                                        <input type="radio" name="size" value="Small" checked style="display:none"> Small
                                    </label>
                                    <label class="btn btn-outline-custom option-item">
                                        <input type="radio" name="size" value="Medium" style="display:none"> Medium
                                    </label>
                                    <label class="btn btn-outline-custom option-item">
                                        <input type="radio" name="size" value="Large" style="display:none"> Large
                                    </label>
                                </div>
                            </div>

                            <!-- Color Options -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark mb-2">Select Color</label>
                                <div class="d-flex gap-2 flex-wrap">
                                    <label class="btn btn-outline-custom active option-item">
                                        <input type="radio" name="color" value="White" checked style="display:none"> White
                                    </label>
                                    <label class="btn btn-outline-custom option-item">
                                        <input type="radio" name="color" value="Black" style="display:none"> Black
                                    </label>
                                    <label class="btn btn-outline-custom option-item">
                                        <input type="radio" name="color" value="Natural" style="display:none"> Natural
                                    </label>
                                </div>
                            </div>

                            <!-- Personalization Text -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark mb-1">Custom Text / Personalization</label>
                                <p class="text-secondary small mb-2">Enter the text you want engraved or printed on the item.</p>
                                <textarea class="form-control" name="custom_text" rows="3" placeholder="e.g. 'Happy Birthday Sarah!', 'Established 2024'"></textarea>
                            </div>

                            <!-- File Upload -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark mb-1">Upload Reference Image (Optional)</label>
                                <p class="text-secondary small mb-2">Attach a photo or sketch for reference or custom printing.</p>
                                <input type="file" name="custom_image" class="form-control py-2" style="background: var(--cream);">
                            </div>

                            <!-- Quantity & Action -->
                            <div class="row align-items-end mt-4">
                                <div class="col-4">
                                    <label class="form-label fw-bold text-dark">Qty</label>
                                    <input type="number" name="quantity" value="1" min="1" class="form-control py-2 text-center" style="background: var(--cream);">
                                </div>
                                <div class="col-8">
                                    <button type="submit" class="btn btn-primary-custom w-100 py-3 d-flex align-items-center justify-content-center gap-2" style="height: 54px;">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
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
    <script>
        // Option buttons click active class handler
        const optionButtons = document.querySelectorAll('input[type="radio"]');
        optionButtons.forEach(radio => {
            radio.addEventListener('change', () => {
                const group = radio.closest('.d-flex');
                group.querySelectorAll('label').forEach(label => label.classList.remove('active'));
                radio.parentElement.classList.add('active');
            });
        });
    </script>
</body>
</html>
