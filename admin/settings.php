<?php
$pageTitle = 'Settings';
include 'includes/header.php';
include 'includes/sidebar.php';

$success = ''; $error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    try {
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE admins SET name=?, email=?, password=? WHERE id=?")->execute([$name, $email, $hash, $_SESSION['admin_id']]);
        } else {
            $pdo->prepare("UPDATE admins SET name=?, email=? WHERE id=?")->execute([$name, $email, $_SESSION['admin_id']]);
        }
        $_SESSION['admin_email'] = $email;
        $success = "Profile updated successfully ♡";
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}

// Fetch admin data
$admin = $pdo->prepare("SELECT * FROM admins WHERE id=?")->execute([$_SESSION['admin_id']]);
$admin = $pdo->prepare("SELECT * FROM admins WHERE id=?");
$admin->execute([$_SESSION['admin_id']]);
$admin = $admin->fetch();
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif;">♡ Settings ♡</h2>
            <p style="color: var(--text-light); margin: 0;">Admin profile & website preferences</p>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success py-2"><?php echo htmlspecialchars($success); ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Profile Settings -->
        <div class="col-lg-6">
            <div class="glass-card">
                <h5 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif;">♥ Admin Profile ♥</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($admin['name'] ?? 'Admin'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">New Password <span class="text-muted fw-normal">(leave blank to keep current)</span></label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Role</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($admin['role'] ?? 'admin'); ?>" disabled>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-premium"><i class="fa-solid fa-save me-2"></i>Save Changes</button>
                </form>
            </div>
        </div>

        <!-- Website Info -->
        <div class="col-lg-6">
            <div class="glass-card">
                <h5 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif;">♡ Website Info ♡</h5>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Site Name</label>
                    <input type="text" class="form-control" value="CraftyGifts" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Database</label>
                    <input type="text" class="form-control" value="custom_craft_db" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">PHP Version</label>
                    <input type="text" class="form-control" value="<?php echo phpversion(); ?>" disabled>
                </div>
                <hr style="border-color: var(--pink-100);">
                <h6 class="fw-bold" style="font-family: 'Playfair Display', serif;">♥ Quick Stats ♥</h6>
                <?php
                $stats = [
                    'Categories' => $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
                    'Products' => $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
                    'Orders' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
                    'Users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
                    'Reviews' => $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn(),
                    'Messages' => $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn(),
                ];
                ?>
                <div class="row g-2 mt-2">
                    <?php foreach ($stats as $label => $count): ?>
                        <div class="col-4">
                            <div class="text-center p-2" style="background: var(--pink-50); border-radius: 10px;">
                                <div class="fw-bold" style="color: var(--primary);"><?php echo $count; ?></div>
                                <div style="color: var(--text-light); font-size: 0.65rem;"><?php echo $label; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Theme -->
            <div class="glass-card mt-4">
                <h5 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif;">♥ Theme ♥</h5>
                <p class="small" style="color: var(--text-light);">CraftyGifts is styled with a soft pink aesthetic. Custom theme options can be extended here.</p>
                <div class="d-flex gap-3">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--primary); border: 3px solid var(--pink-200);"></div>
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: #6c4dff; border: 3px solid transparent; opacity: 0.4;"></div>
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: #15803d; border: 3px solid transparent; opacity: 0.4;"></div>
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: #b45309; border: 3px solid transparent; opacity: 0.4;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
