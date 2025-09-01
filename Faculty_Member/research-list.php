<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['faculty_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../db/db_connect.php'; // DB connection

// Fetch all colleges for the dropdown
$colleges = $conn->query("SELECT id, college_name FROM colleges ORDER BY college_name ASC")->fetch_all(MYSQLI_ASSOC);

// Initialize selected values

$selectedCollege = $_GET['college'] ?? '';
$selectedCourse  = $_GET['course'] ?? '';



$goal = isset($_GET['goal']) ? intval($_GET['goal']) : 0;
$year = isset($_GET['year']) ? intval($_GET['year']) : 0;
$college_id = isset($_GET['college']) ? intval($_GET['college']) : 0;
$course_id = isset($_GET['course']) ? intval($_GET['course']) : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : "";

// Fetch researches with JOINs
$sql = "SELECT r.id, r.title, r.author, r.year, r.sdg_goal, 
               c.college_name, co.course_name
        FROM researches r
        LEFT JOIN colleges c ON r.college_id = c.id
        LEFT JOIN courses co ON r.course_id = co.id
        WHERE 1=1";

if ($goal > 0) {
    $sql .= " AND r.sdg_goal = $goal";
}
if ($year > 0) {
    $sql .= " AND r.year = $year";
}
if ($college_id > 0) {
    $sql .= " AND r.college_id = $college_id";
}
if ($course_id > 0) {
    $sql .= " AND r.course_id = $course_id";
}
if (!empty($search)) {
    $searchEscaped = $conn->real_escape_string($search);
    $sql .= " AND (r.title LIKE '%$searchEscaped%' OR r.author LIKE '%$searchEscaped%' OR r.year LIKE '%$searchEscaped%')";
}

$sql .= " ORDER BY r.title ASC";

$result = $conn->query($sql);

$researches = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['goal_name'] = $sdg[$row['sdg_goal']] ?? 'N/A';
        $researches[] = $row;
    }
}

