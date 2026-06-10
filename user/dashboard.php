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
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css?v=2.0">
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
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar li { margin-bottom: 1rem; }
        .sidebar a { display: block; padding: 0.8rem; border-radius: 0.5rem; text-decoration: none; color: var(--text); }
        .sidebar a:hover { background: var(--pink-50); color: var(--primary); }
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
    <!-- Navbar brand with cute ribbon -->
    <nav class="navbar navbar-expand-lg glass-nav">
        <div class="container-fluid">
            <a class="navbar-brand font-serif fs-3 fw-bold text-gradient" href="../index.php">
                CraftyGifts
                <svg class="brand-ribbon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="36" height="36" style="color: var(--primary); fill: none; stroke: currentColor; stroke-width: 3; stroke-linecap: round; stroke-linejoin: round; vertical-align: middle; margin-left: 2px;">
                    <path d="M32 32 C20 18, 10 24, 16 36 C20 44, 30 36, 32 32 Z" fill="rgba(226, 95, 132, 0.15)"/>
                    <path d="M32 32 C44 18, 54 24, 48 36 C44 44, 34 36, 32 32 Z" fill="rgba(226, 95, 132, 0.15)"/>
                    <path d="M28 34 C24 44, 18 50, 20 54 M20 54 C23 54, 25 50, 27 46"/>
                    <path d="M36 34 C40 44, 46 50, 44 54 M44 54 C41 54, 39 50, 37 46"/>
                    <circle cx="32" cy="32" r="5" fill="var(--primary)"/>
                </svg>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars" style="color: var(--primary);"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link fw-medium" href="../index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="../products.php">Shop</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="../custom-request.php">Custom Order</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="../index.php#about">About</a></li>
                </ul>
                <div class="d-flex gap-2">
                    <a href="dashboard.php" class="btn btn-outline-custom">Dashboard</a>
                    <a href="../auth/logout.php" class="btn btn-primary-custom">Logout</a>
                </div>
            </div>
        </div>
    </nav>
    <div class="navbar-scallop-divider"></div>

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
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
