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

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $base_price = floatval($_POST['base_price'] ?? 0);
    $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;

    // Handle image upload
    $image_url_or_path = '';
    
    // Check if user uploaded a file
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = '../assets/img/products/';
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $filename = time() . '_' . basename($_FILES['image']['name']);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            // Store path relative to the root index.php (e.g. assets/img/products/123_test.png)
            $image_url_or_path = 'assets/img/products/' . $filename;
        } else {
            $error = 'Failed to move uploaded file.';
        }
    } else {
        // Fallback or external URL if provided
        $image_url_or_path = trim($_POST['image_url'] ?? '');
    }

    if (empty($error)) {
        if (empty($name) || empty($base_price)) {
            $error = 'Product name and base price are required.';
        } else {
            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO products (category_id, name, description, base_price, image, gallery_images, stock_quantity, is_featured, is_new) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $gallery_json = json_encode([]);
                $stmt->execute([
                    $category_id ?: null,
                    $name,
                    $description,
                    $base_price,
                    $image_url_or_path ?: 'https://images.unsplash.com/photo-1514228742587-6b1558fcca3d?auto=format&fit=crop&q=80&w=500',
                    $gallery_json,
                    $stock_quantity,
                    $is_featured,
                    $is_new
                ]);
                $success = 'Product created successfully.';
            } catch (PDOException $e) {
                $error = 'Failed to create product: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Add Product';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif;">♥ Add New Product ♥</h2>
            <p style="color: var(--text-light); margin: 0;">List a new handcrafted gift</p>
        </div>
        <a href="manage_products.php" class="btn btn-premium-outline btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>Back</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success py-2" style="border-radius: 14px; border: none;"><?php echo $success; ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger py-2" style="border-radius: 14px; border: none;"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="glass-card">
        <form method="POST" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold small">Product Name <span style="color: var(--primary);">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Handmade Ceramic Coffee Mug" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold small">Category <span style="color: var(--primary);">*</span></label>
                <select name="category_id" class="form-select" required>
                    <option value="">Select Category</option>
                    <?php
                    $catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
                    while ($cat = $catStmt->fetch()):
                    ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold small">Description</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Detail the craftsmanship, size, material, customization options..."></textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold small">Base Price (₹) <span style="color: var(--primary);">*</span></label>
                <input type="number" step="0.01" name="base_price" class="form-control" placeholder="499" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold small">Stock Quantity</label>
                <input type="number" name="stock_quantity" class="form-control" placeholder="10" value="0">
            </div>
            <div class="col-md-4 d-flex align-items-end gap-3 pb-1">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_featured" id="featured" style="border-color: var(--primary);">
                    <label class="form-check-label small fw-semibold" for="featured">Featured</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_new" id="new" checked style="border-color: var(--primary);">
                    <label class="form-check-label small fw-semibold" for="new">New Arrival</label>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold small">Upload Image</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold small">Or Image URL</label>
                <input type="url" name="image_url" class="form-control" placeholder="https://images.unsplash.com/...">
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                <button type="reset" class="btn btn-premium-outline btn-sm">Reset</button>
                <button type="submit" class="btn btn-premium btn-sm"><i class="fa-solid fa-save me-2"></i>Create Product</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
