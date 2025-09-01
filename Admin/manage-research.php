<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../Faculty_Member/login.php");
    exit();
}

require_once '../db/db_connect.php'; // DB connection

// Fetch research list from database with college and course names
$sql = "
SELECT r.id, r.title, r.author, r.year, r.sdg_goal, r.created_at,
       c.college_name, co.course_name
FROM researches r
LEFT JOIN colleges c ON r.college_id = c.id
LEFT JOIN courses co ON r.course_id = co.id
";

$result = $conn->query($sql);

$researches = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $researches[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Research</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: Arial, sans-serif;
    }
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
    .navbar-custom {
      background-color: #8d1515;
    }
    .navbar-brand, .navbar-text {
      color: #f8cc69 !important;
    }
    .truncate {
      max-width: 160px; /* adjust width as needed */
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .table-container {
      max-width: 1300px;
      margin: 40px auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.05);
    }
    .table th {
      background-color: #8d1515;
      color: #fff;
      text-align: center;
    }
    .table {
      table-layout: fixed;
    }
    .action-icons {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 10px;
    }
    .action-icons i {
      cursor: pointer;
    }
    .action-icons i:hover {
      opacity: 0.8;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-custom px-3">
  <a class="navbar-brand d-flex align-items-center" href="admin-dashboard.php">
    <img src="../assets/logo/ZPPSU_LOGO.png" alt="Logo" style="height: 40px; margin-right: 10px;">
    <span class="navbar-text">ZPPSU Admin Panel</span>
  </a>
</nav>

<!-- Back Button -->
<div class="container mt-3">
  <a href="admin-dashboard.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
</div>

<main>
<!-- Table of Research -->
<div class="table-container">
  <h4 class="mb-4 text-danger"><i class="bi bi-journals me-2"></i>Manage Research Records</h4>

  <div class="mb-3">
    <input type="text" id="searchInput" class="form-control" placeholder="Search research by title, author, college, course, SDG goal, or created time...">
  </div>

  <table class="table table-bordered table-striped table-hover align-middle">
    <thead>
      <tr>
        <th>Title</th>
        <th>Author</th>
        <th>Year</th>
        <th>SDG Goal</th>
        <th>College</th>
        <th>Course</th>
        <th>Created Time</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($researches)): ?>
        <?php foreach ($researches as $r): ?>
          <tr>
            <td class="truncate" title="<?= htmlspecialchars($r['title']) ?>"><?= htmlspecialchars($r['title']) ?></td>
            <td class="truncate" title="<?= htmlspecialchars($r['author']) ?>"><?= htmlspecialchars($r['author']) ?></td>
            <td><?= $r['year'] ?></td>
            <td class="truncate" title="<?= htmlspecialchars($r['sdg_goal']) ?>"><?= htmlspecialchars($r['sdg_goal']) ?></td>
            <td class="truncate" title="<?= htmlspecialchars($r['college_name']) ?>"><?= htmlspecialchars($r['college_name']) ?></td>
            <td class="truncate" title="<?= htmlspecialchars($r['course_name']) ?>"><?= htmlspecialchars($r['course_name']) ?></td>
            <td><?= !empty($r['created_at']) ? date('F j, Y', strtotime($r['created_at'])) : '—' ?></td>
            <td class="action-icons">
              <a href="edit-research.php?id=<?= $r['id'] ?>" class="text-primary"><i class="bi bi-pencil-square"></i></a>
              <a href="../Admin/research/delete-research.php?id=<?= $r['id'] ?>" class="text-danger" onclick="return confirm('Are you sure you want to delete this research?');"><i class="bi bi-trash"></i></a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="8" class="text-center text-muted">No research records found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</main>

<footer>
    &copy; 2025 - Research Archive System
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Simple table search
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
