<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit;
}

checkCSRF();

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;

if ($product_id <= 0 || $qty <= 0) {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $conn->prepare("
    SELECT id, qty
    FROM products
    WHERE id = ? AND status = 1
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo json_encode(['success' => false]);
    exit;
}

if ($qty > $product['qty']) {
    $qty = $product['qty'];
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id] += $qty;

    if ($_SESSION['cart'][$product_id] > $product['qty']) {
        $_SESSION['cart'][$product_id] = $product['qty'];
    }
} else {
    $_SESSION['cart'][$product_id] = $qty;
}

$count = array_sum($_SESSION['cart']);

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'count' => $count
]);
exit;