<?php
/**
 * Shared Cart Helper Functions
 */

function getCartCount() {
    return isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
}

function getCartItems() {
    global $pdo;
    $items = $_SESSION['cart'] ?? [];
    if (empty($items)) return [];
    $ids = array_column($items, 'product_id');
    $ids = array_unique($ids);
    if (empty($ids)) return [];
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id, name, image, base_price, stock_quantity FROM products WHERE id IN ($ph)");
    $stmt->execute(array_values($ids));
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $prodMap = [];
    foreach ($products as $p) $prodMap[$p['id']] = $p;

    $result = [];
    foreach ($items as $i) {
        $pid = $i['product_id'];
        $p = $prodMap[$pid] ?? null;
        if (!$p) continue;
        $qty = (int)($i['quantity'] ?? 1);
        $result[] = [
            'product_id'   => $pid,
            'name'         => $p['name'],
            'image'        => $p['image'],
            'price'        => (float)$p['base_price'],
            'stock'        => (int)$p['stock_quantity'],
            'quantity'     => $qty,
            'subtotal'     => (float)$p['base_price'] * $qty,
            'size'         => $i['size'] ?? 'Standard',
            'color'        => $i['color'] ?? 'Default',
            'custom_text'  => $i['custom_text'] ?? '',
        ];
    }
    return $result;
}

function getCartTotal() {
    $items = getCartItems();
    return array_sum(array_column($items, 'subtotal'));
}

function getWishlistIds($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT product_id FROM wishlists WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function isInWishlist($product_id, $user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT 1 FROM wishlists WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    return (bool)$stmt->fetch();
}

function imgSrc($path) {
    if (!$path) return 'assets/img/products/default.jpg';
    if (preg_match('/^https?:\/\//i', $path)) return $path;
    return '../' . ltrim($path, '/');
}

function getShipping() {
    $total = getCartTotal();
    return $total >= 500 ? 0 : 49;
}

// Recently viewed
function trackRecentView($user_id, $product_id) {
    global $pdo;
    $pdo->prepare("INSERT INTO recently_viewed (user_id, product_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE viewed_at = NOW()")
        ->execute([$user_id, $product_id]);
    // Keep only last 10
    $pdo->prepare("DELETE rv FROM recently_viewed rv JOIN (SELECT id FROM recently_viewed WHERE user_id = ? ORDER BY viewed_at DESC LIMIT 10, 999) d ON rv.id = d.id")->execute([$user_id]);
}

function getRecentViews($user_id, $limit = 6) {
    global $pdo;
    $limit = (int)$limit;
    $stmt = $pdo->prepare("
        SELECT p.id, p.name, p.image, p.base_price, p.stock_quantity
        FROM recently_viewed rv
        JOIN products p ON rv.product_id = p.id
        WHERE rv.user_id = ?
        ORDER BY rv.viewed_at DESC
        LIMIT $limit
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
