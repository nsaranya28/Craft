<?php
session_start();
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();
require_once __DIR__ . '/../includes/db.php';

$error = '';
$success = '';

// ── Handle Add / Edit ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $id          = (int) ($_POST['id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $error = 'Category name is required.';
    } else {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $description, $id]);
            $success = 'Category updated successfully.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            $success = 'Category added successfully.';
        }
    }
}

// ── Handle Delete ─────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
    $success = 'Category deleted successfully.';
}

// ── Fetch all categories ──────────────────────────────────────────────────
$categories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count FROM categories ORDER BY name")->fetchAll();

// ── Fetch category for editing ────────────────────────────────────────────
$editCat = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([(int) $_GET['edit']]);
    $editCat = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories | Admin</title>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    <div class="container-fluid px-4 py-4">
        <div class="row justify-content-center">
            <?php include __DIR__ . '/includes/sidebar.php'; ?>

            <div class="col-lg-9 col-md-8">
                <h2 class="fw-bold mb-4">Manage Categories</h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger rounded-4"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success rounded-4"><?php echo $success; ?></div>
                <?php endif; ?>

                <!-- Add / Edit Form -->
                <div class="card rounded-4 shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3"><?php echo $editCat ? 'Edit Category' : 'Add New Category'; ?></h5>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?php echo $editCat['id'] ?? '0'; ?>">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <input type="text" name="name" class="form-control rounded-3" placeholder="Category name" value="<?php echo htmlspecialchars($editCat['name'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="description" class="form-control rounded-3" placeholder="Short description (optional)" value="<?php echo htmlspecialchars($editCat['description'] ?? ''); ?>">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" name="save" class="btn btn-primary w-100 rounded-3">
                                        <?php echo $editCat ? 'Update' : 'Add'; ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Categories Table -->
                <div class="card rounded-4 shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">ID</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Products</th>
                                        <th class="pe-4 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $cat): ?>
                                        <tr>
                                            <td class="ps-4 fw-semibold"><?php echo $cat['id']; ?></td>
                                            <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                            <td class="text-secondary"><?php echo htmlspecialchars($cat['description'] ?? ''); ?></td>
                                            <td><span class="badge bg-light text-dark border"><?php echo $cat['product_count']; ?></span></td>
                                            <td class="pe-4 text-end">
                                                <a href="?edit=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-secondary rounded-3 me-1"><i class="fa-solid fa-pen"></i></a>
                                                <a href="?delete=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-danger rounded-3" onclick="return confirm('Delete this category? Products will become uncategorized.');"><i class="fa-solid fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($categories)): ?>
                                        <tr><td colspan="5" class="text-center text-secondary py-4">No categories yet. Add one above.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
