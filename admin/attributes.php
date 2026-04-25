<?php
 /**
 *
 * @author Lana (Svetlana Muraveckaja-Odincova)
 */
    require_once __DIR__ .'/includes/init.php';

    requireAdmin();

    include('./includes/header.php');
    // Delete
    if(isset($_GET['delete'])) {
        $id = (int) $_GET['delete'];
        // Check if attribute is used for any product
        $check = $conn->prepare("SELECT COUNT(*) FROM attributes_product WHERE attributeID = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if($count == 0) {
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
    $sql = "SELECT a.id, a.name, GROUP_CONCAT(c.category SEPARATOR ', ') AS categories FROM attributes a
            LEFT JOIN attributes_category ca ON a.id = ca.attributeID
            LEFT JOIN categories c ON ca.categoryID = c.id
            GROUP BY a.id LIMIT $offset, $perPage";
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
        <div class="row">
            <div class="float-start col-md-5">Name</div>
            <div class="float-start col-md-5">Categories</div>
            <div class="float-start col-md-2">Actions</div>
        </div>
    </div>    
</section>
<section id="tableBody"> 
    <div class="container text-center">    
        <?php
        while ($row = $result->fetch_assoc()) {

            $check = $conn->prepare("
                SELECT COUNT(*) 
                FROM attributes_product 
                WHERE attributeID = ?
            ");
            $check->bind_param("i", $row['id']);
            $check->execute();
            $check->bind_result($count);
            $check->fetch();
            $check->close();
            $canDelete = $count == 0;
        
                echo "<div class='row pt-3 pb-3 item-row'>
                        <div class='float-start col-md-5'>".htmlspecialchars($row['name'])."</div>
                        <div class='float-start col-md-5'>".htmlspecialchars($row['categories'] ?? '-')."</div>
                        <div class='float-start col-md-2 text-center'>
                            <a href='attributeValues.php?attribute_id=".$row['id']."' class='btn btn-sm btn-primary'>
                                Values
                            </a>
                            <a href='?delete=".$row['id']."' class='btn btn-sm btn-danger ".(!$canDelete ? "disabled" : "")."' onclick='return confirm(\"Delete attribute? \")'><i class='fa-solid fa-trash-can'></i></a>
                        </div>
                    </div> ";  
        }         
?>
       
    </div>
    <?php renderPagination($totalPages, $page, 'attributes.php?'); ?>
</section>
