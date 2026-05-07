<?php
require_once __DIR__ . '/includes/init.php';

checkCSRF();

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

if ($product_id > 0) {
    unset($_SESSION['cart'][$product_id]);
}

redirect('cart.php');