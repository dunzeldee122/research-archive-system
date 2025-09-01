<?php
session_start();
include "../../db/db_connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $college_id = intval($_POST['college_id']);

    if ($college_id > 0) {
        // Delete all courses under this college
        $stmt = $conn->prepare("DELETE FROM courses WHERE college_id = ?");
        $stmt->bind_param("i", $college_id);
        $stmt->execute();
        $stmt->close();

        // Delete the college itself
        $stmt = $conn->prepare("DELETE FROM colleges WHERE id = ?");
        $stmt->bind_param("i", $college_id);
        $stmt->execute();
        $stmt->close();

        // ✅ Show alert and redirect
        echo "<script>
            alert('College and Courses deleted successfully!');
            window.location.href = '../manage-colleges-courses.php';
        </script>";
        exit;
    } else {
        echo "<script>
            alert('Invalid college ID!');
            window.location.href = '../manage-colleges-courses.php';
        </script>";
        exit;
    }
} else {
    echo "<script>
        alert('Invalid request method!');
        window.location.href = '../manage-colleges-courses.php';
    </script>";
    exit;
}
?>
