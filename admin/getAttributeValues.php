<?php
require_once __DIR__ . '/includes/init.php';
requireAdmin();

$attributeID = (int)($_GET['attribute_id'] ?? 0);

$data = [];

if ($attributeID > 0) {
    $stmt = $conn->prepare("
        SELECT id, value 
        FROM attribute_values 
        WHERE attributeID = ?
        ORDER BY value ASC
    ");
    $stmt->bind_param("i", $attributeID);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

header('Content-Type: application/json');
echo json_encode($data);