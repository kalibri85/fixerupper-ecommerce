 <?php
    /**
     *
     * @author Lana (Svetlana Muraveckaja-Odincova)
     */

    require_once __DIR__ .'/includes/init.php';

    requireAdmin();

    include('./includes/header.php');
  
    $message = "";
    // Get attribute by ID
    if (!isset($_GET['attribute_id']) || !is_numeric($_GET['attribute_id'])) {
        redirect("attributes.php");
    }
    $attributeID = (int)$_GET['attribute_id'];

    // Get attribute
    $sql = $conn->prepare("SELECT * FROM attributes WHERE id = ?");
    $sql->bind_param("i", $attributeID);
    $sql->execute();
    $result = $sql->get_result();

    if ($result->num_rows === 0) redirect("attributes.php");
    $attribute = $result->fetch_assoc();
    $sql->close();

    // Add values
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add'])) {
        checkCSRF();
        
       $value = trim($_POST['value'] ?? '');

       if ($value === '') {
            $error = "Value cannot be empty";
       } else {
            // Break down the row
            $values = parseMultiInput($value);
            // Clean spaces
            $values = array_map(fn($v) => trim($v), $values);
            // Clean empty
            $values = array_filter($values);
            // Clean duplication
            $values = array_unique($values);
            if(empty($values)) {
                $message = "No valid values";
            } else {
                $sql = $conn->prepare("INSERT INTO attribute_values (attributeID, value) VALUES (?, ?)");

                $conn->begin_transaction();

                try {
                    foreach ($values as $v) {
                        if($v === '' || strlen($v) > 255) continue;
                        $sql->bind_param("is", $attributeID, $v);
                        $sql->execute();
                    }
                    $conn->commit();
                    redirect("attributeValues.php?attribute_id=$attributeID&success=1");
                } catch (Exception $e) {
                    $conn->rollback();
                    $message = "Error while saving values";
                }
                $sql->close();
            }
       }
    }
    // Edit value
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {

        checkCSRF();

        $id = (int)($_POST['id'] ?? 0);
        $value = trim($_POST['value'] ?? '');

        if ($id > 0 && $value !== '') {

            if (strlen($value) > 255) redirect("attributeValues.php?attribute_id=$attributeID&msg=too_long");

            $sql = $conn->prepare("UPDATE attribute_values  SET value = ? WHERE id = ? AND attributeID = ?");
            $sql->bind_param("sii", $value, $id, $attributeID);
            $sql->execute();
            $sql->close();

            redirect("attributeValues.php?attribute_id=$attributeID&msg=updated");
        }
    }
    // Delete value
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
        checkCSRF();

        $valueID = (int)($_POST['id'] ?? 0);

        if ($valueID > 0) {
            $sql = $conn->prepare("SELECT COUNT(*) FROM attributes_product WHERE valueID = ?");
            $sql->bind_param("i", $valueID);
            $sql->execute();
            $sql->bind_result($count);
            $sql->fetch();
            $sql->close();

            if ($count > 0) redirect("attributeValues.php?attribute_id=$attributeID&msg=used");
            $sql = $conn->prepare("DELETE FROM attribute_values WHERE id = ?");
            $sql->bind_param("i", $valueID);
            $sql->execute();
            $sql->close();

            redirect("attributeValues.php?attribute_id=$attributeID&msg=deleted");
        }
    }   
    
    // Pagination
    // Count total
    $sql = $conn->prepare("SELECT COUNT(*) FROM attribute_values WHERE attributeID = ?");
    $sql->bind_param("i", $attributeID);
    $sql->execute();
    $sql->bind_result($total);
    $sql->fetch();
    $sql->close();

    $pagination = paginate($total, 5);
    $page = $pagination['page'];
    $perPage = $pagination['perPage'];
    $offset = $pagination['offset'];
    $totalPages = $pagination['totalPages'];

    // Get values
    $sql = $conn->prepare("SELECT atv.id, atv.value, COUNT(ap.id) as usage_count FROM attribute_values atv
    LEFT JOIN attributes_product ap ON ap.valueID = atv.id WHERE atv.attributeID = ? GROUP BY atv.id LIMIT $offset, $perPage");
    $sql->bind_param("i", $attributeID);
    $sql->execute();
    $values = $sql->get_result();
?>
<section id="titleSection" class="pt-3 pb-1">
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <h1>Attribute: <?= htmlspecialchars($attribute['name']) ?></h1>
        </div> 
    </div>
    <div class="container">
        <?php
            if(!empty($message)){
                echo "<div class='alert alert-info'>".htmlspecialchars($message)."</div>";
            }
        ?>
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-info">
                <?php
                    $messages = [
                        'deleted' => 'Value deleted',
                        'used' => 'Cannot delete: value is used in products'
                    ];
                    echo $messages[$_GET['msg']] ?? '';
                ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Values added successfully</div>
        <?php endif; ?> 
    </div>            
</section>
<section id="addValuesForm">  
    <div class="container text-center">
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
            <div class="row"> 
                <div class="col-mb-12 text-start">
                    <input type="text" name="value" class="form-control" placeholder="e.g. Red|Blue|Green">
                    <small class="text-muted">
                        You can add multiple values separated by |
                    </small>
                </div>
            </div> 
            <div class="row pt-3 pb-3">  
                <div class="col-md-5 text-start">
                    <button type="submit" name="add" class="btn btn-primary">Add Values</button>
                </div>
            </div>  
        </form>
    </div>
</section>
<!-- Values list -->
<!-- Table header -->
<section id="tableHeader" class="pt-2 pb-2">
    <div class="container text-center">
        <div class="row g-0">
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
        <?php while ($row = $values->fetch_assoc()): ?>   
            <div class='row pt-3 pb-1 item-row'>
                <div class="col-md-3">
                    <span id="text-<?= $row['id'] ?>">
                        <?= htmlspecialchars($row['value']) ?>
                    </span>    
                    <small class="text-muted">(used: <?= $row['usage_count'] ?>)</small>
                </div>
                <div class="col-md-7">
                    <form method="POST" id="edit-form-<?= $row['id'] ?>" class="hidden mt-2">
                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                        <div class="d-flex gap-2 align-items-center">
                            <input type="text" name="value" class="form-control form-control-sm" value="<?= htmlspecialchars($row['value']) ?>">
                            <button type='submit' name='update' class='btn btn-success btn-sm'>
                                Update
                            </button>

                            <button type='button' class='btn btn-secondary btn-sm cancel-btn' data-id="<?= $row['id'] ?>">
                                Cancel
                            </button>
                        </div>
                    </form>    
                </div>
                <div class="col-md-1">  
                    <button type='button' class='btn btn-link p-0 text-primary edit-btn' data-id="<?= $row['id'] ?>">
                        <i class='fa-solid fa-pen-to-square'></i>
                    </button>
                </div>     
                <div class="col-md-1">
                    <!-- DELETE -->
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">

                        <button type="submit" name="delete" class="btn btn-danger btn-sm delete-btn" <?= $row['usage_count'] > 0 ? 'disabled' : '' ?> data-confirm="Delete attribute's value?">
                            <i class='fa-solid fa-trash-can'></i>
                        </button>


                        
                    </form>
                </div>
            </div>
        <?php endwhile; ?>       
    </div>    
    <?php renderPagination($totalPages, $page, 'attributeValues.php?attribute_id='.$attributeID.'&'); ?>   
</section>
<script src="./js/inline-edit.js"></script>