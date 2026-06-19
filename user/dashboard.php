<?php
session_start();
include '../includes/db.php';
include '../includes/cart-helper.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$active_tab = $_GET['tab'] ?? 'orders';

// Get user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Ensure reviews table has required columns
foreach ([
    "ALTER TABLE reviews ADD COLUMN is_approved TINYINT(1) DEFAULT 0 AFTER comment",
    "ALTER TABLE reviews ADD COLUMN order_id INT DEFAULT NULL AFTER product_id"
] as $sql) {
    try { $pdo->exec($sql); } catch (PDOException $e) {}
}

// Get Orders
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// Get order items for all user orders
$orderItems = [];
$reviewedItems = [];
if (!empty($orders)) {
    $orderIds = array_column($orders, 'id');
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $stmt = $pdo->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id IN ($placeholders)");
    $stmt->execute($orderIds);
    $items = $stmt->fetchAll();
    foreach ($items as $item) {
        $orderItems[$item['order_id']][] = $item;
    }

    // Get existing reviews to know what's already reviewed
    $stmt = $pdo->prepare("SELECT product_id, order_id FROM reviews WHERE user_id = ?");
    $stmt->execute([$user_id]);
    foreach ($stmt->fetchAll() as $rv) {
        $reviewedItems[$rv['order_id'] . '_' . $rv['product_id']] = true;
    }
}

