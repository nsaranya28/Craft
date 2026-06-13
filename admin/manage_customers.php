<?php
$pageTitle = 'Customers';
include 'includes/header.php';
include 'includes/sidebar.php';

$search = trim($_GET['search'] ?? '');
try {
    $sql = "SELECT u.*, 
            (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,
            (SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE user_id = u.id AND payment_status='paid') as total_spent
            FROM users u";
    if ($search) {
        $sql .= " WHERE u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?";
        $stmt = $pdo->prepare($sql . " ORDER BY u.created_at DESC");
        $s = "%$search%";
        $stmt->execute([$s, $s, $s]);
    } else {
        $stmt = $pdo->query($sql . " ORDER BY u.created_at DESC");
    }
    $customers = $stmt->fetchAll();
} catch (PDOException $e) {
    $customers = [];
    $error = $e->getMessage();
}
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif;">♡ Customers ♡</h2>
            <p style="color: var(--text-light); margin: 0;"><?php echo count($customers); ?> registered customers</p>
        </div>
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search customers..." value="<?php echo htmlspecialchars($search); ?>" style="border-radius: 12px; min-width: 220px;">
            <button type="submit" class="btn btn-premium btn-sm"><i class="fa-solid fa-search"></i></button>
        </form>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr><th>Customer</th><th>Email</th><th>Phone</th><th>Location</th><th>Orders</th><th>Total Spent</th><th>Joined</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                        <tr><td colspan="8" class="text-center py-4" style="color: var(--text-light);">No customers found ♡</td></tr>
                    <?php else: ?>
                        <?php foreach ($customers as $c): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--pink-100), var(--pink-200)); display: flex; align-items: center; justify-content: center; color: var(--primary); font-weight: 600; font-size: 0.85rem;">
                                            <?php echo strtoupper(substr($c['name'], 0, 1)); ?>
                                        </div>
                                        <span class="fw-semibold"><?php echo htmlspecialchars($c['name']); ?></span>
                                    </div>
                                </td>
                                <td style="font-size: 0.85rem;"><?php echo htmlspecialchars($c['email']); ?></td>
                                <td style="font-size: 0.85rem;"><?php echo htmlspecialchars($c['phone'] ?? '—'); ?></td>
                                <td style="font-size: 0.85rem;"><?php echo htmlspecialchars(implode(', ', array_filter([$c['city'] ?? '', $c['state'] ?? ''])) ?: '—'); ?></td>
                                <td><span class="badge" style="background: var(--pink-50); color: var(--primary);"><?php echo $c['order_count']; ?></span></td>
                                <td class="fw-semibold">₹<?php echo number_format($c['total_spent'], 2); ?></td>
                                <td style="font-size: 0.78rem; color: var(--text-light);"><?php echo date('M d, Y', strtotime($c['created_at'])); ?></td>
                                <td>
                                    <a href="manage_orders.php?search=<?php echo urlencode($c['email']); ?>" class="btn btn-premium-outline btn-sm py-1 px-2" title="View Orders">
                                        <i class="fa-solid fa-receipt"></i>
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
