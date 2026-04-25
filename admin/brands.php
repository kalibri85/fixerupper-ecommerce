<?php
 /**
 *
 * @author Lana (Svetlana Muraveckaja-Odincova)
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
    require_once __DIR__ .'/includes/init.php';

    requireAdmin();

    include('./includes/header.php');

    $message = "";
    // Abb Brand
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add'])) {
        checkCSRF();
        $input = trim($_POST["name"]) ?? '';
        
        if($input === '') {
            $message = "Name cannot be empty";
        } else {    
            $brands = parseMultiInput($input);

            if (empty($brands)) {
                $message = "No valid brands";
            } else{
                $sql = $conn->prepare("INSERT INTO brands (name) VALUES (?)");
                $conn->begin_transaction();

                try {
                    foreach ($brands as $b) {
                        if($b === '' || strlen($b) > 255) continue;

                        $sql->bind_param("s", $b);
                        $sql->execute();
                    }

                    $conn->commit();
                    redirect("brands.php?success=1");
                } catch (Exception $e) {
                    $conn->rollback();
                    $message = "Error while saving brands";
                }
                $sql->close();
            }
        }
    }

      // Update value
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {

        checkCSRF();

        $id = (int)($_POST['id'] ?? 0);
        $value = trim($_POST['value'] ?? '');

        if ($id > 0 && $value !== '') {

            if (strlen($value) > 255) redirect("brands.php?id=$id&msg=too_long");

            $sql = $conn->prepare("UPDATE brands SET name = ? WHERE id = ?");
            $sql->bind_param("si", $value, $id);
            $sql->execute();
            $sql->close();

            redirect("brands.php?id=$id&msg=updated");
        }
    }
    // Delete Brand
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {

        checkCSRF();

        $id = (int)($_POST['id'] ?? 0);

        if ($id > 0) {
            // Check if attribute is used for any product
            $check = $conn->prepare("SELECT COUNT(*) FROM products WHERE brandID = ?");
            $check->bind_param("i", $id);
            $check->execute();
            $check->bind_result($count);
            $check->fetch();
            $check->close();

            if($count == 0) {
                // Delete connections with categories
                $sql = $conn->prepare("DELETE FROM brands WHERE id = ?");
                $sql->bind_param("i", $id);
                $sql->execute();
                $sql->close();

                redirect("brands.php?msg=deleted");
            } else {
                redirect("brands.php?msg=used");
            }
        }
    }  

    // Pagination
    $resultTotal = $conn->query("SELECT COUNT(*) as total FROM brands");
    $total = $resultTotal->fetch_assoc()['total'];

    $pagination = paginate($total, 5);
    $page = $pagination['page'];
    $perPage = $pagination['perPage'];
    $offset = $pagination['offset'];
    $totalPages = $pagination['totalPages'];

    // Get Data
    $sql = "SELECT b.id, b.name, COUNT(p.id) as usage_count FROM brands b
            LEFT JOIN products p ON p.brandID = b.id GROUP BY b.id LIMIT $offset, $perPage";

    $result = $conn->query($sql);  
?>   
<!-- Title -->
<section id="titleSection" class="pt-3 pb-1">
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <h1>Brands</h1>
        </div> 
    </div>
    <div class="container">
        <?php if(!empty($message)): ?>
            <div class='alert alert-info'><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-info">
                <?php
                    $messages = [
                        'deleted' => 'Value deleted',
                        'used' => 'Cannot delete: value is used in products',
                        'updated' => 'Brand updated',
                        'too_long' => 'Name too long'
                    ];
                    echo $messages[$_GET['msg']] ?? '';
                ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Brand added successfully</div>
        <?php endif; ?> 
    </div>    
</section>
<!-- Add form -->
<section id="addBrandForm">  
    <div class="container text-center">
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
            <div class="row"> 
                <div class="col-mb-12 text-start">
                    <textarea name="name" class="form-control" placeholder="e.g. Bosh|Makita|AEG"></textarea>
                    <small class="text-muted">
                        You can add multiple brands separated by |
                    </small>
                </div>
            </div> 
            <div class="row pt-3 pb-3">  
                <div class="col-md-5 text-start">
                    <button type="submit" name="add" class="btn btn-primary">Add Brand</button>
                </div>
            </div>  
        </form>
    </div>
</section>  
<!-- Table header -->
<section id="tableHeader" class="pt-3 pb-3">
    <div class="container text-center">
        <div class="row">
            <div class="float-start col-md-3">Name</div>
            <div class="float-start col-md-7"></div>
            <div class="float-start col-md-1">Edit</div>
            <div class="float-start col-md-1">Delete</div>
        </div>
    </div>    
</section>
<!-- Table body -->
<section id="tableBody"> 
    <div class="container text-center">    
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class='row pt-3 pb-1 item-row'>
                <div class='col-md-3'>
                    <?= htmlspecialchars($row['name']) ?> 
                    <small class='text-muted'>(used: <?= $row['usage_count'] ?>)</small>
                </div>
                <div class='col-md-7'>
                    <form method='POST' id="edit-form-<?= $row['id'] ?>" class=' mt-2 hidden'>
                        <input type='hidden' name='csrf_token' value='<?= csrf() ?>'>
                        <input type='hidden' name='id' value="<?= (int)$row['id'] ?>">
                        <div class='d-flex gap-2 align-items-center'>
                            <input type='text' name='value' class='form-control form-control-sm' value="<?= htmlspecialchars($row['name']) ?>">
                            <button type='submit' name='update' class='btn btn-success btn-sm'>
                                Update
                            </button>

                            <button type='button' class='btn btn-secondary btn-sm cancel-btn' data-id="<?= $row['id'] ?>">
                                Cancel
                            </button>
                        </div>
                    </form> 
                </div>
                <div class='float-start col-md-1'>
                    <button type='button' class='btn btn-link p-0 text-primary edit-btn' data-id="<?= $row['id'] ?>">
                        <i class='fa-solid fa-pen-to-square'></i>
                    </button>
                    
                </div>
                <div class='float-start col-md-1'>    
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">

                        <button name="delete"
                                class="btn btn-danger btn-sm"
                                <?= $row['usage_count'] > 0 ? 'disabled' : '' ?>>
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>    
    </div>
    <?php renderPagination($totalPages, $page, 'brands.php?'); ?>
</section>
<script src="./js/inline-edit.js"></script>