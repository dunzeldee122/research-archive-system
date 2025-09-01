<?php
session_start();
require '../../db/db_connect.php';

if (!isset($_POST['id'])) {
    header("Location: ../manage-research.php");
    exit;
}

$id = intval($_POST['id']);
$title = trim($_POST['title']);
$abstract = trim($_POST['abstract']);
$author = trim($_POST['author']);
$year = $_POST['year'];
$goal = $_POST['goal'];
$college_id = $_POST['college_id'] ?? null;
$course_id = $_POST['course_id'] ?? null;

// Fetch existing file path
$stmt = $conn->prepare("SELECT file_path FROM researches WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$research = $result->fetch_assoc();
$currentFile = $research['file_path'] ?? null;
$stmt->close();

// Handle file upload if a new file is selected
$filePath = $currentFile;
if (isset($_FILES['research_file']) && $_FILES['research_file']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['research_file']['tmp_name'];
    $fileName = $_FILES['research_file']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if ($fileExtension !== 'pdf') {
        header("Location: ../edit-research.php?id=$id&error=Only PDF files are allowed");
        exit;
    }

    $newFileName = time() . "_" . preg_replace("/[^a-zA-Z0-9_\-\.]/", "_", $fileName);
    $uploadDir = '../../uploads/researches/';
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
    $destPath = $uploadDir . $newFileName;

    if (move_uploaded_file($fileTmpPath, $destPath)) {
        // Delete old file if exists
        if ($currentFile && file_exists('../../' . $currentFile)) {
            unlink('../../' . $currentFile);
        }
        $filePath = 'uploads/researches/' . $newFileName;
    } else {
        header("Location: ../edit-research.php?id=$id&error=Error uploading the file");
        exit;
    }
}

// Update research in DB
$stmt = $conn->prepare("
    UPDATE researches 
    SET title=?, abstract=?, author=?, year=?, sdg_goal=?, college_id=?, course_id=?, file_path=? 
    WHERE id=?
");
$stmt->bind_param("sssssiisi", $title, $abstract, $author, $year, $goal, $college_id, $course_id, $filePath, $id);

if ($stmt->execute()) {
    header("Location: ../edit-research.php?id=$id&success=Research updated successfully");
} else {
    header("Location: ../edit-research.php?id=$id&error=Database error: " . $stmt->error);
}

$stmt->close();
$conn->close();
