<?php
session_start();
include "../../db/db_connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = intval($_POST['course_id']);
    $college_id = intval($_POST['college_id'] ?? 0); // optional, for reference

    if ($course_id > 0) {
        $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $stmt->close();

        // ✅ Show alert and redirect
        echo "<script>
                alert('Course deleted successfully!');
                window.location.href = '../manage-colleges-courses.php';
              </script>";
        exit;
    } else {
        echo "<script>
                alert('Invalid course ID!');
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
