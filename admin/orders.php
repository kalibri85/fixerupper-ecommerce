<?php
    /**
     * Admin — Orders
     * @author Lana (Svetlana Muraveckaja-Odincova)
     */
    require_once __DIR__ . '/includes/init.php';
    requireAdmin();
    include('./includes/header.php');

    // Filters & sorting
    $status_filter = $_GET['status'] ?? '';
    $sort          = $_GET['sort']   ?? 'newest';

    $allowed_statuses = ['pending', 'shipped', 'completed'];
    $allowed_sorts    = ['newest', 'oldest', 'total_asc', 'total_desc'];

    if (!in_array($status_filter, $allowed_statuses)) $status_filter = '';
    if (!in_array($sort, $allowed_sorts))             $sort = 'newest';

    $order_by = match($sort) {
        'oldest'     => 'o.created_at ASC',
        'total_asc'  => 'o.total ASC',
        'total_desc' => 'o.total DESC',
        default      => 'o.created_at DESC'
    };

    $where = $status_filter ? "WHERE o.status = '" . $conn->real_escape_string($status_filter) . "'" : '';

    // Pagination
    $total_result = $conn->query("SELECT COUNT(*) as total FROM orders o $where");
    $total        = $total_result->fetch_assoc()['total'];
    $pagination   = paginate($total, 15);
    $page         = $pagination['page'];
    $perPage      = $pagination['perPage'];
    $offset       = $pagination['offset'];
    $totalPages   = $pagination['totalPages'];

    // Orders
    $result = $conn->query("
        SELECT
            o.id,
            o.status,
            o.total,
            o.totalItems,
            o.created_at,
            u.name,
            u.surname
        FROM orders o
        LEFT JOIN users u ON u.id = o.userID
        $where
        ORDER BY $order_by
        LIMIT $offset, $perPage
    ");

    $base_url = 'orders.php?' . http_build_query(array_filter([
        'status' => $status_filter,
        'sort'   => $sort,
    ])) . '&';
?>

<!-- Title -->
<section id="titleSection" class="pt-3 pb-1">
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <h1>Orders</h1>
        </div>

        <!-- Filters -->
        <form method="GET" class="row g-2 mb-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="pending"   <?= $status_filter === 'pending'   ? 'selected' : '' ?>>Pending</option>
                    <option value="shipped"   <?= $status_filter === 'shipped'   ? 'selected' : '' ?>>Shipped</option>
                    <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Sort by</label>
                <select name="sort" class="form-select form-select-sm">
                    <option value="newest"     <?= $sort === 'newest'     ? 'selected' : '' ?>>Date: Newest first</option>
                    <option value="oldest"     <?= $sort === 'oldest'     ? 'selected' : '' ?>>Date: Oldest first</option>
                    <option value="total_desc" <?= $sort === 'total_desc' ? 'selected' : '' ?>>Total: High to Low</option>
                    <option value="total_asc"  <?= $sort === 'total_asc'  ? 'selected' : '' ?>>Total: Low to High</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Apply</button>
            </div>
            <?php if ($status_filter || $sort !== 'newest'): ?>
                <div class="col-md-2">
                    <a href="orders.php" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</section>

<!-- Table header -->
<section id="tableHeader" class="pt-3 pb-3">
    <div class="container text-center">
        <div class="row fw-semibold">
            <div class="float-start col-md-2">Order</div>
            <div class="float-start col-md-3">Customer</div>
            <div class="float-start col-md-2">Date</div>
            <div class="float-start col-md-1">Items</div>
            <div class="float-start col-md-2">Total</div>
            <div class="float-start col-md-1">Status</div>
            <div class="float-start col-md-1"></div>
        </div>
    </div>
</section>

<!-- Table body -->
<section id="tableBody">
    <div class="container text-center">

        <?php if ($total === 0): ?>
            <div class="py-4 text-muted">No orders found.</div>
        <?php endif; ?>

        <?php while ($row = $result->fetch_assoc()): ?>
            <?php
            $order_number = 'FU-' . str_pad($row['id'], 5, '0', STR_PAD_LEFT);
            $status_class = match($row['status']) {
                'pending'   => 'bg-warning text-dark',
                'shipped'   => 'bg-info text-dark',
                'completed' => 'bg-success',
                default     => 'bg-secondary'
            };
            ?>
            <div class="row pt-3 pb-2 item-row align-items-center">
                <div class="float-start col-md-2">
                    <strong><?= $order_number ?></strong>
                </div>
                <div class="float-start col-md-3">
                    <?= htmlspecialchars($row['name'] . ' ' . $row['surname']) ?>
                </div>
                <div class="float-start col-md-2">
                    <?= date('d M Y', strtotime($row['created_at'])) ?>
                </div>
                <div class="float-start col-md-1">
                    <?= $row['totalItems'] ?>
                </div>
                <div class="float-start col-md-2">
                    £<?= number_format($row['total'], 2) ?>
                </div>
                <div class="float-start col-md-1">
                    <span class="badge <?= $status_class ?>">
                        <?= ucfirst($row['status']) ?>
                    </span>
                </div>
                <div class="float-start col-md-1">
                    <a href="order.php?id=<?= $row['id'] ?>" class="btn btn-primary">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                </div>
            </div>
        <?php endwhile; ?>

    </div>
    <?php renderPagination($totalPages, $page, $base_url); ?>
</section>