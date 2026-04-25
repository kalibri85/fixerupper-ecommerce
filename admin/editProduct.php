<?php
    require_once __DIR__ . '/includes/init.php';
    requireAdmin();
    include('./includes/header.php');
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    $productID = (int)($_GET['id'] ?? 0);
    if (!$productID) die("No product ID");

    // Load product
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $productID);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$product) die("Product not found");

    // Load attribute by category
    $attributes = [];

    $stmt = $conn->prepare("
        SELECT a.id, a.name 
        FROM attributes a
        JOIN attributes_category ac ON ac.attributeID = a.id
        WHERE ac.categoryID = ?
    ");
    $stmt->bind_param("i", $product['categoryID']);
    $stmt->execute();
    $attributes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Load selected attributes
    $selectedAttributes = [];
    $res = $conn->query("SELECT attributeID, valueID FROM attributes_product WHERE productID = $productID");

    while ($r = $res->fetch_assoc()) {
        $selectedAttributes[$r['attributeID']][] = $r['valueID'];
    }
    foreach ($selectedAttributes as $attrID => $vals) {
        $found = false;
        foreach ($attributes as $a) {
            if ($a['id'] == $attrID) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            $res = $conn->query("SELECT id, name FROM attributes WHERE id = $attrID");
            if ($row = $res->fetch_assoc()) {
                $attributes[] = $row;
            }
        }
    }
    // Load variations
    $variation = [];
    $res = $conn->query("SELECT * FROM product_variation WHERE productID = $productID");
    $variation = $res->fetch_all(MYSQLI_ASSOC);

    // Update product
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        checkCSRF();
        $conn->begin_transaction();
        try {
            $title = trim($_POST['title']);
            $category = $_POST['category'] != '' ? (int)$_POST['category'] : null;
            $brand = $_POST['brand'] != '' ? (int)$_POST['brand'] : null;
            $desc = trim($_POST['description']);
            $price = $_POST['price'] !== '' ? (float)$_POST['price'] : 0;
            $sku = trim($_POST['sku']);
            $qty= $_POST['qty'] !== '' ? (int)$_POST['qty'] : 0;
            $status = isset($_POST['publish']) ? 1 : 0;

            // Image
            $photo = uploadImage($_FILES['image'], __DIR__ . "/../img/products/") ?? $product['image'];

            // Update product
            $stmt = $conn->prepare("
                UPDATE products SET
                    categoryID=?,
                    brandID=?,
                    sku=?,
                    name=?,
                    description=?,
                    image=?,
                    price=?,
                    qty_inventory=?,
                    status=?
                WHERE id=?
            ");

            $stmt->bind_param(
                "iissssdiii",
                $category,
                $brand,
                $sku,
                $title,
                $desc,
                $photo,
                $price,
                $qty,
                $status,
                $productID
            );

            $stmt->execute();
            $stmt->close();

            // Clear old relations
            $conn->query("DELETE FROM attributes_product WHERE productID = $productID");
            $conn->query("DELETE FROM product_variation WHERE productID = $productID");

            // Insert attributes
            if (!empty($_POST['attributes'])) {
                foreach ($_POST['attributes'] as $attrID => $data) {
                    foreach ($data['values'] ?? [] as $valueID) {
                        $sql = $conn->prepare("
                            INSERT INTO attributes_product (productID, attributeID, valueID)
                            VALUES (?, ?, ?)
                        ");
                        $sql->bind_param("iii", $productID, $attrID, $valueID);
                        $sql->execute();
                        $sql->close();
                    }
                }
            }

            // Variation (ONLY ONE ATTRIBUTE)
            if (!empty($_POST['variation_attr']) && !empty($_POST['variation_values'])) {

                $attrID = (int)$_POST['variation_attr'];
                $check = $conn->query("SELECT id FROM attributes WHERE id = $attrID");

                if($check && $check->num_rows > 0){
                    foreach ($_POST['variation_values'] as $i => $valueID) {
                        if (empty($valueID)) continue;

                        $rawPrice = $_POST['variation_price'][$i] ?? '';
                        $price = ($rawPrice === '' ? null : (float)$rawPrice);
                        $skuV = trim($_POST['variation_sku'][$i]) ?? null;

                        $sql = $conn->prepare("
                            INSERT INTO product_variation
                            (productID, attributeID, valueID, priceOverride, sku)
                            VALUES (?, ?, ?, ?, ?)
                        ");

                        $sql->bind_param("iiids", $productID, $attrID, $valueID, $price, $skuV);
                        $sql->execute();
                        $sql->close();
                    }
                }
            }

            $conn->commit();
            redirect("dashboard.php?msg=updated");
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            die("Error: " . $e->getMessage());
        }
    }
    $savedAttrID = null;

    $res = $conn->query("
        SELECT DISTINCT attributeID 
        FROM product_variation 
        WHERE productID = $productID 
        LIMIT 1
    ");

    if ($row = $res->fetch_assoc()) {
        $savedAttrID = $row['attributeID'];
    }
?>
<section id="titleSection" class="pt-3 pb-1">
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <h1>Edin Product</h1> 
        </div> 
    </div>
    <?php if (isset($_GET['msg'])): ?>
    <div class="container mt-5 pt-4">
      <div class="alert alert-info">
        <?php
            $messages = [
                'success' => 'Product created successfully. You can continue editing.',
                'csrf_error' => 'Security error',
                'invalid_id' => 'Invalid ID'
            ];
            echo $messages[$_GET['msg']] ?? 'Unknown error';
        ?>
      </div>
    </div> 
  <?php endif; ?>             
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
                        <input class="form-control mb-2" name="title" value="<?= htmlspecialchars($product['name']) ?>" placeholder="Product title">

                        <select id="categorySelect" name="category" class="form-select mb-2">
                            <option value="">Select category</option>
                            <?php
                                $res = $conn->query("SELECT * FROM categories");
                                while ($r = $res->fetch_assoc()):
                            ?>        
                            <option value="<?= $r['id'] ?>" <?= $r['id'] == $product['categoryID'] ? 'selected' : '' ?>><?= $r['category'] ?></option>
                            <?php endwhile; ?>
                        </select>

                        <select name="brand" class="form-select mb-2">
                            <option value="">Select brand</option>
                            <?php $res = $conn->query("SELECT * FROM brands");
                                while ($b = $res->fetch_assoc()):
                            ?>        
                                <option value="<?= $b['id'] ?>" <?= $b['id'] == $product['brandID'] ? 'selected' : '' ?>><?= $b['name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                        <textarea id="description" name="description" class="form-control mb-2" placeholder="Description"><?= $product['description'] ?></textarea>
                        <input type="file" name="image" class="form-control mb-2 mt-2">
                        <img src='../img/products/<?= $product['image'] ?>' alt='item' width ='80px'>
                    </div>

                    <!-- PRICING -->
                    <div class="tab-pane-custom d-none" id="pricing">
                        <input type="number" step="0.01" min="0" class="form-control mb-2" name="price" placeholder="Price" value="<?= $product['price'] ?>">
                        <input type="text" class="form-control mb-2" name="sku" placeholder="SKU" value="<?= $product['sku'] ?>">
                        <input type="number" step="1" min="0" class="form-control mb-2" name="qty" placeholder="Quantity" value="<?= $product['qty_inventory'] ?>">
                    </div>

                    <!-- ATTRIBUTES -->
                    <div class="tab-pane-custom d-none" id="attributes">

                        <label>Add attribute</label>

                        <select id="attributeSelect" class="form-select mb-2">
                            <option value="">Select attribute</option>
                            <?php foreach ($attributes as $attr): ?>
                                <option value="<?= $attr['id'] ?>"><?= $attr['name'] ?></option>
                            <?php endforeach; ?>
                        </select>

                        <button type="button" class="btn btn-sm btn-primary mb-3" id="addAttributeBtn">Add</button>

                        <div id="selectedAttributes">

                            <?php foreach ($attributes as $attr): ?>

                                <?php if (isset($selectedAttributes[$attr['id']])): ?>

                                    <div class="border p-2 mb-2" data-attr="<?= $attr['id'] ?>">

                                        <div class="d-flex justify-content-between">
                                            <strong><?= $attr['name'] ?></strong>
                                            <button type="button" class="btn btn-sm btn-danger remove-attr">✕</button>
                                        </div>

                                        <select multiple class="form-select values mt-2"
                                                name="attributes[<?= $attr['id'] ?>][values][]">

                                            <?php
                                            $vals = $conn->query("
                                                SELECT * FROM attribute_values
                                                WHERE attributeID = {$attr['id']}
                                            ");

                                            while ($v = $vals->fetch_assoc()):
                                            ?>

                                            <option value="<?= $v['id'] ?>"
                                                <?= in_array($v['id'], $selectedAttributes[$attr['id']]) ? 'selected' : '' ?>>
                                                <?= $v['value'] ?>
                                            </option>

                                            <?php endwhile; ?>

                                        </select>

                                    </div>

                                <?php endif; ?>

                            <?php endforeach; ?>

                        </div>
                    </div>
                    <!-- VARIATION -->
                    <div class="tab-pane-custom d-none" id="variation">
                        <select name="variation_attr" id="variationSelect" class="form-select mb-3">
                            <option>Select attribute for variation</option>    
                            <?php foreach ($attributes as $attr): ?>
                                <option value="<?= $attr['id'] ?>"
                                    <?= ($attr['id'] == ($savedAttrID ?? null)) ? 'selected' : '' ?>>
                                    <?= $attr['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <div id="variationValues">
                            <?php foreach ($variation as $v): 
                                $valRes = $conn->query("SELECT value FROM attribute_values WHERE id = {$v['valueID']}");
                                $valName = $valRes->fetch_assoc()['value'] ?? '';
                            ?>
                                <div class="border p-2 mb-2">
                                    <strong><?= htmlspecialchars($valName) ?></strong>
                                    <input type="hidden" name="variation_values[]" value="<?= $v['valueID'] ?>" class="form-control mb-1">
                                    <input name="variation_price[]" value="<?= $v['priceOverride'] ?>" placeholder="Price Override" class="form-control mb-1">
                                    <input name="variation_sku[]" value="<?= $v['sku'] ?>" placeholder="SKU" class="form-control mb-1">

                                </div>
                            <?php endforeach; ?>
                        </div>
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