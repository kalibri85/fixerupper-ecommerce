<?php
require_once __DIR__ . '/includes/init.php';
include('./includes/header.php');

$cart = $_SESSION['cart'] ?? [];

$cart_items = [];
$total = 0;

if (!empty($cart)) {
    $ids = implode(',', array_map('intval', array_keys($cart)));

    $stmt = $conn->prepare("
        SELECT id, name, image, qty
        FROM products
        WHERE id IN ($ids) AND status = 1
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    $products_data = [];
    while ($row = $result->fetch_assoc()) {
        $products_data[$row['id']] = $row;
    }
    $stmt->close();

    // Flatten cart into list of items
    foreach ($cart as $product_id => $variants) {
        if (!isset($products_data[$product_id])) continue;
        $product = $products_data[$product_id];

        foreach ($variants as $variation_key => $item) {
            $subtotal      = $item['price'] * $item['qty'];
            $total        += $subtotal;
            $cart_items[]  = [
                'id'              => $product_id,
                'variation_key'   => $variation_key,
                'name'            => $product['name'],
                'image'           => $product['image'],
                'stock'           => $product['qty'],
                'price'           => $item['price'],
                'qty'             => $item['qty'],
                'variation_label' => $item['variation_label'],
                'subtotal'        => $subtotal,
            ];
        }
    }
}
?>

<section class="py-5">
    <div class="container">

        <nav class="breadcrumbs mb-4">
            <a href="index.php">Home</a> /
            <span>Cart</span>
        </nav>

        <h1 class="mb-4">Shopping Cart</h1>

        <?php if (empty($cart_items)): ?>
            <div class="alert alert-light border">
                Your cart is empty.
            </div>

        <?php else: ?>
        <div class="row">

            <!-- LEFT -->
            <div class="col-lg-8">

                <?php foreach ($cart_items as $product): ?>
                    <div class="card mb-3 p-3">
                        <div class="row align-items-center">

                            <div class="col-md-2">
                                <img src="img/products/<?= htmlspecialchars($product['image']) ?>"
                                     class="img-fluid rounded">
                            </div>

                            <div class="col-md-4">
                                <h6 class="mb-1">
                                    <?= htmlspecialchars($product['name']) ?>
                                </h6>
                                 <?php if (!empty($product['variation_label'])): ?>
                                    <div class="text-muted small">
                                        <?= htmlspecialchars($product['variation_label']) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="text-muted">
                                    £<?= number_format($product['price'], 2) ?>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <form method="POST" action="updateCart.php" class="d-flex">
                                    <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="variation_key" value="<?= htmlspecialchars($product['variation_key']) ?>">


                                    <input type="number"
                                           name="qty"
                                           value="<?= $product['qty'] ?>"
                                           min="1"
                                           max="<?= $product['stock'] ?>"
                                           class="form-control me-2">

                                    <button class="btn btn-outline-primary">
                                        Update
                                    </button>
                                </form>
                            </div>

                            <div class="col-md-2 text-end">
                                <strong>
                                    £<?= number_format($product['subtotal'], 2) ?>
                                </strong>
                            </div>

                            <div class="col-md-1 text-end">
                                <form method="POST" action="deleteCart.php" class="d-inline remove-form">
                                    <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="variation_key" value="<?= htmlspecialchars($product['variation_key']) ?>">

                                    <button type="submit" class="btn btn-link text-danger p-0 border-0">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>

            </div>

            <!-- RIGHT -->
            <div class="col-lg-4">
                <div class="card p-4 sticky-top">
                    <h5 class="mb-3">Order Summary</h5>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>£<?= number_format($total, 2) ?></span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total</strong>
                        <strong>£<?= number_format($total, 2) ?></strong>
                    </div>

                    <a href="checkout.php" class="btn-cta text-center">
                        Proceed to Checkout
                    </a>
                </div>
            </div>

        </div>
        <?php endif; ?>

    </div>
</section>
<script src="./js/cart.js"></script>
<?php include('includes/footer.php'); ?>