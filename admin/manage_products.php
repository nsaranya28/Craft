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

$pageTitle = 'Manage Products';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="glass-card">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Products Catalogue</h4>
            <p class="text-muted small mb-0">Browse, edit, delete, and add new handcrafted gifts</p>
        </div>
        <a href="create_product.php" class="btn btn-premium"><i class="fa-solid fa-plus me-2"></i>Add Product</a>
    </div>

    <!-- Search Form -->
    <form method="GET" class="row g-2 mb-4 align-items-center">
        <div class="col-md-8">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name or description..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-premium-outline flex-grow-1">Search</button>
            <?php if (!empty($search)): ?>
                <a href="manage_products.php" class="btn btn-light"><i class="fa-solid fa-times"></i></a>
            <?php endif; ?>
        </div>
    </form>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
        <div class="alert alert-success"><i class="fa-solid fa-check-circle me-2"></i>Product deleted successfully!</div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th style="width: 80px;">Image</th>
                    <th>Product Details</th>
                    <th>Category</th>
                    <th>Base Price</th>
                    <th>Stock</th>
                    <th>Status Flags</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No products found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td>
                                <?php
                                $imgSrc = $p['image'];
                                // If it doesn't look like an absolute HTTP URL, prepend relative path helper
                                if (!preg_match('/^https?:\/\//i', $imgSrc) && !empty($imgSrc)) {
                                    $imgSrc = '../' . $imgSrc;
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($imgSrc ?: '../assets/img/products/default.jpg'); ?>" 
                                     alt="Product thumbnail" 
                                     class="rounded-3 shadow-sm"
                                     style="width: 60px; height: 60px; object-fit: cover;">
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($p['name']); ?></div>
                                <div class="text-muted small text-truncate" style="max-width: 280px;"><?php echo htmlspecialchars(strip_tags($p['description'])); ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($p['category_name'] ?? 'Uncategorized'); ?></span>
                            </td>
                            <td class="fw-semibold text-dark">
                                $<?php echo number_format($p['base_price'], 2); ?>
                            </td>
                            <td>
                                <?php if ($p['stock_quantity'] <= 0): ?>
                                    <span class="text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation me-1"></i> Out of Stock</span>
                                <?php elseif ($p['stock_quantity'] <= 5): ?>
                                    <span class="text-warning fw-bold"><?php echo $p['stock_quantity']; ?> left</span>
                                <?php else: ?>
                                    <span class="text-success"><?php echo $p['stock_quantity']; ?> in stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['is_featured']): ?>
                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-star me-1"></i> Featured</span>
                                <?php endif; ?>
                                <?php if ($p['is_new']): ?>
                                    <span class="badge bg-info text-white"><i class="fa-solid fa-fire me-1"></i> New</span>
                                <?php endif; ?>
                                <?php if (!$p['is_featured'] && !$p['is_new']): ?>
                                    <span class="text-muted small">Standard</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="edit_product.php?id=<?php echo $p['id']; ?>" class="btn btn-premium-outline btn-sm py-1 px-2 flex-grow-1 text-center">
                                        <i class="fa-regular fa-edit"></i> Edit
                                    </a>
                                    <a href="delete_product.php?id=<?php echo $p['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this product?');" 
                                       class="btn btn-outline-danger btn-sm py-1 px-2">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
