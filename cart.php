<?php
require_once __DIR__ . '/admin/includes/init.php';
include('./includes/header.php');

$cart = $_SESSION['cart'] ?? [];

$products = [];
$total = 0;

if (!empty($cart)) {
    $ids = implode(',', array_map('intval', array_keys($cart)));

    $result = $conn->query("
        SELECT id, name, price, image, qty
        FROM products
        WHERE id IN ($ids) AND status = 1
    ");

    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
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

        <?php if (empty($products)): ?>
            <div class="alert alert-light border">
                Your cart is empty.
            </div>

        <?php else: ?>
        <div class="row">

            <!-- LEFT -->
            <div class="col-lg-8">

                <?php foreach ($products as $product): ?>
                    <?php
                        $qty = $cart[$product['id']];
                        $subtotal = $product['price'] * $qty;
                        $total += $subtotal;
                    ?>

                    <div class="card mb-3 p-3">
                        <div class="row align-items-center">

                            <div class="col-md-2">
                                <img src="img/products/<?= $product['image'] ?>"
                                     class="img-fluid rounded">
                            </div>

                            <div class="col-md-4">
                                <h6 class="mb-1">
                                    <?= htmlspecialchars($product['name']) ?>
                                </h6>
                                <div class="text-muted">
                                    £<?= number_format($product['price'], 2) ?>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <form method="POST" action="updateCart.php" class="d-flex">
                                    <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                                    <input type="number"
                                           name="qty"
                                           value="<?= $qty ?>"
                                           min="1"
                                           max="<?= $product['qty'] ?>"
                                           class="form-control me-2">

                                    <button class="btn btn-outline-primary">
                                        Update
                                    </button>
                                </form>
                            </div>

                            <div class="col-md-2 text-end">
                                <strong>
                                    £<?= number_format($subtotal, 2) ?>
                                </strong>
                            </div>

                            <div class="col-md-1 text-end">
                                <form method="POST" action="deleteCart.php" class="d-inline remove-form">
                                    <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

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
                <div class="card p-4 sticky-top" style="top:120px;">
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