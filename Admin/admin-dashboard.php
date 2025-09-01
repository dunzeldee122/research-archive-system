<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../Faculty_Member/login.php");
    exit();
}

// Database connection
$mysqli = new mysqli("localhost", "root", "", "research_archive");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Fetch Admin info
$adminName = "Admin"; // default
$adminRes = $mysqli->query("SELECT full_name FROM admins WHERE id = " . intval($_SESSION['admin_id']));
if ($adminRes && $adminRes->num_rows > 0) {
    $adminData = $adminRes->fetch_assoc();
    $adminName = $adminData['full_name'];
}

// Fetch Notifications with College Name
$notifications = [];
$sql = "
    SELECT n.id, fm.first_name, fm.last_name, fm.id_number, fm.email, fm.phone, c.college_name, n.message, n.created_at
    FROM notifications AS n
    JOIN faculty_members AS fm ON n.id_number = fm.id_number
    LEFT JOIN colleges AS c ON fm.college_id = c.id
    ORDER BY n.created_at DESC
";
$notifResult = $mysqli->query($sql);
if ($notifResult) {
    while ($row = $notifResult->fetch_assoc()) {
        $row['time'] = date("M d, Y H:i", strtotime($row['created_at']));
        $notifications[] = $row;
    }
}
$notificationCount = count($notifications);

