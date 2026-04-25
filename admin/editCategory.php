<?php
 /**
 *
 * @author Lana (Svetlana Muraveckaja-Odincova)
 */
    require_once __DIR__ .'/includes/init.php';

    requireAdmin();

    include('./includes/header.php');

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        redirect("categories.php?msg=invalid_id");
        exit;
    }
    $categoryID = (int)$_GET['id'];
    // Get category
    $sql = $conn->prepare("SELECT id, category, parent, active FROM categories WHERE id = ?");
    $sql->bind_param("i", $categoryID);
    $sql->execute();
    $result = $sql->get_result();

    if ($result->num_rows === 0) {
        redirect("categories.php?msg=invalid_id");
        exit;
    }
    $category = $result->fetch_assoc();
    $sql->close();
    // Update category
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        checkCSRF();
        $name = trim($_POST['category'] ?? '');
        $parent = isset($_POST['parent']) && is_numeric($_POST['parent']) ? (int)$_POST['parent'] : null;
        $active = isset($_POST['active']) ? 1 : 0;
        // Validation
        if($name === '' || strlen($name) > 255) {
            $error = "Category name cannot be empty or longer than 255 character";
        } elseif ($parent === $categoryID) {
            $error = "Category cannot be its own parent";
        } else {
            // UPDATE
            $sql = $conn->prepare("UPDATE categories SET category = ?, parent = ?, active = ? WHERE id = ?");
            $sql->bind_param("siii", $name, $parent, $active, $categoryID);

            if ($sql->execute()) {
                redirect("categories.php?update=1");
            } else {
                $error = "Update failed";
            }
            $sql->close();
        }
    }
    $parents = $conn->query("SELECT id, category FROM categories WHERE id != $categoryID ORDER BY category ASC");
?>    
<section id="titleSection" class="pt-3 pb-1">
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <h1>Edit Category</h1>
        </div> 
    </div>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>          
</section>
<section id="formBody"> 
    <div class="container text-left pb-3 pt-3"">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
            <div class="mb-3">
                <label for="category" class="form-label">Category Name</label>
                <input type="text" id="category" name="category" class="form-control" value="<?= htmlspecialchars($category['category']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="parent" class="form-label">Parent Category</label>
                <select id="parent" name="parent" class="form-select">
                    <option value="0">— None —</option>
                    <?php while ($row = $parents->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>" <?= $row['id'] == $category['parent'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['category']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" id="active" name="active" class="form-check-input" <?= $category['active'] ? 'checked' : '' ?>>
                <label for="active" class="form-check-label">Active</label>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="categories.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</section>    