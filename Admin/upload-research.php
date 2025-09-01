<?php
session_start();
require '../db/db_connect.php';

// Redirect to login if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../Faculty_Member/login.php");
    exit();
}


$message = "";

// Fetch all colleges for the dropdown
$colleges = $conn->query("SELECT id, college_name FROM colleges ORDER BY college_name ASC")->fetch_all(MYSQLI_ASSOC);

// Initialize selected values
$selectedCollege = $_POST['college'] ?? '';
$selectedCourse = $_POST['course'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $abstract = trim($_POST['abstract']);
    $author = trim($_POST['author']);
    $year = $_POST['year'];
    $goal = $_POST['goal'];
    $college = intval($_POST['college']);
    $course = intval($_POST['course']);

    // Check for duplicate research (title OR abstract)
    $stmtCheck = $conn->prepare("SELECT id FROM researches WHERE title = ? OR abstract = ?");
    $stmtCheck->bind_param("ss", $title, $abstract);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        $message = "<div class='alert alert-warning'>The Title or Abstract Already Exist!</div>";
        $stmtCheck->close();
    } else {
        $stmtCheck->close();

        // File upload handling
        if (isset($_FILES['research_file']) && $_FILES['research_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['research_file']['tmp_name'];
            $fileName = $_FILES['research_file']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if ($fileExtension !== 'pdf') {
                $message = "<div class='alert alert-danger'>Only PDF files are allowed.</div>";
            } else {
                $newFileName = time() . "_" . preg_replace("/[^a-zA-Z0-9_\-\.]/", "_", $fileName);
                $uploadFileDir = '../uploads/researches/';
                if (!file_exists($uploadFileDir)) mkdir($uploadFileDir, 0777, true);

                $dest_path = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $dbFilePath = 'uploads/researches/' . $newFileName;

                    $stmt = $conn->prepare("INSERT INTO researches (title, abstract, author, year, sdg_goal, college_id, course_id, file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssiis", $title, $abstract, $author, $year, $goal, $college, $course, $dbFilePath);

                    if ($stmt->execute()) {
                        $message = "<div class='alert alert-success'>Research uploaded successfully!</div>";
                        // Clear form fields
                        $_POST = [];
                        $selectedCollege = '';
                        $selectedCourse = '';
                    } else {
                        $message = "<div class='alert alert-danger'>Database error: " . $stmt->error . "</div>";
                    }
                    $stmt->close();
                } else {
                    $message = "<div class='alert alert-danger'>Error uploading the file.</div>";
                }
            }
        } else {
            $message = "<div class='alert alert-danger'>Please upload a file.</div>";
        }
    }
}

// Fetch courses for selected college
$courses = [];
if ($selectedCollege) {
    $stmt = $conn->prepare("SELECT id, course_name FROM courses WHERE college_id = ? ORDER BY course_name ASC");
    $stmt->bind_param("i", $selectedCollege);
    $stmt->execute();
    $courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Upload Research</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; font-family: Arial, sans-serif; display: flex; flex-direction: column; min-height: 100vh; }
