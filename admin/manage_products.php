<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Enforce admin login
if (!isAdminLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

// Handle search query
$search = trim($_GET['search'] ?? '');

try {
    if (!empty($search)) {
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.name LIKE ? OR p.description LIKE ? ORDER BY p.created_at DESC");
        $stmt->execute(["%$search%", "%$search%"]);
        $products = $stmt->fetchAll();
    } else {
        $products = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC")->fetchAll();
    }
} catch (PDOException $e) {
    $error = "Failed to load products: " . $e->getMessage();
    $products = [];
}

$pageTitle = 'Products';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif;">♡ Products ♡</h2>
            <p style="color: var(--text-light); margin: 0;">Browse, edit, and manage your handcrafted gifts</p>
        </div>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>" style="border-radius: 12px; min-width: 200px;">
                <button type="submit" class="btn btn-premium btn-sm"><i class="fa-solid fa-search"></i></button>
                <?php if (!empty($search)): ?>
                    <a href="manage_products.php" class="btn btn-premium-outline btn-sm"><i class="fa-solid fa-times"></i></a>
                <?php endif; ?>
            </form>
            <a href="create_product.php" class="btn btn-premium btn-sm"><i class="fa-solid fa-plus me-1"></i>Add Product</a>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger py-2" style="border-radius: 14px; border: none;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
        <div class="alert alert-success py-2" style="border-radius: 14px; border: none;">♥ Product deleted successfully!</div>
    <?php endif; ?>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr><th style="width: 70px;">Image</th><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Flags</th><th style="width: 140px;">Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="7" class="text-center py-4" style="color: var(--text-light);">No products yet ♡</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td>
                                    <?php
                                    $imgSrc = $p['image'];
                                    if (!preg_match('/^https?:\/\//i', $imgSrc) && !empty($imgSrc)) $imgSrc = '../' . $imgSrc;
                                    ?>
                                    <img src="<?php echo htmlspecialchars($imgSrc ?: '../assets/img/products/default.jpg'); ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 12px;">
                                </td>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($p['name']); ?></div>
                                    <div style="color: var(--text-light); font-size: 0.75rem; max-width: 220px;" class="text-truncate"><?php echo htmlspecialchars(strip_tags($p['description'])); ?></div>
                                </td>
                                <td><span class="badge" style="background: var(--pink-50); color: var(--primary);"><?php echo htmlspecialchars($p['category_name'] ?? 'Uncategorized'); ?></span></td>
                                <td class="fw-semibold" style="color: var(--primary);">₹<?php echo number_format($p['base_price'], 2); ?></td>
                                <td>
                                    <?php if ($p['stock_quantity'] <= 0): ?>
                                        <span style="color: #b91c1c; font-size: 0.8rem;">Out of Stock</span>
                                    <?php elseif ($p['stock_quantity'] <= 5): ?>
                                        <span style="color: #d97706; font-size: 0.8rem;"><?php echo $p['stock_quantity']; ?> left</span>
                                    <?php else: ?>
                                        <span style="color: #15803d; font-size: 0.8rem;"><?php echo $p['stock_quantity']; ?> in stock</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($p['is_featured']): ?><span class="badge-status status-processing" style="font-size: 0.65rem;">Featured</span><?php endif; ?>
                                    <?php if ($p['is_new']): ?><span class="badge-status status-ordered" style="font-size: 0.65rem;">New</span><?php endif; ?>
                                    <?php if (!$p['is_featured'] && !$p['is_new']): ?><span style="color: var(--text-light); font-size: 0.7rem;">—</span><?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="edit_product.php?id=<?php echo $p['id']; ?>" class="btn btn-premium-outline btn-sm py-1 px-2"><i class="fa-regular fa-edit"></i></a>
                                        <a href="delete_product.php?id=<?php echo $p['id']; ?>" onclick="return confirm('Delete this product?');" class="btn btn-sm py-1 px-2" style="background: #fee2e2; color: #b91c1c; border: none; border-radius: 10px;"><i class="fa-regular fa-trash-can"></i></a>
                                    </div>
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
