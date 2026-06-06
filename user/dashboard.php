<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get Orders
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// Get Custom Requests
$stmt = $pdo->prepare("SELECT * FROM custom_orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | CraftyGifts</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dashboard-layout {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 2rem;
            min-height: 80vh;
        }
        .sidebar {
            padding: 2rem;
            height: fit-content;
        }
        .sidebar ul { list-style: none; }
        .sidebar li { margin-bottom: 1rem; }
        .sidebar a { display: block; padding: 0.8rem; border-radius: 0.5rem; }
        .sidebar a.active { background: var(--primary); color: white; }
        
        .tab-content { padding: 1rem; }
        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 1rem;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-processing { background: #dbeafe; color: #1e40af; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { text-align: left; padding: 1rem; border-bottom: 1px solid #f1f5f9; }
        th { color: var(--text-light); font-weight: 500; font-size: 0.9rem; }
    </style>
</head>
<body>
    <header class="glass">
        <nav>
            <a href="../index.php" class="logo">CraftyGifts</a>
            <ul class="nav-links">
                <li><a href="../index.php">Home</a></li>
                <li><a href="../products.php">Shop</a></li>
            </ul>
            <div class="nav-btns">
                <a href="../auth/logout.php" class="btn btn-outline">Logout</a>
            </div>
        </nav>
    </header>

    <main style="padding: 4rem 5%;">
        <div class="dashboard-layout">
            <aside class="card sidebar">
                <ul>
                    <li><a href="#" class="active">Orders</a></li>
                    <li><a href="#">Custom Requests</a></li>
                    <li><a href="#">My Profile</a></li>
                    <li><a href="#">Settings</a></li>
                </ul>
            </aside>

            <div class="tab-content">
                <h2 style="margin-bottom: 2rem;">Welcome, <?php echo $_SESSION['user_name']; ?></h2>
                
                <div class="card" style="margin-bottom: 3rem;">
                    <h3>Recent Orders</h3>
                    <?php if (empty($orders)): ?>
                        <p style="margin-top: 1rem; color: var(--text-light);">No orders found. <a href="../products.php" class="text-gradient">Start shopping</a></p>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orders as $order): ?>
                                <tr>
                                    <td>#ORD-<?php echo $order['id']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                    <td>$<?php echo $order['total_amount']; ?></td>
                                    <td><span class="status-badge status-processing"><?php echo ucfirst($order['status']); ?></span></td>
                                    <td><a href="#" class="text-gradient">Track</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h3>Custom Requests</h3>
                    <?php if (empty($requests)): ?>
                        <p style="margin-top: 1rem; color: var(--text-light);">No custom requests. <a href="../custom-request.php" class="text-gradient">Create one</a></p>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Request</th>
                                    <th>Submitted</th>
                                    <th>Estimate</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($requests as $req): ?>
                                <tr>
                                    <td><?php echo $req['title']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($req['created_at'])); ?></td>
                                    <td><?php echo $req['price_estimate'] ? '$'.$req['price_estimate'] : 'Pending'; ?></td>
                                    <td><span class="status-badge status-pending"><?php echo ucfirst($req['status']); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
