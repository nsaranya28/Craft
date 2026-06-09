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

$pageTitle = 'Manage Deliveries';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i>
        <div><?php echo htmlspecialchars($success); ?></div>
    </div>
<?php elseif ($error): ?>
    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>
        <div><?php echo htmlspecialchars($error); ?></div>
    </div>
<?php endif; ?>

<!-- Edit Order Card (Conditional) -->
<?php if ($editOrder): ?>
    <div class="glass-card mb-4 border border-warning" style="background: rgba(255, 255, 255, 0.85);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1"><i class="fa-solid fa-truck-fast text-warning me-2"></i>Update Order & Delivery #<?php echo $editOrder['id']; ?></h4>
                <p class="text-muted small mb-0">Customer: <strong><?php echo htmlspecialchars($editOrder['user_name']); ?></strong> (<?php echo htmlspecialchars($editOrder['user_email']); ?>)</p>
            </div>
            <a href="manage_orders.php" class="btn btn-sm btn-outline-secondary">Cancel Edit</a>
        </div>

        <form method="POST" action="manage_orders.php" class="row g-3">
            <input type="hidden" name="order_id" value="<?php echo $editOrder['id']; ?>">
            <input type="hidden" name="update_order" value="1">
            
            <div class="col-md-4">
                <label class="form-label fw-bold">Delivery Status</label>
                <select class="form-select" name="status" required>
                    <option value="ordered" <?php echo $editOrder['status'] === 'ordered' ? 'selected' : ''; ?>>Ordered</option>
                    <option value="processing" <?php echo $editOrder['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="shipped" <?php echo $editOrder['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                    <option value="delivered" <?php echo $editOrder['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="cancelled" <?php echo $editOrder['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">Payment Status</label>
                <select class="form-select" name="payment_status" required>
                    <option value="pending" <?php echo $editOrder['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="paid" <?php echo $editOrder['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                    <option value="failed" <?php echo $editOrder['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">Tracking Number</label>
                <input type="text" class="form-control" name="tracking_number" placeholder="e.g. TRK12345678" value="<?php echo htmlspecialchars($editOrder['tracking_number'] ?? ''); ?>">
            </div>

            <div class="col-12">
                <label class="form-label fw-bold">Shipping Address</label>
                <textarea class="form-control" name="shipping_address" rows="3" required><?php echo htmlspecialchars($editOrder['shipping_address']); ?></textarea>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 mt-4">
                <a href="manage_orders.php" class="btn btn-premium-outline">Discard Changes</a>
                <button type="submit" class="btn btn-premium"><i class="fa-solid fa-save me-2"></i>Save Delivery Settings</button>
            </div>
        </form>
    </div>
<?php endif; ?>

<!-- Orders Table Card -->
<div class="glass-card">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Deliveries & Orders Log</h4>
            <p class="text-muted small mb-0">Track order processing, update payment flags, and set carrier tracking IDs</p>
        </div>
        <a href="create_order.php" class="btn btn-premium btn-sm"><i class="fa-solid fa-circle-plus me-2"></i>Create Custom Order</a>
    </div>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Order Info</th>
                    <th>Customer</th>
                    <th>Total Amount</th>
                    <th>Payment Status</th>
                    <th>Delivery Status</th>
                    <th>Tracking ID</th>
                    <th style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No orders placed yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">#<?php echo $o['id']; ?></div>
                                <div class="text-muted small"><?php echo date('M d, Y', strtotime($o['order_date'])); ?></div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?php echo htmlspecialchars($o['user_name']); ?></div>
                                <div class="text-muted small text-truncate" style="max-width: 200px;"><?php echo htmlspecialchars($o['user_email']); ?></div>
                            </td>
                            <td class="fw-bold text-dark">
                                $<?php echo number_format($o['total_amount'], 2); ?>
                            </td>
                            <td>
                                <span class="badge-status status-<?php echo strtolower($o['payment_status']); ?>">
                                    <?php echo htmlspecialchars($o['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge-status status-<?php echo strtolower($o['status']); ?>">
                                    <?php echo htmlspecialchars($o['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($o['tracking_number'])): ?>
                                    <span class="text-dark small"><i class="fa-solid fa-truck text-muted me-1"></i> <code><?php echo htmlspecialchars($o['tracking_number']); ?></code></span>
                                <?php else: ?>
                                    <span class="text-muted small font-italic">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="manage_orders.php?edit_id=<?php echo $o['id']; ?>" class="btn btn-premium-outline btn-sm py-1 px-3 w-100 text-center">
                                    <i class="fa-regular fa-edit me-1"></i> Manage
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
