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

<div class="container-fluid p-0">
    <!-- Welcome Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif;">Welcome back, <span style="background: linear-gradient(135deg, var(--primary), var(--primary-light)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Admin</span></h2>
            <p class="text-muted mb-0" style="color: var(--text-light) !important;"><i class="fa-regular fa-calendar me-2"></i><?php echo date('l, F j, Y'); ?></p>
        </div>
        <a href="manage_orders.php" class="btn btn-premium"><i class="fa-solid fa-truck me-2"></i>Manage Deliveries</a>
    </div>

    <!-- Stat Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="glass-card text-center text-sm-start">
                <div class="stat-icon-wrapper mx-auto mx-sm-0" style="background: linear-gradient(135deg, var(--pink-100), var(--pink-200)); color: var(--primary);">
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                </div>
                <h6 class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 1px; color: var(--text-light) !important;">Total Revenue</h6>
                <h3 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;">₹<?php echo number_format($revenue, 2); ?></h3>
                <span class="badge mt-2" style="background: var(--pink-50); color: var(--primary); font-size: 0.7rem;">Paid orders</span>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="glass-card text-center text-sm-start">
                <div class="stat-icon-wrapper mx-auto mx-sm-0" style="background: linear-gradient(135deg, #e8e0ff, #d4c8ff); color: #6c4dff;">
                    <i class="fa-solid fa-users"></i>
                </div>
                <h6 class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 1px; color: var(--text-light) !important;">Active Users</h6>
                <h3 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;"><?php echo number_format($userCount); ?></h3>
                <span class="badge mt-2" style="background: #f0edfe; color: #6c4dff; font-size: 0.7rem;">Registered</span>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="glass-card text-center text-sm-start">
                <div class="stat-icon-wrapper mx-auto mx-sm-0" style="background: linear-gradient(135deg, #dcfce7, #bbf7d0); color: #15803d;">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
                <h6 class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 1px; color: var(--text-light) !important;">Total Orders</h6>
                <h3 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;"><?php echo number_format($orderCount); ?></h3>
                <span class="badge mt-2" style="background: #ebfdf1; color: #15803d; font-size: 0.7rem;">+<?php echo rand(1,5); ?> this week</span>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="glass-card text-center text-sm-start">
                <div class="stat-icon-wrapper mx-auto mx-sm-0" style="background: linear-gradient(135deg, #fef3c7, #fde68a); color: #b45309;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <h6 class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 1px; color: var(--text-light) !important;">Custom Requests</h6>
                <h3 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;"><?php echo number_format($requestCount); ?></h3>
                <span class="badge mt-2" style="background: #fff7e6; color: #b45309; font-size: 0.7rem;">New requests</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <a href="manage_products.php" class="text-decoration-none">
                <div class="glass-card text-center py-4">
                    <div class="stat-icon-wrapper mx-auto" style="background: linear-gradient(135deg, var(--pink-100), var(--pink-200)); color: var(--primary); width: 60px; height: 60px;">
                        <i class="fa-solid fa-box"></i>
                    </div>
                    <h5 class="fw-bold mt-3 mb-1 text-dark" style="font-family: 'Playfair Display', serif;">Manage Products</h5>
                    <p class="small mb-0" style="color: var(--text-light);">Add or edit your craft items</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="manage_categories.php" class="text-decoration-none">
                <div class="glass-card text-center py-4">
                    <div class="stat-icon-wrapper mx-auto" style="background: linear-gradient(135deg, #e8e0ff, #d4c8ff); color: #6c4dff; width: 60px; height: 60px;">
                        <i class="fa-solid fa-tags"></i>
                    </div>
                    <h5 class="fw-bold mt-3 mb-1 text-dark" style="font-family: 'Playfair Display', serif;">Manage Categories</h5>
                    <p class="small mb-0" style="color: var(--text-light);">Organize your collections</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="manage_orders.php" class="text-decoration-none">
                <div class="glass-card text-center py-4">
                    <div class="stat-icon-wrapper mx-auto" style="background: linear-gradient(135deg, #dcfce7, #bbf7d0); color: #15803d; width: 60px; height: 60px;">
                        <i class="fa-solid fa-truck-fast"></i>
                    </div>
                    <h5 class="fw-bold mt-3 mb-1 text-dark" style="font-family: 'Playfair Display', serif;">Manage Orders</h5>
                    <p class="small mb-0" style="color: var(--text-light);">Track and fulfill orders</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Orders Section -->
    <div class="glass-card">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h4 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif;">Recent Orders</h4>
                <p class="small mb-0" style="color: var(--text-light);">Overview of the last 5 placed customer orders</p>
            </div>
            <a href="manage_orders.php" class="btn btn-premium btn-sm"><i class="fa-solid fa-list-check me-2"></i>View All Orders</a>
        </div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4" style="color: var(--text-light);">No recent orders found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $ro): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark"><?php echo htmlspecialchars($ro['user_name']); ?></div>
                                    <div style="color: var(--text-light); font-size: 0.8rem;">Order #<?php echo $ro['id']; ?></div>
                                </td>
                                <td class="fw-semibold" style="color: var(--text);">₹<?php echo number_format($ro['total_amount'], 2); ?></td>
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
                                <td style="color: var(--text-light); font-size: 0.85rem;">
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
</div>

<?php include 'includes/footer.php'; ?>
