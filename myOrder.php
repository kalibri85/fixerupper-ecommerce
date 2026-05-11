<?php
    /**
     * My Order — single order details
     * @author Lana (Svetlana Muraveckaja-Odincova)
     */
    require_once __DIR__ . '/includes/init.php';
    requireCustomer();

    $order_id = (int)($_GET['id'] ?? 0);
    if (!$order_id) redirect('myOrders.php');

    // Load order — must belong to this customer
    $stmt = $conn->prepare("
        SELECT
            o.id,
            o.status,
            o.total,
            o.totalItems,
            o.deliveryPrice,
            o.created_at,
            pm.title          AS payment_method,
            dm.title          AS delivery_method,
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
        LEFT JOIN delivery d ON d.id = o.deliveryID
        LEFT JOIN delivery_method dm ON dm.id = d.methodID
        LEFT JOIN payment_methods pm ON pm.id = o.peymentMethodID
        LEFT JOIN addresses da ON da.id = d.addressID
        LEFT JOIN addresses ba ON ba.userID = o.userID AND ba.billing = 1
        WHERE o.id = ? AND o.userID = ?
    ");
    $stmt->bind_param("ii", $order_id, $_SESSION['customer_id']);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if (!$order) redirect('my_orders.php');

    // Load order items
    $items_stmt = $conn->prepare("
        SELECT
            oi.quantity,
            oi.price,
            p.name,
            p.image
        FROM order_items oi
        JOIN products p ON p.id = oi.productID
        WHERE oi.orderID = ?
    ");
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items = $items_stmt->get_result();

    $order_number = 'FU-' . str_pad($order['id'], 5, '0', STR_PAD_LEFT);
    $subtotal     = $order['total'] - $order['deliveryPrice'];

    // Status badges
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

<section class="py-5">
    <div class="container">

        <nav class="breadcrumbs mb-4">
            <a href="index.php">Home</a> /
            <a href="my_orders.php">My Orders</a> /
            <span><?= $order_number ?></span>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Order <?= $order_number ?></h1>
            <span class="badge <?= $status_class ?> fs-6"><?= ucfirst($order['status']) ?></span>
        </div>

        <div class="row">

            <!-- LEFT: order items -->
            <div class="col-lg-7">
                <div class="row mg-4 g-3">
                    <!-- Delivery address -->
                    <div class="col-md-6">
                        <h5 class="mb-3">Delivery Address</h5>
                        <p class="mb-1"><?= htmlspecialchars($order['del_fullName'] ?? '—') ?></p>
                        <p class="mb-1"><?= htmlspecialchars($order['del_address'] ?? '—') ?></p>
                        <p class="mb-1"><?= htmlspecialchars($order['del_city'] ?? '—') ?>,</p> 
                        <p><?= htmlspecialchars($order['del_postcode'] ?? '—') ?></p>
                        <p class="mb-0"><?= htmlspecialchars($order['del_country'] ?? '—') ?></p>
                    </div>

                    <!-- Billing address -->
                    <div class="col-md-6">
                        <h5 class="mb-3">Billing Address</h5>
                        <p class="mb-1"><?= htmlspecialchars($order['bill_fullName'] ?? '—') ?></p>
                        <p class="mb-1"><?= htmlspecialchars($order['bill_address'] ?? '—') ?></p>
                        <p class="mb-1"><?= htmlspecialchars($order['bill_city'] ?? '—') ?>, </p>
                        <p><?= htmlspecialchars($order['bill_postcode'] ?? '—') ?></p>
                        <p class="mb-0"><?= htmlspecialchars($order['bill_country'] ?? '—') ?></p>
                    </div>
                </div>    
                <div class="card p-4 my-4 ">
                    <h5 class="mb-3">Items</h5>

                    <?php while ($item = $items->fetch_assoc()): ?>
                        <div class="d-flex align-items-center mb-3 gap-3">
                            <?php if ($item['image']): ?>
                                <img src="./img/products/<?= htmlspecialchars($item['image']) ?>"
                                     alt="<?= htmlspecialchars($item['name']) ?>"
                                     class="rounded" width="60" height="60"
                                     loading="lazy">
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <div><?= htmlspecialchars($item['name']) ?></div>
                                <small class="text-muted">× <?= $item['quantity'] ?></small>
                            </div>
                            <div>£<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                        </div>
                    <?php endwhile; ?>

                    <hr>

                    <div class="d-flex justify-content-between mb-1">
                        <span>Subtotal</span>
                        <span>£<?= number_format($subtotal, 2) ?></span>
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

            <!-- RIGHT: delivery + payment info -->
            <div class="col-lg-5">

                <!-- Delivery status -->
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
                            <span><?= date('d M Y', strtotime($order['shipped_at'])) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($order['delivered_at']): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Delivered</span>
                            <span><?= date('d M Y', strtotime($order['delivered_at'])) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Payment -->
                <div class="card p-4 mb-4">
                    <h5 class="mb-3">Payment</h5>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Method</span>
                        <span><?= htmlspecialchars($order['payment_method'] ?? '—') ?></span>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <span class="text-muted">Date</span>
                        <span><?= date('d M Y', strtotime($order['created_at'])) ?></span>
                    </div>
                </div>

            </div>
        </div>

        <a href="myOrders.php" class="btn btn-outline-secondary">← Back to My Orders</a>

    </div>
</section>