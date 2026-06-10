<?php
session_start();
include '../includes/db.php';

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

// Get Orders
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

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
                </ul>
                <div class="d-flex gap-2">
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
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Shipping</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orders as $order): ?>
                                <tr>
                                    <td><strong>#ORD-<?= $order['id'] ?></strong></td>
                                    <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                    <td><strong>₹<?= number_format($order['total_amount'], 2) ?></strong></td>
                                    <td>
                                        <span class="badge-status badge-<?= $order['status'] ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                    <td style="font-size:0.82rem; color:var(--text-light); max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        <?= htmlspecialchars($order['shipping_address']) ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
