<?php
$pageTitle = 'Dashboard';
include 'includes/header.php';
include 'includes/sidebar.php';

// Stats
try {
    $totalOrders     = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $pendingOrders   = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='ordered' OR status='processing'")->fetchColumn();
    $completedOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='delivered'")->fetchColumn();
    $totalRevenue    = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE payment_status='paid'")->fetchColumn() ?: 0;
    $totalProducts   = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $totalCustomers  = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalReviews    = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
    $pendingReviews  = $pdo->query("SELECT COUNT(*) FROM reviews WHERE is_approved=0")->fetchColumn();
    $unreadMessages  = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn();
    $customRequests  = $pdo->query("SELECT COUNT(*) FROM custom_orders")->fetchColumn();
    $pendingCustom   = $pdo->query("SELECT COUNT(*) FROM custom_orders WHERE status='pending'")->fetchColumn();

    $recentOrders    = $pdo->query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC LIMIT 5")->fetchAll();
    $topProducts     = $pdo->query("SELECT p.name, p.image, SUM(oi.quantity) as total_sold FROM order_items oi JOIN products p ON oi.product_id = p.id GROUP BY oi.product_id ORDER BY total_sold DESC LIMIT 5")->fetchAll();
    $recentCustom    = $pdo->query("SELECT co.*, u.name as user_name FROM custom_orders co JOIN users u ON co.user_id = u.id ORDER BY co.created_at DESC LIMIT 5")->fetchAll();
} catch (PDOException $e) {
    $totalOrders = $pendingOrders = $completedOrders = $totalRevenue = $totalProducts = $totalCustomers = $totalReviews = $pendingReviews = $unreadMessages = $customRequests = $pendingCustom = 0;
    $recentOrders = $topProducts = $recentCustom = [];
}
?>

