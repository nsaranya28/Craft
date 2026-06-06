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
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .cart-grid {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
        }
        .cart-item {
            display: flex;
            gap: 1.5rem;
            padding: 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            align-items: center;
        }
        .cart-item img { width: 100px; height: 100px; border-radius: 0.5rem; object-fit: cover; }
        .summary-card { position: sticky; top: 120px; height: fit-content; }
    </style>
</head>
<body>
    <header class="glass">
        <nav>
            <a href="index.php" class="logo">CraftyGifts</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Shop</a></li>
            </ul>
        </nav>
    </header>

    <main style="padding: 4rem 10%;">
        <h1 style="margin-bottom: 3rem;">Shopping Cart</h1>

        <?php if (empty($display_items)): ?>
            <div class="card" style="text-align: center; padding: 4rem;">
                <h2 style="color: var(--text-light); margin-bottom: 1rem;">Your cart is empty</h2>
                <a href="products.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-grid">
                <div class="card">
                    <?php foreach($display_items as $di): ?>
                    <div class="cart-item">
                        <img src="<?php echo $di['image']; ?>" alt="">
                        <div style="flex: 1;">
                            <h3 style="font-size: 1.1rem;"><?php echo $di['name']; ?></h3>
                            <p style="font-size: 0.85rem; color: var(--text-light); margin: 0.3rem 0;"><?php echo $di['options']; ?></p>
                            <span class="price" style="font-size: 1rem;">$<?php echo $di['price']; ?> x <?php echo $di['quantity']; ?></span>
                        </div>
                        <div style="font-weight: 700; color: var(--text);">$<?php echo number_format($di['subtotal'], 2); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="card summary-card">
                    <h3 style="margin-bottom: 1.5rem;">Order Summary</h3>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; color: var(--text-light);">
                        <span>Subtotal</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; color: var(--text-light);">
                        <span>Shipping</span>
                        <span>FREE</span>
                    </div>
                    <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 1.5rem 0;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 2rem; font-weight: 800; font-size: 1.25rem;">
                        <span>Total</span>
                        <span class="text-gradient">$<?php echo number_format($total, 2); ?></span>
                    </div>
                    <a href="checkout.php" class="btn btn-primary" style="display: block; text-align: center;">Proceed to Checkout</a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer style="margin-top: 4rem;">
        <p>&copy; 2026 CraftyGifts. All rights reserved.</p>
    </footer>
</body>
</html>
