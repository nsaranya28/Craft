<?php
session_start();
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();
require_once __DIR__ . '/../includes/db.php';

$error = '';
$success = '';

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
            $success = "Category updated.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            $success = "Category added.";
        }
    }
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
    header('Location: manage_categories.php');
    exit;
}

$editCat = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([(int) $_GET['edit']]);
    $editCat = $stmt->fetch();
}

$categories = $pdo->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.name")->fetchAll();

$pageTitle = 'Categories';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif;">♡ Categories ♡</h2>
            <p style="color: var(--text-light); margin: 0;">Organize your product collections</p>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2" style="border-radius: 14px; border: none;"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success py-2" style="border-radius: 14px; border: none;"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Add / Edit Form -->
    <div class="glass-card mb-4">
        <h5 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif;"><?php echo $editCat ? '♥ Edit Category ♥' : '♥ Add New Category ♥'; ?></h5>
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $editCat['id'] ?? '0'; ?>">
            <div class="row g-2">
                <div class="col-md-5">
                    <input type="text" name="name" class="form-control" placeholder="Category name" value="<?php echo htmlspecialchars($editCat['name'] ?? ''); ?>" required>
                </div>
                <div class="col-md-5">
                    <input type="text" name="description" class="form-control" placeholder="Short description (optional)" value="<?php echo htmlspecialchars($editCat['description'] ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" name="save" class="btn btn-premium w-100"><?php echo $editCat ? 'Update' : 'Add'; ?></button>
                </div>
            </div>
            <?php if ($editCat): ?>
                <a href="manage_categories.php" class="btn btn-premium-outline btn-sm mt-2">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Categories Table -->
    <div class="glass-card">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Description</th><th>Products</th><th style="width: 120px;">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td class="fw-semibold">#<?php echo $cat['id']; ?></td>
                            <td class="fw-semibold"><?php echo htmlspecialchars($cat['name']); ?></td>
                            <td style="color: var(--text-light); font-size: 0.85rem;"><?php echo htmlspecialchars($cat['description'] ?? '—'); ?></td>
                            <td><span class="badge" style="background: var(--pink-50); color: var(--primary);"><?php echo $cat['product_count']; ?></span></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="?edit=<?php echo $cat['id']; ?>" class="btn btn-premium-outline btn-sm py-1 px-2"><i class="fa-solid fa-pen"></i></a>
                                    <a href="?delete=<?php echo $cat['id']; ?>" class="btn btn-sm py-1 px-2" style="background: #fee2e2; color: #b91c1c; border: none; border-radius: 10px;" onclick="return confirm('Delete this category?');"><i class="fa-solid fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($categories)): ?>
                        <tr><td colspan="5" class="text-center py-4" style="color: var(--text-light);">No categories yet ♡</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
