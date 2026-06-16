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
$order_id = (int)($_POST['order_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if (!$product_id || !$order_id || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

// Verify the user actually owns this order and product
$stmt = $pdo->prepare("SELECT 1 FROM orders o JOIN order_items oi ON o.id = oi.order_id WHERE o.id = ? AND o.user_id = ? AND oi.product_id = ?");
$stmt->execute([$order_id, $user_id, $product_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Order not found.']);
    exit;
}

// Check if already reviewed
$stmt = $pdo->prepare("SELECT 1 FROM reviews WHERE user_id = ? AND product_id = ? AND order_id = ?");
$stmt->execute([$user_id, $product_id, $order_id]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'You already reviewed this product.']);
    exit;
}

// Ensure is_approved and order_id columns exist
foreach ([
    "ALTER TABLE reviews ADD COLUMN is_approved TINYINT(1) DEFAULT 0 AFTER comment",
    "ALTER TABLE reviews ADD COLUMN order_id INT DEFAULT NULL AFTER product_id"
] as $sql) {
    try { $pdo->exec($sql); } catch (PDOException $e) {}
}

try {
    $stmt = $pdo->prepare("INSERT INTO reviews (user_id, product_id, order_id, rating, comment, is_approved) VALUES (?, ?, ?, ?, ?, 0)");
    $stmt->execute([$user_id, $product_id, $order_id, $rating, $comment]);
    echo json_encode(['success' => true, 'message' => 'Review submitted! It will appear after admin approval. ♡']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to submit review.']);
}
