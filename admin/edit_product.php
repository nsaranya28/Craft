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

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: manage_products.php');
    exit;
}

// Fetch current product
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) {
        header('Location: manage_products.php');
        exit;
    }
} catch (PDOException $e) {
    die("Error loading product: " . $e->getMessage());
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

    $image_url_or_path = $product['image']; // Default to current

    // Handle image upload if a new one is selected
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = '../assets/img/products/';
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $filename = time() . '_' . basename($_FILES['image']['name']);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_url_or_path = 'assets/img/products/' . $filename;
        } else {
            $error = 'Failed to move uploaded file.';
        }
    } elseif (!empty($_POST['image_url'])) {
        $image_url_or_path = trim($_POST['image_url']);
    }

    if (empty($error)) {
        if (empty($name) || empty($base_price)) {
            $error = 'Product name and base price are required.';
        } else {
            try {
                $updateStmt = $pdo->prepare(
                    "UPDATE products SET category_id = ?, name = ?, description = ?, base_price = ?, image = ?, stock_quantity = ?, is_featured = ?, is_new = ? WHERE id = ?"
                );
                $updateStmt->execute([
                    $category_id ?: null,
                    $name,
                    $description,
                    $base_price,
                    $image_url_or_path,
                    $stock_quantity,
                    $is_featured,
                    $is_new,
                    $id
                ]);
                $success = 'Product updated successfully.';
                
                // Refresh product data
                $stmt->execute([$id]);
                $product = $stmt->fetch();
            } catch (PDOException $e) {
                $error = 'Failed to update product: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Edit Product';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="glass-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Edit Product</h4>
            <p class="text-muted small mb-0">Modify information for product #<?php echo $product['id']; ?></p>
        </div>
        <a href="manage_products.php" class="btn btn-premium-outline btn-sm"><i class="fa-solid fa-arrow-left me-2"></i>Back to Products</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            <div><?php echo $success; ?></div>
        </div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <div><?php echo $error; ?></div>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-bold">Product Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        </div>
        
        <div class="col-md-6">
            <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
            <select name="category_id" class="form-select" required>
                <option value="">Select Category</option>
                <?php
                $catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
                while ($cat = $catStmt->fetch()):
                    $selected = ($cat['id'] == $product['category_id']) ? 'selected' : '';
                ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-12">
            <label class="form-label fw-bold">Description</label>
            <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold">Base Price ($) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" name="base_price" class="form-control" value="<?php echo htmlspecialchars($product['base_price']); ?>" required>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold">Stock Quantity</label>
            <input type="number" name="stock_quantity" class="form-control" value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" required>
        </div>

        <div class="col-md-4 d-flex align-items-center mt-4 pt-2">
            <div class="form-check me-4">
                <input class="form-check-input" type="checkbox" name="is_featured" id="featured" <?php echo $product['is_featured'] ? 'checked' : ''; ?>>
                <label class="form-check-label fw-semibold" for="featured">Featured Product</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_new" id="new" <?php echo $product['is_new'] ? 'checked' : ''; ?>>
                <label class="form-check-label fw-semibold" for="new">New Arrival</label>
            </div>
        </div>

        <div class="col-md-3">
            <label class="form-label fw-bold d-block">Current Image</label>
            <?php
            $imgSrc = $product['image'];
            if (!preg_match('/^https?:\/\//i', $imgSrc) && !empty($imgSrc)) {
                $imgSrc = '../' . $imgSrc;
            }
            ?>
            <img src="<?php echo htmlspecialchars($imgSrc ?: '../assets/img/products/default.jpg'); ?>" 
                 alt="Current product image" 
                 class="rounded-3 border shadow-sm img-thumbnail"
                 style="max-width: 120px; height: 120px; object-fit: cover;">
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold">Replace Product Image</label>
            <input type="file" name="image" class="form-control" accept="image/*">
            <div class="form-text">Leave blank to keep current image.</div>
        </div>

        <div class="col-md-5">
            <label class="form-label fw-bold">Or Change Image URL</label>
            <input type="url" name="image_url" class="form-control" placeholder="https://images.unsplash.com/..." value="<?php echo htmlspecialchars($product['image']); ?>">
            <div class="form-text">Will be overridden if a new file is uploaded.</div>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2 mt-4">
            <a href="manage_products.php" class="btn btn-premium-outline">Cancel</a>
            <button type="submit" class="btn btn-premium"><i class="fa-solid fa-save me-2"></i>Save Changes</button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
