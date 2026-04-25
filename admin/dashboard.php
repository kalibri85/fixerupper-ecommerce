<?php
 /**
 *
 * @author Lana (Svetlana Muraveckaja-Odincova)
 */
  require_once __DIR__ . '/includes/init.php';

  requireAdmin();

  include('./includes/header.php');
  error_reporting(E_ALL);

  // Uptdate products table, set visible
  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["mark"])) {
    if (!empty($_POST["available"])) {
      foreach ($_POST["available"] as $id) {
          $id = (int)$id;
          $sql = $conn->prepare("UPDATE products SET status = 1 WHERE id = ? and status != 2");
          $sql->bind_param("i", $id);
          $sql->execute();
      }
    }
    redirect("dashboard.php?msg=updated");
    exit;
  }
  // Delete product from database after confirmation
  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) {

      $id = (int)($_POST["delete"]);

      if ($id > 0) {
        $sql = $conn->prepare("UPDATE products SET status = 2 WHERE id = ?");
        $sql->bind_param("i", $id);
        $sql->execute();
        redirect("dashboard.php?msg=deleted");
        exit;
      } else {
        redirect("dashboard.php?msg=invalid_id");
        exit;
      }
  }
  // Paggination
  $resultTotal = $conn->query("SELECT COUNT(*) as total FROM products WHERE status != 2");
  $total = $resultTotal->fetch_assoc()['total'];
  $pagination = paginate($total, 5);

  $page = $pagination['page'];
  $perPage = $pagination['perPage'];
  $offset = $pagination['offset'];
  $totalPages = $pagination['totalPages'];

  // Get all products from database
  $sql = "SELECT p.*, c.category AS category_name FROM products p LEFT JOIN categories c ON p.categoryID = c.id WHERE p.status != 2 LIMIT $offset, $perPage";
  $result = $conn->query($sql);
?> 
<section id="titleSection" class="pt-3 pb-1">
    <div class="container d-flex justify-content-between mb-3">
      <h1>Products</h1>
      <a href="addProduct.php" class="btn btn-success">
          <i class="fa-solid fa-plus"></i> Add Product
      </a>    
    </div> 

  <?php if (isset($_GET['msg'])): ?>
    <div class="container mt-5 pt-4">
      <div class="alert alert-info">
        <?php
            $messages = [
                'deleted' => 'Product deleted',
                'csrf_error' => 'Security error',
                'invalid_id' => 'Invalid ID',
                'error' => 'Something went wrong',
                'updated' => 'Products updated'
            ];
            echo $messages[$_GET['msg']] ?? 'Unknown error';
        ?>
      </div>
    </div> 
  <?php endif; ?>     
</section>
<!-- Products list's header start -->  
<section id="tableHeader" class="pt-3 pb-3">
  <div class="container text-center">
    <div class="row">
      <div class="float-start col-md-2"></div>
      <div class="float-start col-md-2">Title</div>
      <div class="float-start col-md-2">Category</div>
      <div class="float-start col-md-2">Description</div>
      <div class="float-start col-md-1">Price</div>
      <div class="float-start col-md-1">Visible</div>
      <div class="float-start col-md-1"></div>
      <div class="float-start col-md-1"></div>
    </div>
  </div>    
</section>
<!-- Products list's header end -->  

<!-- Products list starts -->
<section id="tableBody"> 
    <div class="container text-center">    
        <form method="POST" action="dashboard.php">   
          <input type="hidden" name="csrf_token" id="csrf_token" value="<?= csrf() ?>">
          <?php
            while ($row = $result->fetch_assoc()) :
          ?>     
            <div class='row pt-3 pb-3 item-row'>
              <div class='float-start col-md-2'>
                <?php
                  $path = "../img/products/" . $row['image'];

                  $image = (!empty($row['image']) && file_exists($path))
                      ? $path
                      : "../img/products/default/no-image.png";
                  ?>
              <img src='<?= $image ?>' alt='item' width ='40px'></div>
              <div class='float-start col-md-2'><?= $row['name'] ?></div>
              <div class='float-start col-md-2'><?= htmlspecialchars($row['category_name'] ?? 'No category') ?></div>
              <div class='float-start col-md-2'><?= htmlspecialchars(mb_substr(strip_tags($row['description']), 0, 50)) ?>...</div>
              <div class='float-start col-md-1 text-center'>£<?= $row['price'] ?></div>
              <div class='float-start col-md-1 text-center'><input class='form-check-input' type='checkbox' name='available[]' value ='<?= $row['id'] ?>'<?= $row['status'] == 1 ? ' checked' : '' ?> ></div>
              <div class='float-start col-md-1 text-center'><a href='editProduct.php?id=<?= $row['id'] ?>'><i class='fa-solid fa-pen-to-square'></i></a></div>
              <div class='float-start col-md-1'>
                <button type="submit" name="delete" value="<?= $row['id'] ?>"
                    class="btn btn-danger btn-sm"
                    onclick="return confirm('Delete this product?')">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
              </div>
            </div>
          <?php endwhile; ?>  
        <div class="mt-4 text-end pb-3">
          <button type="submit" name="mark" class="btn btn-primary">
              <i class="fa-regular fa-eye"></i> Mark as Visible
          </button>
        </div>
    </form>
    </div>
    <?php renderPagination($totalPages, $page, 'dashboard.php?'); ?>   s
</section>
<!-- Products list end -->

	
