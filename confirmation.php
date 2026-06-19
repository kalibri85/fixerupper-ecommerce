<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('checkout.php');
}

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    redirect('cart.php');
}

requireCustomer();
checkCSRF();

// Validate delivery method
$deliveryMethodID = (int)($_POST['deliveryMethodID'] ?? 0);
$del_stmt = $conn->prepare("SELECT id, title, price FROM delivery_method WHERE id = ? AND active = 1");
$del_stmt->bind_param("i", $deliveryMethodID);
$del_stmt->execute();
$delivery_method = $del_stmt->get_result()->fetch_assoc();
if (!$delivery_method) redirect('checkout.php');

// Validate payment method
$paymentMethodID = (int)($_POST['paymentMethodID'] ?? 0);
$pay_stmt = $conn->prepare("SELECT id, title FROM payment_methods WHERE id = ?");
$pay_stmt->bind_param("i", $paymentMethodID);
$pay_stmt->execute();
$payment_method = $pay_stmt->get_result()->fetch_assoc();
if (!$payment_method) redirect('checkout.php');

// Resolve delivery address
$address_choice = $_POST['address_choice'] ?? 'billing';

if ($address_choice === 'new') {
    $ship_address  = trim($_POST['new_address']  ?? '');
    $ship_city     = trim($_POST['new_city']     ?? '');
    $ship_postcode = trim($_POST['new_postcode'] ?? '');
    $ship_country  = trim($_POST['new_country']  ?? 'United Kingdom');
    $ship_phone    = trim($_POST['new_phone']    ?? '');
    $ship_fullname = trim($_POST['new_fullname'] ?? $_SESSION['customer_name']);

    if (empty($ship_address) || empty($ship_city) || empty($ship_postcode)) {
        redirect('checkout.php');
    }

    // Save new shipping address (billing = 0)
    $new_addr = $conn->prepare("
        INSERT INTO addresses (userID, fullName, phone, address, city, postcode, country, billing)
        VALUES (?, ?, ?, ?, ?, ?, ?, 0)
    ");
    $new_addr->bind_param("issssss",
        $_SESSION['customer_id'],
        $ship_fullname,
        $ship_phone,
        $ship_address,
        $ship_city,
        $ship_postcode,
        $ship_country
    );
    $new_addr->execute();
    $shippingAddressID = $conn->insert_id;
} else {
    // Use billing address
    $addr_stmt = $conn->prepare("SELECT id FROM addresses WHERE userID = ? AND billing = 1 LIMIT 1");
    $addr_stmt->bind_param("i", $_SESSION['customer_id']);
    $addr_stmt->execute();
    $billing = $addr_stmt->get_result()->fetch_assoc();
    $shippingAddressID = $billing['id'] ?? 0;
}

// Load cart products
$ids    = implode(',', array_map('intval', array_keys($cart)));
$result = $conn->query("SELECT id, name, qty FROM products WHERE id IN ($ids) AND status = 1");

$products_data = [];
while ($row = $result->fetch_assoc()) {
    $products_data[$row['id']] = $row;
}

$products   = [];
$totalItems = 0;
$subtotal   = 0;

foreach ($cart as $product_id => $variants) {
    if (!isset($products_data[$product_id])) continue;
    $base = $products_data[$product_id];

    foreach ($variants as $variation_key => $item) {
        $row = $base;
        $row['variation_key']   = $variation_key;
        $row['variation_label'] = $item['variation_label'];
        $row['price']           = $item['price'];
        $row['qty_order']       = $item['qty'];
        $row['subtotal']        = $item['price'] * $item['qty'];

        $totalItems += $item['qty'];
        $subtotal   += $row['subtotal'];
        $products[]  = $row;
    }
}

$deliveryPrice = $delivery_method['price'];
$total         = $subtotal + $deliveryPrice;

// Step 1 — save order first (deliveryID will be updated after)
$order_stmt = $conn->prepare("
    INSERT INTO orders (userID, peymentMethodID, status, totalItems, deliveryPrice, total, created_at)
    VALUES (?, ?, 'pending', ?, ?, ?, NOW())
");
$order_stmt->bind_param("iiddd",
    $_SESSION['customer_id'],
    $paymentMethodID,
    $totalItems,
    $deliveryPrice,
    $total
);
$order_stmt->execute();
$order_id = $conn->insert_id;

// Step 2 — create delivery record with orderID
$delivery_stmt = $conn->prepare("
    INSERT INTO delivery (orderID, addressID, methodID)
    VALUES (?, ?, ?)
");
$delivery_stmt->bind_param("iii", $order_id, $shippingAddressID, $deliveryMethodID);
$delivery_stmt->execute();
$deliveryID = $conn->insert_id;

// Step 3 — update order with deliveryID
$upd_stmt = $conn->prepare("UPDATE orders SET deliveryID = ? WHERE id = ?");
$upd_stmt->bind_param("ii", $deliveryID, $order_id);
$upd_stmt->execute();

// Step 4 — save order items + reduce stock
$item_stmt = $conn->prepare("INSERT INTO order_items (orderID, productID, variation_label, quantity, price) VALUES (?, ?, ?, ?, ?)");
$qty_stmt  = $conn->prepare("UPDATE products SET qty = qty - ? WHERE id = ? AND qty >= ?");

foreach ($products as $p) {
    $item_stmt->bind_param("iisid", $order_id, $p['id'], $p['variation_label'], $p['qty_order'], $p['price']);
    $item_stmt->execute();

    $qty_stmt->bind_param("iii", $p['qty_order'], $p['id'], $p['qty_order']);
    $qty_stmt->execute();
}

// Clear cart
unset($_SESSION['cart']);

$order_number = orderNumber($order_id);

include('./includes/header.php');
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 text-center">
                <div class="card p-5">

                    <div class="mb-3"><i class="fa-solid fa-square-check text-success"></i>
                    <h2 class="mb-2">Order Confirmed!</h2>
                    <p class="text-muted mb-1">
                        Thank you, <strong><?= htmlspecialchars($_SESSION['customer_name']) ?></strong>.
                    </p>
                    <p class="text-muted mb-4">
                        Your order <strong><?= $order_number ?></strong> has been placed successfully.
                    </p>

                    <hr class="mb-4">

                    <h5 class="mb-3 text-start">Order Summary</h5>

                    <?php foreach ($products as $p): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>
                                <?= htmlspecialchars($p['name']) ?>
                                <?php if (!empty($p['variation_label'])): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($p['variation_label']) ?></small>
                                <?php endif; ?>
                                × <?= $p['qty_order'] ?>
                            </span>
                            <span>£<?= number_format($p['subtotal'], 2) ?></span>
                        </div>
                    <?php endforeach; ?>

                    <hr>

                    <div class="d-flex justify-content-between mb-1">
                        <span>Subtotal</span>
                        <span>£<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Delivery — <?= htmlspecialchars($delivery_method['title']) ?></span>
                        <span><?= $deliveryPrice > 0 ? '£' . number_format($deliveryPrice, 2) : 'Free' ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Payment</span>
                        <span><?= htmlspecialchars($payment_method['title']) ?></span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-4">
                        <strong>Total</strong>
                        <strong>£<?= number_format($total, 2) ?></strong>
                    </div>

                    <a href="index.php" class="btn-cta">Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include('includes/footer.php'); ?>