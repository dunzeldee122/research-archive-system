<?php
require '../../db/db_connect.php';

header('Content-Type: application/json');

$title = isset($_GET['title']) ? trim($_GET['title']) : '';
$abstract = isset($_GET['abstract']) ? trim($_GET['abstract']) : '';

if ($title === '' && $abstract === '') {
    echo json_encode(['exists' => false]);
    exit;
}

// Use LIKE with case-insensitive check
$stmt = $conn->prepare("
    SELECT id FROM researches 
    WHERE TRIM(LOWER(title)) = LOWER(?) 
       OR TRIM(LOWER(abstract)) = LOWER(?) 
    LIMIT 1
");
$stmt->bind_param("ss", $title, $abstract);
$stmt->execute();
$result = $stmt->get_result();

echo json_encode(['exists' => $result->num_rows > 0]);

$stmt->close();
$conn->close();
