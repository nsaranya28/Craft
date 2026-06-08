<?php
session_start();
include '../includes/db.php';

// Simple admin check - assume admin session is set elsewhere
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    die('Access Denied. Admins only.');
}

// Fetch users for dropdown
$usersStmt = $pdo->query("SELECT id, name, email FROM users ORDER BY name");
$users = $usersStmt->fetchAll();

// Fetch coupons for dropdown
$couponStmt = $pdo->query("SELECT id, code FROM coupons WHERE is_active = 1");
$coupons = $couponStmt->fetchAll();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $total_amount = $_POST['total_amount'];
    $shipping_address = $_POST['shipping_address'];
    $coupon_id = $_POST['coupon_id'] !== '' ? $_POST['coupon_id'] : null;
    $status = $_POST['status'];
    $payment_status = $_POST['payment_status'];
    $tracking_number = $_POST['tracking_number'];

    // Insert order
    $orderStmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, coupon_id, status, payment_status, tracking_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $orderStmt->execute([$user_id, $total_amount, $shipping_address, $coupon_id, $status, $payment_status, $tracking_number]);
    $order_id = $pdo->lastInsertId();

    // Process order items (expected format: product_id:qty,product_id:qty,...)
    $itemsRaw = trim($_POST['order_items']);
    if (!empty($itemsRaw)) {
        $items = explode(',', $itemsRaw);
        $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) SELECT ?, p.id, ?, p.base_price FROM products p WHERE p.id = ?");
        foreach ($items as $pair) {
            [$pid, $qty] = array_map('trim', explode(':', $pair));
            if (is_numeric($pid) && is_numeric($qty) && $qty > 0) {
                $itemStmt->execute([$order_id, $qty, $pid]);
            }
        }
    }
    $success = "Order #{$order_id} created successfully.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Order – Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="mb-4">Create New Order</h1>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <form method="POST" class="needs-validation" novalidate>
        <div class="mb-3">
            <label class="form-label" for="user_id">Customer</label>
            <select class="form-select" id="user_id" name="user_id" required>
                <option value="" disabled selected>Select a user</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['name'] . ' (' . $u['email'] . ')'); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label" for="total_amount">Total Amount</label>
            <input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="shipping_address">Shipping Address</label>
            <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label" for="coupon_id">Coupon (optional)</label>
            <select class="form-select" id="coupon_id" name="coupon_id">
                <option value="" selected>None</option>
                <?php foreach ($coupons as $c): ?>
                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['code']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label" for="status">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="ordered" selected>Ordered</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="payment_status">Payment Status</label>
                <select class="form-select" id="payment_status" name="payment_status" required>
                    <option value="pending" selected>Pending</option>
                    <option value="paid">Paid</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="tracking_number">Tracking Number (optional)</label>
                <input type="text" class="form-control" id="tracking_number" name="tracking_number">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="order_items">Order Items (product_id:quantity, ...) </label>
            <input type="text" class="form-control" id="order_items" name="order_items" placeholder="e.g., 12:2,5:1" required>
            <div class="form-text">Enter a comma‑separated list of product IDs and quantities.</div>
        </div>
        <button type="submit" class="btn btn-primary">Create Order</button>
        <a href="dashboard.php" class="btn btn-secondary ms-2">Back to Dashboard</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
