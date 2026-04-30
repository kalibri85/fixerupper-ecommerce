<?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    require_once __DIR__ . '/admin/includes/init.php';
    include('./includes/header.php');

    $category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    $order = "ORDER BY p.id DESC";

    if (!empty($_GET['sort'])) {

        switch ($_GET['sort']) {

            case 'price_asc':
                $order = "ORDER BY p.price ASC";
                break;

            case 'price_desc':
                $order = "ORDER BY p.price DESC";
                break;
        }
    }

    // Get category
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $category = $stmt->get_result()->fetch_assoc();

    if (!$category) {
        echo "Category not found";
        exit;
    }

    // Get subcategories
    $cat_ids = [$category_id];

    $res = $conn->query("SELECT id FROM categories WHERE parent = $category_id");
    while ($row = $res->fetch_assoc()) {
        $cat_ids[] = $row['id'];
    }

    $cat_ids_str = implode(',', $cat_ids);

    // Filters
    $brands = $_GET['brand'] ?? [];
    $price_min = $_GET['price_min'] ?? null;
    $price_max = $_GET['price_max'] ?? null;
    $attrs = $_GET['attr'] ?? [];

    // Build where
    $where = "WHERE p.categoryID IN ($cat_ids_str) AND p.status = 1";

    // Brand filter
    if (!empty($brands)) {
        $brands = array_map('intval', $brands);
        $brands = implode(',', $brands);
        $where .= " AND p.brandID IN ($brands)";
    }

    // Price filter
    if ($price_min !== null && $price_min !== '') {
        $min = (float)$price_min;
        $where .= " AND p.price >= $min";
    }

    if ($price_max !== null && $price_max !== '') {
        $max = (float)$price_max;
        $where .= " AND p.price <= $max";
    }

    // Attribute filter
    if (!empty($attrs)) {
        foreach ($attrs as $attr_id => $values) {

            $attr_id = (int)$attr_id;
            $values = array_map('intval', $values);
            $values = implode(',', $values);

            $where .= "
                AND EXISTS (
                    SELECT 1
                    FROM attributes_product ap
                    WHERE ap.productID = p.id
                    AND ap.attributeID = $attr_id
                    AND ap.valueID IN ($values)
                )
            ";
        }
    }

    // Get products
    $sql = "
        SELECT p.*, b.name AS brand_name 
        FROM products p
        LEFT JOIN brands b ON p.brandID = b.id
        $where
        $order
    ";
    $products = $conn->query($sql);
?>
<section id="titleSection" class="category-hero">
    <img src="img/categories/<?= $category['id'] ?>.jpg" class="hero-img">
    <div class="overlay">
        <div class="container text-center text-white">
            <h1><?= htmlspecialchars($category['category']) ?></h1>
            <p>
                Explore our <?= htmlspecialchars($category['category']) ?> products
            </p>
        </div>
    </div>
</section>
<section class="category-toolbar">
  <div class="container pt-4">
    <div class="row align-items-center">

      <!-- LEFT: BREADCRUMBS -->
      <div class="col-md-6">
        <nav class="breadcrumbs">
          <a href="index.php">Home</a> /
          <span><?= htmlspecialchars($category['category']) ?></span>
        </nav>
      </div>

      <!-- RIGHT: SORT -->
      <div class="col-md-6 text-end">
        <select id="sortSelect" class="form-select form-select-sm w-auto d-inline-block">
            <option value="">Sort by</option>
            <option value="price_asc" <?= ($_GET['sort'] ?? '') == 'price_asc' ? 'selected' : '' ?>>
                Price: Low → High
            </option>
            <option value="price_desc" <?= ($_GET['sort'] ?? '') == 'price_desc' ? 'selected' : '' ?>>
                Price: High → Low
            </option>
        </select>
      </div>

    </div>
  </div>
</section>

