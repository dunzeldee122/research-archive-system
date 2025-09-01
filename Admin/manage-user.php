<?php
include '../db/db_connect.php'; // Adjust path

session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../Faculty_Member/login.php");
    exit();
}


// Fetch approved users with college_name and course_name
$approvedUsers = [];
$sql = "
    SELECT 
        fm.id, fm.first_name, fm.last_name, fm.email, fm.id_number, fm.phone,
        c.college_name, co.course_name
    FROM faculty_members fm
    LEFT JOIN colleges c ON fm.college_id = c.id
    LEFT JOIN courses co ON fm.course_id = co.id
    WHERE fm.status = 'approved'
";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['name'] = $row['last_name'] . ', ' . $row['first_name'];
        $approvedUsers[] = $row;
    }
} else {
    echo "Error fetching users: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Manage Users</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
<style>
html, body { height: 100%; margin: 0; }
body { display: flex; flex-direction: column; min-height: 100vh; background-color: #f8f9fa; font-family: Arial, sans-serif; }
main { flex: 1; }
footer { background-color: #8d1515; color: white; text-align: center; padding: 15px 0; width: 100%; }
.navbar-custom { background-color: #8d1515; }
.navbar-brand, .navbar-text { color: #f8cc69 !important; }
.table-container { max-width: 1400px; margin: 40px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.05); }
.table th { background-color: #8d1515; color: white; text-align: center; }
.table { table-layout: fixed; }
.action-icons { display: flex; justify-content: center; align-items: center; gap: 20px; }
#userTable td { max-width: 190px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.action-icons a { margin-right: 8px; font-size: 18px; }
.search-input { max-width: 300px; }
</style>
</head>
<body>

<nav class="navbar navbar-custom px-3">
  <a class="navbar-brand d-flex align-items-center" href="admin-dashboard.php">
    <img src="../assets/logo/ZPPSU_LOGO.png" alt="Logo" style="height: 40px; margin-right: 10px;" />
    <span class="navbar-text">ZPPSU Admin Panel</span>
  </a>
</nav>

<div class="container mt-3">
  <a href="admin-dashboard.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
</div>

<main>
<div class="table-container">
  <h4 class="mb-4 text-danger"><i class="bi bi-people me-2"></i>Manage Approved Users</h4>

  <div class="mb-3">
    <input type="text" id="searchInput" class="form-control search-input" placeholder="Search user..." />
  </div>

  <?php if (count($approvedUsers) === 0): ?>
    <div class="alert alert-info text-center">No approved users found.</div>
  <?php else: ?>
    <table class="table table-hover table-bordered align-middle" id="userTable">
      <thead>
        <tr>
          <th>ID Number</th>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>College</th>
          <th>Course</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($approvedUsers as $user): ?>
          <tr>
            <td><?= htmlspecialchars($user['id_number']) ?></td>
            <td><?= htmlspecialchars($user['name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['phone']) ?></td>
            <td><?= htmlspecialchars($user['college_name'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($user['course_name'] ?? 'N/A') ?></td>
            <td class="action-icons">
              <a href="edit-user-account.php?id=<?= $user['id'] ?>" class="text-warning" title="Edit"><i class="bi bi-pencil-square"></i></a>
              <a href="../Admin/User/delete-user.php?id=<?= $user['id'] ?>" class="text-danger" onclick="return confirm('Are you sure you want to delete this user?');" title="Delete"><i class="bi bi-trash-fill"></i></a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
</main>

<footer>
    &copy; 2025 - Research Archive System
</footer>

<script>
document.getElementById("searchInput").addEventListener("keyup", function () {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll("#userTable tbody tr");
    rows.forEach(row => row.style.display = row.textContent.toLowerCase().includes(value) ? "" : "none");
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
<script>alert("User Delete Successfully");</script>
<?php endif; ?>

</body>
</html>
