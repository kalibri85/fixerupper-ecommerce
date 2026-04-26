<?php
/**
 *
 * @author Lana (Svetlana Muraveckaja-Odincova)
 */

require_once __DIR__ .'/includes/init.php';

requireAdmin();

include('./includes/header.php');

// ================= VALIDATE ID =================
if (!isset($_GET['id'])) {
    redirect("attributes.php");
}

$id = (int) $_GET['id'];

// ================= GET ATTRIBUTE =================
$stmt = $conn->prepare("SELECT name FROM attributes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect("attributes.php");
}

$attribute = $result->fetch_assoc();

// ================= GET SELECTED CATEGORIES =================
$selectedCategories = [];
$res = $conn->prepare("SELECT categoryID FROM attributes_category WHERE attributeID = ?");
$res->bind_param("i", $id);
$res->execute();
$resResult = $res->get_result();

while ($row = $resResult->fetch_assoc()) {
    $selectedCategories[] = $row['categoryID'];
}

// ================= UPDATE =================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['name'])) {

    checkCSRF();

    $name = trim($_POST["name"]);
    $categories = $_POST['categories'] ?? [];

    if (!empty($name)) {

        // Update attribute name
        $sql = $conn->prepare("UPDATE attributes SET name = ? WHERE id = ?");
        $sql->bind_param("si", $name, $id);
        $sql->execute();

        // Remove old category relations
        $sql = $conn->prepare("DELETE FROM attributes_category WHERE attributeID = ?");
        $sql->bind_param("i", $id);
        $sql->execute();

        // Insert new category relations
        foreach ($categories as $cat_id) {
            $cat_id = (int)$cat_id;

            $sql = $conn->prepare("
                INSERT INTO attributes_category (categoryID, attributeID)
                VALUES (?, ?)
            ");
            $sql->bind_param("ii", $cat_id, $id);
            $sql->execute();
        }

        redirect("attributes.php");
        exit;
    }
}
?>

<section id="titleSection" class="pt-3 pb-1">
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <h1>Edit Attribute</h1>
        </div>
    </div>
</section>

<section id="tableBody">
    <div class="container pb-3 pt-3">

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">

            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" class="form-control"
                       value="<?= htmlspecialchars($attribute['name']) ?>" required>
            </div>

            <div class="mb-3">
                <label>Categories</label>
                <select name="categories[]" class="form-select" multiple>

                    <?php
                    $res = $conn->query("SELECT id, category FROM categories");

                    while ($row = $res->fetch_assoc()) {
                        $selected = in_array($row['id'], $selectedCategories) ? 'selected' : '';

                        echo "<option value='".(int)$row['id']."' $selected>"
                            .htmlspecialchars($row['category'])."</option>";
                    }
                    ?>

                </select>
            </div>

            <button class="btn btn-primary">Update</button>
            <a href="attributes.php" class="btn btn-secondary">Cancel</a>
        </form>

    </div>
</section>