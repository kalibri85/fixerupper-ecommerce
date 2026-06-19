<?php
require_once __DIR__ . '/includes/init.php';

checkCSRF();

$product_id    = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$variation_key = $_POST['variation_key'] ?? 'default';

if ($product_id > 0) {
    unset($_SESSION['cart'][$product_id][$variation_key]);

    // If no more variations left, remove product entirely
    if (empty($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

redirect('cart.php');