<?php 
// reject-user.php

include '../../db/db_connect.php'; // your DB connection file

if (!isset($_GET['id'])) {
    header("Location: ../approval-users.php");
    exit;
}

$id = intval($_GET['id']); // sanitize input

$sql = "DELETE FROM faculty_members WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $id);

// Execute once and check result
if ($stmt->execute()) {
    echo "<script>
      alert('User ID $id has been rejected.');
      window.location.href = '../approval-users.php';
    </script>";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
