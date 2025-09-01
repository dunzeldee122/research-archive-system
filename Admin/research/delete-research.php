<?php
session_start();
require_once '../../db/db_connect.php'; // Database connection

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>
        alert('No research ID provided.');
        window.location.href = 'manage-research.php';
    </script>";
    exit();
}

$id = intval($_GET['id']);

// 1. Get the file path from database
$check_sql = "SELECT file_path FROM researches WHERE id = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    echo "<script>
        alert('Research not found.');
        window.location.href = 'manage-research.php';
    </script>";
    exit();
}

$row = $result->fetch_assoc();
$filePath = $row['file_path']; 
$stmt->close();

// 2. Normalize file path (handle old + new uploads)
if (!empty($filePath)) {
    // If it's only filename (old records)
    if (strpos($filePath, 'uploads/researches/') === false) {
        $filePath = '../../uploads/researches/' . $filePath;
    } else {
        // New records already have path
        $filePath = '../../' . $filePath;
    }

    // Delete file if exists
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

// 3. Delete research record from DB
$delete_sql = "DELETE FROM researches WHERE id = ?";
$stmt = $conn->prepare($delete_sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    echo "<script>
        alert('Research and PDF deleted successfully.');
        window.location.href = '../manage-research.php';
    </script>";
    exit();
} else {
    $stmt->close();
    $conn->close();
    echo "<script>
        alert('Failed to delete research.');
        window.location.href = '../manage-research.php';
    </script>";
    exit();
}
?>
