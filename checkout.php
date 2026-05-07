<?php
/**
 * Checkout — customer must be logged in
 * @author Lana (Svetlana Muraveckaja-Odincova)
 */
require_once __DIR__ . '/admin/includes/init.php';

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    redirect('cart.php');
}
requireCustomer();

// Load cart products
$ids     = implode(',', array_map('intval', array_keys($cart)));
$result  = $conn->query("SELECT `id`, `name`, `price`, `image` FROM products WHERE id IN ($ids) AND status = 1");
$products = [];
$totalItems = 0;
$total = 0;

while ($row = $result->fetch_assoc()) {
    $qty             = $cart[$row['id']];
    $row['qty']      = $qty;
    $row['subtotal'] = $row['price'] * $qty;
    $totalItems     += $qty;
    $total          += $row['subtotal'];
    $products[]      = $row;
}

// Load delivery options
$delivery_options = $conn->query("SELECT `id`, `title`, `price` FROM delivery_method WHERE `active` = 1 ORDER BY price ASC");

// Load customer details for pre-fill
$stmt = $conn->prepare("SELECT `name`, `surname`, `email`, `tel` FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['customer_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

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

            <!-- LEFT: delivery details -->
            <div class="col-lg-7">
                <div class="card p-4 mb-4">
                    <h5 class="mb-3">Your Details</h5>

                    <form method="POST" action="confirm.php">
                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">

                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label">First Name</label>
                                <input type="text" name="name" class="form-control"
                                       value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>
                            <div class="col">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="surname" class="form-control"
                                       value="<?= htmlspecialchars($user['surname']) ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="tel" class="form-control"
                                   value="<?= htmlspecialchars($user['tel'] ?? '') ?>">
                        </div>

                        <!-- Delivery options from DB -->
                        <h5 class="mb-3">Delivery Method</h5>
                        <?php
                        $first = true;
                        while ($d = $delivery_options->fetch_assoc()):
                        ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio"
                                       name="deliveryID" value="<?= $d['id'] ?>"
                                       id="del_<?= $d['id'] ?>"
                                       data-price="<?= $d['price'] ?>"
                                       <?= $first ? 'checked' : '' ?>>
                                <label class="form-check-label" for="del_<?= $d['id'] ?>">
                                    <?= htmlspecialchars($d['title']) ?>
                                    — <?= $d['price'] > 0 ? '£' . number_format($d['price'], 2) : 'Free' ?>
                                </label>
                            </div>
                        <?php $first = false; endwhile; ?>

                        <button type="submit" class="btn-cta w-100 mt-4">Confirm Order</button>
                    </form>
                </div>
            </div>

            <!-- RIGHT: order summary -->
            <div class="col-lg-5">
                <div class="card p-4 sticky-top" style="top:120px;">
                    <h5 class="mb-3">Order Summary</h5>

                    <?php foreach ($products as $p): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= htmlspecialchars($p['name']) ?> × <?= $p['qty'] ?></span>
                            <span>£<?= number_format($p['subtotal'], 2) ?></span>
                        </div>
                    <?php endforeach; ?>

                    <hr>

                    <div class="d-flex justify-content-between mb-1">
                        <span>Subtotal</span>
                        <span>£<?= number_format($total, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Delivery</span>
                        <span id="deliveryCost">Free</span>
                    </div>

                    <div class="d-flex justify-content-between">
                        <strong>Total</strong>
                        <strong>£<span id="grandTotal"><?= number_format($total, 2) ?></span></strong>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<script src="./js/cart.js"></script>

<?php include('includes/footer.php'); ?>