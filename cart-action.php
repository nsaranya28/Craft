<?php
session_start();
include 'includes/db.php';
include 'includes/cart-helper.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// ── ADD TO CART ──
if ($action === 'add') {
    $pid = (int)($_POST['product_id'] ?? 0);
    $qty = max(1, (int)($_POST['quantity'] ?? 1));
    $size = $_POST['size'] ?? 'Standard';
    $color = $_POST['color'] ?? 'Default';
    $custom_text = $_POST['custom_text'] ?? '';

    if (!$pid) {
        echo json_encode(['success' => false, 'message' => 'Invalid product.']);
        exit;
    }

    // Check stock
    $stmt = $pdo->prepare("SELECT stock_quantity, name FROM products WHERE id = ?");
    $stmt->execute([$pid]);
    $prod = $stmt->fetch();
    if (!$prod) {
        echo json_encode(['success' => false, 'message' => 'Product not found.']);
        exit;
    }

    // Add to session cart
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    // If already in cart, increment quantity
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ((int)$item['product_id'] === $pid && $item['size'] === $size && $item['color'] === $color) {
            $item['quantity'] = min((int)$item['quantity'] + $qty, $prod['stock_quantity']);
            $found = true;
            break;
        }
    }
    unset($item);
    if (!$found) {
        $_SESSION['cart'][] = [
            'product_id'  => $pid,
            'quantity'    => min($qty, $prod['stock_quantity']),
            'size'        => $size,
            'color'       => $color,
            'custom_text' => $custom_text,
        ];
    }

    echo json_encode([
        'success' => true,
        'action'  => 'added',
        'count'   => getCartCount(),
        'message' => $prod['name'] . ' added to cart! ♡'
    ]);
    exit;
}

// ── UPDATE QUANTITY ──
if ($action === 'update') {
    $pid = (int)($_POST['product_id'] ?? 0);
    $qty = max(1, (int)($_POST['quantity'] ?? 1));

    $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
    $stmt->execute([$pid]);
    $stock = (int)$stmt->fetchColumn();
    $qty = min($qty, $stock);

    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as &$item) {
            if ((int)$item['product_id'] === $pid) {
                $item['quantity'] = $qty;
                break;
            }
        }
        unset($item);
    }

    echo json_encode([
        'success'  => true,
        'count'    => getCartCount(),
        'subtotal' => getCartTotal(),
        'total'    => getCartTotal(),
        'shipping' => getShipping(),
        'grand'    => getCartTotal() + getShipping(),
    ]);
    exit;
}

// ── REMOVE ──
if ($action === 'remove') {
    $pid = (int)($_POST['product_id'] ?? 0);
    if (isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], fn($i) => (int)$i['product_id'] !== $pid));
    }
    echo json_encode([
        'success'  => true,
        'count'    => getCartCount(),
        'subtotal' => getCartTotal(),
        'shipping' => getShipping(),
        'grand'    => getCartTotal() + getShipping(),
    ]);
    exit;
}

// ── CLEAR ──
if ($action === 'clear') {
    $_SESSION['cart'] = [];
    echo json_encode(['success' => true, 'count' => 0]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
