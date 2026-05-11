<?php
  /**
   * Admin — Feedback moderation
   * @author Lana (Svetlana Muraveckaja-Odincova)
   */
  require_once __DIR__ . '/includes/init.php';
  requireAdmin();
  include('./includes/header.php');

  // Approve
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve'])) {
      checkCSRF();
      $id = (int)($_POST['id'] ?? 0);
      if ($id > 0) {
          $stmt = $conn->prepare("UPDATE reviews SET status = 1 WHERE id = ?");
          $stmt->bind_param("i", $id);
          $stmt->execute();
      }
      redirect("feedback.php?status=" . ($_GET['status'] ?? '') . "&msg=approved");
  }

  // Delete
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
      checkCSRF();
      $id = (int)($_POST['id'] ?? 0);
      if ($id > 0) {
          $stmt = $conn->prepare("UPDATE reviews SET status = 2 WHERE id = ?");
          $stmt->bind_param("i", $id);
          $stmt->execute();
      }
      redirect("feedback.php?status=" . ($_GET['status'] ?? '') . "&msg=deleted");
  }

  // Filter
  $status_filter = $_GET['status'] ?? '0'; // default: pending
  $allowed = ['0', '1', '2', 'all'];
  if (!in_array($status_filter, $allowed)) $status_filter = '0';

  $where = $status_filter === 'all' ? '' : "WHERE r.status = $status_filter";

  // Pagination
  $total      = $conn->query("SELECT COUNT(*) as total FROM reviews r $where")->fetch_assoc()['total'];
  $pagination = paginate($total, 15);
  $page       = $pagination['page'];
  $perPage    = $pagination['perPage'];
  $offset     = $pagination['offset'];
  $totalPages = $pagination['totalPages'];

  // Reviews
  $result = $conn->query("
      SELECT
          r.id,
          r.rating,
          r.comment,
          r.status,
          r.created_at,
          u.name    AS user_name,
          u.surname AS user_surname,
          p.name    AS product_name,
          p.id      AS product_id
      FROM reviews r
      JOIN users u    ON u.id = r.userID
      JOIN products p ON p.id = r.productID
      $where
      ORDER BY r.created_at DESC
      LIMIT $offset, $perPage
  ");

  $base_url = 'feedback.php?status=' . $status_filter . '&';

  // Status labels
  $status_labels = [
      '0' => 'Pending',
      '1' => 'Approved',
      '2' => 'Deleted',
  ];
?>

<!-- Title -->
<section id="titleSection" class="pt-3 pb-1">
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <h1>Feedback</h1>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success">
                <?= $_GET['msg'] === 'approved' ? 'Review approved.' : 'Review deleted.' ?>
            </div>
        <?php endif; ?>

        <!-- Filter tabs -->
        <ul class="nav nav-pills mb-3">
            <li class="nav-item">
                <a class="nav-link <?= $status_filter === '0'   ? 'active' : '' ?>" href="feedback.php?status=0">Pending</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $status_filter === '1'   ? 'active' : '' ?>" href="feedback.php?status=1">Approved</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $status_filter === '2'   ? 'active' : '' ?>" href="feedback.php?status=2">Deleted</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $status_filter === 'all' ? 'active' : '' ?>" href="feedback.php?status=all">All</a>
            </li>
        </ul>
    </div>
</section>

<!-- Table header -->
<section id="tableHeader" class="pt-3 pb-3">
    <div class="container">
        <div class="row fw-semibold">
            <div class="col-md-2">Customer</div>
            <div class="col-md-3">Product</div>
            <div class="col-md-1 text-center">Rating</div>
            <div class="col-md-3">Comment</div>
            <div class="col-md-1">Date</div>
            <div class="col-md-1 text-center">Status</div>
            <div class="col-md-1 text-center">Actions</div>
        </div>
    </div>
</section>

<!-- Table body -->
<section id="tableBody">
    <div class="container">

        <?php if ($total === 0): ?>
            <div class="py-4 text-muted">No reviews found.</div>
        <?php endif; ?>

        <?php while ($row = $result->fetch_assoc()): ?>
            <?php
            $status_class = match((string)$row['status']) {
                '0' => 'bg-warning text-dark',
                '1' => 'bg-success',
                '2' => 'bg-secondary',
                default => 'bg-secondary'
            };
            ?>
            <div class="row pt-3 pb-2 item-row align-items-center">

                <div class="col-md-2">
                    <?= htmlspecialchars($row['user_name'] . ' ' . $row['user_surname']) ?>
                </div>

                <div class="col-md-3">
                    <a href="../product.php?id=<?= $row['product_id'] ?>" target="_blank">
                        <?= htmlspecialchars($row['product_name']) ?>
                    </a>
                </div>

                <div class="col-md-1 text-center">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fa-solid fa-star fa-xs <?= $i <= $row['rating'] ? 'star-filled' : 'star-empty' ?>"></i>
                    <?php endfor; ?>
                </div>

                <div class="col-md-3">
                    <small><?= htmlspecialchars($row['comment'] ?: '—') ?></small>
                </div>

                <div class="col-md-1">
                    <small><?= date('d M Y', strtotime($row['created_at'])) ?></small>
                </div>

                <div class="col-md-1 text-center">
                    <span class="badge <?= $status_class ?>">
                        <?= $status_labels[(string)$row['status']] ?>
                    </span>
                </div>

                <div class="col-md-1 text-center d-flex gap-1 justify-content-center">
                    <?php if ($row['status'] != 1): ?>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button name="approve" class="btn btn-success btn-sm"
                                    title="Approve">
                                <i class="fa-solid fa-check"></i>
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if ($row['status'] != 2): ?>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button name="delete" class="btn btn-danger btn-sm"
                                    title="Delete"
                                    onclick="return confirm('Delete this review?')">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

            </div>
        <?php endwhile; ?>

    </div>
    <?php renderPagination($totalPages, $page, $base_url); ?>
</section>