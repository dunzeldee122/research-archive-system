<?php
$mysqli = new mysqli("localhost", "root", "", "research_archive");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$term = isset($_GET['term']) ? $_GET['term'] : '';

// Fetch top 10 suggestions
$stmt = $mysqli->prepare("
    SELECT id, title, sdg_goal, year 
    FROM researches 
    WHERE title LIKE CONCAT('%', ?, '%') 
       OR sdg_goal LIKE CONCAT('%', ?, '%') 
       OR year LIKE CONCAT('%', ?, '%')
    ORDER BY title ASC
    LIMIT 10
");
$stmt->bind_param("sss", $term, $term, $term);
$stmt->execute();
$result = $stmt->get_result();

$suggestions = [];
while ($row = $result->fetch_assoc()) {
    $suggestions[] = $row;
}

header('Content-Type: application/json');
echo json_encode($suggestions);
