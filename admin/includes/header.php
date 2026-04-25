<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>FixerUpper</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Google Fonts: Exo2 and Roboto -->
<link href="https://fonts.googleapis.com/css2?family=Exo+2:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <!-- Main CSS -->
  <link rel="stylesheet" href="./css/style8.css">
  <!-- Bootstrap 5 JS (Bundle with Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="./js/tinymce/tinymce.min.js"></script>
</head>
<body>
<!-- header.php -->
<header>
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background: #191b32;">
    <div class="container">
      <!-- Logo -->
      <a class="navbar-brand" href="../index.php">
        Fixer<span>Upper</span>
      </a>
      <!-- Toggler/collapse button -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <!-- Navbar links -->
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
          <!-- Add active class to current page -->
          <li class="nav-item"><a class="nav-link <?= ($currentPage == 'dashboard.php' ? 'active' : '') ?>" href="dashboard.php">Products</a></li>
          <li class="nav-item"><a class="nav-link <?= ($currentPage == 'categories.php' ? 'active' : '') ?>" href="categories.php">Categories</a></li>
          <li class="nav-item"><a class="nav-link <?= ($currentPage == 'attributes.php' ? 'active' : '') ?>" href="attributes.php">Attributes</a></li>
          <li class="nav-item"><a class="nav-link <?= ($currentPage == 'brands.php' ? 'active' : '') ?>" href="brands.php">Brands</a></li>
          <li class="nav-item"><a class="nav-link <?= ($currentPage == 'feedbacks.php' ? 'active' : '') ?>" href="feedbacks.php">Feedbacks</a></li>
          <li class="nav-item">
            <!-- Check if admin logged in. If user logged in show Logout link instead of Admin Sign In-->
            <?php 
              if(isset($_SESSION['admin'])) {
                echo' <a class="nav-link" href="logout.php"><i class="bi bi-person-fill"></i> Logout</a>';
              } else {
                echo' <a class="nav-link" href="login.php"><i class="bi bi-person-fill"></i> Sign In</a>';
              }
              ?>
            </li>
        </ul>
      </div>
    </div>
  </nav>
</header>