<div class="container-fluid p-0">
    <!-- Welcome Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif;">♡ Welcome back, <span style="background: linear-gradient(135deg, var(--primary), var(--primary-light)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Admin</span> ♡</h2>
            <p style="color: var(--text-light); margin: 0;"><i class="fa-regular fa-calendar me-2"></i><?php echo date('l, F j, Y'); ?> &nbsp;·&nbsp; <i class="fa-regular fa-bell me-1"></i> <?php echo $pendingCustom + $pendingReviews + $unreadMessages; ?> notifications</p>
        </div>
        <div class="d-flex gap-2">
            <a href="manage_orders.php" class="btn btn-premium"><i class="fa-solid fa-truck me-2"></i>Orders</a>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="glass-card d-flex align-items-center gap-3">
                <div class="stat-icon-wrapper flex-shrink-0 mb-0" style="background: linear-gradient(135deg, var(--pink-100), var(--pink-200)); color: var(--primary); width: 48px; height: 48px; font-size: 1.2rem;">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
                <div>
                    <h6 class="small fw-semibold text-uppercase mb-0" style="letter-spacing: 0.5px; color: var(--text-light);">Total Orders</h6>
                    <h3 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;"><?php echo number_format($totalOrders); ?></h3>
                    <span class="small" style="color: var(--primary);"><?php echo $pendingOrders; ?> pending</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="glass-card d-flex align-items-center gap-3">
                <div class="stat-icon-wrapper flex-shrink-0 mb-0" style="background: linear-gradient(135deg, #dcfce7, #bbf7d0); color: #15803d; width: 48px; height: 48px; font-size: 1.2rem;">
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                </div>
                <div>
                    <h6 class="small fw-semibold text-uppercase mb-0" style="letter-spacing: 0.5px; color: var(--text-light);">Total Revenue</h6>
                    <h3 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;">₹<?php echo number_format($totalRevenue, 2); ?></h3>
                    <span class="small" style="color: #15803d;">Paid orders</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="glass-card d-flex align-items-center gap-3">
                <div class="stat-icon-wrapper flex-shrink-0 mb-0" style="background: linear-gradient(135deg, #e8e0ff, #d4c8ff); color: #6c4dff; width: 48px; height: 48px; font-size: 1.2rem;">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <h6 class="small fw-semibold text-uppercase mb-0" style="letter-spacing: 0.5px; color: var(--text-light);">Customers</h6>
                    <h3 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;"><?php echo number_format($totalCustomers); ?></h3>
                    <span class="small" style="color: #6c4dff;">Registered users</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="glass-card d-flex align-items-center gap-3">
                <div class="stat-icon-wrapper flex-shrink-0 mb-0" style="background: linear-gradient(135deg, #fef3c7, #fde68a); color: #b45309; width: 48px; height: 48px; font-size: 1.2rem;">
                    <i class="fa-solid fa-gift"></i>
                </div>
                <div>
                    <h6 class="small fw-semibold text-uppercase mb-0" style="letter-spacing: 0.5px; color: var(--text-light);">Products</h6>
                    <h3 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;"><?php echo number_format($totalProducts); ?></h3>
                    <span class="small" style="color: #b45309;">In stock</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="glass-card text-center py-3">
                <div class="stat-icon-wrapper mx-auto mb-2" style="background: linear-gradient(135deg, #fee2e2, #fecaca); color: #b91c1c; width: 40px; height: 40px; font-size: 1rem;">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <h6 class="small fw-semibold mb-0" style="color: var(--text-light);">Pending</h6>
                <h4 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;"><?php echo $pendingOrders; ?></h4>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="glass-card text-center py-3">
                <div class="stat-icon-wrapper mx-auto mb-2" style="background: linear-gradient(135deg, #dcfce7, #bbf7d0); color: #15803d; width: 40px; height: 40px; font-size: 1rem;">
                    <i class="fa-solid fa-check-circle"></i>
                </div>
                <h6 class="small fw-semibold mb-0" style="color: var(--text-light);">Completed</h6>
                <h4 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;"><?php echo $completedOrders; ?></h4>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="glass-card text-center py-3">
                <div class="stat-icon-wrapper mx-auto mb-2" style="background: linear-gradient(135deg, #fef3c7, #fde68a); color: #b45309; width: 40px; height: 40px; font-size: 1rem;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <h6 class="small fw-semibold mb-0" style="color: var(--text-light);">Custom Req.</h6>
                <h4 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;"><?php echo $pendingCustom; ?></h4>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="glass-card text-center py-3">
                <div class="stat-icon-wrapper mx-auto mb-2" style="background: linear-gradient(135deg, #e8e0ff, #d4c8ff); color: #6c4dff; width: 40px; height: 40px; font-size: 1rem;">
                    <i class="fa-solid fa-envelope"></i>
                </div>
                <h6 class="small fw-semibold mb-0" style="color: var(--text-light);">Unread</h6>
                <h4 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;"><?php echo $unreadMessages; ?></h4>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;">♡ Revenue Overview</h5>
                    <span class="badge" style="background: var(--pink-50); color: var(--primary);">This Month</span>
                </div>
                <div style="height: 260px; background: linear-gradient(180deg, var(--pink-50), transparent); border-radius: 16px; display: flex; align-items: flex-end; justify-content: space-around; padding: 1rem 0.5rem;">
                    <?php $heights = [40, 65, 50, 80, 60, 90, 70, 85, 55, 75, 95, 45, 70, 88, 62]; ?>
                    <?php foreach ($heights as $h): ?>
                        <div style="width: 4%; background: linear-gradient(180deg, var(--primary), var(--primary-light)); height: <?php echo $h; ?>%; border-radius: 8px 8px 4px 4px; transition: height 0.5s; animation: growUp 1.5s ease-out; min-width: 8px;"></div>
                    <?php endforeach; ?>
                </div>
                <div class="d-flex justify-content-between mt-2 small" style="color: var(--text-light);">
                    <span>Jun 1</span><span>Jun 8</span><span>Jun 15</span><span>Jun 22</span><span>Jun 30</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card h-100">
                <h5 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif;">♥ Top Selling ♥</h5>
                <?php if (empty($topProducts)): ?>
                    <div class="text-center py-4" style="color: var(--text-light);">
                        <i class="fa-solid fa-box-open fa-2x mb-2" style="color: var(--pink-200);"></i>
                        <p class="small mb-0">No sales data yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($topProducts as $i => $tp): ?>
                        <div class="d-flex align-items-center gap-3 mb-3 pb-2" style="border-bottom: 1px solid var(--pink-100);">
                            <span class="fw-bold" style="color: var(--primary); font-size: 1.1rem;">#<?php echo $i + 1; ?></span>
                            <div class="flex-grow-1">
                                <div class="fw-semibold small"><?php echo htmlspecialchars($tp['name']); ?></div>
                                <div style="color: var(--text-light); font-size: 0.75rem;"><?php echo $tp['total_sold']; ?> sold</div>
                            </div>
                            <span class="badge" style="background: var(--pink-50); color: var(--primary);"><?php echo $tp['total_sold']; ?>x</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Recent Orders + Custom Requests -->
    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h5 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;">♥ Recent Orders ♥</h5>
                    <a href="manage_orders.php" class="btn btn-premium-outline btn-sm">View All <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentOrders)): ?>
                                <tr><td colspan="5" class="text-center py-4" style="color: var(--text-light);">No orders yet ♡</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentOrders as $ro): ?>
                                    <tr>
                                        <td><span class="fw-semibold"><?php echo htmlspecialchars($ro['user_name']); ?></span></td>
                                        <td class="fw-semibold">₹<?php echo number_format($ro['total_amount'], 2); ?></td>
                                        <td><span class="badge-status status-<?php echo strtolower($ro['payment_status']); ?>"><?php echo $ro['payment_status']; ?></span></td>
                                        <td><span class="badge-status status-<?php echo strtolower($ro['status']); ?>"><?php echo $ro['status']; ?></span></td>
                                        <td style="font-size: 0.78rem; color: var(--text-light);"><?php echo date('M d', strtotime($ro['order_date'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="glass-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;">♡ Custom Requests ♡</h5>
                    <a href="manage_custom_orders.php" class="btn btn-premium-outline btn-sm">View <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>
                <?php if (empty($recentCustom)): ?>
                    <div class="text-center py-4" style="color: var(--text-light);">
                        <i class="fa-solid fa-wand-magic-sparkles fa-2x mb-2" style="color: var(--pink-200);"></i>
                        <p class="small mb-0">No custom requests yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentCustom as $rc): ?>
                        <div class="d-flex align-items-center gap-2 mb-2 pb-2" style="border-bottom: 1px solid var(--pink-50);">
                            <div class="flex-grow-1">
                                <div class="fw-semibold small"><?php echo htmlspecialchars($rc['title'] ?: 'Custom Gift'); ?></div>
                                <div style="color: var(--text-light); font-size: 0.7rem;">by <?php echo htmlspecialchars($rc['user_name']); ?></div>
                            </div>
                            <span class="badge-status status-<?php echo $rc['status'] === 'pending' ? 'ordered' : ($rc['status'] === 'accepted' ? 'delivered' : ($rc['status'] === 'rejected' ? 'cancelled' : 'processing')); ?>"><?php echo $rc['status']; ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions Grid -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <a href="manage_products.php" class="text-decoration-none">
                <div class="glass-card text-center py-3">
                    <div class="stat-icon-wrapper mx-auto mb-2" style="background: linear-gradient(135deg, var(--pink-100), var(--pink-200)); color: var(--primary); width: 44px; height: 44px; font-size: 1.1rem;">
                        <i class="fa-solid fa-box"></i>
                    </div>
                    <h6 class="fw-semibold mb-0 small">Products</h6>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="manage_categories.php" class="text-decoration-none">
                <div class="glass-card text-center py-3">
                    <div class="stat-icon-wrapper mx-auto mb-2" style="background: linear-gradient(135deg, #e8e0ff, #d4c8ff); color: #6c4dff; width: 44px; height: 44px; font-size: 1.1rem;">
                        <i class="fa-solid fa-tags"></i>
                    </div>
                    <h6 class="fw-semibold mb-0 small">Categories</h6>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="manage_customers.php" class="text-decoration-none">
                <div class="glass-card text-center py-3">
                    <div class="stat-icon-wrapper mx-auto mb-2" style="background: linear-gradient(135deg, #dcfce7, #bbf7d0); color: #15803d; width: 44px; height: 44px; font-size: 1.1rem;">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <h6 class="fw-semibold mb-0 small">Customers</h6>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="reports.php" class="text-decoration-none">
                <div class="glass-card text-center py-3">
                    <div class="stat-icon-wrapper mx-auto mb-2" style="background: linear-gradient(135deg, #fef3c7, #fde68a); color: #b45309; width: 44px; height: 44px; font-size: 1.1rem;">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <h6 class="fw-semibold mb-0 small">Reports</h6>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
    @keyframes growUp {
        from { height: 0 !important; }
        to { height: var(--h); }
    }
    .glass-card:hover .stat-icon-wrapper {
        transform: scale(1.05);
        transition: transform 0.3s;
    }
</style>

<?php include 'includes/footer.php'; ?>
