<?php
header('Content-Type: application/json');
require_once '../../db/db_connect.php';

if (!isset($_GET['college_id'])) {
    echo json_encode([]);
    exit;
}

$college_id = intval($_GET['college_id']);

// Fetch courses for the given college
$stmt = $conn->prepare("SELECT id, course_name FROM courses WHERE college_id = ? ORDER BY course_name ASC");
$stmt->bind_param("i", $college_id);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

echo json_encode($courses);
