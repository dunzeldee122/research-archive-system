<?php
// approve-user.php
include '../../db/db_connect.php'; // adjust the path as needed

// Check if user ID is passed
if (!isset($_GET['id'])) {
  header("Location: ../approval-user.php");
  exit;
}

$id = intval($_GET['id']); // sanitize input

// Update user status to 'approved'
$sql = "UPDATE faculty_members SET status = 'approved' WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
  echo "<script>
    alert('User ID $id has been approved!');
    window.location.href = '../approval-users.php';
  </script>";
} else {
  echo "<script>
    alert('Failed to approve user.');
    window.location.href = '../approval-users.php';
  </script>";
}
$stmt->close();
$conn->close();
