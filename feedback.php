<?php
    /**
     * Leave Feedback — review products from completed orders
     * @author Lana (Svetlana Muraveckaja-Odincova)
     */
    require_once __DIR__ . '/includes/init.php';
    requireCustomer();

    $message = '';
    $error   = '';

    // Submit review
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
        checkCSRF();

        $productID = (int)($_POST['productID'] ?? 0);
        $orderID   = (int)($_POST['orderID']   ?? 0);
        $rating    = (int)($_POST['rating']    ?? 0);
        $comment   = trim($_POST['comment']    ?? '');

        if ($rating < 1 || $rating > 5) {
            $error = "Please select a rating.";
        } elseif (strlen($comment) > 1000) {
            $error = "Comment is too long (max 1000 characters).";
        } else {
            // Verify product belongs to a completed order by this customer
            $check = $conn->prepare("
                SELECT oi.id
                FROM order_items oi
                JOIN orders o ON o.id = oi.orderID
                WHERE oi.productID = ?
                AND oi.orderID   = ?
                AND o.userID     = ?
                AND o.status     = 'completed'
            ");
            $check->bind_param("iii", $productID, $orderID, $_SESSION['customer_id']);
            $check->execute();
            $check->store_result();

            if ($check->num_rows === 0) {
                $error = "You can only review products from completed orders.";
            } else {
                // Insert review — ignore if already reviewed
                $stmt = $conn->prepare("
                    INSERT IGNORE INTO reviews (userID, productID, orderID, rating, comment)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("iiiis",
                    $_SESSION['customer_id'],
                    $productID,
                    $orderID,
                    $rating,
                    $comment
                );
                $stmt->execute();
                $message = "Thank you for your feedback!";
            }
        }
    }

    // Load products pending review
    // Products from completed orders that haven't been reviewed yet
    $stmt = $conn->prepare("
        SELECT DISTINCT
            p.id,
            p.name,
            p.image,
            o.id AS orderID
        FROM order_items oi
        JOIN orders o   ON o.id  = oi.orderID
        JOIN products p ON p.id  = oi.productID
        WHERE o.userID  = ?
        AND o.status  = 'completed'
        AND NOT EXISTS (
            SELECT 1 FROM reviews r
            WHERE r.userID    = ?
                AND r.productID = p.id
        )
        ORDER BY o.created_at DESC
    ");
    $stmt->bind_param("ii", $_SESSION['customer_id'], $_SESSION['customer_id']);
    $stmt->execute();
    $products = $stmt->get_result();

    include('./includes/header.php');
?>

<section class="py-5">
    <div class="container">

        <nav class="breadcrumbs mb-4">
            <a href="index.php">Home</a> / <span>Leave Feedback</span>
        </nav>

        <h1 class="mb-4">Leave Feedback</h1>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($products->num_rows === 0): ?>
            <div class="card p-5 text-center">
                <p class="text-muted mb-3">
                    No products to review yet.<br>
                    Reviews are available for products from completed orders.
                </p>
                <a href="my_orders.php" class="btn-cta d-inline-block mx-auto">My Orders</a>
            </div>
        <?php else: ?>

            <div class="accordion" id="feedbackAccordion">
                <?php while ($product = $products->fetch_assoc()): ?>
                    <?php $collapse_id = 'product_' . $product['id'] . '_' . $product['orderID']; ?>

                    <div class="accordion-item mb-2">

                        <!-- Product header -->
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#<?= $collapse_id ?>">
                                <div class="d-flex align-items-center gap-3">
                                    <?php if ($product['image']): ?>
                                        <img src="./img/products/<?= htmlspecialchars($product['image']) ?>"
                                             width="48" height="48" class="rounded" loading="lazy"
                                             alt="<?= htmlspecialchars($product['name']) ?>">
                                    <?php endif; ?>
                                    <div>
                                        <div><?= htmlspecialchars($product['name']) ?></div>
                                        <small class="text-muted">
                                            Order <?= orderNumber($product['orderID']) ?>
                                        </small>
                                    </div>
                                </div>
                            </button>
                        </h2>

                        <!-- Review form -->
                        <div id="<?= $collapse_id ?>" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <form method="POST" class="needs-validation" novalidate>
                                    <input type="hidden" name="csrf_token"  value="<?= csrf() ?>">
                                    <input type="hidden" name="productID"   value="<?= $product['id'] ?>">
                                    <input type="hidden" name="orderID"     value="<?= $product['orderID'] ?>">

                                    <!-- Star rating -->
                                    <div class="mb-3">
                                        <label class="form-label">Rating <span class="text-danger">*</span></label>
                                        <div class="star-rating">
                                            <?php for ($i = 5; $i >=1; $i--): ?>
                                                <input type="radio"
                                                       name="rating"
                                                       id="star_<?= $collapse_id ?>_<?= $i ?>"
                                                       value="<?= $i ?>"
                                                       required>
                                                <label for="star_<?= $collapse_id ?>_<?= $i ?>">
                                                    <i class="fa-regular fa-star fa-2x"></i>
                                                </label>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="invalid-feedback d-block">Please select a rating.</div>
                                    </div>

                                    <!-- Comment -->
                                    <div class="mb-3">
                                        <label class="form-label">Comment</label>
                                        <textarea name="comment" class="form-control" rows="3"
                                                  maxlength="1000"
                                                  placeholder="Share your experience..."></textarea>
                                        <small class="text-muted">Optional, max 1000 characters.</small>
                                    </div>

                                    <button type="submit" name="submit_review" class="btn-cta">
                                        Submit Review
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                <?php endwhile; ?>
            </div>

        <?php endif; ?>

    </div>
</section>

<?php include('includes/footer.php'); ?>