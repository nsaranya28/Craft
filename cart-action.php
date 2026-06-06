<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $item = [
        'product_id' => $_POST['product_id'],
        'size' => $_POST['size'] ?? 'Standard',
        'color' => $_POST['color'] ?? 'Default',
        'custom_text' => $_POST['custom_text'] ?? '',
        'quantity' => $_POST['quantity'] ?? 1
    ];

    // Optional image handling in a real app would save the path here
    
    $_SESSION['cart'][] = $item;
    header("Location: cart.php");
    exit;
}
?>
