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
if ($id > 0) {
    try {
        // Safe delete: database handles ON DELETE SET NULL for category_id,
        // and we cascade or set null on orders. Since order_items uses SET NULL on product_id deletion,
        // we can safely execute DELETE FROM products.
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: manage_products.php?msg=deleted');
        exit;
    } catch (PDOException $e) {
        die("Error deleting product. It might be referenced by active orders. Error details: " . $e->getMessage());
    }
} else {
    header('Location: manage_products.php');
    exit;
}
?>
