<?php
 /**
 *
 * @author Lana (Svetlana Muraveckaja-Odincova)
 */
    require_once __DIR__ . '/includes/init.php';

    requireAdmin();

    include('./includes/header.php');
   
    //Save selected categories as active
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["mark"])) {
        checkCSRF();
        $activeIDs = isset($_POST["active"]) ? array_map('intval', $_POST['active']) : [];

        $conn->query("UPDATE categories SET active = 0");

        if(!empty($activeIDs)) {
            $ids = implode(',', $activeIDs);

            $conn->query("UPDATE `categories` SET active = 1 WHERE `id` IN ($ids)");
        }

        redirect("categories.php?page=$page&updated=1");
        exit;       
    }
    // Delete Category
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {

        // CSRF
        checkCSRF();
        
        $id = intval($_POST['id']);

        if ($id <= 0) {
            redirect("categories.php?msg=invalid_id");
            exit;
        }

        // Check if products in this category exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE categoryID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            redirect("categories.php?msg=has_products&page=$page");
            exit;
        }

        // Check if this category has subcategories
        $stmt = $conn->prepare("SELECT COUNT(*) FROM categories WHERE parent = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($childCount);
        $stmt->fetch();
        $stmt->close();

        if ($childCount > 0) {
            redirect("categories.php?msg=has_children&page=$page");
            exit;
        }

        $conn->begin_transaction();
        try {
            // Deleting attributes
            $stmt = $conn->prepare("DELETE FROM attributes_category WHERE categoryID = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            // Deleting category
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            $conn->commit();

            redirect("categories.php?msg=deleted&page=$page");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            redirect("categories.php?msg=error&page=$page");
            exit;
        }        
    }
    // Paggination
    $resultTotal = $conn->query("SELECT COUNT(*) as total FROM categories");
    $total = $resultTotal->fetch_assoc()['total'];
    $pagination = paginate($total, 5);

    $page = $pagination['page'];
    $perPage = $pagination['perPage'];
    $offset = $pagination['offset'];
    $totalPages = $pagination['totalPages'];

    // Get Data
    $sql = "SELECT c.id, c.category, c.active, p.category AS parent_name, COUNT(prod.id) as product_count FROM categories c 
            LEFT JOIN categories p ON c.parent = p.id LEFT JOIN products prod ON prod.categoryID = c.id GROUP BY c.id LIMIT $offset, $perPage";

    $result = $conn->query($sql);
?>
<section id="titleSection" class="pt-3 pb-1">
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <h1>Categories</h1>
            <a href="addCategory.php" class="btn btn-success">
                <i class="fa-solid fa-plus"></i> Add Category
            </a>    
        </div> 
    </div>
    <?php if (isset($_GET['msg'])): ?>
        <div class="container mt-5 pt-4">
            <div class="alert alert-info">
                <?php
                    $messages = [
                        'deleted' => 'Category deleted',
                        'has_products' => 'Cannot delete: category has products',
                        'has_children' => 'Cannot delete: category has subcategories',
                        'csrf_error' => 'Security error',
                        'invalid_id' => 'Invalid ID',
                        'error' => 'Something went wrong'
                    ];
                    echo $messages[$_GET['msg']] ?? 'Unknown error';
                ?>
            </div>
        </div> 
    <?php endif; ?>                 
</section>

<!-- Table header -->
<section id="tableHeader" class="pt-2 pb-2">
    <div class="container text-center">
        <div class="row">
            <div class="float-start col-md-3">Category Name</div>
            <div class="float-start col-md-4">Parent Category Name</div>
            <div class="float-start col-md-1">Products</div>
            <div class="float-start col-md-2">Active</div>
            <div class="float-start col-md-1">Edit</div>
            <div class="float-start col-md-1">Delete</div>
        </div>
    </div>    
</section>
<!-- Table body -->
<section id="tableBody"> 
    <div class="container text-center">
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars("Categories updated successfully") ?>
            </div>
        <?php endif; ?>    
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">       
            <?php
                while ($row = $result->fetch_assoc()) {
                    $isActive = $row['active'] == 1 ? "checked" : "";
                    $canDelete = ($row['product_count'] == 0);
                    echo "<div class='row pt-3 pb-1 item-row'>
                            <div class='float-start col-md-3'>".htmlspecialchars($row['category'])."</div>
                            <div class='float-start col-md-4'>".htmlspecialchars($row['parent_name'] ?? '—')."</div>
                            <div class='col-md-1'>".$row['product_count']."</div>
                            <div class='float-start col-md-2'>
                                <input class='form-check-input' type='checkbox' name='active[]' value ='".(int)$row['id']."' ".$isActive.">
                            </div>
                            <div class='float-start col-md-1 text-center'><a href='editCategory.php?id=".$row['id']."'><i class='fa-solid fa-pen-to-square'></i></a></div>
                            <div class='float-start col-md-1'>
                                <button type='button' class='btn btn-danger btn-sm delete-btn' data-id='".$row['id']."'>
                                    <i class='fa-solid fa-trash-can'></i>
                                </button>
                            </div>
                        </div>";  
                }
            ?>
            <div class="text-end pt-3 pb-3">
                <button type="submit" name="mark" class="btn btn-primary">Set As Active</button>
            </div>
        </form>
    </div>
     <?php renderPagination($totalPages, $page, 'categories.php?'); ?>    
</section>


<script src="./js/categories.js"></script>