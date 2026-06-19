<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit;
}

checkCSRF();

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$qty        = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
$variations = isset($_POST['variation']) && is_array($_POST['variation']) 
              ? $_POST['variation'] 
              : [];

if ($product_id <= 0 || $qty <= 0) {
    echo json_encode(['success' => false]);
    exit;
}

// Get base product
$stmt = $conn->prepare("SELECT id, price, qty FROM products WHERE id = ? AND status = 1");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    echo json_encode(['success' => false]);
    exit;
}

// Resolve price and variation label
$final_price     = (float)$product['price'];
$variation_label = '';

if (!empty($variations)) {
    $parts = [];

    foreach ($variations as $attr => $value_id) {
        $value_id = (int)$value_id;

        // Get variation price override and label
        $stmt = $conn->prepare("
            SELECT pv.priceOverride, a.name AS attr_name, av.value AS attr_value
            FROM product_variation pv
            JOIN attributes a  ON pv.attributeID = a.id
            JOIN attribute_values av ON pv.valueID = av.id
            WHERE pv.productID = ? AND pv.valueID = ?
        ");
        $stmt->bind_param("ii", $product_id, $value_id);
        $stmt->execute();
        $var = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($var) {
            // Use price override only if it is set
            if ($var['priceOverride'] !== null) {
                $final_price = (float)$var['priceOverride'];
            }
            $parts[] = htmlspecialchars($var['attr_name']) . ': ' . htmlspecialchars($var['attr_value']);
        }
    }

    $variation_label = implode(', ', $parts); 
}

// Build a unique cart key: product_id + variation
$variation_key = !empty($variations) ? md5(json_encode($variations)) : 'default';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (!isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id] = [];
}

$stock = $product['qty'];

if (isset($_SESSION['cart'][$product_id][$variation_key])) {
    // Item already in cart — increase qty
    $new_qty = $_SESSION['cart'][$product_id][$variation_key]['qty'] + $qty;
    $_SESSION['cart'][$product_id][$variation_key]['qty'] = min($new_qty, $stock);
} else {
    // New item
    $_SESSION['cart'][$product_id][$variation_key] = [
        'qty'             => min($qty, $stock),
        'price'           => $final_price,
        'variation_label' => $variation_label,
        'variation_ids'   => $variations,
    ];
}

$count = 0;
foreach ($_SESSION['cart'] as $variants) {
    foreach ($variants as $item) {
        $count += $item['qty'];
    }
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'count' => $count]);
exit;