<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['faculty_id'])) {
    header("Location: login.php");
    exit;
}

// Database connection
$mysqli = new mysqli("localhost", "root", "", "research_archive"); // change DB name/user/pass if needed

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// SDG goals mapping
$sdg = [
    1 => 'No Poverty',
    2 => 'Zero Hunger',
    3 => 'Good Health and Well-being',
    4 => 'Quality Education',
    5 => 'Gender Equality',
    6 => 'Clean Water and Sanitation',
    7 => 'Affordable and Clean Energy',
    8 => 'Decent Work and Economic Growth',
    9 => 'Industry, Innovation and Infrastructure',
    10 => 'Reduced Inequality',
    11 => 'Sustainable Cities and Communities',
    12 => 'Responsible Consumption and Production',
    13 => 'Climate Action',
    14 => 'Life Below Water',
    15 => 'Life on Land',
    16 => 'Peace, Justice and Strong Institutions',
    17 => 'Partnerships for the Goals'
];

// Get research ID
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die("Invalid research ID.");
}

// Fetch research data
$stmt = $mysqli->prepare("
    SELECT r.*, 
           c.college_name, 
           cs.course_name
    FROM researches r
    LEFT JOIN colleges c ON r.college_id = c.id
    LEFT JOIN courses cs ON r.course_id = cs.id
    WHERE r.id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$research = $result->fetch_assoc();

if (!$research) {
    die("Research not found.");
}

$goal_name = $sdg[$research['sdg_goal']] ?? 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Research</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    body { background-color: #f9f9f9; font-family: Arial, sans-serif; }
    .navbar-custom { background-color: #8d1515; }
    .navbar-brand, .navbar-text { color: #f8cc69 !important; }
    .container { margin-top: 40px; margin-bottom: 40px; }
    .card { background-color: #fff; border: 1px solid #ddd; border-radius: 10px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .card-title { color: #8d1515; font-weight: bold; }
    .badge-sdg { background-color: #f8cc69; color: #8d1515; font-weight: bold; padding: 8px 12px; border-radius: 20px; font-size: 14px; }
    .btn-back { background-color: #8d1515; color: #f8cc69; border: none; }
    .btn-back:hover { background-color: #a61b1b; color: white; }
    .description-box { background-color: #f0f0f0; padding: 15px; border-radius: 8px; }
    .pdf-link { color: #38b6ff; text-decoration: none; }
    .pdf-link:hover { text-decoration: underline; }

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

<main>
<!-- Main Content -->
<div class="container">
  <a href="research-list.php" class="btn btn-back mb-4"><i class="bi bi-arrow-left"></i> Back to List</a>

      <div class="mb-3">
      <label class="form-label">Uploaded Date & Time</label>
      <input type="text" class="form-control" 
            value="<?= !empty($research['created_at']) ? date('F j, Y g:i A', strtotime($research['created_at'])) : '—' ?>" 
            readonly>
    </div>

  <div class="card">
    <h2 class="card-title"><?= htmlspecialchars($research['title']) ?></h2>
    <p><strong>Author:</strong> <?= htmlspecialchars($research['author']) ?></p>
    <p><strong>Year:</strong> <?= htmlspecialchars($research['year']) ?></p>
    <p><strong>College:</strong> <?= htmlspecialchars($research['college_name'] ?? 'N/A') ?></p>
    <p><strong>Course:</strong> <?= htmlspecialchars($research['course_name'] ?? 'N/A') ?></p>
    <p><strong>SDG GOAL:</strong> <?= htmlspecialchars($research['sdg_goal']) ?></p>

    <hr>

    <h5>Abstract:</h5>
    <div class="description-box mb-3">
      <?= nl2br(htmlspecialchars($research['abstract'])) ?>
    </div>

    <div class="current-file mt-2">
        PDF FILE: 
        <?php if (!empty($research['file_path'])): ?>
          <a href="/research-archive-system/<?= htmlspecialchars($research['file_path']) ?>" target="_blank">
              <strong>Click Here to View PDF</strong>
          </a>
        <?php else: ?>
          <em>No file uploaded</em>
        <?php endif; ?>
    </div>
  </div>
</div>
</main>

<footer>
    &copy; 2025 - Research Archive System
</footer>

</body>
</html>
