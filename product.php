<?php
   /**
    *
    * @author Lana (Svetlana Muraveckaja-Odincova)
    */
    require_once __DIR__ . '/admin/includes/init.php';
    include('./includes/header.php');

    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    // PRODUCT
    $stmt = $conn->prepare("
        SELECT p.*, b.name AS brand_name
        FROM products p
        LEFT JOIN brands b ON p.brandID = b.id
        WHERE p.id = ? AND p.status = 1
    ");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if (!$product) {
        echo "Product not found";
        exit;
    }

    // CATEGORY (Breadcrumbs)
    $cat = $conn->query("
        SELECT c.*, parent.category AS parent_name, parent.id AS parent_id
        FROM categories c
        LEFT JOIN categories parent ON c.parent = parent.id
        WHERE c.id = {$product['categoryID']}
    ")->fetch_assoc();

    // ATTRIBUTES
    $attributes = $conn->query("
        SELECT a.name, av.value
        FROM attributes_product ap
        JOIN attributes a ON ap.attributeID = a.id
        JOIN attribute_values av ON ap.valueID = av.id
        WHERE ap.productID = {$product['id']}
    ");
    // Variations
    $variations = $conn->query("
        SELECT pv.*, a.name AS attr_name, av.value AS attr_value
        FROM product_variation pv
        JOIN attributes a ON pv.attributeID = a.id
        JOIN attribute_values av ON pv.valueID = av.id
        WHERE pv.productID = {$product['id']}
    ");

    $variation_map = [];

    while ($v = $variations->fetch_assoc()) {
        $variation_map[$v['attr_name']][] = $v;
    }

?>

<!-- BREADCRUMBS -->
<section class="py-5">
    <div class="container">
        <nav class="breadcrumbs">
            <a href="index.php">Home</a> /

            <?php if (!empty($cat['parent_id'])): ?>
                <a href="category.php?id=<?= $cat['parent_id'] ?>">
                    <?= htmlspecialchars($cat['parent_name']) ?>
                </a> /
            <?php endif; ?>

            <a href="category.php?id=<?= $cat['id'] ?>">
                <?= htmlspecialchars($cat['category']) ?>
            </a> /

            <span><?= htmlspecialchars($product['name']) ?></span>
        </nav>
    </div>
</section>

<!-- PRODUCT -->
<section>
    <div class="container">
        <div class="row">

            <!-- IMAGE -->
            <div class="col-md-6">
                <div class="product-image">
                    <img src="img/products/<?= $product['image'] ?>" class="img-fluid w-100">
                </div>
            </div>

            <!-- INFO -->
            <div class="col-md-6">
              <h1><?= htmlspecialchars($product['name']) ?></h1>
              <div class="product-meta">
                <?php if (!empty($product['brand_name'])): ?>
                  <div class="product-brand">
                      <?= htmlspecialchars($product['brand_name']) ?>
                  </div>
                <?php endif; ?>
                <div class="rating">★★★★★</div>
              </div>

              <div class="price mb-3">£<?= number_format($product['price'], 2) ?></div>

              <?php if ($product['qty'] > 0): ?>
                <div class="text-success mb-3">In stock</div>
              <?php else: ?>
                <div class="text-danger mb-3">Out of stock</div>
              <?php endif; ?>
            
              <!-- QUANTITY -->
              
                <form method="POST" action="addToCart.php" class="add-to-cart-form">

                    <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                    <div class="row">
                        <!-- VARIATIONS -->
                        <div class="col-md-12 ">
                            <?php foreach ($variation_map as $attr => $values): ?>
                                    <label class="form-label fw-bold">
                                        <?= htmlspecialchars($attr) ?>
                                    </label>

                                    <select class="form-select variation-select"
                                            name="variation[<?= $attr ?>]">

                                        <option value="">Select</option>

                                        <?php foreach ($values as $v): ?>
                                            <option value="<?= $v['valueID'] ?>"
                                                    data-price="<?= $v['priceOverride'] ?>">
                                                <?= htmlspecialchars($v['attr_value']) ?>
                                            </option>
                                        <?php endforeach; ?>

                                    </select>
                            <?php endforeach; ?>

                        </div>
                    </div>
                    <div class="row product-actions align-items-center mt-3 mb-2">
    
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-3">

                                <label for="qty" class="fw-bold mb-0">
                                    Quantity:
                                </label>

                                <div class="input-group quantity-group">
                                    <button type="button" class="btn btn-outline-secondary qty-minus">−</button>

                                    <input type="number"
                                        id="qty"
                                        name="qty"
                                        class="form-control text-center qty-input"
                                        value="1"
                                        min="1"
                                        max="<?= $product['qty'] ?>">

                                    <button type="button" class="btn btn-outline-secondary qty-plus">+</button>
                                </div>

                            </div>
                        </div>

                        <div class="col-md-6 d-flex justify-content-end">
                            <button type="submit" class="btn-cta">
                                <i class="fa-solid fa-cart-shopping"></i> Add to cart
                            </button>
                        </div>
                    </div>
                </form>
        </div>
    </div>
</section>

<!-- TABS -->
<section class="pt-3 pb-5">
    <div class="container">
      <!-- TABS NAV -->
      <ul class="nav nav-tabs">
        <li class="nav-item">
            <button class="nav-link active"
                    data-bs-toggle="tab"
                    data-bs-target="#desc">
                Description
            </button>
        </li>

        <li class="nav-item">
            <button class="nav-link"
                    data-bs-toggle="tab"
                    data-bs-target="#features">
                Features
            </button>
        </li>

        <li class="nav-item">
            <button class="nav-link"
                    data-bs-toggle="tab"
                    data-bs-target="#reviews">
                Reviews
            </button>
        </li>
      </ul>
    <div class="tab-content p-3 border border-top-0">

        <div class="tab-pane fade show active" id="desc">
            <div class="product-description">
                <?= $product['description'] ?>
            </div>
        </div>

        <div class="tab-pane fade" id="features">
            <?php if ($attributes->num_rows > 0): ?>
                <ul>
                    <?php while ($a = $attributes->fetch_assoc()): ?>
                        <li>
                            <strong><?= htmlspecialchars($a['name']) ?>:</strong>
                            <?= htmlspecialchars($a['value']) ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No features available</p>
            <?php endif; ?>
        </div>

        <div class="tab-pane fade" id="reviews">
            <p>No reviews yet.</p>
        </div>

    </div>

  </div>
</section>
<script src="./js/addToCart.js"></script>
<script src="./js/product.js"></script>
<?php include('includes/footer.php'); ?>