// Fetch Researches with College Name
$researches = [];
$researchResult = $mysqli->query("
    SELECT r.title, r.sdg_goal, c.college_name
    FROM researches r
    LEFT JOIN colleges c ON r.college_id = c.id
");
if ($researchResult) {
    while ($row = $researchResult->fetch_assoc()) {
        $researches[] = $row;
    }
}

// Group Researches by College
$researchByCollege = [];
foreach ($researches as $r) {
    $college = $r['college_name'] ?? 'Unknown';
    $researchByCollege[$college] = ($researchByCollege[$college] ?? 0) + 1;
}

// Group Researches by SDG Goal
$researchBySDG = [];
foreach ($researches as $r) {
    $goal = $r['sdg_goal'] ?? 0;
    $researchBySDG[$goal] = ($researchBySDG[$goal] ?? 0) + 1;
}

// Fetch Approved Users with College Name
$users = [];
$userResult = $mysqli->query("
    SELECT fm.id, fm.first_name, fm.last_name, fm.status, c.college_name
    FROM faculty_members fm
    LEFT JOIN colleges c ON fm.college_id = c.id
    WHERE fm.status = 'approved'
");
if ($userResult) {
    while ($row = $userResult->fetch_assoc()) {
        $users[] = $row;
    }
}

// Fetch count of pending users separately
$pendingUsers = [];
$pendingResult = $mysqli->query("SELECT id FROM faculty_members WHERE status = 'pending'");
if ($pendingResult) {
    while ($row = $pendingResult->fetch_assoc()) {
        $pendingUsers[] = $row;
    }
}

// Group Users by College
$userByCollege = [];
foreach ($users as $u) {
    $college = $u['college_name'] ?? 'Unknown';
    $userByCollege[$college] = ($userByCollege[$college] ?? 0) + 1;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
   html, body { height: 100%; margin: 0; }
   body { display: flex; flex-direction: column; min-height: 100vh; }
   main { flex: 1; }
   footer { background-color: #8d1515; color: white; text-align: center; padding: 15px 0; width: 100%; }
   .container{ margin-bottom: 100px; }
   .navbar-custom { background-color: #8d1515; }
   .navbar-brand, .nav-link, .navbar-text { color: #f8cc69 !important; }
   .offcanvas { background-color: #8d1515; color: #f8cc69; }
   .offcanvas a { color: #f8cc69; text-decoration: none; display: block; padding: 10px 0; }
   .offcanvas a:hover { background-color: #a61b1b; padding-left: 5px; border-radius: 5px; }
   .dashboard-title { color: #8d1515; font-weight: bold; }
   .card { border-left: 5px solid #8d1515; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-custom px-3 d-flex justify-content-between align-items-center">
  <div class="d-flex align-items-center">
    <button class="btn btn-sm text-white" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar">
      <i class="bi bi-list" style="font-size: 24px;"></i>
    </button>
    <a class="navbar-brand ms-3 d-flex align-items-center" href="#">
      <img src="../assets/logo/ZPPSU_LOGO.png" alt="Logo" style="height: 40px; margin-right: 10px;" />
      <span class="navbar-text">ZPPSU Research Admin</span>
    </a>
  </div>

  <!-- Notification Bell + Admin Name -->
  <div class="d-flex align-items-center me-3">
    <div class="dropdown me-3">
      <button class="btn position-relative text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell-fill fs-5"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
          <?= $notificationCount ?>
        </span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end p-2 shadow" style="width: 320px; max-height: 300px; overflow-y: auto;">
        <li class="dropdown-header fw-bold text-dark d-flex justify-content-between align-items-center">
          <span>Notifications</span>
          <?php if ($notificationCount > 0): ?>
            <form action="../Admin/notif/clear-notifications.php" method="POST" style="display:inline;" 
                  onsubmit="return confirm('Are you sure you want to clear all notifications?');">
              <button type="submit" class="btn btn-sm btn-outline-danger">Clear All</button>
            </form>
          <?php endif; ?>
        </li>

        <?php if (!empty($notifications)): ?>
          <?php foreach ($notifications as $note): ?>
            <li>
              <button class="dropdown-item text-wrap small text-start" data-bs-toggle="modal" data-bs-target="#notifModal<?= $note['id'] ?>">
                <?= htmlspecialchars($note['last_name'] . ', ' . $note['first_name'] . ' (ID: ' . $note['id_number'] . ')') ?> - <?= htmlspecialchars($note['message']) ?>
                <div class="text-muted small"><?= $note['time'] ?></div>
              </button>
            </li>
          <?php endforeach; ?>
        <?php else: ?>
          <li class="dropdown-item text-muted text-center">No new notifications</li>
        <?php endif; ?>
      </ul>
    </div>

    <div class="text-white">Hello, <strong><?= htmlspecialchars($adminName) ?></strong></div>
  </div>

  <?php foreach ($notifications as $note): ?>
  <div class="modal fade" id="notifModal<?= $note['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #8d1515; color: #f8cc69;">
          <h5 class="modal-title"><i class="bi bi-envelope-exclamation me-2"></i>Password Request</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p><strong>Name:</strong> <?= htmlspecialchars($note['last_name'] . ', ' . $note['first_name']) ?></p>
          <p><strong>ID Number:</strong> <?= htmlspecialchars($note['id_number']) ?></p>
          <p><strong>College:</strong> <?= htmlspecialchars($note['college_name'] ?? 'Unknown') ?></p>
          <p><strong>Email:</strong> <?= htmlspecialchars($note['email']) ?></p>
          <p><strong>Phone:</strong> <?= htmlspecialchars($note['phone']) ?></p>
          <p><strong>Message:</strong> <?= htmlspecialchars($note['message']) ?></p>
        </div>
        <div class="modal-footer" style="background-color: #fae7e7;">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</nav>

<!-- Sidebar -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="adminSidebar">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Admin Panel</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <a href="manage-colleges-courses.php"><i class="bi bi-building"></i> Add Colleges/Courses</a>
    <a href="upload-research.php"><i class="bi bi-cloud-upload"></i> Upload Research</a>
    <a href="manage-research.php" class="d-flex justify-content-between align-items-center">
      <span><i class="bi bi-journals"></i> Manage Research</span>
      <?php if (count($researches) > 0): ?><span class="badge bg-danger"><?= count($researches) ?></span><?php endif; ?>
    </a>
    <a href="approval-users.php" class="d-flex justify-content-between align-items-center">
      <span><i class="bi bi-person-check"></i> Pending Faculty Members</span>
      <?php
        $pendingResult = $mysqli->query("SELECT COUNT(*) as total FROM faculty_members WHERE status = 'pending'");
        $pendingCount = 0;
        if ($pendingResult) { $row = $pendingResult->fetch_assoc(); $pendingCount = $row['total']; }
      ?>
      <?php if ($pendingCount > 0): ?><span class="badge bg-danger"><?= $pendingCount ?></span><?php endif; ?>
    </a>
    <a href="manage-user.php" class="d-flex justify-content-between align-items-center">
      <span><i class="bi bi-people"></i> Manage Faculty Members</span>
      <?php if (count($users) > 0): ?><span class="badge bg-danger"><?= count($users) ?></span><?php endif; ?>
    </a>
    <a href="../Admin/User/logout.php" class="text-danger mt-3" onclick="return confirm('Are you sure you want to Logout?');">
      <i class="bi bi-box-arrow-right"></i> Logout
    </a>
  </div>
</div>

<main>
<div class="container mt-4">
  <h3 class="dashboard-title mb-4"><i class="bi bi-speedometer2"></i> Dashboard Overview</h3>

  <div class="row">
    <div class="col-md-4 mb-3">
      <div class="card p-3"><h5>Total Users</h5><p class="fs-4"><?= count($users) ?></p></div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="card p-3"><h5>Total Researches</h5><p class="fs-4"><?= count($researches) ?></p></div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="card p-3"><h5>Date & Time</h5><p id="live-date-time" class="fs-5"></p></div>
    </div>
  </div>

  <!-- Dashboard Charts Row -->
  <div class="row mt-4">
    <!-- Research by Colleges -->
    <div class="col-md-4">
      <h5 class="dashboard-title"><i class="bi bi-building"></i> Research by Colleges</h5>
      <div class="card p-3 d-flex justify-content-center align-items-center">
        <canvas id="researchCollegeChart" width="200" height="200"></canvas>
      </div>
    </div>
    <!-- Research by SDG Goal -->
    <div class="col-md-4">
      <h5 class="dashboard-title"><i class="bi bi-globe"></i> Research by SDG Goal</h5>
      <div class="card p-3 d-flex justify-content-center align-items-center">
        <canvas id="researchSDGChart" width="200" height="200"></canvas>
      </div>
    </div>
    <!-- Users by College -->
    <div class="col-md-4">
      <h5 class="dashboard-title"><i class="bi bi-people-fill"></i> Faculty Members by Colleges</h5>
      <div class="card p-3 d-flex justify-content-center align-items-center">
        <canvas id="userCollegeChart" width="200" height="200"></canvas>
      </div>
    </div>
  </div>
</div>
</main>

<footer>&copy; 2025 - Research Archive System</footer>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
<script>
Chart.register(ChartDataLabels);

// Live Date & Time
function updateDateTime() {
    const now = new Date();
    const options = { year:'numeric', month:'long', day:'numeric', hour:'numeric', minute:'numeric', second:'numeric', hour12:true };
    document.getElementById('live-date-time').textContent = now.toLocaleString('en-US', options);
}
updateDateTime(); setInterval(updateDateTime,1000);

// Researches by College
new Chart(document.getElementById('researchCollegeChart').getContext('2d'), {
    type:'pie',
    data:{
        labels: <?= json_encode(array_keys($researchByCollege)) ?>,
        datasets:[{
            label:'Researches',
            data: <?= json_encode(array_values($researchByCollege)) ?>,
            backgroundColor:['#8d1515','#f8cc69','#6c757d','#198754','#0dcaf0','#ffc107','#dc3545'],
            borderColor:'#fff',
            borderWidth:1
        }]
    },
    options:{responsive:true,plugins:{legend:{position:'bottom'},datalabels:{color:'#fff',font:{weight:'bold'},formatter:(value)=>value}}}
});

// Researches by SDG Goal
new Chart(document.getElementById('researchSDGChart').getContext('2d'), {
    type:'pie',
    data:{
            labels: <?= json_encode(array_map(function($g) { return 'Goal ' . $g; }, array_keys($researchBySDG))) ?>,        datasets:[{
            label:'Researches',
            data: <?= json_encode(array_values($researchBySDG)) ?>,
            backgroundColor:['#ff6384','#36a2eb','#ffce56','#4bc0c0','#9966ff','#ff9f40','#c9cbcf','#ffcd56','#8d1515','#f8cc69','#198754','#0dcaf0','#ffc107','#dc3545','#6f42c1','#fd7e14','#20c997'],
            borderColor:'#fff',
            borderWidth:1
        }]
    },
    options:{responsive:true,plugins:{legend:{position:'bottom'},datalabels:{color:'#fff',font:{weight:'bold'},formatter:(value)=>value}}}
});

// Users by College
new Chart(document.getElementById('userCollegeChart').getContext('2d'), {
    type:'pie',
    data:{
        labels: <?= json_encode(array_keys($userByCollege)) ?>,
        datasets:[{
            label:'Users',
            data: <?= json_encode(array_values($userByCollege)) ?>,
            backgroundColor:['#8d1515','#f8cc69','#198754','#0dcaf0','#ffc107','#dc3545'],
            borderColor:'#fff',
            borderWidth:1
        }]
    },
    options:{responsive:true,plugins:{legend:{position:'bottom'},datalabels:{color:'#fff',font:{weight:'bold'},formatter:(value)=>value}}}
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