// Get Custom Requests
$stmt = $pdo->prepare("SELECT * FROM custom_orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$requests = $stmt->fetchAll();

// Flash messages
$settings_success = $_SESSION['settings_success'] ?? null;
$settings_error   = $_SESSION['settings_error'] ?? null;
unset($_SESSION['settings_success'], $_SESSION['settings_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | CraftyGifts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=2.0">
    <style>
        body { background: var(--cream); }

        /* Layout */
        .dashboard-wrap {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 2rem;
            padding: 3rem 5%;
            min-height: 80vh;
        }

        /* Sidebar */
        .dash-sidebar {
            background: white;
            border-radius: 1.25rem;
            padding: 2rem 1.25rem;
            border: 1.5px solid var(--pink-100);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }
        .dash-sidebar .user-avatar {
            width: 72px; height: 72px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; color: white; margin: 0 auto 0.75rem;
        }
        .dash-sidebar .user-name { font-weight: 700; color: var(--text); font-size: 1.05rem; text-align:center; }
        .dash-sidebar .user-email { color: var(--text-light); font-size: 0.8rem; text-align:center; margin-bottom: 1.5rem; }

        .dash-nav { list-style: none; padding: 0; margin: 0; }
        .dash-nav li { margin-bottom: 0.4rem; }
        .dash-nav a {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            text-decoration: none;
            color: var(--text);
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        .dash-nav a:hover { background: var(--pink-50); color: var(--primary); }
        .dash-nav a.active { background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; }
        .dash-nav a .nav-icon { width: 20px; text-align: center; }

        /* Content cards */
        .dash-content { display: flex; flex-direction: column; gap: 1.5rem; }
        .dash-card {
            background: white;
            border-radius: 1.25rem;
            border: 1.5px solid var(--pink-100);
            padding: 2rem;
        }
        .dash-card h3 {
            font-family: var(--font-serif);
            color: var(--text);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px dashed var(--pink-100);
            display: flex; align-items: center; gap: 0.6rem;
        }
        .dash-card h3 i { color: var(--primary); }

        /* Tables */
        .dash-table { width: 100%; border-collapse: collapse; }
        .dash-table th {
            color: var(--text-light); font-weight: 600; font-size: 0.8rem;
            text-transform: uppercase; letter-spacing: 0.05em;
            padding: 0.75rem 1rem; border-bottom: 2px solid var(--pink-50);
            text-align: left;
        }
        .dash-table td { padding: 0.9rem 1rem; border-bottom: 1px solid var(--pink-50); color: var(--text); font-size: 0.92rem; }
        .dash-table tr:last-child td { border-bottom: none; }
        .dash-table tr:hover td { background: var(--pink-50); }

        /* Status badges */
        .badge-status {
            padding: 0.3rem 0.8rem; border-radius: 2rem; font-size: 0.75rem; font-weight: 600;
        }
        .badge-ordered   { background: #e0f2fe; color: #0369a1; }
        .badge-processing{ background: #fef3c7; color: #92400e; }
        .badge-shipped   { background: #ede9fe; color: #6d28d9; }
        .badge-delivered { background: #d1fae5; color: #065f46; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }
        .badge-pending   { background: #fef3c7; color: #92400e; }

        /* Settings Form */
        .settings-section { margin-bottom: 2rem; }
        .settings-section h5 {
            font-family: var(--font-serif);
            font-size: 1.1rem; color: var(--text);
            margin-bottom: 1rem; padding-bottom: 0.5rem;
            border-bottom: 1px dashed var(--pink-100);
            display: flex; align-items: center; gap: 0.5rem;
        }
        .settings-section h5 i { color: var(--primary); }
        .form-label { font-weight: 600; font-size: 0.88rem; color: var(--text); }
        .form-control, .form-select {
            border: 1.5px solid var(--pink-100); border-radius: 0.6rem;
            padding: 0.65rem 0.9rem; font-size: 0.93rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(226,95,132,0.12);
        }
        .btn-save {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white; border: none; border-radius: 0.75rem;
            padding: 0.65rem 2rem; font-weight: 600;
            transition: opacity 0.2s, transform 0.15s;
        }
        .btn-save:hover { opacity: 0.88; transform: translateY(-1px); }

        .empty-state {
            text-align: center; padding: 2.5rem 1rem;
            color: var(--text-light);
        }
        .empty-state .empty-icon { font-size: 3rem; margin-bottom: 0.75rem; opacity: 0.4; }

        @media (max-width: 768px) {
            .dashboard-wrap { grid-template-columns: 1fr; padding: 1.5rem; }
            .dash-sidebar { position: static; }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg glass-nav">
        <div class="container-fluid">
            <a class="navbar-brand font-serif fs-3 fw-bold text-gradient" href="../index.php">
                CraftyGifts
                <svg class="brand-ribbon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="36" height="36" style="color:var(--primary);fill:none;stroke:currentColor;stroke-width:3;stroke-linecap:round;stroke-linejoin:round;vertical-align:middle;margin-left:2px;">
                    <path d="M32 32 C20 18, 10 24, 16 36 C20 44, 30 36, 32 32 Z" fill="rgba(226,95,132,0.15)"/>
                    <path d="M32 32 C44 18, 54 24, 48 36 C44 44, 34 36, 32 32 Z" fill="rgba(226,95,132,0.15)"/>
                    <path d="M28 34 C24 44, 18 50, 20 54 M20 54 C23 54, 25 50, 27 46"/>
                    <path d="M36 34 C40 44, 46 50, 44 54 M44 54 C41 54, 39 50, 37 46"/>
                    <circle cx="32" cy="32" r="5" fill="var(--primary)"/>
                </svg>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars" style="color:var(--primary);"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link fw-medium" href="../index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="../products.php">Shop</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="../custom-request.php">Custom Order</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="../cart.php"><i class="fas fa-shopping-cart me-1"></i>Cart <span class="badge bg-primary text-white rounded-pill px-2" style="font-size:0.75rem;"><?= getCartCount() ?></span></a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="wishlist.php"><i class="fa-regular fa-heart me-1"></i>Wishlist</a></li>
                </ul>
                <div class="d-flex gap-2 align-items-center">
                    <a href="dashboard.php" class="btn btn-outline-custom">Dashboard</a>
                    <a href="../auth/logout.php" class="btn btn-primary-custom">Logout</a>
                </div>
            </div>
        </div>
    </nav>
    <div class="navbar-scallop-divider"></div>

    <div class="dashboard-wrap">
        <!-- Sidebar -->
        <aside class="dash-sidebar">
            <div class="user-avatar"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></div>
            <div class="user-name"><?php echo htmlspecialchars($user['name']); ?></div>
            <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>

            <ul class="dash-nav">
                <li>
                    <a href="?tab=orders" class="<?= $active_tab === 'orders' ? 'active' : '' ?>">
                        <i class="fas fa-box-open nav-icon"></i> My Orders
                        <?php if (count($orders)): ?><span class="badge ms-auto" style="background:var(--primary);color:white;font-size:0.7rem;"><?= count($orders) ?></span><?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="?tab=requests" class="<?= $active_tab === 'requests' ? 'active' : '' ?>">
                        <i class="fas fa-paint-brush nav-icon"></i> Custom Requests
                        <?php if (count($requests)): ?><span class="badge ms-auto" style="background:var(--primary);color:white;font-size:0.7rem;"><?= count($requests) ?></span><?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="wishlist.php">
                        <i class="fa-regular fa-heart nav-icon"></i> My Wishlist
                    </a>
                </li>
                <li>
                    <a href="?tab=profile" class="<?= $active_tab === 'profile' ? 'active' : '' ?>">
                        <i class="fas fa-user nav-icon"></i> My Profile
                    </a>
                </li>
                <li>
                    <a href="?tab=settings" class="<?= $active_tab === 'settings' ? 'active' : '' ?>">
                        <i class="fas fa-cog nav-icon"></i> Settings
                    </a>
                </li>
                <li style="margin-top:1.5rem; border-top:1px dashed var(--pink-100); padding-top:1rem;">
                    <a href="../auth/logout.php" style="color:#e55;">
                        <i class="fas fa-sign-out-alt nav-icon"></i> Logout
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <div class="dash-content">

            <!-- ========== ORDERS TAB ========== -->
            <?php if ($active_tab === 'orders'): ?>
            <div class="dash-card fade-up visible">
                <h3><i class="fas fa-box-open"></i> My Orders</h3>
                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📦</div>
                        <p>No orders yet. <a href="../products.php" class="text-gradient fw-semibold">Start shopping!</a></p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="dash-table" id="ordersTable">
                            <thead>
                                <tr>
                                    <th style="width:30px;"></th>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Items</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orders as $order):
                                    $items = $orderItems[$order['id']] ?? [];
                                ?>
                                <tr class="order-row" data-order="<?= $order['id'] ?>">
                                    <td style="text-align:center;">
                                        <i class="fas fa-chevron-down expand-icon" style="cursor:pointer;color:var(--text-light);transition:transform 0.2s;" onclick="toggleOrderItems(<?= $order['id'] ?>)"></i>
                                    </td>
                                    <td><strong>#ORD-<?= $order['id'] ?></strong></td>
                                    <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                    <td><strong>₹<?= number_format($order['total_amount'], 2) ?></strong></td>
                                    <td>
                                        <span class="badge-status badge-<?= $order['status'] ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                    <td><span class="badge" style="background:var(--pink-50);color:var(--primary);font-size:0.75rem;"><?= count($items) ?> item(s)</span></td>
                                </tr>
                                <tr class="order-items-row" id="items-<?= $order['id'] ?>" style="display:none;">
                                    <td colspan="6" style="padding:0;">
                                        <div style="padding:0.75rem 1.5rem; background:var(--cream); border-bottom:2px solid var(--pink-100);">
                                            <div class="row g-2">
                                                <?php if (empty($items)): ?>
                                                    <div class="col-12 text-center py-2" style="color:var(--text-light);font-size:0.85rem;">No items recorded for this order.</div>
                                                <?php else: ?>
                                                    <?php foreach ($items as $item):
                                                        $isReviewed = isset($reviewedItems[$order['id'] . '_' . $item['product_id']]);
                                                        $imgSrc = $item['image'];
                                                        if ($imgSrc && !preg_match('/^https?:\/\//i', $imgSrc)) $imgSrc = '../' . $imgSrc;
                                                    ?>
                                                    <div class="col-md-6">
                                                        <div style="display:flex;gap:0.75rem;align-items:center;background:white;border-radius:10px;padding:0.6rem 0.8rem;border:1px solid var(--pink-50);">
                                                            <div style="width:50px;height:50px;border-radius:8px;overflow:hidden;background:var(--pink-50);flex-shrink:0;">
                                                                <img src="<?= htmlspecialchars($imgSrc ?: '../assets/img/products/default.jpg') ?>" alt="" style="width:100%;height:100%;object-fit:cover;" onerror="this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;color:var(--pink-200);font-size:1.2rem;\'><i class=\'fa-solid fa-gift\'></i></div>'">
                                                            </div>
                                                            <div style="flex-grow:1;min-width:0;">
                                                                <div style="font-weight:600;font-size:0.82rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($item['name']) ?></div>
                                                                <div style="font-size:0.72rem;color:var(--text-light);">Qty: <?= $item['quantity'] ?> × ₹<?= number_format($item['price'], 2) ?></div>
                                                            </div>
                                                            <div style="flex-shrink:0;">
                                                                <?php if ($isReviewed): ?>
                                                                    <span style="font-size:0.7rem;color:#15803d;"><i class="fa-solid fa-check-circle me-1"></i>Reviewed</span>
                                                                <?php else: ?>
                                                                    <button class="btn btn-sm" style="background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;border:none;border-radius:8px;font-size:0.7rem;padding:0.3rem 0.6rem;white-space:nowrap;" onclick="openReview(<?= $item['product_id'] ?>, <?= $order['id'] ?>, '<?= htmlspecialchars(addslashes($item['name'])) ?>')">
                                                                        <i class="fa-solid fa-star me-1"></i>Review
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ========== REQUESTS TAB ========== -->
            <?php elseif ($active_tab === 'requests'): ?>
            <div class="dash-card fade-up visible">
                <h3><i class="fas fa-paint-brush"></i> Custom Requests</h3>
                <?php if (empty($requests)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">🎨</div>
                        <p>No custom requests yet. <a href="../custom-request.php" class="text-gradient fw-semibold">Create one!</a></p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Submitted</th>
                                    <th>Estimate</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($requests as $req): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($req['title']) ?></strong></td>
                                    <td><?= date('M d, Y', strtotime($req['created_at'])) ?></td>
                                    <td><?= $req['price_estimate'] ? '₹'.$req['price_estimate'] : '<em style="color:var(--text-light);">Pending</em>' ?></td>
                                    <td><span class="badge-status badge-<?= $req['status'] ?>"><?= ucfirst($req['status']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ========== PROFILE TAB ========== -->
            <?php elseif ($active_tab === 'profile'): ?>
            <div class="dash-card fade-up visible">
                <h3><i class="fas fa-user"></i> My Profile</h3>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div style="background:var(--cream); border-radius:0.75rem; padding:1rem 1.25rem;">
                            <div style="font-size:0.75rem; color:var(--text-light); text-transform:uppercase; letter-spacing:0.05em;">Full Name</div>
                            <div style="font-weight:700; margin-top:0.25rem;"><?= htmlspecialchars($user['name']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="background:var(--cream); border-radius:0.75rem; padding:1rem 1.25rem;">
                            <div style="font-size:0.75rem; color:var(--text-light); text-transform:uppercase; letter-spacing:0.05em;">Email</div>
                            <div style="font-weight:700; margin-top:0.25rem;"><?= htmlspecialchars($user['email']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="background:var(--cream); border-radius:0.75rem; padding:1rem 1.25rem;">
                            <div style="font-size:0.75rem; color:var(--text-light); text-transform:uppercase; letter-spacing:0.05em;">Phone</div>
                            <div style="font-weight:700; margin-top:0.25rem;"><?= $user['phone'] ? htmlspecialchars($user['phone']) : '<em style="color:var(--text-light);">Not set</em>' ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="background:var(--cream); border-radius:0.75rem; padding:1rem 1.25rem;">
                            <div style="font-size:0.75rem; color:var(--text-light); text-transform:uppercase; letter-spacing:0.05em;">Member Since</div>
                            <div style="font-weight:700; margin-top:0.25rem;"><?= date('M d, Y', strtotime($user['created_at'])) ?></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div style="background:var(--cream); border-radius:0.75rem; padding:1rem 1.25rem;">
                            <div style="font-size:0.75rem; color:var(--text-light); text-transform:uppercase; letter-spacing:0.05em;">Address</div>
                            <div style="font-weight:700; margin-top:0.25rem;"><?= $user['address'] ? htmlspecialchars($user['address']) : '<em style="color:var(--text-light);">Not set</em>' ?></div>
                        </div>
                    </div>
                    <div class="col-12 mt-2">
                        <a href="?tab=settings" class="btn btn-save">
                            <i class="fas fa-edit me-2"></i>Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- ========== SETTINGS TAB ========== -->
            <?php elseif ($active_tab === 'settings'): ?>
            <div class="dash-card fade-up visible">
                <h3><i class="fas fa-cog"></i> Settings</h3>

                <?php if ($settings_success): ?>
                    <div class="alert alert-success rounded-3 d-flex align-items-center gap-2 mb-4" style="border:none; background:#d1fae5; color:#065f46;">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($settings_success) ?>
                    </div>
                <?php endif; ?>
                <?php if ($settings_error): ?>
                    <div class="alert alert-danger rounded-3 d-flex align-items-center gap-2 mb-4" style="border:none; background:#fee2e2; color:#991b1b;">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($settings_error) ?>
                    </div>
                <?php endif; ?>

                <!-- Update Profile -->
                <div class="settings-section">
                    <h5><i class="fas fa-user-edit"></i> Update Profile Information</h5>
                    <form method="POST" action="settings.php">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span style="color:var(--primary);">*</span></label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span style="color:var(--primary);">*</span></label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+91 XXXXX XXXXX">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-save">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <hr style="border-color:var(--pink-100);">

                <!-- Change Password -->
                <div class="settings-section mb-0">
                    <h5><i class="fas fa-lock"></i> Change Password</h5>
                    <form method="POST" action="settings.php">
                        <input type="hidden" name="action" value="change_password">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Current Password</label>
                                <div class="input-group">
                                    <input type="password" name="current_password" id="currentPwd" class="form-control" placeholder="Enter current password" required>
                                    <button class="btn" type="button" onclick="togglePwd('currentPwd', this)" style="border:1.5px solid var(--pink-100); border-left:none; border-radius:0 0.6rem 0.6rem 0;">
                                        <i class="fas fa-eye" style="color:var(--text-light);"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <div class="input-group">
                                    <input type="password" name="new_password" id="newPwd" class="form-control" placeholder="Min. 6 characters" required>
                                    <button class="btn" type="button" onclick="togglePwd('newPwd', this)" style="border:1.5px solid var(--pink-100); border-left:none; border-radius:0 0.6rem 0.6rem 0;">
                                        <i class="fas fa-eye" style="color:var(--text-light);"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password</label>
                                <div class="input-group">
                                    <input type="password" name="confirm_password" id="confirmPwd" class="form-control" placeholder="Repeat new password" required>
                                    <button class="btn" type="button" onclick="togglePwd('confirmPwd', this)" style="border:1.5px solid var(--pink-100); border-left:none; border-radius:0 0.6rem 0.6rem 0;">
                                        <i class="fas fa-eye" style="color:var(--text-light);"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-save">
                                    <i class="fas fa-key me-2"></i>Change Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

        </div><!-- /dash-content -->
    </div><!-- /dashboard-wrap -->

    <!-- Footer -->
    <footer class="mt-4 pb-4">
        <div class="container text-center">
            <p class="mb-0 text-secondary small">&copy; 2026 CraftyGifts. All rights reserved. Handcrafted with <i class="fas fa-heart text-danger mx-1"></i></p>
        </div>
    </footer>

    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:20px;border:none;">
                <div class="modal-header" style="border-bottom:2px dashed var(--pink-100);">
                    <h5 class="modal-title fw-bold" style="font-family:var(--font-serif);">♥ Write a Review ♥</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p style="color:var(--text-light);font-size:0.9rem;" id="reviewProductName">Rate your purchase</p>

                    <!-- Star Rating -->
                    <div style="text-align:center;margin-bottom:1.5rem;">
                        <div class="star-rating" style="display:flex;gap:6px;justify-content:center;font-size:2.2rem;">
                            <i class="fa-regular fa-star" data-star="1" style="cursor:pointer;color:var(--pink-200);transition:all 0.2s;"></i>
                            <i class="fa-regular fa-star" data-star="2" style="cursor:pointer;color:var(--pink-200);transition:all 0.2s;"></i>
                            <i class="fa-regular fa-star" data-star="3" style="cursor:pointer;color:var(--pink-200);transition:all 0.2s;"></i>
                            <i class="fa-regular fa-star" data-star="4" style="cursor:pointer;color:var(--pink-200);transition:all 0.2s;"></i>
                            <i class="fa-regular fa-star" data-star="5" style="cursor:pointer;color:var(--pink-200);transition:all 0.2s;"></i>
                        </div>
                        <div style="font-size:0.8rem;color:var(--text-light);margin-top:0.3rem;" id="ratingLabel">Click a star to rate</div>
                    </div>

                    <input type="hidden" id="reviewProductId" value="">
                    <input type="hidden" id="reviewOrderId" value="">
                    <input type="hidden" id="reviewRating" value="0">

                    <div class="mb-3">
                        <label class="form-label">Your Review</label>
                        <textarea id="reviewComment" class="form-control" rows="4" placeholder="Tell us what you think about this product..." style="border-radius:12px;border-color:var(--pink-200);resize:none;"></textarea>
                    </div>

                    <button class="btn w-100 py-2" id="submitReviewBtn" style="background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;border:none;border-radius:12px;font-weight:600;" onclick="submitReview()">
                        <i class="fa-solid fa-paper-plane me-2"></i>Submit Review
                    </button>
                    <div id="reviewFeedback" style="margin-top:0.5rem;font-size:0.85rem;text-align:center;"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ── Toggle order items ──────────────────────────────────────────────
        function toggleOrderItems(id) {
            const row = document.getElementById('items-' + id);
            const icon = document.querySelector(`.order-row[data-order="${id}"] .expand-icon`);
            if (row.style.display === 'none') {
                row.style.display = 'table-row';
                icon.style.transform = 'rotate(180deg)';
            } else {
                row.style.display = 'none';
                icon.style.transform = 'rotate(0deg)';
            }
        }

        // ── Star rating ─────────────────────────────────────────────────────
        let selectedRating = 0;
        document.querySelectorAll('.star-rating i').forEach(star => {
            star.addEventListener('mouseenter', function() {
                const val = parseInt(this.dataset.star);
                highlightStars(val);
            });
            star.addEventListener('mouseleave', function() {
                highlightStars(selectedRating);
            });
            star.addEventListener('click', function() {
                selectedRating = parseInt(this.dataset.star);
                document.getElementById('reviewRating').value = selectedRating;
                highlightStars(selectedRating);
                const labels = ['', 'Terrible 😢', 'Bad 🙁', 'Okay 😐', 'Good 😊', 'Amazing 🥰'];
                document.getElementById('ratingLabel').textContent = labels[selectedRating];
            });
        });

        function highlightStars(count) {
            document.querySelectorAll('.star-rating i').forEach(s => {
                const val = parseInt(s.dataset.star);
                if (val <= count) {
                    s.className = 'fa-solid fa-star';
                    s.style.color = '#f59e0b';
                } else {
                    s.className = 'fa-regular fa-star';
                    s.style.color = 'var(--pink-200)';
                }
            });
        }

        // ── Open review modal ───────────────────────────────────────────────
        function openReview(productId, orderId, productName) {
            document.getElementById('reviewProductId').value = productId;
            document.getElementById('reviewOrderId').value = orderId;
            document.getElementById('reviewProductName').textContent = '♡ ' + productName;
            document.getElementById('reviewRating').value = 0;
            document.getElementById('reviewComment').value = '';
            document.getElementById('reviewFeedback').innerHTML = '';
            selectedRating = 0;
            highlightStars(0);
            document.getElementById('ratingLabel').textContent = 'Click a star to rate';
            const modal = new bootstrap.Modal(document.getElementById('reviewModal'));
            modal.show();
        }

        // ── Submit review ───────────────────────────────────────────────────
        async function submitReview() {
            const btn = document.getElementById('submitReviewBtn');
            const feedback = document.getElementById('reviewFeedback');
            const productId = document.getElementById('reviewProductId').value;
            const orderId = document.getElementById('reviewOrderId').value;
            const rating = document.getElementById('reviewRating').value;
            const comment = document.getElementById('reviewComment').value.trim();

            if (!rating || rating < 1) {
                feedback.innerHTML = '<span style="color:#b91c1c;">Please select a star rating.</span>';
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';

            try {
                const form = new FormData();
                form.append('product_id', productId);
                form.append('order_id', orderId);
                form.append('rating', rating);
                form.append('comment', comment);

                const res = await fetch('submit-review.php', { method: 'POST', body: form });
                const data = await res.json();

                if (data.success) {
                    feedback.innerHTML = '<span style="color:#15803d;"><i class="fa-solid fa-check-circle me-1"></i>' + data.message + '</span>';
                    setTimeout(() => location.reload(), 1500);
                } else {
                    feedback.innerHTML = '<span style="color:#b91c1c;">' + data.message + '</span>';
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i>Submit Review';
                }
            } catch(e) {
                feedback.innerHTML = '<span style="color:#b91c1c;">Connection error. Try again.</span>';
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i>Submit Review';
            }
        }

        // ── Toggle password visibility ──────────────────────────────────────
        function togglePwd(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon  = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
