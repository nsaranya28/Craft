<?php
session_start();
include '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = (int)($_POST['product_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    exit;
}

if ($action === 'add') {
    try {
        $pdo->prepare("INSERT IGNORE INTO wishlists (user_id, product_id) VALUES (?, ?)")->execute([$user_id, $product_id]);
        $count = $pdo->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ?")->execute([$user_id]);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ?");
        $stmt->execute([$user_id]);
        echo json_encode(['success' => true, 'action' => 'added', 'count' => (int)$stmt->fetchColumn()]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Could not add to wishlist.']);
    }
} elseif ($action === 'remove') {
    $pdo->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?")->execute([$user_id, $product_id]);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ?");
    $stmt->execute([$user_id]);
    echo json_encode(['success' => true, 'action' => 'removed', 'count' => (int)$stmt->fetchColumn()]);
} elseif ($action === 'check') {
    $stmt = $pdo->prepare("SELECT 1 FROM wishlists WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    echo json_encode(['success' => true, 'wishlisted' => (bool)$stmt->fetch()]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}
