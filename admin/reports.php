<?php
$pageTitle = 'Reports';
include 'includes/header.php';
include 'includes/sidebar.php';

try {
    // Sales by month
    $monthlySales = $pdo->query("SELECT DATE_FORMAT(order_date, '%Y-%m') as month, COUNT(*) as orders, SUM(total_amount) as revenue FROM orders WHERE payment_status='paid' GROUP BY month ORDER BY month DESC LIMIT 12")->fetchAll();
    
    // Top products
    $topProducts = $pdo->query("SELECT p.name, SUM(oi.quantity) as qty, SUM(oi.quantity * oi.price) as revenue FROM order_items oi JOIN products p ON oi.product_id = p.id GROUP BY oi.product_id ORDER BY revenue DESC LIMIT 10")->fetchAll();
    
    // Category sales
    $catSales = $pdo->query("SELECT c.name, COUNT(oi.id) as items FROM order_items oi JOIN products p ON oi.product_id = p.id RIGHT JOIN categories c ON p.category_id = c.id GROUP BY c.id ORDER BY items DESC")->fetchAll();
    
    // Customer stats
    $customerStats = $pdo->query("SELECT COUNT(*) as total, COALESCE(AVG(order_count),0) as avg_orders FROM (SELECT user_id, COUNT(*) as order_count FROM orders GROUP BY user_id) t")->fetch();
    
    $totalSales = $pdo->query("SELECT COUNT(*) as total, SUM(total_amount) as revenue FROM orders WHERE payment_status='paid'")->fetch();
} catch (PDOException $e) {
    $monthlySales = $topProducts = $catSales = [];
    $customerStats = ['total' => 0, 'avg_orders' => 0];
    $totalSales = ['total' => 0, 'revenue' => 0];
}
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif;">♡ Reports ♡</h2>
            <p style="color: var(--text-light); margin: 0;">Analytics and insights</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-premium-outline btn-sm" onclick="window.print()"><i class="fa-solid fa-download me-1"></i>Download PDF</button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="glass-card text-center py-3">
                <h6 class="small fw-semibold" style="color: var(--text-light);">Total Paid Orders</h6>
                <h3 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;"><?php echo number_format($totalSales['total']); ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card text-center py-3">
                <h6 class="small fw-semibold" style="color: var(--text-light);">Total Revenue</h6>
                <h3 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;">₹<?php echo number_format($totalSales['revenue'] ?? 0, 2); ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card text-center py-3">
                <h6 class="small fw-semibold" style="color: var(--text-light);">Avg Orders / Customer</h6>
                <h3 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;"><?php echo number_format($customerStats['avg_orders'], 1); ?></h3>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Monthly Sales -->
        <div class="col-md-6">
            <div class="glass-card">
                <h5 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif;">♥ Monthly Sales ♥</h5>
                <?php if (empty($monthlySales)): ?>
                    <p class="text-center py-3" style="color: var(--text-light);">No data yet</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead><tr><th>Month</th><th>Orders</th><th>Revenue</th></tr></thead>
                            <tbody>
                                <?php foreach ($monthlySales as $ms): ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo $ms['month']; ?></td>
                                        <td><?php echo $ms['orders']; ?></td>
                                        <td class="fw-semibold">₹<?php echo number_format($ms['revenue'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Products -->
        <div class="col-md-6">
            <div class="glass-card">
                <h5 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif;">♥ Top Products ♥</h5>
                <?php if (empty($topProducts)): ?>
                    <p class="text-center py-3" style="color: var(--text-light);">No data yet</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead><tr><th>#</th><th>Product</th><th>Qty Sold</th><th>Revenue</th></tr></thead>
                            <tbody>
                                <?php foreach ($topProducts as $i => $tp): ?>
                                    <tr>
                                        <td><span class="fw-bold" style="color: var(--primary);">#<?php echo $i + 1; ?></span></td>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($tp['name']); ?></td>
                                        <td><?php echo $tp['qty']; ?></td>
                                        <td class="fw-semibold">₹<?php echo number_format($tp['revenue'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Category Sales -->
        <div class="col-md-6">
            <div class="glass-card">
                <h5 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif;">♡ Category Performance ♡</h5>
                <?php if (empty($catSales)): ?>
                    <p class="text-center py-3" style="color: var(--text-light);">No data yet</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead><tr><th>Category</th><th>Items Sold</th></tr></thead>
                            <tbody>
                                <?php foreach ($catSales as $cs): ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($cs['name'] ?: 'Uncategorized'); ?></td>
                                        <td><?php echo $cs['items']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Export Section -->
        <div class="col-md-6">
            <div class="glass-card text-center py-4">
                <h5 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif;">♡ Export Data ♡</h5>
                <p class="small" style="color: var(--text-light);">Download reports for offline analysis</p>
                <div class="d-flex justify-content-center gap-3 mt-3">
                    <a href="?export=sales" class="btn btn-premium btn-sm"><i class="fa-solid fa-file-csv me-1"></i>Sales CSV</a>
                    <a href="?export=products" class="btn btn-premium-outline btn-sm"><i class="fa-solid fa-file-csv me-1"></i>Products CSV</a>
                    <a href="?export=customers" class="btn btn-premium-outline btn-sm"><i class="fa-solid fa-file-csv me-1"></i>Customers CSV</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Export handlers
$export = $_GET['export'] ?? '';
if ($export === 'sales') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=sales_report.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Month', 'Orders', 'Revenue']);
    foreach ($monthlySales as $r) fputcsv($out, [$r['month'], $r['orders'], $r['revenue']]);
    fclose($out); exit;
} elseif ($export === 'products') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=products_report.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Product', 'Qty Sold', 'Revenue']);
    foreach ($topProducts as $r) fputcsv($out, [$r['name'], $r['qty'], $r['revenue']]);
    fclose($out); exit;
} elseif ($export === 'customers') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=customers_report.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Name', 'Email', 'Orders', 'Total Spent']);
    $customers = $pdo->query("SELECT u.name, u.email, (SELECT COUNT(*) FROM orders WHERE user_id=u.id) as orders, (SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE user_id=u.id AND payment_status='paid') as spent FROM users u")->fetchAll();
    foreach ($customers as $c) fputcsv($out, [$c['name'], $c['email'], $c['orders'], $c['spent']]);
    fclose($out); exit;
}
?>

<?php include 'includes/footer.php'; ?>
