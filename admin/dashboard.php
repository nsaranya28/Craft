<?php
$pageTitle = 'Dashboard';
include 'includes/header.php';
include 'includes/sidebar.php';

// Stats
try {
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $orderCount = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $requestCount = $pdo->query("SELECT COUNT(*) FROM custom_orders")->fetchColumn();
    $revenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE payment_status='paid'")->fetchColumn() ?: 0;
    
    $recentOrders = $pdo->query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC LIMIT 5")->fetchAll();
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Database query error: " . $e->getMessage() . "</div>";
    $userCount = 0;
    $orderCount = 0;
    $requestCount = 0;
    $revenue = 0;
    $recentOrders = [];
}
?>

<!-- Stat Cards Grid -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="glass-card h-100 d-flex flex-column justify-content-between">
            <div>
                <div class="stat-icon-wrapper" style="background: rgba(124, 77, 255, 0.15); color: var(--accent-purple);">
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                </div>
                <h6 class="text-muted small uppercase fw-bold">Total Revenue</h6>
                <h3 class="fw-bold mt-2">$<?php echo number_format($revenue, 2); ?></h3>
            </div>
            <p class="text-success small mb-0 mt-3"><i class="fa-solid fa-arrow-up me-1"></i> Paid orders only</p>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="glass-card h-100 d-flex flex-column justify-content-between">
            <div>
                <div class="stat-icon-wrapper" style="background: rgba(232, 98, 140, 0.15); color: var(--accent-pink);">
                    <i class="fa-solid fa-users"></i>
                </div>
                <h6 class="text-muted small uppercase fw-bold">Active Users</h6>
                <h3 class="fw-bold mt-2"><?php echo number_format($userCount); ?></h3>
            </div>
            <p class="text-muted small mb-0 mt-3">Registered accounts</p>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="glass-card h-100 d-flex flex-column justify-content-between">
            <div>
                <div class="stat-icon-wrapper" style="background: rgba(74, 222, 128, 0.15); color: #15803d;">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
                <h6 class="text-muted small uppercase fw-bold">Total Orders</h6>
                <h3 class="fw-bold mt-2"><?php echo number_format($orderCount); ?></h3>
            </div>
            <p class="text-muted small mb-0 mt-3"><a href="manage_orders.php" class="text-decoration-none" style="color: var(--accent-purple);">Manage Deliveries →</a></p>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="glass-card h-100 d-flex flex-column justify-content-between">
            <div>
                <div class="stat-icon-wrapper" style="background: rgba(251, 191, 36, 0.15); color: #b45309;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <h6 class="text-muted small uppercase fw-bold">Custom Requests</h6>
                <h3 class="fw-bold mt-2"><?php echo number_format($requestCount); ?></h3>
            </div>
            <p class="text-muted small mb-0 mt-3">Pending quotes/designs</p>
        </div>
    </div>
</div>

<!-- Recent Orders Section -->
<div class="glass-card">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Recent Orders</h4>
            <p class="text-muted small mb-0">Overview of the last 5 placed customer orders</p>
        </div>
        <a href="manage_orders.php" class="btn btn-premium btn-sm"><i class="fa-solid fa-list-check me-2"></i>View All Deliveries</a>
    </div>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Total Amount</th>
                    <th>Payment Status</th>
                    <th>Delivery Status</th>
                    <th>Order Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentOrders)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No recent orders found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentOrders as $ro): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($ro['user_name']); ?></div>
                                <div class="text-muted small">Order #<?php echo $ro['id']; ?></div>
                            </td>
                            <td class="fw-semibold text-dark">
                                $<?php echo number_format($ro['total_amount'], 2); ?>
                            </td>
                            <td>
                                <span class="badge-status status-<?php echo strtolower($ro['payment_status']); ?>">
                                    <?php echo htmlspecialchars($ro['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge-status status-<?php echo strtolower($ro['status']); ?>">
                                    <?php echo htmlspecialchars($ro['status']); ?>
                                </span>
                            </td>
                            <td class="text-muted small">
                                <?php echo date('M d, Y h:i A', strtotime($ro['order_date'])); ?>
                            </td>
                            <td>
                                <a href="manage_orders.php?edit_id=<?php echo $ro['id']; ?>" class="btn btn-premium-outline btn-sm py-1 px-3">
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
