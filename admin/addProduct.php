 <?php
    /**
     *
     * @author Lana (Svetlana Muraveckaja-Odincova)
     */
    require_once __DIR__ . '/includes/init.php';

    requireAdmin();

    include('./includes/header.php');
  
    // Get Brands
    $brands = [];

    $res = $conn->query("SELECT id, name FROM brands ORDER BY name ASC");

    if ($res) {
        $brands = $res->fetch_all(MYSQLI_ASSOC);
    }
// Save new product information to database
    if ($_SERVER["REQUEST_METHOD"] === 'POST') {
        
        checkCSRF();
        $conn->begin_transaction();

        try{
            $title = trim($_POST['title']);
            $category = $_POST['category'] != '' ? (int)$_POST['category'] : null;
            $brand = $_POST['brand'] != '' ? (int)$_POST['brand'] : null;
            $desc = trim($_POST['description']);
            $price = $_POST['price'] !== '' ? (float)$_POST['price'] : 0;
            $sku= trim($_POST['sku']);
            $qty= $_POST['qty'] !== '' ? (int)$_POST['qty'] : 0;
           // Image Upload
           $photo = null;
           $photo = uploadImage($_FILES['image'], __DIR__ . "/../img/products/");
           $status = isset($_POST['publish']) ? 1 : 0;
           // Insert Product
           $sql = $conn->prepare("INSERT INTO products 
                (`categoryID`, `brandID`, `sku`, `name`, `description`, `image`, `price`, `qty_inventory`, `status`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
           $sql->bind_param("iissssdii", $category, $brand, $sku, $title, $desc, $photo, $price, $qty, $status);
           $sql->execute();
           $productID = $conn->insert_id;
           $sql->close();

           // Attributes + Variation (Limit to 1)
           //$hasVariation = false;

           if (!empty($_POST['attributes'])) {
                foreach ($_POST['attributes'] as $attrID => $values) {
                    foreach ($values as $valueID) {
                        $sql = $conn->prepare("INSERT INTO attributes_product (productID, attributeID, valueID) VALUES (?, ?, ?)");
                        $sql->bind_param("iii", $productID, $attrID, $valueID);
                        $sql->execute();
                        $sql->close();
                        
                    }
                }
           }
            // Variation
            if (!empty($_POST['variation_attr'])) {
                $attrID = (int)$_POST['variation_attr'];

                foreach ($_POST['variation_values'] as $i => $valueID) {

                    $priceOverride = $_POST['variation_price'][$i] !== '' 
                        ? (float)$_POST['variation_price'][$i] 
                        : null;

                    $skuVar = trim($_POST['variation_sku'][$i]);

                    $sql = $conn->prepare("
                        INSERT INTO product_variation (productID, attributeID, valueID, priceOverride, sku)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $sql->bind_param("iiids", $productID, $attrID, $valueID, $priceOverride, $skuVar);
                    $sql->execute();
                    $sql->close();
                }
            }

            $conn->commit();
            redirect("editProduct.php?id=".$productID."&msg=success");
        } catch (Exception $e) {
            $conn->rollback();
            die("Error: " . $e->getMessage());
        }   
    }
 ?>
<section id="titleSection" class="pt-3 pb-1">
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <h1>Add Product</h1> 
        </div> 
    </div>         
</section>
<section id="formBody">  
    <div class="container">
        <form method="post" enctype="multipart/form-data" id="productForm">
            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
            <div class="row pt-3 pb-3">
                <!-- LEFT MENU -->
                <div class="col-md-3">
                    <div class="list-group">
                        <a href="#" class="list-group-item active tab-link" data-tab="general">General</a>
                        <a href="#" class="list-group-item tab-link" data-tab="pricing">Pricing</a>
                        <a href="#" class="list-group-item tab-link" data-tab="attributes">Attributes</a>
                        <a href="#" class="list-group-item tab-link" data-tab="variation">Variation</a>
                    </div>
                </div>
                <!-- RIGHT CONTENT -->
                <div class="col-md-9">

                    <!-- GENERAL -->
                    <div class="tab-pane-custom" id="general">
                        <input class="form-control mb-2" name="title" placeholder="Product title" required>

                        <select id="categorySelect" name="category" class="form-select mb-2">
                            <option value="">Select category</option>
                            <?php
                                $res = $conn->query("SELECT * FROM categories");
                                while ($r = $res->fetch_assoc())
                                    echo "<option value='{$r['id']}'>{$r['category']}</option>";
                            ?>
                        </select>

                        <select name="brand" class="form-select mb-2">
                            <option value="">Select brand</option>
                                <?php foreach ($brands as $b): ?>
                                    <option value="<?= $b['id'] ?>"><?= $b['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <textarea id="description" name="description" class="form-control mb-2" placeholder="Description"></textarea>
                            <input type="file" name="image" class="form-control mt-2 mb-2">
                    </div>

                    <!-- PRICING -->
                    <div class="tab-pane-custom d-none" id="pricing">
                        <input type="number" step="0.01" min="0" class="form-control mb-2" name="price" placeholder="Price">
                        <input type="text" class="form-control mb-2" name="sku" placeholder="SKU">
                        <input type="number" step="1" min="0" class="form-control mb-2" name="qty" placeholder="Quantity">
                    </div>

                    <!-- ATTRIBUTES -->
                    <div class="tab-pane-custom d-none" id="attributes">
                        <label>Add attribute</label>
                        <select id="attributeSelect" class="form-select mb-2">
                            <option value="">Select attribute</option>
                            <?php foreach ($attributes as $a): ?>
                                <option value="<?= $a['id'] ?>"><?= $a['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn btn-sm btn-primary mb-3" id="addAttributeBtn">Add</button>
                        <div id="selectedAttributes"></div>
                    </div>

                    <!-- VARIATION -->
                    <div class="tab-pane-custom d-none" id="variation">
                        <select name="variation_attr" id="variationSelect" class="form-select mb-3"><option value="">Select attribute for variation</option></select>
                        <div id="variationValues"></div>
                    </div>

                    <!-- BUTTONS -->
                    <div class="mt-4 text-end">
                        <button type="submit" name="save" value="0" class="btn btn-secondary">Save Draft</button>
                        <button type="submit" name="publish" value="1" class="btn btn-success">Publish</button>
                    </div>
                </div>
            </div>    

        </form>
    </div>
</section>

<script src="./js/product.js"></script>