<!-- MAIN -->
<section class="py-4">
  <div class="container">
    <div class="row">

      <!-- FILTERS -->
      <div class="col-md-3">
        <div class="p-3 border rounded bg-light">
            <h5>Filters</h5>
            <form method="GET" action="category.php">
                <input type="hidden" name="id" value="<?= (int)$category_id ?>">

                <!-- PRICE -->
                <div class="mb-3">
                    <h6>Price</h6>

                    <input type="number" name="price_min"
                        class="form-control mb-2"
                        placeholder="Min" min="1"
                        value="<?= $_GET['price_min'] ?? '' ?>">

                    <input type="number" name="price_max"
                        class="form-control"
                        placeholder="Max" min="1"
                        value="<?= $_GET['price_max'] ?? '' ?>">
                </div>

                <!-- BRANDS -->
                <div class="mb-3">
                    <h6>Brands</h6>

                    <?php
                    $brands = $conn->query("
                        SELECT DISTINCT b.id, b.name
                        FROM brands b
                        JOIN products p ON p.brandID = b.id
                        WHERE p.categoryID IN ($cat_ids_str)
                    ");

                    while ($b = $brands->fetch_assoc()):
                    ?>
                        <div class="form-check">
                            <input class="form-check-input"
                                type="checkbox"
                                name="brand[]"
                                value="<?= $b['id'] ?>"
                                <?= (isset($_GET['brand']) && in_array($b['id'], $_GET['brand'])) ? 'checked' : '' ?>>

                            <label class="form-check-label">
                            <?= htmlspecialchars($b['name']) ?>
                            </label>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- ATTRIBUTES -->
                <div class="mb-3">
                    <h6>Attributes</h6>

                    <?php
                    $attrs = $conn->query("
                        SELECT DISTINCT a.id, a.name
                        FROM attributes a
                        JOIN attributes_category ac ON ac.attributeID = a.id
                        JOIN attributes_product ap ON ap.attributeID = a.id
                        JOIN products p ON p.id = ap.productID
                        WHERE p.categoryID IN ($cat_ids_str)
                    ");

                    while ($a = $attrs->fetch_assoc()):
                    ?>
                        <div class="mb-2">
                            <strong><?= htmlspecialchars($a['name']) ?></strong>

                            <?php
                            $values = $conn->query("
                                SELECT DISTINCT av.id, av.value
                                FROM attribute_values av
                                JOIN attributes_product ap ON ap.valueID = av.id
                                JOIN products p ON p.id = ap.productID
                                WHERE ap.attributeID = {$a['id']}
                                AND p.categoryID IN ($cat_ids_str)
                            ");

                            while ($v = $values->fetch_assoc()):
                            ?>
                            <div class="form-check ms-2">
                                <input class="form-check-input"
                                    type="checkbox"
                                    name="attr[<?= $a['id'] ?>][]"
                                    value="<?= $v['id'] ?>"
                                    <?= (isset($_GET['attr'][$a['id']]) && in_array($v['id'], $_GET['attr'][$a['id']])) ? 'checked' : '' ?>>

                                <label class="form-check-label">
                                <?= htmlspecialchars($v['value']) ?>
                                </label>
                            </div>
                        <?php endwhile; ?>

                    </div>
                    <?php endwhile; ?>
                </div>

                <button class="btn btn-primary w-100">Apply filters</button>

            </form>
        </div>
      </div>

      <!-- PRODUCTS -->
      <div class="col-md-9">

        <div class="row g-4">

          <?php while ($p = $products->fetch_assoc()): ?>
            <div class="col-md-3 col-6">
              <a href="product.php?id=<?= $p['id'] ?>">
                <div class="product-card p-2">

                  <img src="img/products/<?= $p['image'] ?>" class="img-fluid">

                  <div class="p-2">
                    <div class="rating text-end">★★★★★</div>

                    <?php if (!empty($p['brand_name'])): ?>
                      <div class="product-brand">
                        <?= htmlspecialchars($p['brand_name']) ?>
                      </div>
                    <?php endif; ?>

                    <h6><?= htmlspecialchars($p['name']) ?></h6>

                    <div class="price">£<?= number_format($p['price'], 2) ?></div>
                        <div class="text-center">
                             <form method="POST" action="addToCart.php" class="add-to-cart-form">
                                <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                <input type="hidden" name="qty" value="1">
                                    <?php if ($p['qty'] > 0): ?>
                                        <button type="submit" class="btn-primary-custom mt-2">
                                            <i class="fa-solid fa-cart-shopping"></i> Add to cart
                                        </button>
                                    <?php else: ?>
                                        <div class="text-danger mb-2 pt-3">Out of stock</div>
                                    <?php endif; ?>
                            </form>
                        </div>
                  </div>
                </div>
              </a>
            </div>
          <?php endwhile; ?>

        </div>

      </div>

    </div>
  </div>
</section>
<script src="./js/category.js"></script>
<script src="./js/addToCart.js"></script>
<?php include('includes/footer.php'); ?>
