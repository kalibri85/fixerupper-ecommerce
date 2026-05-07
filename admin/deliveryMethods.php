<?php
require_once __DIR__ . '/includes/init.php';
requireAdmin();
include('./includes/header.php');

$message = '';

/* ADD */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    checkCSRF();

    $name  = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);

    if ($name === '') {
        $message = "Name cannot be empty.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO delivery_method (title, price, active)
            VALUES (?, ?, 0)
        ");
        $stmt->bind_param("sd", $name, $price);
        $stmt->execute();

        redirect("deliveryMethods.php?success=1");
        exit;
    }
}

/* SAVE ACTIVE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_active'])) {
    checkCSRF();

    $ids = $_POST['active_ids'] ?? [];
    $ids = array_map('intval', $ids);

    $conn->query("
        UPDATE delivery_method
        SET active = 0
        WHERE active != 2
    ");

    if (!empty($ids)) {
        $in = implode(',', $ids);

        $conn->query("
            UPDATE delivery_method
            SET active = 1
            WHERE id IN ($in)
            AND active != 2
        ");
    }

    redirect("deliveryMethods.php?msg=updated");
    exit;
}

/* UPDATE PRICE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    checkCSRF();

    $id = (int)($_POST['update'] ?? 0);
    $price = (float)($_POST['prices'][$id] ?? 0);

    if ($id > 0) {
        $stmt = $conn->prepare("
            UPDATE delivery_method
            SET price = ?
            WHERE id = ?
        ");
        $stmt->bind_param("di", $price, $id);
        $stmt->execute();

        redirect("deliveryMethods.php?msg=updated");
        exit;
    }
}

/* DELETE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    checkCSRF();

    $id = (int)($_POST['delete'] ?? 0);

    if ($id > 0) {
        $stmt = $conn->prepare("
            UPDATE delivery_method
            SET active = 2
            WHERE id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        redirect("deliveryMethods.php?msg=deleted");
        exit;
    }
}

/* GET DATA */
$result = $conn->query("
    SELECT id, title, price, active
    FROM delivery_method
    WHERE active != 2
    ORDER BY id DESC
");
?>

<section id="titleSection" class="pt-3 pb-1">
    <div class="container">
        <h1>Delivery Methods</h1>

        <?php if (!empty($message)): ?>
            <div class="alert alert-info mt-3">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-info mt-3">
                <?php
                $msgs = [
                    'deleted' => 'Delivery method deleted.',
                    'updated' => 'Delivery method updated.'
                ];
                echo $msgs[$_GET['msg']] ?? '';
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success mt-3">
                Delivery method added successfully.
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ADD -->
<section class="pb-4">
    <div class="container">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">

            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Method Name</label>
                    <input type="text"
                           name="name"
                           class="form-control"
                           maxlength="255"
                           required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Price (£)</label>
                    <input type="number"
                           name="price"
                           class="form-control"
                           step="0.01"
                           min="0"
                           value="0">
                </div>

                <div class="col-md-2">
                    <button type="submit"
                            name="add"
                            class="btn btn-primary w-100">
                        Add Method
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- TABLE -->
<section>
    <div class="container">

        <div class="row fw-bold border-bottom pb-2 mb-2 text-center">
            <div class="col-md-3">Name</div>
            <div class="col-md-2">Price</div>
            <div class="col-md-4">Edit price</div>
            <div class="col-md-1">Active</div>
            <div class="col-md-1">Edit</div>
            <div class="col-md-1">Delete</div>
        </div>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">

            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="row align-items-center py-3 border-bottom text-center">

                    <!-- NAME -->
                    <div class="col-md-3">
                        <?= htmlspecialchars($row['title']) ?>
                    </div>

                    <!-- PRICE -->
                    <div class="col-md-2">
                        <?= $row['price'] > 0
                            ? '£' . number_format($row['price'], 2)
                            : 'Free' ?>
                    </div>

                    <!-- INLINE EDIT -->
                    <div class="col-md-4">
                        <div id="edit-form-<?= $row['id'] ?>" class="mt-2 hidden">
                            <div class="d-flex gap-2 align-items-center">
                                <input type="number"
                                       name="prices[<?= $row['id'] ?>]"
                                       value="<?= $row['price'] ?>"
                                       step="0.01"
                                       min="0"
                                       class="form-control form-control-sm">

                                <button type="submit"
                                        name="update"
                                        value="<?= $row['id'] ?>"
                                        class="btn btn-success btn-sm">
                                    Save
                                </button>

                                <button type="button"
                                        class="btn btn-secondary btn-sm cancel-btn"
                                        data-id="<?= $row['id'] ?>">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ACTIVE -->
                    <div class="col-md-1">
                        <input type="checkbox"
                               name="active_ids[]"
                               value="<?= $row['id'] ?>"
                               class="form-check-input"
                               <?= $row['active'] == 1 ? 'checked' : '' ?>>
                    </div>

                    <!-- EDIT BTN -->
                    <div class="col-md-1">
                        <button type="button"
                                class="btn btn-link p-0 text-primary edit-btn"
                                data-id="<?= $row['id'] ?>">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                    </div>

                    <!-- DELETE -->
                    <div class="col-md-1">
                        <button type="submit"
                                name="delete"
                                value="<?= $row['id'] ?>"
                                class="btn btn-danger btn-sm delete-btn"
                                data-confirm="Delete delivery method?">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>

            <div class="text-end mt-4">
                <button type="submit"
                        name="save_active"
                        class="btn btn-primary">
                    Set As Active
                </button>
            </div>
        </form>
    </div>
</section>

<script src="./js/inline-edit.js"></script>