footer { background-color: #8d1515; color: white; text-align: center; padding: 15px 0; width: 100%; }
.navbar-custom { background-color: #8d1515; }
.navbar-brand, .nav-link, .navbar-text { color: #f8cc69 !important; }
.form-container { max-width: 800px; margin: 40px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.05); }
.form-title { color: #8d1515; font-weight: bold; }
.submit-btn { background-color: #8d1515; color: #f8cc69; }
.submit-btn:hover { background-color: #a61b1b; color: white; }
.text-danger { font-size: 14px; }
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
  <a href="admin-dashboard.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
</div>

<main>
<div class="form-container">
<?= $message ?>
<h4 class="form-title mb-4"><i class="bi bi-cloud-upload me-2"></i>Upload New Research</h4>

<form method="POST" action="#" enctype="multipart/form-data">
<div class="mb-3">
  <label class="form-label">Research Title</label>
  <input type="text" name="title" id="titleInput" class="form-control" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
  <div id="titleWarning" class="text-danger mt-1" style="display:none;">The Title or Abstract Already Exist!</div>
</div>

<div class="mb-3">
  <label class="form-label">Abstract</label>
  <textarea name="abstract" id="abstractInput" class="form-control" rows="5" required><?= htmlspecialchars($_POST['abstract'] ?? '') ?></textarea>
  <div id="abstractWarning" class="text-danger mt-1" style="display:none;">The Title or Abstract Already Exist!</div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label">Author</label>
      <input type="text" name="author" class="form-control" value="<?= htmlspecialchars($_POST['author'] ?? '') ?>" required>
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Year</label>
      <select name="year" class="form-select" required>
        <option value="">Select Year</option>
        <?php for ($y = date("Y"); $y >= 2000; $y--): ?>
          <option value="<?= $y ?>" <?= (isset($_POST['year']) && $_POST['year']==$y) ? 'selected' : '' ?>><?= $y ?></option>
        <?php endfor; ?>
      </select>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label">SDG Goal</label>
      <select name="goal" class="form-select" required>
        <option value="">Select Goal</option>
        <?php
        $sdg = [
          '1 No Poverty','2 Zero Hunger','3 Good Health and Well-Being','4 Quality Education','5 Gender Equality',
          '6 Clean Water and Sanitation','7 Affordable and Clean Energy','8 Decent Work and Economic Growth',
          '9 Industry, Innovation and Infrastructure','10 Reduced Inequality','11 Sustainable Cities and Communities',
          '12 Responsible Consumption and Production','13 Climate Action','14 Life Below Water','15 Life on Land',
          '16 Peace, Justice and Strong Institutions','17 Partnerships for the Goals'
        ];
        foreach ($sdg as $name) {
            $selected = (isset($_POST['goal']) && $_POST['goal']==$name) ? 'selected' : '';
            echo "<option value=\"$name\" $selected>$name</option>";
        }
        ?>
      </select>
    </div>

    <div class="col-md-6 mb-3">
      <label class="form-label">College</label>
      <select name="college" class="form-select" id="collegeSelect" required>
        <option value="">Select College</option>
        <?php foreach($colleges as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $selectedCollege==$c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['college_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
</div>

<div class="mb-3">
  <label class="form-label">Course</label>
  <select name="course" class="form-select" id="courseSelect" required>
    <option value="">Select Course</option>
    <?php foreach($courses as $c): ?>
      <option value="<?= $c['id'] ?>" <?= $selectedCourse==$c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['course_name']) ?></option>
    <?php endforeach; ?>
  </select>
</div>

<div class="mb-4">
  <label class="form-label">Upload File (PDF Only)</label>
  <input type="file" name="research_file" accept="application/pdf" class="form-control" required>
</div>

<div class="text-end">
  <button type="submit" class="btn submit-btn"><i class="bi bi-upload me-1"></i>Submit</button>
</div>
</form>
</div>
</main>

<footer>&copy; 2025 - Research Archive System</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const collegeSelect = document.getElementById('collegeSelect');
const courseSelect = document.getElementById('courseSelect');

collegeSelect.addEventListener('change', function() {
    const collegeId = this.value;
    courseSelect.innerHTML = '<option>Loading courses...</option>';
    fetch(`api/get-courses.php?college_id=${collegeId}`)
      .then(res => res.json())
      .then(data => {
          courseSelect.innerHTML = '<option value="">Select Course</option>';
          data.forEach(course => {
              const opt = document.createElement('option');
              opt.value = course.id;
              opt.textContent = course.course_name;
              courseSelect.appendChild(opt);
          });
      });
});

const titleInput = document.getElementById('titleInput');
const abstractInput = document.getElementById('abstractInput');
const titleWarning = document.getElementById('titleWarning');
const abstractWarning = document.getElementById('abstractWarning');
const submitBtn = document.querySelector('button[type="submit"]');

function checkDuplicate() {
    const title = titleInput.value.trim();
    const abstract = abstractInput.value.trim();

    if (title && abstract) {
        fetch(`api/check-research.php?title=${encodeURIComponent(title)}&abstract=${encodeURIComponent(abstract)}`)
        .then(res => res.json())
        .then(data => {
            if (data.exists) {
                titleWarning.style.display = 'block';
                abstractWarning.style.display = 'block';
                submitBtn.disabled = true;
            } else {
                titleWarning.style.display = 'none';
                abstractWarning.style.display = 'none';
                submitBtn.disabled = false;
            }
        });
    } else {
        titleWarning.style.display = 'none';
        abstractWarning.style.display = 'none';
        submitBtn.disabled = false;
    }
}

titleInput.addEventListener('input', checkDuplicate);
abstractInput.addEventListener('input', checkDuplicate);
</script>
</body>
</html>
