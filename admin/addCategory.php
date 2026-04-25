 <?php
    /**
     *
     * @author Lana (Svetlana Muraveckaja-Odincova)
     */
    require_once __DIR__ .'/includes/init.php';

    requireAdmin();

    include('./includes/header.php');
    $message = "";
    //Add new category to database
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['category'])) {
        checkCSRF();
        $parent = isset($_POST['parent']) ? (int) $_POST['parent'] : 0;
        $category = isset($_POST['category']) ? trim($_POST['category']) : "";

        if (empty($category)) {
            $message = "Category name is required";
        } elseif (strlen($category) > 255) {
            $message = "Category name is too long";
        } else {
            $sql = $conn->prepare("INSERT INTO `categories` (`parent`, `category`, `active`) VALUES (?, ?, 1)");
            $sql->bind_param("is",
                            $parent,       
                            $category
                            );
            if($sql->execute()) {
                redirect("categories.php?success=1");
                exit;
            } else{
                $message = "Something went wrong. Please try again.";
            }
        }
    }
    if(isset($_GET['success'])) {
        $message = "Category saved successfully";
    }
?>
<section id="titleSection" class="pt-3 pb-1">
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <h1>Add Category</h1>
        </div> 
    </div>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>          
</section>
<section id="formBody">  
    <div class="container text-center pb-3 pt-3"">
        <?php
            if(!empty($message)){
                echo "<div class='alert alert-info' role='alert'>".htmlspecialchars($message)."</div>";
            }
        ?>     
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
            <div class="row"> 
                <div class="col-mb-12 text-start">
                    <label for="category" class="form-label">Category Name</label>
                    <input type="text" class="form-control" name="category" id="category" aria-describedby="Category name" required>
                </div>
            </div>
            <div class="row"> 
                <div class="col-mb-12 text-start">
                    <label for="parent" class="form-label">Parent Category</label>
                    <select id="parent" name="parent" class="form-select">
                        <!-- Get categories-->
                        <option value="0">No parent category</option>  
                        <?php
                            $sql = "SELECT id, category FROM `categories`";
                            $result = $conn->query($sql);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value=".(int)$row['id'].">".htmlspecialchars($row['category'])."</option>";
                            }
                        ?>  
                    </select>
                </div>
            </div>  
            <div class="row pt-3 pb-3">  
                <div class="col-md-5 text-start">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>  
        </form>
    </div>
</section>
