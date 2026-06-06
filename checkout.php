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
    
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, payment_status) VALUES (?, ?, 'ordered', 'paid')");
    $stmt->execute([$user_id, $total]);
    
    $_SESSION['cart'] = []; // Clear cart
    $success = true;
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
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="glass"><nav><a href="index.php" class="logo">CraftyGifts</a></nav></header>

    <main style="padding: 4rem 10%; text-align: center;">
        <div class="card" style="max-width: 600px; margin: 0 auto; padding: 4rem;">
            <?php if (isset($success)): ?>
                <div style="font-size: 4rem; margin-bottom: 2rem;">🎉</div>
                <h2 style="margin-bottom: 1rem;">Order Placed Successfully!</h2>
                <p style="color: var(--text-light); margin-bottom: 2.5rem;">Your unique crafts are being prepared with love. You can track your order in your dashboard.</p>
                <a href="user/dashboard.php" class="btn btn-primary">Go to Dashboard</a>
            <?php else: ?>
                <h2 style="margin-bottom: 2rem;">Complete Your Order</h2>
                <p style="margin-bottom: 2rem;">Total Amount: <span class="price">$<?php echo number_format($total, 2); ?></span></p>
                <form method="POST">
                    <input type="hidden" name="total" value="<?php echo $total; ?>">
                    <div style="text-align: left; margin-bottom: 2rem;">
                        <h4 style="margin-bottom: 1rem;">Payment Method</h4>
                        <div class="card" style="padding: 1rem; border: 1px solid var(--primary);">
                            <strong>Secure Demo Payment</strong>
                            <p style="font-size: 0.8rem; color: var(--text-light);">This is a demonstration. No real payment will be charged.</p>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; height: 50px;">Pay & Place Order</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
