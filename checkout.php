<?php
    /**
     * Checkout
     * @author Lana (Svetlana Muraveckaja-Odincova)
     */
    require_once __DIR__ . '/includes/init.php';

    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        redirect('cart.php');
    }
    requireCustomer();

    // Cart products
    $ids      = implode(',', array_map('intval', array_keys($cart)));
    $result   = $conn->query("SELECT id, name, price, image FROM products WHERE id IN ($ids) AND status = 1");
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
            $row['qty']             = $item['qty'];
            $row['subtotal']        = $item['price'] * $item['qty'];

            $totalItems += $item['qty'];
            $subtotal   += $row['subtotal'];
            $products[]  = $row;
        }
    }

    // Delivery methods
    $delivery_result = $conn->query("SELECT id, title, price FROM delivery_method WHERE active = 1 ORDER BY price ASC");
    $delivery_methods = [];
    while ($d = $delivery_result->fetch_assoc()) {
        $delivery_methods[] = $d;
    }

    // Payment methods
    $payment_result = $conn->query("SELECT id, title FROM payment_methods ORDER BY id ASC");
    $payment_methods = [];
    while ($p = $payment_result->fetch_assoc()) {
        $payment_methods[] = $p;
    }

    // Customer + billing address
    $stmt = $conn->prepare("SELECT name, surname, email, tel FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['customer_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    $addr_stmt = $conn->prepare("SELECT * FROM addresses WHERE userID = ? AND billing = 1 LIMIT 1");
    $addr_stmt->bind_param("i", $_SESSION['customer_id']);
    $addr_stmt->execute();
    $billing = $addr_stmt->get_result()->fetch_assoc();

    include('./includes/header.php');
?>

<section class="py-5">
    <div class="container">

        <nav class="breadcrumbs mb-4">
            <a href="index.php">Home</a> /
            <a href="cart.php">Cart</a> /
            <span>Checkout</span>
        </nav>

        <h1 class="mb-4">Checkout</h1>

        <div class="row">

            <!-- LEFT -->
            <div class="col-lg-7">
                <form method="POST" action="confirmation.php" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= csrf() ?>">

                    <!-- Delivery Address -->
                    <div class="card p-4 mb-4">
                        <h5 class="mb-3">Delivery Address</h5>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="address_choice"
                                   id="addrBilling" value="billing" checked>
                            <label class="form-check-label" for="addrBilling">
                                <strong>Billing address</strong>
                                <?php if ($billing): ?>
                                    <br><small class="text-muted">
                                        <?= htmlspecialchars($billing['address']) ?>,
                                        <?= htmlspecialchars($billing['city']) ?>,
                                        <?= htmlspecialchars($billing['postcode']) ?>
                                    </small>
                                <?php endif; ?>
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="address_choice"
                                   id="addrNew" value="new">
                            <label class="form-check-label" for="addrNew">
                                Deliver to a different address
                            </label>
                        </div>

                        <!-- New address fields -->
                        <div id="newAddressFields" class="d-none">
                            <div class="mb-3">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="new_fullname" class="form-control"
                                       value="<?= htmlspecialchars($user['name'] . ' ' . $user['surname']) ?>"
                                       minlength="2" maxlength="255" autocomplete="name">
                                <div class="invalid-feedback">Please enter full name.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="new_phone" class="form-control"
                                       value="<?= htmlspecialchars($user['tel'] ?? '') ?>"
                                       pattern="[\d\s\+\-\(\)]{7,20}" maxlength="20">
                                <div class="invalid-feedback">Please enter a valid phone number.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Address <span class="text-danger">*</span></label>
                                <input type="text" name="new_address" class="form-control"
                                       value=""
                                       minlength="5" maxlength="255" autocomplete="street-address">
                                <div class="invalid-feedback">Please enter your address.</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <label class="form-label">City <span class="text-danger">*</span></label>
                                    <input type="text" name="new_city" class="form-control"
                                           value=""
                                           minlength="2" maxlength="100" autocomplete="address-level2">
                                    <div class="invalid-feedback">Please enter your city.</div>
                                </div>
                                <div class="col">
                                    <label class="form-label">Postcode <span class="text-danger">*</span></label>
                                    <input type="text" name="new_postcode" class="form-control"
                                           value=""
                                           pattern="[A-Za-z]{1,2}\d{1,2}[A-Za-z]?\s?\d[A-Za-z]{2}"
                                           maxlength="10" autocomplete="postal-code">
                                    <div class="invalid-feedback">Please enter a valid UK postcode (e.g. LE1 3BK).</div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Country <span class="text-danger">*</span></label>
                                <input type="text" name="new_country" class="form-control"
                                       value="United Kingdom"
                                       minlength="2" maxlength="100" autocomplete="country-name">
                                <div class="invalid-feedback">Please enter your country.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Method -->
                    <div class="card p-4 mb-4">
                        <h5 class="mb-3">Delivery Method</h5>
                        <?php foreach ($delivery_methods as $i => $d): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input delivery-radio" type="radio"
                                       name="deliveryMethodID" value="<?= $d['id'] ?>"
                                       id="del_<?= $d['id'] ?>"
                                       data-price="<?= (float)$d['price'] ?>"
                                       <?= $i === 0 ? 'checked' : '' ?>>
                                <label class="form-check-label" for="del_<?= $d['id'] ?>">
                                    <?= htmlspecialchars($d['title']) ?>
                                    — <?= $d['price'] > 0 ? '£' . number_format($d['price'], 2) : 'Free' ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Payment Method -->
                    <div class="card p-4 mb-4">
                        <h5 class="mb-3">Payment Method</h5>
                        <?php foreach ($payment_methods as $i => $p): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio"
                                       name="paymentMethodID" value="<?= $p['id'] ?>"
                                       id="pay_<?= $p['id'] ?>"
                                       required
                                       <?= $i === 0 ? 'checked' : '' ?>>
                                <label class="form-check-label" for="pay_<?= $p['id'] ?>">
                                    <?= htmlspecialchars($p['title']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" class="btn-cta w-100">Confirm Order</button>
                </form>
            </div>

            <!-- RIGHT: Order Summary -->
            <div class="col-lg-5">
                <div class="card p-4 sticky-top summary-sticky">
                    <h5 class="mb-3">Order Summary</h5>

                    <?php foreach ($products as $p): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>
                                <?= htmlspecialchars($p['name']) ?>
                                <?php if (!empty($p['variation_label'])): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($p['variation_label']) ?></small>
                                <?php endif; ?>
                                × <?= $p['qty'] ?>
                            </span>
                            <span>£<?= number_format($p['subtotal'], 2) ?></span>
                        </div>
                    <?php endforeach; ?>

                    <hr>

                    <div class="d-flex justify-content-between mb-1">
                        <span>Subtotal</span>
                        <span>£<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Delivery</span>
                        <span id="deliveryCost">
                            <?= !empty($delivery_methods) && $delivery_methods[0]['price'] > 0
                                ? '£' . number_format($delivery_methods[0]['price'], 2)
                                : 'Free' ?>
                        </span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total</strong>
                        <strong>£<span id="grandTotal">
                            <?= number_format($subtotal + (!empty($delivery_methods) ? $delivery_methods[0]['price'] : 0), 2) ?>
                        </span></strong>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Pass subtotal to JS via data attribute (CSP compliant) -->
<div id="checkoutData" data-subtotal="<?= $subtotal ?>" class="d-none"></div>

<script src="./js/checkout.js" defer></script>
<?php include('includes/footer.php'); ?>