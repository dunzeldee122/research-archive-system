<?php
include '../../db/db_connect.php'; // Adjust path as needed

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete the user
    $stmt = $conn->prepare("DELETE FROM faculty_members WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Redirect back with success message
    header("Location: ../manage-user.php?deleted=1");
    exit();
}
?>
