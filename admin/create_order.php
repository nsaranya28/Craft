<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Enforce admin login
if (!isAdminLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

// Fetch users for dropdown
$usersStmt = $pdo->query("SELECT id, name, email FROM users ORDER BY name");
$users = $usersStmt->fetchAll();

// Fetch coupons for dropdown
$couponStmt = $pdo->query("SELECT id, code FROM coupons WHERE is_active = 1");
$coupons = $couponStmt->fetchAll();

$success = '';
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $total_amount = floatval($_POST['total_amount']);
    $shipping_address = trim($_POST['shipping_address']);
    $coupon_id = $_POST['coupon_id'] !== '' ? intval($_POST['coupon_id']) : null;
    $status = $_POST['status'];
    $payment_status = $_POST['payment_status'];
    $tracking_number = trim($_POST['tracking_number']);

    if (empty($user_id) || empty($total_amount) || empty($shipping_address)) {
        $error = "Customer, Total Amount, and Shipping Address are required fields.";
    } else {
        try {
            // Insert order
            $orderStmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, coupon_id, status, payment_status, tracking_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $orderStmt->execute([$user_id, $total_amount, $shipping_address, $coupon_id, $status, $payment_status, $tracking_number ?: null]);
            $order_id = $pdo->lastInsertId();

            // Process order items (expected format: product_id:qty,product_id:qty,...)
            $itemsRaw = trim($_POST['order_items']);
            if (!empty($itemsRaw)) {
                $items = explode(',', $itemsRaw);
                $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) SELECT ?, p.id, ?, p.base_price FROM products p WHERE p.id = ?");
                foreach ($items as $pair) {
                    $pairParts = explode(':', $pair);
                    if (count($pairParts) === 2) {
                        [$pid, $qty] = array_map('trim', $pairParts);
                        if (is_numeric($pid) && is_numeric($qty) && $qty > 0) {
                            $itemStmt->execute([$order_id, $qty, $pid]);
                        }
                    }
                }
            }
            $success = "Order #{$order_id} created successfully.";
        } catch (PDOException $e) {
            $error = "Failed to create order: " . $e->getMessage();
        }
    }
}

$pageTitle = 'Create Order';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="glass-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Create New Order</h4>
            <p class="text-muted small mb-0">Record a new offline or phone customer order manually</p>
        </div>
        <a href="manage_orders.php" class="btn btn-premium-outline btn-sm"><i class="fa-solid fa-arrow-left me-2"></i>Back to Orders</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            <div><?php echo htmlspecialchars($success); ?></div>
        </div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <div><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <form method="POST" class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-bold" for="user_id">Customer <span class="text-danger">*</span></label>
            <select class="form-select" id="user_id" name="user_id" required>
                <option value="" disabled selected>Select a customer...</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['name'] . ' (' . $u['email'] . ')'); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold" for="total_amount">Total Amount ($) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" placeholder="0.00" required>
        </div>

        <div class="col-12">
            <label class="form-label fw-bold" for="shipping_address">Shipping Address <span class="text-danger">*</span></label>
            <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" placeholder="Enter complete customer delivery address..." required></textarea>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold" for="coupon_id">Coupon Applied</label>
            <select class="form-select" id="coupon_id" name="coupon_id">
                <option value="" selected>None</option>
                <?php foreach ($coupons as $c): ?>
                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['code']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold" for="tracking_number">Tracking Number (optional)</label>
            <input type="text" class="form-control" id="tracking_number" name="tracking_number" placeholder="e.g. UPS12345678">
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold" for="status">Order/Delivery Status</label>
            <select class="form-select" id="status" name="status" required>
                <option value="ordered" selected>Ordered</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold" for="payment_status">Payment Status</label>
            <select class="form-select" id="payment_status" name="payment_status" required>
                <option value="pending" selected>Pending</option>
                <option value="paid">Paid</option>
                <option value="failed">Failed</option>
            </select>
        </div>

        <div class="col-12">
            <label class="form-label fw-bold" for="order_items">Order Items <span class="text-muted">(Format: product_id:quantity, ...)</span></label>
            <input type="text" class="form-control" id="order_items" name="order_items" placeholder="e.g. 1:2,3:1">
            <div class="form-text">Comma-separated list of product IDs and quantities. (e.g. Product ID 1 with qty 2, Product ID 3 with qty 1).</div>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2 mt-4">
            <button type="reset" class="btn btn-premium-outline">Reset Form</button>
            <button type="submit" class="btn btn-premium"><i class="fa-solid fa-cart-plus me-2"></i>Create Order</button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
