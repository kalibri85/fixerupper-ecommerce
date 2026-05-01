<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FixerUpper</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Google Fonts: Exo2 and Roboto -->
  <link href="https://fonts.googleapis.com/css2?family=Exo+2:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <!-- Montserrat font -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet"> 
  <!-- Main CSS -->
  <link rel="stylesheet" href="./css/style15.css">
</head>
<body>
  <!-- header.php -->
  <?php
    $cartCount = 0;

    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $cartCount = array_sum($_SESSION['cart']);
    }
  ?>
  <header>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top custom-navbar">
      <div class="container d-flex align-items-center justify-content-between">
        <!-- LEFT: LOGO -->
        <a class="navbar-brand" href="index.php">
          Fixer<span class="logo-accent">Upper</span>
        </a>

        <!-- RIGHT TOP (ALWAYS VISIBLE) -->
        <div class="d-flex align-items-center order-lg-3">

          <!-- CART -->
          <a class="nav-link me-4 position-relative" href="cart.php">
            <i class="fa-solid fa-cart-shopping"></i> 
            <span id="cartCount" class="badge bg-danger position-absolute top-0 start-100 translate-middle"> <?= $cartCount ?> </span>
          </a>
          <!-- AUTH -->
          <?php if (!isset($_SESSION['user'])): ?>
            <a class="nav-link text-center me-2" href="login.php">
              <i class="fa-solid fa-user"></i> <small>Login</small>
            </a>
          <?php else: ?>
            <div class="dropdown text-center me-3">
              <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                <i class="fa-solid fa-user-circle"></i><br>
                <small>Account</small>
              </a>

              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="account.php">My Account</a></li>
                <li><a class="dropdown-item" href="orders.php">Orders</a></li>
                <li><a class="dropdown-item" href="addresses.php">Addresses</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
              </ul>
            </div>
          <?php endif; ?>

          <!-- HAMBURGER -->
          <button class="navbar-toggler ms-2" type="button"
                  data-bs-toggle="offcanvas"
                  data-bs-target="#mobileMenu">
            <span class="navbar-toggler-icon"></span>
          </button>

        </div>

        <!-- CENTER MENU -->
        <div class="collapse navbar-collapse order-lg-2" id="mainNav">

          <ul class="navbar-nav mx-auto align-items-center">

            <!-- HOME -->
            <li class="nav-item">
              <a class="nav-link" href="index.php">Home</a>
            </li>

            <!-- SHOP -->
            <li class="nav-item dropdown position-static">
              <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                Shop
              </a>

              <div class="dropdown-menu w-100 mega-menu">
                <div class="container">
                  <div class="row">

                    <?php
                    $cats = $conn->query("SELECT * FROM categories WHERE parent = 0");
                    while ($cat = $cats->fetch_assoc()):
                    ?>
                      <div class="col-md-3">
                        <h6 class="mega-title">
                          <a href="category.php?id=<?= $cat['id'] ?>">
                            <?= htmlspecialchars($cat['category']) ?>
                          </a>
                        </h6>

                        <?php
                        $subs = $conn->query("SELECT * FROM categories WHERE parent = ".(int)$cat['id']);
                        while ($sub = $subs->fetch_assoc()):
                        ?>
                          <a class="dropdown-item"
                            href="category.php?id=<?= $sub['id'] ?>">
                            <?= htmlspecialchars($sub['category']) ?>
                          </a>
                        <?php endwhile; ?>

                      </div>
                    <?php endwhile; ?>

                  </div>
                </div>
              </div>
            </li>

            <!-- TOP LEVEL -->
            <?php
            $cats = $conn->query("SELECT * FROM categories WHERE parent = 0");
            while ($cat = $cats->fetch_assoc()):
            ?>
              <li class="nav-item dropdown position-static">

                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                  <?= htmlspecialchars($cat['category']) ?>
                </a>

                <div class="dropdown-menu w-100 mega-menu">
                  <div class="container">
                    <div class="row">

                      <div class="col-md-3">
                        <h6 class="mega-title">
                          <a href="category.php?id=<?= $cat['id'] ?>">
                            All <?= htmlspecialchars($cat['category']) ?>
                          </a>
                        </h6>
                      </div>

                      <div class="col-md-9">
                        <div class="row">

                          <?php
                          $subs = $conn->query("SELECT * FROM categories WHERE parent = ".(int)$cat['id']);
                          while ($sub = $subs->fetch_assoc()):
                          ?>
                            <div class="col-md-4">

                              <a class="dropdown-item fw-semibold mega-title"
                                href="category.php?id=<?= $sub['id'] ?>">
                                <?= htmlspecialchars($sub['category']) ?>
                              </a>

                              <?php
                              $subsubs = $conn->query("SELECT * FROM categories WHERE parent = ".(int)$sub['id']);
                              while ($ss = $subsubs->fetch_assoc()):
                              ?>
                                <a class="dropdown-item sub-item"
                                  href="category.php?id=<?= $ss['id'] ?>">
                                  <?= htmlspecialchars($ss['category']) ?>
                                </a>
                              <?php endwhile; ?>

                            </div>
                          <?php endwhile; ?>

                        </div>
                      </div>

                    </div>
                  </div>
                </div>

              </li>
            <?php endwhile; ?>

            <li class="nav-item">
              <a class="nav-link" href="contact.php">Contact</a>
            </li>

          </ul>
        </div>
      </div>
    </nav>

    <!-- MOBILE MENU -->
    <div class="offcanvas offcanvas-start mobile-menu" tabindex="-1" id="mobileMenu">

      <div class="offcanvas-header">
        <h5 class="offcanvas-title">Menu</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
      </div>

      <div class="offcanvas-body">

        <a href="index.php" class="mobile-link">Home</a>

        <?php
        $cats = $conn->query("SELECT * FROM categories WHERE parent = 0");
        while ($cat = $cats->fetch_assoc()):
        ?>
          <div class="mobile-category">

            <button class="mobile-cat-btn"
                    data-bs-toggle="collapse"
                    data-bs-target="#cat<?= $cat['id'] ?>">
              <?= htmlspecialchars($cat['category']) ?>
              <i class="fa-solid fa-chevron-down"></i>
            </button>

            <div class="collapse" id="cat<?= $cat['id'] ?>">

              <a href="category.php?id=<?= $cat['id'] ?>" class="mobile-sub">
                All <?= htmlspecialchars($cat['category']) ?>
              </a>

              <?php
              $subs = $conn->query("SELECT * FROM categories WHERE parent = ".(int)$cat['id']);
              while ($sub = $subs->fetch_assoc()):
              ?>
                <a href="category.php?id=<?= $sub['id'] ?>" class="mobile-sub">
                  <?= htmlspecialchars($sub['category']) ?>
                </a>
              <?php endwhile; ?>

            </div>

          </div>
        <?php endwhile; ?>

        <a href="contact.php" class="mobile-link mt-3">Contact</a>

      </div>
    </div>
  </header>