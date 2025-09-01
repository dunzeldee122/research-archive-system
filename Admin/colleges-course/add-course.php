<?php
include "../../db/db_connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $college_id = intval($_POST['college_id']);
    $new_courses = $_POST['new_courses'] ?? [];

    if ($college_id > 0 && !empty($new_courses)) {
        $stmt = $conn->prepare("INSERT INTO courses (college_id, course_name) VALUES (?, ?)");
        foreach ($new_courses as $course) {
            $course = trim($course);
            if (!empty($course)) {
                $stmt->bind_param("is", $college_id, $course);
                $stmt->execute();
            }
        }
        $stmt->close();

        echo "<script>
            alert('Course(s) added successfully!');
            window.location.href = '../manage-colleges-courses.php';
        </script>";
        exit;
    } else {
        echo "<script>
            alert('Please select a college and enter course names.');
            window.history.back();
        </script>";
    }
}
?>
