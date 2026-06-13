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

$success = '';
$error = '';

// Handle order status / delivery updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'] ?? '';
    $payment_status = $_POST['payment_status'] ?? '';
    $tracking_number = trim($_POST['tracking_number'] ?? '');
    $shipping_address = trim($_POST['shipping_address'] ?? '');

    try {
        // Update order details
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, payment_status = ?, tracking_number = ?, shipping_address = ? WHERE id = ?");
        $stmt->execute([$status, $payment_status, $tracking_number ?: null, $shipping_address, $order_id]);
        
        // Log status change if status changed
        $historyStmt = $pdo->prepare("INSERT INTO order_status_history (order_id, status) VALUES (?, ?)");
        $historyStmt->execute([$order_id, $status]);
        
        $success = "Order #{$order_id} updated successfully.";
    } catch (PDOException $e) {
        $error = "Failed to update order: " . $e->getMessage();
    }
}

// Fetch single order if edit_id is set
$editOrder = null;
$edit_id = intval($_GET['edit_id'] ?? 0);
if ($edit_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
        $stmt->execute([$edit_id]);
        $editOrder = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Failed to load order details: " . $e->getMessage();
    }
}

// Fetch all orders
try {
    $orders = $pdo->query("SELECT o.*, u.name as user_name, u.email as user_email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC")->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to load orders: " . $e->getMessage();
    $orders = [];
}

$pageTitle = 'Orders';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif;">♥ Orders ♥</h2>
            <p style="color: var(--text-light); margin: 0;">Manage deliveries, payments, and tracking</p>
        </div>
        <div class="d-flex gap-2">
            <a href="create_order.php" class="btn btn-premium btn-sm"><i class="fa-solid fa-circle-plus me-2"></i>New Order</a>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success py-2 d-flex align-items-center gap-2" style="border-radius: 14px; border: none;">
            <i class="fa-solid fa-circle-check" style="color: #15803d;"></i>
            <div><?php echo htmlspecialchars($success); ?></div>
        </div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger py-2 d-flex align-items-center gap-2" style="border-radius: 14px; border: none;">
            <i class="fa-solid fa-triangle-exclamation" style="color: #b91c1c;"></i>
            <div><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <!-- Edit Order Card (Conditional) -->
    <?php if ($editOrder): ?>
        <div class="glass-card mb-4" style="border: 2px solid var(--primary-light);">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif;">♡ Update Order #<?php echo $editOrder['id']; ?> ♡</h4>
                    <p style="color: var(--text-light); font-size: 0.85rem;">Customer: <strong><?php echo htmlspecialchars($editOrder['user_name']); ?></strong> &middot; <?php echo htmlspecialchars($editOrder['user_email']); ?></p>
                </div>
                <a href="manage_orders.php" class="btn btn-premium-outline btn-sm">Cancel</a>
            </div>
            <form method="POST" action="manage_orders.php" class="row g-3">
                <input type="hidden" name="order_id" value="<?php echo $editOrder['id']; ?>">
                <input type="hidden" name="update_order" value="1">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Delivery Status</label>
                    <select class="form-select" name="status" required>
                        <option value="ordered" <?php echo $editOrder['status'] === 'ordered' ? 'selected' : ''; ?>>Ordered</option>
                        <option value="processing" <?php echo $editOrder['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?php echo $editOrder['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo $editOrder['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $editOrder['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Payment Status</label>
                    <select class="form-select" name="payment_status" required>
                        <option value="pending" <?php echo $editOrder['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="paid" <?php echo $editOrder['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="failed" <?php echo $editOrder['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Tracking Number</label>
                    <input type="text" class="form-control" name="tracking_number" placeholder="e.g. TRK12345678" value="<?php echo htmlspecialchars($editOrder['tracking_number'] ?? ''); ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold small">Shipping Address</label>
                    <textarea class="form-control" name="shipping_address" rows="2" required><?php echo htmlspecialchars($editOrder['shipping_address']); ?></textarea>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="manage_orders.php" class="btn btn-premium-outline btn-sm">Discard</a>
                    <button type="submit" class="btn btn-premium btn-sm"><i class="fa-solid fa-save me-2"></i>Save Changes</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Orders Table -->
    <div class="glass-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;">♡ All Orders ♡</h5>
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search orders..." style="border-radius: 12px; min-width: 180px;">
                <button type="submit" class="btn btn-premium btn-sm"><i class="fa-solid fa-search"></i></button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr><th>Order</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Tracking</th><th style="width: 100px;">Action</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="7" class="text-center py-4" style="color: var(--text-light);">No orders yet ♡</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold">#<?php echo $o['id']; ?></div>
                                    <div style="color: var(--text-light); font-size: 0.75rem;"><?php echo date('M d, Y', strtotime($o['order_date'])); ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($o['user_name']); ?></div>
                                    <div style="color: var(--text-light); font-size: 0.75rem;"><?php echo htmlspecialchars($o['user_email']); ?></div>
                                </td>
                                <td class="fw-bold" style="color: var(--primary);">₹<?php echo number_format($o['total_amount'], 2); ?></td>
                                <td><span class="badge-status status-<?php echo strtolower($o['payment_status']); ?>"><?php echo $o['payment_status']; ?></span></td>
                                <td><span class="badge-status status-<?php echo strtolower($o['status']); ?>"><?php echo $o['status']; ?></span></td>
                                <td style="font-size: 0.8rem;">
                                    <?php if (!empty($o['tracking_number'])): ?>
                                        <code style="background: var(--pink-50); padding: 2px 6px; border-radius: 6px;"><?php echo htmlspecialchars($o['tracking_number']); ?></code>
                                    <?php else: ?>
                                        <span style="color: var(--text-light);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><a href="manage_orders.php?edit_id=<?php echo $o['id']; ?>" class="btn btn-premium-outline btn-sm py-1 px-3 w-100"><i class="fa-regular fa-edit me-1"></i>Edit</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
