<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/admin/includes/init.php';
include('includes/header.php'); 
?>

<section class="hero">
  <div class="hero-content">
    <h1>Tools & Appliances for Every Home</h1>
    <p>Everything you need - from power tools to home appliances</p>
    <a href="shop.php" class="btn hero-btn">Shop Now</a>
  </div>
</section>
<!-- SHOP BY CATEGORY -->
<section class="py-5">
  <div class="container">

    <h2 class="section-title text-center mb-4">Shop by Category</h2>

    <div class="row g-4">

      <?php
      $cats = $conn->query("SELECT * FROM categories WHERE parent = 0 LIMIT 4");

      while ($cat = $cats->fetch_assoc()):
      ?>

      <div class="col-md-4 col-6">

        <a href="category.php?id=<?= $cat['id'] ?>" class="category-card">

          <div class="category-box">

            <img src="img/categories/<?= $cat['id'].'.jpg' ?? 'placeholder.jpg' ?>" alt="">

            <div class="category-overlay">
              <h5><?= htmlspecialchars($cat['category']) ?></h5>
            </div>

          </div>

        </a>

      </div>

      <?php endwhile; ?>

    </div>
  </div>
</section>


<!-- FEATURED PRODUCTS -->
<section class="py-5 bg-light">
  <div class="container">

    <h2 class="section-title text-center mb-4">Featured Products</h2>

    <div class="row g-4">

      <?php
      $sql = "SELECT products.*, brands.name AS brand_name FROM products LEFT JOIN brands ON products.brandID = brands.id ORDER BY id DESC LIMIT 8";
      $res = $conn->query($sql);
      while ($p = $res->fetch_assoc()):
      ?>
        <div class="col-md-3 col-6">
          <a href="product.php?id=<?= $p['id'] ?>">
            <div class="product-card p-2">
              <img src="img/products/<?= $p['image'] ?>" class="img-fluid">
              <div class="p-2">
                <div class="rating">★★★★★</div>
                <div class="product-brand">
                  <?php if (!empty($row['brand_name'])): ?>
                    <div class="product-brand">
                      <?= htmlspecialchars($row['brand_name']) ?>
                    </div>
                  <?php endif; ?>
                </div>
                <h6><?= htmlspecialchars($p['name']) ?></h6>
                <div class="price">£<?= $p['price'] ?></div>
                <button class="btn-buy mt-2"><i class="fa-solid fa-cart-shopping pr-2"></i>  Add to cart</button>
              </div>
            </div>
          </a>
        </div>
      <?php endwhile; ?>

    </div>
  </div>
</section>


<!-- WHY CHOOSE US -->
<section class="py-5">
  <div class="container text-center">

    <h2 class="section-title mb-4">Why Choose Us</h2>

    <div class="row">

      <div class="col-md-4 col-4">
        <i class="fa-solid fa-truck fa-2x mb-2"></i>
        <p>Fast Delivery</p>
      </div>

      <div class="col-md-4 col-4">
        <i class="fa-solid fa-shield-halved fa-2x mb-2"></i>
        <p>Warranty Included</p>
      </div>

      <div class="col-md-4 col-4">
        <i class="fa-solid fa-credit-card fa-2x mb-2"></i>
        <p>Secure Payment</p>
      </div>

    </div>

  </div>
</section>


<!-- BRANDS -->
<section class="py-4 bg-light">
  <div class="container text-center">

    <div class="row align-items-center">

      <div class="col brand-item">
        <img src="img/brands/bosch.jpg" class="brand-logo">
      </div>

      <div class="col brand-item">
        <img src="img/brands/makita.jpg" class="brand-logo">
      </div>

      <div class="col brand-item">
        <img src="img/brands/deWalt.jpg" class="brand-logo">
      </div>

      <div class="col brand-item">
        <img src="img/brands/philips.jpg" class="brand-logo">
      </div>

      <div class="col brand-item">
        <img src="img/brands/karcher.jpg" class="brand-logo">
      </div>

      <div class="col brand-item">
        <img src="img/brands/samsung.jpg" class="brand-logo">
      </div>

    </div>

  </div>
</section>
<?php include('includes/footer.php'); ?>
