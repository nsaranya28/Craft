<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied. Admins only.");
}

// Stats
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$orderCount = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$requestCount = $pdo->query("SELECT COUNT(*) FROM custom_orders")->fetchColumn();
$revenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE payment_status='paid'")->fetchColumn() ?: 0;

$recentOrders = $pdo->query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | CraftyGifts</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        .stat-card {
            padding: 2rem;
            text-align: center;
        }
        .stat-card h4 { color: var(--text-light); font-size: 0.9rem; margin-bottom: 0.5rem; }
        .stat-card .value { font-size: 2rem; font-weight: 800; color: var(--primary); }
        
        .admin-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
        }
        .admin-nav { height: calc(100vh - 150px); }
        .admin-nav ul { list-style: none; }
        .admin-nav li { margin-bottom: 0.5rem; }
        .admin-nav a { padding: 1rem; border-radius: 0.5rem; display: block; }
        .admin-nav a:hover { background: #f1f5f9; }
        .admin-nav a.active { background: var(--primary); color: white; }
    </style>
</head>
<body>
    <header class="glass">
        <nav>
            <a href="../index.php" class="logo">CraftyGifts <span style="font-size: 0.8rem; vertical-align: middle; opacity: 0.7;">Admin</span></a>
            <div class="nav-btns">
                <a href="../auth/logout.php" class="btn btn-outline">Logout</a>
            </div>
        </nav>
    </header>

    <main style="padding: 3rem 5%;">
        <div class="admin-layout">
            <aside class="card admin-nav">
                <ul>
                    <li><a href="#" class="active">Overview</a></li>
                    <li><a href="create_product.php" class="">Manage Products</a></li>
                    <li><a href="#">Categories</a></li>
                    <li><a href="create_order.php" class="">Create Order</a></li>
                    <li><a href="#">Custom Requests</a></li>
                    <li><a href="#">Users</a></li>
                    <li><a href="#">Reports</a></li>
                </ul>
            </aside>

            <div class="content">
                <div class="stats-grid">
                    <div class="card stat-card">
                        <h4>Total Revenue</h4>
                        <div class="value">$<?php echo number_format($revenue, 2); ?></div>
                    </div>
                    <div class="card stat-card">
                        <h4>Active Users</h4>
                        <div class="value"><?php echo $userCount; ?></div>
                    </div>
                    <div class="card stat-card">
                        <h4>Total Orders</h4>
                        <div class="value"><?php echo $orderCount; ?></div>
                    </div>
                    <div class="card stat-card">
                        <h4>Custom Req.</h4>
                        <div class="value"><?php echo $requestCount; ?></div>
                    </div>
                </div>

                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h3>Recent Orders</h3>
                        <a href="#" class="text-gradient">View All Orders</a>
                    </div>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 1px solid #f1f5f9;">
                                <th style="padding: 1rem;">Customer</th>
                                <th style="padding: 1rem;">Amount</th>
                                <th style="padding: 1rem;">Status</th>
                                <th style="padding: 1rem;">Date</th>
                                <th style="padding: 1rem;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recentOrders as $ro): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 1rem;"><?php echo $ro['user_name']; ?></td>
                                <td style="padding: 1rem;">$<?php echo $ro['total_amount']; ?></td>
                                <td style="padding: 1rem;"><span style="padding: 0.2rem 0.6rem; border-radius: 1rem; background: #ffe0ed; color: #E8628C; font-size: 0.8rem;"><?php echo $ro['status']; ?></span></td>
                                <td style="padding: 1rem;"><?php echo date('M d', strtotime($ro['order_date'])); ?></td>
                                <td style="padding: 1rem;"><a href="#" class="text-gradient">Edit</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
