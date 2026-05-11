<?php
    /**
     * Admin — Single Order
     * @author Lana (Svetlana Muraveckaja-Odincova)
     */
    require_once __DIR__ . '/includes/init.php';
    requireAdmin();

    $order_id = (int)($_GET['id'] ?? 0);
    if (!$order_id) redirect('orders.php');

    // Update status
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        checkCSRF();

        $new_status = $_POST['status'] ?? '';
        $allowed    = ['pending', 'shipped', 'completed'];

        if (in_array($new_status, $allowed)) {
            $now = date('Y-m-d H:i:s');

            // Update orders table
            $upd = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $upd->bind_param("si", $new_status, $order_id);
            $upd->execute();

            // Update delivery table based on status
            if ($new_status === 'shipped') {
                // orders: shipped, delivery: shipped + shipped_at
                $upd_del = $conn->prepare("
                    UPDATE delivery SET status = 'shipped', shipped_at = ?
                    WHERE orderID = ?
                ");
                $upd_del->bind_param("si", $now, $order_id);
                $upd_del->execute();

            } elseif ($new_status === 'completed') {
                // orders: completed, delivery: delivered + delivered_at
                $upd_del = $conn->prepare("
                    UPDATE delivery SET status = 'delivered', delivered_at = ?
                    WHERE orderID = ?
                ");
                $upd_del->bind_param("si", $now, $order_id);
                $upd_del->execute();
            }

            redirect("order.php?id=$order_id&msg=updated");
        }
    }

    // Handle failed delivery
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_failed'])) {
        checkCSRF();
        $now = date('Y-m-d H:i:s');

        $upd_del = $conn->prepare("
            UPDATE delivery SET status = 'failed', delivered_at = ?
            WHERE orderID = ?
        ");
        $upd_del->bind_param("si", $now, $order_id);
        $upd_del->execute();

        redirect("order.php?id=$order_id&msg=failed");
    }

    // Load order
    $stmt = $conn->prepare("
        SELECT
            o.id,
            o.status,
            o.total,
            o.totalItems,
            o.deliveryPrice,
            o.created_at,
            u.name,
            u.surname,
            u.email,
            u.tel,
            pm.title          AS payment_method,
            dm.title          AS delivery_method,
            d.id              AS delivery_id,
            d.status          AS delivery_status,
            d.shipped_at,
            d.delivered_at,
            da.fullName       AS del_fullName,
            da.address        AS del_address,
            da.city           AS del_city,
            da.postcode       AS del_postcode,
            da.country        AS del_country,
            ba.fullName       AS bill_fullName,
            ba.address        AS bill_address,
            ba.city           AS bill_city,
            ba.postcode       AS bill_postcode,
            ba.country        AS bill_country
        FROM orders o
        LEFT JOIN users u            ON u.id  = o.userID
        LEFT JOIN delivery d         ON d.id  = o.deliveryID
        LEFT JOIN delivery_method dm ON dm.id = d.methodID
        LEFT JOIN payment_methods pm ON pm.id = o.peymentMethodID
        LEFT JOIN addresses da       ON da.id = d.addressID
        LEFT JOIN addresses ba       ON ba.userID = o.userID AND ba.billing = 1
        WHERE o.id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if (!$order) redirect('orders.php');

    // Load order items 
    $items_stmt = $conn->prepare("
        SELECT oi.quantity, oi.price, p.name, p.image, p.sku
        FROM order_items oi
        JOIN products p ON p.id = oi.productID
        WHERE oi.orderID = ?
    ");
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items = $items_stmt->get_result();

    $order_number = 'FU-' . str_pad($order['id'], 5, '0', STR_PAD_LEFT);
    $subtotal     = $order['total'] - $order['deliveryPrice'];

    $status_class = match($order['status']) {
        'pending'   => 'bg-warning text-dark',
        'shipped'   => 'bg-info text-dark',
        'completed' => 'bg-success',
        default     => 'bg-secondary'
    };

    $del_class = match($order['delivery_status']) {
        'shipped'   => 'bg-info text-dark',
        'delivered' => 'bg-success',
        'failed'    => 'bg-danger',
        default     => 'bg-secondary'
    };

    include('./includes/header.php');
?>

<!-- Title -->
<section id="titleSection" class="pt-3 pb-1">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <a href="orders.php" class="btn btn-sm btn-outline-secondary me-3">
                    ← Orders
                </a>
                <h1 class="d-inline">Order <?= $order_number ?></h1>
            </div>
            <span class="badge <?= $status_class ?> fs-6"><?= ucfirst($order['status']) ?></span>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success">
                <?php
                    $msgs = [
                        'updated' => 'Order status updated.',
                        'failed'  => 'Delivery marked as failed.',
                    ];
                    echo $msgs[$_GET['msg']] ?? '';
                ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="pb-5">
    <div class="container">
        <div class="row">

            <!-- LEFT: items + status -->
            <div class="col-lg-7">
                <!-- Update status -->
                <div class="card p-4 mb-4">
                    <h5 class="mb-3">Update Order Status</h5>
                    <form method="POST" class="d-flex gap-2 align-items-center">
                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                        <select name="status" class="form-select">
                            <option value="pending"   <?= $order['status'] === 'pending'   ? 'selected' : '' ?>>Pending</option>
                            <option value="shipped"   <?= $order['status'] === 'shipped'   ? 'selected' : '' ?>>Shipped</option>
                            <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                        </select>
                        <button type="submit" name="update_status" class="btn btn-primary">
                            Update
                        </button>
                    </form>

                    <?php if ($order['delivery_status'] !== 'failed'): ?>
                        <form method="POST" class="mt-3">
                            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                            <button type="submit" name="mark_failed" class="btn btn-outline-danger btn-sm"
                                    onclick="return confirm('Mark delivery as failed?')">
                                Mark Delivery as Failed
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                <div class="row p-4 mb-4">
                    <!-- Billing Address -->
                    <div class="col-md-6">
                        <h5 class="mb-3">Billing Address</h5>
                        <p class="mb-1"><?= htmlspecialchars($order['bill_fullName'] ?? '—') ?></p>
                        <p class="mb-1"><?= htmlspecialchars($order['bill_address'] ?? '—') ?></p>
                        <p class="mb-1"><?= htmlspecialchars($order['bill_city'] ?? '—') ?>, <?= htmlspecialchars($order['bill_postcode'] ?? '—') ?></p>
                        <p class="mb-0"><?= htmlspecialchars($order['bill_country'] ?? '—') ?></p>
                    </div>
                    <!-- Delivery Address -->
                    <div class="col-md-6">
                        <h5 class="mb-3">Delivery Address</h5>
                        <p class="mb-1"><?= htmlspecialchars($order['del_fullName'] ?? '—') ?></p>
                        <p class="mb-1"><?= htmlspecialchars($order['del_address'] ?? '—') ?></p>
                        <p class="mb-1"><?= htmlspecialchars($order['del_city'] ?? '—') ?>, <?= htmlspecialchars($order['del_postcode'] ?? '—') ?></p>
                        <p class="mb-0"><?= htmlspecialchars($order['del_country'] ?? '—') ?></p>
                    </div>
                </div>    
                <!-- Order items -->
                <div class="card p-4 mb-4">
                    <h5 class="mb-3">Items</h5>

                    <?php while ($item = $items->fetch_assoc()): ?>
                        <div class="d-flex align-items-center mb-3 gap-3">
                            <?php if ($item['image']): ?>
                                <img src="../img/products/<?= htmlspecialchars($item['image']) ?>"
                                     alt="<?= htmlspecialchars($item['name']) ?>"
                                     class="rounded" width="60" height="60" loading="lazy">
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <div><?= htmlspecialchars($item['name']) ?></div>
                                <small class="text-muted">SKU: <?= htmlspecialchars($item['sku']) ?></small>
                            </div>
                            <div class="text-end">
                                <div>× <?= $item['quantity'] ?></div>
                                <div>£<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                            </div>
                        </div>
                    <?php endwhile; ?>

                    <hr>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Subtotal</span><span>£<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Delivery</span>
                        <span><?= $order['deliveryPrice'] > 0 ? '£' . number_format($order['deliveryPrice'], 2) : 'Free' ?></span>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <strong>Total</strong>
                        <strong>£<?= number_format($order['total'], 2) ?></strong>
                    </div>
                </div>
            </div>

            <!-- RIGHT: customer + delivery info -->
            <div class="col-lg-5">

                <!-- Customer -->
                <div class="card p-4 mb-4">
                    <h5 class="mb-3">Customer</h5>
                    <p class="mb-1"><strong><?= htmlspecialchars($order['name'] . ' ' . $order['surname']) ?></strong></p>
                    <p class="mb-1"><?= htmlspecialchars($order['email']) ?></p>
                    <p class="mb-0"><?= htmlspecialchars($order['tel'] ?? '—') ?></p>
                </div>

                <!-- Delivery -->
                <div class="card p-4 mb-4">
                    <h5 class="mb-3">Delivery</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Method</span>
                        <span><?= htmlspecialchars($order['delivery_method'] ?? '—') ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Status</span>
                        <span class="badge <?= $del_class ?>">
                            <?= $order['delivery_status'] ? ucfirst($order['delivery_status']) : 'Pending' ?>
                        </span>
                    </div>
                    <?php if ($order['shipped_at']): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Shipped</span>
                            <span><?= date('d M Y H:i', strtotime($order['shipped_at'])) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($order['delivered_at']): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Delivered</span>
                            <span><?= date('d M Y H:i', strtotime($order['delivered_at'])) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Payment -->
                <div class="card p-4">
                    <h5 class="mb-3">Payment</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Method</span>
                        <span><?= htmlspecialchars($order['payment_method'] ?? '—') ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Date</span>
                        <span><?= date('d M Y H:i', strtotime($order['created_at'])) ?></span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>