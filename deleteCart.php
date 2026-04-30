<?php
require_once __DIR__ . '/admin/includes/init.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

unset($_SESSION['cart'][$product_id]);

redirect('cart.php');
?>