// Apply filters in PHP (optional since SQL already does it)
$filtered = $researches;
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Research List</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    body { background-color: #f8f9fa; font-family: Arial, sans-serif; }
    .navbar-custom { background-color: #8d1515; }
    .navbar-brand, .navbar-text, .nav-link { color: #f8cc69 !important; }

    html, body {
    height: 100%;
    margin: 0;
    }

    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    main {
        flex: 1; /* Push footer down */
    }

    footer {
        background-color: #8d1515;
        color: white;
        text-align: center;
        padding: 15px 0;
        width: 100%;
    }


    table {
      max-width: 1300px;
      margin: 10px auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.05); /* Keep table within container */
      table-layout: fixed; /* Forces fixed width for truncation */
  }
  table thead tr th {
    background-color: #8d1515 !important;
    color: white !important;
    text-align: center;
  }
  /* Clickable row style */
  .clickable-row {
    cursor: pointer;
  }
  /* Truncate text in cells */
  .truncate {
    max-width: 160px; /* Adjust this value as needed */
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  /* Adjust table row height */
  table tbody tr {
    height: 50px; /* Increased row height */
    vertical-align: middle; /* Center text vertically */
  }
  /* Hover effect */
  .clickable-row:hover {
    background-color: #f9d6d6; /* Light red hover */
  }
  
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom px-4">
  <a class="navbar-brand d-flex align-items-center" href="home.php">
    <img src="../assets/logo/ZPPSU_LOGO.png" alt="Logo" style="height: 40px; margin-right: 10px;">
    <span class="navbar-text">Zamboanga Research Archives</span>
  </a>
</nav>

<!-- Back Button --> 
<div class="container mt-3">
  <a href="home.php" class="btn" style="background-color: #8d1515; color: #f8cc69;">
    <i class="bi bi-arrow-left"></i> Back to Home
  </a>
</div>

<main>
<!-- Search & Filter -->
<div class="container mt-4">
  <div class="row mb-3 align-items-center">
    <div class="col-md-9">
      <form method="GET" action="research-list.php">
        <div class="input-group">
        <input type="text" id="searchInput" class="form-control" placeholder="Search research by title, author, college, course, SDG goal">
        </div>
      </form>
    </div>
    
    <div class="col-md-3 text-end">
      <button class="btn btn-outline-secondary w-100" data-bs-toggle="collapse" data-bs-target="#filterPanel"><i class="bi bi-funnel-fill"></i> Filters</button>
    </div>
  </div>


  <div class="collapse" id="filterPanel">
    <div class="card card-body mb-3">
      <form method="GET" action="research-list.php">
        <div class="row g-3">
          <div class="col-md-6 col-lg-3">
            <select name="goal" class="form-select">
              <option value="0">All SDG</option>
              <?php
              $sdg = [
                1 => 'No Poverty', 2 => 'Zero Hunger', 3 => 'Good Health and Well-Being',
                4 => 'Quality Education', 5 => 'Gender Equality', 6 => 'Clean Water and Sanitation',
                7 => 'Affordable and Clean Energy', 8 => 'Decent Work and Economic Growth',
                9 => 'Industry, Innovation and Infrastructure', 10 => 'Reduced Inequality',
                11 => 'Sustainable Cities and Communities', 12 => 'Responsible Consumption and Production',
                13 => 'Climate Action', 14 => 'Life Below Water', 15 => 'Life on Land',
                16 => 'Peace, Justice and Strong Institutions', 17 => 'Partnerships for the Goals'
              ];
              foreach ($sdg as $key => $value) {
                $selected = ($goal == $key) ? 'selected' : '';
                echo "<option value=\"$key\" $selected>Goal $key - $value</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-md-6 col-lg-3">
            <select name="year" class="form-select">
              <option value="0">All Year</option>
              <?php for ($y = 2000; $y <= 2025; $y++): ?>
                <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-md-6 col-lg-3">
          <select name="college" class="form-select" id="collegeSelect">
            <option value="">Select College</option>
            <?php foreach($colleges as $c): ?>
              <option value="<?= $c['id'] ?>" <?= $selectedCollege==$c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['college_name']) ?></option>
            <?php endforeach; ?>
          </select>

          </div>
          <div class="col-md-6 col-lg-3">
          <select name="course" class="form-select" id="courseSelect">
              <option value="">Select Course</option>
              <?php foreach($courses as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $selectedCourse==$c['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['course_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>

          </div>
        </div>
        <div class="text-end mt-3">
        <a href="research-list.php" class="btn" style="background-color:#f8cc69; color: #8d1515;">All Research</a>
        <button class="btn" style="background-color:rgb(141, 141, 141); color:rgb(255, 255, 255);" data-bs-toggle="collapse" data-bs-target="#filterPanel">Cancel</button>
          <button type="submit" class="btn" style="background-color: #8d1515; color: #f8cc69;">Apply</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- Research Table -->
<div class="row mt-4">
  <div class="col-12">
    <?php if (count($filtered) === 0): ?>
      <p class="text-center">No research found.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>Title</th>
              <th>Author</th>
              <th>Year</th>
              <th>College</th>
              <th>Course</th>
              <th>SDG Goal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($filtered as $r): ?>
              <tr class="clickable-row" data-href="view-research.php?id=<?= $r['id'] ?>">
                <td class="truncate" title="<?= htmlspecialchars($r['title']) ?>"><?= htmlspecialchars($r['title']) ?>
                <td class="truncate" title="<?= htmlspecialchars($r['author']) ?>"><?= htmlspecialchars($r['author']) ?></td>
                <td><?= $r['year'] ?></td>
                <td class="truncate" title="<?= htmlspecialchars($r['college_name']) ?>"><?= htmlspecialchars($r['college_name']) ?></td>
                <td class="truncate" title="<?= htmlspecialchars($r['course_name']) ?>"><?= htmlspecialchars($r['course_name']) ?></td>
                <td><?= htmlspecialchars($r['sdg_goal']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
</main>

<footer>
    &copy; 2025 - Research Archive System
</footer>

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

document.addEventListener("DOMContentLoaded", function () {
  const selectedCollege = "<?= $selectedCollege ?>";
  const selectedCourse  = "<?= $selectedCourse ?>";

  if (selectedCollege) {
    fetch(`api/get-courses.php?college_id=${selectedCollege}`)
      .then(res => res.json())
      .then(data => {
        courseSelect.innerHTML = '<option value="">Select Course</option>';
        data.forEach(course => {
          const opt = document.createElement('option');
          opt.value = course.id;
          opt.textContent = course.course_name;
          if (course.id == selectedCourse) {
            opt.selected = true;
          }
          courseSelect.appendChild(opt);
        });
      });
  }
});

  // Make rows clickable
  document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".clickable-row").forEach(function(row) {
      row.addEventListener("click", function() {
        window.location.href = this.dataset.href;
      });
    });
  });

  document.getElementById('searchInput').addEventListener('input', function () {
    const searchValue = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');

    rows.forEach(row => {
      const rowText = row.innerText.toLowerCase();
      row.style.display = rowText.includes(searchValue) ? '' : 'none';
    });
  });
</script>

</body>
</html>
