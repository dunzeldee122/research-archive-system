<?php
include "../../db/db_connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $college_id = intval($_POST['college_id']);
    $college_name = trim($_POST['college_name']);
    $course_ids = $_POST['course_ids'] ?? [];
    $course_names = $_POST['course_names'] ?? [];
    $new_courses = $_POST['new_courses'] ?? [];

    // ✅ Update College name
    $stmt = $conn->prepare("UPDATE colleges SET college_name=? WHERE id=?");
    $stmt->bind_param("si", $college_name, $college_id);
    $stmt->execute();
    $stmt->close();

    // ✅ Update existing courses
    foreach ($course_ids as $index => $course_id) {
        $course_name = trim($course_names[$index]);
        if (!empty($course_name)) {
            $stmt = $conn->prepare("UPDATE courses SET course_name=? WHERE id=? AND college_id=?");
            $stmt->bind_param("sii", $course_name, $course_id, $college_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // ✅ Insert new courses
    foreach ($new_courses as $new_course) {
        $new_course = trim($new_course);
        if (!empty($new_course)) {
            $stmt = $conn->prepare("INSERT INTO courses (college_id, course_name) VALUES (?, ?)");
            $stmt->bind_param("is", $college_id, $new_course);
            $stmt->execute();
            $stmt->close();
        }
    }

    // ✅ Success message
    echo "<script>
        alert('College and course update successfully!');
        window.location.href = '../manage-colleges-courses.php';
    </script>";
    exit;
}
?>
