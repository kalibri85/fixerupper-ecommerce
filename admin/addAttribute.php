<?php
 /**
 *
 * @author Lana (Svetlana Muraveckaja-Odincova)
 */
    require_once __DIR__ .'/includes/init.php';

    requireAdmin();

    include('./includes/header.php');

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['name'])) {
        checkCSRF();
        $name = trim($_POST["name"]);
        $categories = $_POST['categories'] ?? [];
        if(!empty($name)) {
            $sql = $conn->prepare("INSERT INTO attributes (name) VALUES (?)");
            $sql->bind_param("s", $name);
            $sql->execute();

            $attribute_id = $sql->insert_id;
            foreach ($categories as $cat_id) {
                $cat_id = (int)$cat_id;
                $sql = $conn->prepare("
                    INSERT INTO attributes_category (categoryID, attributeID)
                    VALUES (?, ?)
                ");
                $sql->bind_param("ii", $cat_id, $attribute_id);
                $sql->execute();
            }

            redirect("attributes.php");
            exit;
        }
    }
?>     
<section id="titleSection" class="pt-3 pb-1">
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <h1>Add Attributes</h1>  
        </div> 
    </div>
</section> 
<section id="tableBody"> 
    <div class="container pb-3 pt-3">    
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Categories</label>
                <select name="categories[]" class="form-select" multiple>

                    <?php
                    $res = $conn->query("SELECT id, category FROM categories");

                    while ($row = $res->fetch_assoc()) {
                        echo "<option value='".(int)$row['id']."'>"
                            .htmlspecialchars($row['category'])."</option>";
                    }
                    ?>

                </select>
            </div>

            <button class="btn btn-primary">Save</button>
        </form>
    </div>
</section>
