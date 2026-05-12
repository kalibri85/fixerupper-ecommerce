<?php
require_once __DIR__ . '/includes/init.php';
requireAdmin();
$categoryID = (int)($_GET['category_id'] ?? 0);

$data = [];

if ($categoryID > 0) {
    $sql = $conn->prepare("
        SELECT a.id, a.name 
        FROM attributes a
        JOIN attributes_category ac ON ac.attributeID = a.id
        WHERE ac.categoryID = ?
    ");
    $sql->bind_param("i", $categoryID);
    $sql->execute();
    $data = $sql->get_result()->fetch_all(MYSQLI_ASSOC);
}

header('Content-Type: application/json');
echo json_encode($data);