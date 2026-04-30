<?php
require_once __DIR__ . '/admin/includes/init.php';

checkCSRF();

$product_id = (int)$_POST['product_id'];
$qty = (int)$_POST['qty'];

if ($qty <= 0) {
    unset($_SESSION['cart'][$product_id]);
} else {
    $_SESSION['cart'][$product_id] = $qty;
}

redirect('cart.php');

?>