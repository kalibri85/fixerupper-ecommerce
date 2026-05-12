<?php
    /**
     * My Orders — customer order history
     * @author Lana (Svetlana Muraveckaja-Odincova)
     */
    require_once __DIR__ . '/includes/init.php';
    requireCustomer();

    // Load orders with delivery status
    $stmt = $conn->prepare("
        SELECT
            o.id,
            o.status,
            o.total,
            o.totalItems,
            o.created_at,
            dm.title AS delivery_method,
            d.status AS delivery_status
        FROM orders o
        LEFT JOIN delivery d ON d.id = o.deliveryID
        LEFT JOIN delivery_method dm ON dm.id = d.methodID
        WHERE o.userID = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->bind_param("i", $_SESSION['customer_id']);
    $stmt->execute();
    $orders = $stmt->get_result();

    include('./includes/header.php');
?>

<section class="py-5">
    <div class="container">

        <nav class="breadcrumbs mb-4">
            <a href="index.php">Home</a> / <span>My Orders</span>
        </nav>

        <h1 class="mb-4">My Orders</h1>

        <?php if ($orders->num_rows === 0): ?>
            <div class="card p-5 text-center">
                <p class="text-muted mb-3">You have no orders yet.</p>
                <a href="index.php" class="btn-cta">Start Shopping</a>
            </div>
        <?php else: ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Delivery</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $orders->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= orderNumber($order['id']) ?></strong></td>
                                <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                                <td><?= $order['totalItems'] ?></td>
                                <td>£<?= number_format($order['total'], 2) ?></td>
                                <td>
                                    <?= htmlspecialchars($order['delivery_method'] ?? '—') ?>
                                    <?php if ($order['delivery_status']): ?>
                                        <br><?= deliveryStatusBadge($order['delivery_status']) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= orderStatusBadge($order['status']) ?></td>
                                <td>
                                    <a href="myOrder.php?id=<?= $order['id'] ?>" class="btn btn-primary">
                                        <i class="fa-regular fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>

    </div>
</section>

<?php include('includes/footer.php'); ?>