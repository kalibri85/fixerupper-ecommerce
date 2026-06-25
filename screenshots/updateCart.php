<?php
require_once __DIR__ . '/includes/init.php';

checkCSRF();

$product_id    = (int)$_POST['product_id'];
$qty           = (int)$_POST['qty'];
$variation_key = $_POST['variation_key'] ?? 'default';

if ($product_id <= 0) {
    redirect('cart.php');
}

// Check stock
$stmt = $conn->prepare("SELECT qty FROM products WHERE id = ? AND status = 1");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    redirect('cart.php');
}

if ($qty <= 0) {
    unset($_SESSION['cart'][$product_id][$variation_key]);

    // If no more variations left, remove product entirely
    if (empty($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
} else {
    $qty = min($qty, $product['qty']);
    $_SESSION['cart'][$product_id][$variation_key]['qty'] = $qty;
}

redirect('cart.php');
