<?php
session_start();
include '../includes/db.php';

// Ensure admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('Access denied. Admins only.');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'] ?? null;
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $base_price = $_POST['base_price'] ?? 0;
    $stock_quantity = $_POST['stock_quantity'] ?? 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;

    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = '../../assets/img/products/';
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_path = $target_dir . time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
    }

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO products (category_id, name, description, base_price, image, gallery_images, stock_quantity, is_featured, is_new) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $gallery_json = json_encode([]); // Empty gallery for now
        $stmt->execute([
            $category_id,
            $name,
            $description,
            $base_price,
            $image_path,
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Product | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="glass">
    <header class="py-3">
        <nav class="container d-flex justify-content-between align-items-center">
            <a href="dashboard.php" class="logo">CraftyGifts <span style="font-size:0.8rem; opacity:0.7;">Admin</span></a>
            <a href="../auth/logout.php" class="btn btn-outline">Logout</a>
        </nav>
    </header>
    <main class="container my-5">
        <h2 class="mb-4 text-primary">Create New Product</h2>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Category</label>
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
            <div class="col-md-6">
                <label class="form-label">Product Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4" required></textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Base Price ($)</label>
                <input type="number" step="0.01" name="base_price" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Stock Quantity</label>
                <input type="number" name="stock_quantity" class="form-control" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_featured" id="featured">
                    <label class="form-check-label" for="featured">Featured</label>
                </div>
                <div class="form-check ms-3">
                    <input class="form-check-input" type="checkbox" name="is_new" id="new">
                    <label class="form-check-label" for="new">New Arrival</label>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">Product Image</label>
                <input type="file" name="image" class="form-control" accept="image/*" required>
            </div>
            <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Create Product</button>
            </div>
        </form>
        <hr class="my-5">
        <a href="dashboard.php" class="text-decoration-none text-primary">← Back to Dashboard</a>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
