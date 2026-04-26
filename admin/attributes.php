<?php
 /**
 *
 * @author Lana (Svetlana Muraveckaja-Odincova)
 */
    require_once __DIR__ .'/includes/init.php';

    requireAdmin();

    include('./includes/header.php');
    // Delete
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
        checkCSRF();

        $id = (int) $_POST['delete'];

        // Check if attribute is used in products
        $check = $conn->prepare("SELECT COUNT(*) FROM attributes_product WHERE attributeID = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count == 0) {
            // Delete connections with categories
            $sql = $conn->prepare("DELETE FROM attributes_category WHERE attributeID = ?");
            $sql->bind_param("i", $id);
            $sql->execute();

            // Delete attribute values
            $sql = $conn->prepare("DELETE FROM attribute_values WHERE attributeID = ?");
            $sql->bind_param("i", $id);
            $sql->execute();

            // Delete attribute
            $sql = $conn->prepare("DELETE FROM attributes WHERE id = ?");
            $sql->bind_param("i", $id);
            $sql->execute();
        }

        header("Location: attributes.php");
        exit;
    }

    // Pagination
    $resultTotal = $conn->query("SELECT COUNT(*) as total FROM attributes");
    $total = $resultTotal->fetch_assoc()['total'];

    $pagination = paginate($total, 5);
    $page = $pagination['page'];
    $perPage = $pagination['perPage'];
    $offset = $pagination['offset'];
    $totalPages = $pagination['totalPages'];

    // Get Data
    $sql = "
        SELECT 
            a.id, 
            a.name, 
            GROUP_CONCAT(DISTINCT c.category SEPARATOR ', ') AS categories,
            COUNT(DISTINCT ap.attributeID) AS used_count
        FROM attributes a
        LEFT JOIN attributes_category ca ON a.id = ca.attributeID
        LEFT JOIN categories c ON ca.categoryID = c.id
        LEFT JOIN attributes_product ap ON a.id = ap.attributeID
        GROUP BY a.id
        LIMIT $offset, $perPage
    ";

    $result = $conn->query($sql);
?>   

<section id="titleSection" class="pt-3 pb-1">
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <h1>Attributes</h1>

            <a href="addAttribute.php" class="btn btn-success">
                <i class="fa-solid fa-plus"></i> Add Attribute
            </a>
        </div>
    </div>
</section>

<section id="tableHeader" class="pt-3 pb-3">
    <div class="container text-center">
        <div class="row fw-bold">
            <div class="col-md-4">Name</div>
            <div class="col-md-4">Categories</div>
            <div class="col-md-1">Edit</div>
            <div class="col-md-2"></div>
            <div class="col-md-1"></div>
        </div>
    </div>
</section>

<section id="tableBody">
    <div class="container text-center">

        <?php while ($row = $result->fetch_assoc()): 
            $canDelete = ($row['used_count'] == 0);
        ?>

        <div class="row pt-3 pb-3 item-row align-items-center">

            <!-- NAME -->
            <div class="col-md-4">
                <?= htmlspecialchars($row['name']) ?>
            </div>

            <!-- CATEGORIES -->
            <div class="col-md-4">
                <?= htmlspecialchars($row['categories'] ?? '-') ?>
            </div>
            <!-- ACTIONS -->
            <div class="col-md-1">
                <!-- EDIT -->
                <a href="editAttribute.php?id=<?= $row['id'] ?>"><i class='fa-solid fa-pen-to-square'></i></a>
            </div>

            <div class="col-md-2">
                <!-- VALUES -->
                <a href="attributeValues.php?attribute_id=<?= $row['id'] ?>" 
                   class="btn btn-sm btn-primary">
                    Values
                </a>
            </div>
            <div class="col-md-1">
                <!-- DELETE -->
                <form method="POST">
                    <input type="hidden" name="delete" value="<?= $row['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                    <button class="btn btn-danger delete-btn <?= !$canDelete ? 'disabled' : '' ?>"
                            <?= !$canDelete ? 'title="Attribute is used in products"' : '' ?>
                             data-confirm="Delete attribute?">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </form>

            </div>

        </div>

        <?php endwhile; ?>

    </div>

    <?php renderPagination($totalPages, $page, 'attributes.php?'); ?>
</section>