<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../Faculty_Member/login.php");
    exit();
}

require_once '../db/db_connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch research record from database with college and course names
$stmt = $conn->prepare("
    SELECT r.id, r.title, r.abstract, r.author, r.year, r.sdg_goal, r.file_path, r.created_at,
           c.college_name, co.course_name
    FROM researches r
    LEFT JOIN colleges c ON r.college_id = c.id
    LEFT JOIN courses co ON r.course_id = co.id
    WHERE r.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$research = $result->fetch_assoc();

$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

if (!$research) {
    die("Research record not found.");
}

// SDG Goals List
$sdg_goals = [
    '1 No Poverty','2 Zero Hunger','3 Good Health and Well-Being','4 Quality Education',
    '5 Gender Equality','6 Clean Water and Sanitation','7 Affordable and Clean Energy',
    '8 Decent Work and Economic Growth','9 Industry, Innovation and Infrastructure',
    '10 Reduced Inequality','11 Sustainable Cities and Communities','12 Responsible Consumption and Production',
    '13 Climate Action','14 Life Below Water','15 Life on Land','16 Peace, Justice and Strong Institutions','17 Partnerships for the Goals'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Research</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
      html, body { height: 100%; margin: 0; }
      body { display: flex; flex-direction: column; min-height: 100vh; background-color: #f8f9fa; font-family: Arial, sans-serif; }
      main { flex: 1; }
      footer { background-color: #8d1515; color: white; text-align: center; padding: 15px 0; width: 100%; }
      .navbar-custom { background-color: #8d1515; }
      .navbar-brand, .navbar-text { color: #f8cc69 !important; }
      .form-container { max-width: 800px; margin: 40px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.05); }
      .form-title { color: #8d1515; font-weight: bold; }
      .update-btn { background-color: #8d1515; color: #f8cc69; }
      .update-btn:hover { background-color: #a61b1b; color: white; }
      .current-file { font-size: 14px; color: #555; }
  </style>
</head>
<body>

<nav class="navbar navbar-custom px-3">
  <a class="navbar-brand d-flex align-items-center" href="admin-dashboard.php">
    <img src="../assets/logo/ZPPSU_LOGO.png" alt="Logo" style="height: 40px; margin-right: 10px;">
    <span class="navbar-text">ZPPSU Admin Panel</span>
  </a>
</nav>

<div class="container mt-3">
  <a href="manage-research.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Manage Research</a>
</div>

<main>
<div class="form-container">
<?php if ($error): ?>
    <div class="alert alert-danger mt-3"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success mt-3"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<h4 class="form-title mb-4"><i class="bi bi-pencil-square me-2"></i>Edit Research Record</h4>

<form action="research/update-research.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $research['id'] ?>">

    <div class="mb-3">
      <label class="form-label">Created Date & Time</label>
      <input type="text" class="form-control" 
            value="<?= !empty($research['created_at']) ? date('F j, Y g:i A', strtotime($research['created_at'])) : '—' ?>" 
            readonly>
    </div>

    <div class="mb-3">
      <label class="form-label">Research Title</label>
      <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($research['title']) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Abstract</label>
      <textarea name="abstract" class="form-control" rows="4" required><?= htmlspecialchars($research['abstract']) ?></textarea>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Author</label>
        <input type="text" name="author" class="form-control" value="<?= htmlspecialchars($research['author']) ?>" required>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Year</label>
        <select name="year" class="form-select" required>
          <?php for ($y = date("Y"); $y >= 2000; $y--): ?>
            <option value="<?= $y ?>" <?= $y == $research['year'] ? 'selected' : '' ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">SDG Goal</label>
        <select name="goal" class="form-select" required>
          <option value="">Select Goal</option>
          <?php foreach ($sdg_goals as $goal): ?>
            <option value="<?= $goal ?>" <?= $goal == $research['sdg_goal'] ? 'selected' : '' ?>><?= $goal ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label">College</label>
        <select name="college_id" id="collegeSelect" class="form-select" required>
            <option value="">Select College</option>
            <?php
            $collegesResult = $conn->query("SELECT * FROM colleges ORDER BY college_name ASC");
            while ($c = $collegesResult->fetch_assoc()):
            ?>
            <option value="<?= $c['id'] ?>" <?= $c['college_name'] === $research['college_name'] ? 'selected' : '' ?>><?= $c['college_name'] ?></option>
            <?php endwhile; ?>
        </select>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Course</label>
      <select name="course_id" id="courseSelect" class="form-select" required>
        <option value="">Select Course</option>
      </select>
    </div>

    <div class="mb-4">
      <label class="form-label">Replace PDF File (optional)</label>
      <input type="file" name="research_file" accept="application/pdf" class="form-control">
      <div class="current-file mt-2">
        Current File:
        <?php if (!empty($research['file_path'])): ?>
          <a href="/research-archive-system/<?= htmlspecialchars($research['file_path']) ?>" target="_blank">
            <strong><?= htmlspecialchars($research['file_path']) ?></strong>
          </a>
        <?php else: ?>
          <em>No file uploaded</em>
        <?php endif; ?>
      </div>
    </div>

<div class="d-flex justify-content-between">
    <a href="../Admin/research/delete-research.php?id=<?= $research['id'] ?>" class="btn btn-danger"
       onclick="return confirm('Are you sure you want to delete this research?');">
       <i class="bi bi-trash"></i> Delete
    </a>
    <button type="submit" class="btn btn-primary"
            onclick="return confirm('Are you sure you want to update this research record?');">
        <i class="bi bi-save"></i> Update Research
    </button>
</div>
</form>
</div>
</main>

<footer>&copy; 2025 - Research Archive System</footer>

<script>
const collegeSelect = document.getElementById('collegeSelect');
const courseSelect = document.getElementById('courseSelect');
const currentCourse = <?= json_encode($research['course_name']) ?>;

function loadCourses(collegeId) {
    courseSelect.innerHTML = '<option value="">Select Course</option>';
    if (!collegeId) return;
    fetch('api/get-courses.php?college_id=' + collegeId)
    .then(res => res.json())
    .then(data => {
        data.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.course_name;
            if (c.course_name === currentCourse) opt.selected = true;
            courseSelect.appendChild(opt);
        });
    });
}

collegeSelect.addEventListener('change', () => loadCourses(collegeSelect.value));
window.addEventListener('DOMContentLoaded', () => loadCourses(collegeSelect.value));
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
 