<?php
// get-courses.php
header('Content-Type: application/json');
include '../../db/db_connect.php'; // Adjust path as needed

if (!isset($_GET['college_id'])) {
    echo json_encode([]);
    exit;
}

$college_id = intval($_GET['college_id']);

// Fetch courses for the selected college
$stmt = $conn->prepare("SELECT id, course_name FROM courses WHERE college_id = ? ORDER BY course_name ASC");
$stmt->bind_param("i", $college_id);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($courses);
