<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "research_archive");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if ($mysqli->query("DELETE FROM notifications") === TRUE) {
    $_SESSION['msg'] = "All notifications cleared successfully.";
} else {
    $_SESSION['msg'] = "Error clearing notifications: " . $mysqli->error;
}

$mysqli->close();
header("Location: ../admin-dashboard.php");
exit();
?>
