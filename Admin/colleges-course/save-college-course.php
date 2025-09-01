<?php
// Admin/college-course/save-college-course.php
include '../../db/db_connect.php'; // adjust if your DB connection file has a different path

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $collegeName = trim($_POST['college_name'] ?? '');
    $courses = $_POST['courses'] ?? [];

    if (!empty($collegeName)) {
        // Insert College
        $stmt = $conn->prepare("INSERT INTO colleges (college_name) VALUES (?)");
        $stmt->bind_param("s", $collegeName);

        if ($stmt->execute()) {
            $collegeId = $stmt->insert_id;
            $stmt->close();

            // Insert Courses if provided
            if (!empty($courses)) {
                $stmtCourse = $conn->prepare("INSERT INTO courses (college_id, course_name) VALUES (?, ?)");
                foreach ($courses as $course) {
                    $courseName = trim($course);
                    if (!empty($courseName)) {
                        $stmtCourse->bind_param("is", $collegeId, $courseName);
                        $stmtCourse->execute();
                    }
                }
                $stmtCourse->close();
            }

            echo "<script>
                alert('College and courses saved successfully!');
                window.location.href = '../manage-colleges-courses.php';
            </script>";
            exit;
        } else {
            echo "<script>
                alert('Error saving college: " . $conn->error . "');
                window.history.back();
            </script>";
        }
    } else {
        echo "<script>
            alert('College name is required!');
            window.history.back();
        </script>";
    }
}
