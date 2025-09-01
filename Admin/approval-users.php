<?php
session_start();

// Only allow logged-in admins
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../Faculty_Member/login.php");
    exit();
}

// DB Connection
$connection = new mysqli("localhost", "root", "", "research_archive");
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// ================== FETCH PENDING USERS ==================
$pendingUsers = [];
$sql = "SELECT fm.id, fm.first_name, fm.last_name, fm.email, fm.phone, fm.id_number,
               c.college_name, cr.course_name
        FROM faculty_members fm
        LEFT JOIN colleges c ON fm.college_id = c.id
        LEFT JOIN courses cr ON fm.course_id = cr.id
        WHERE fm.status = 'pending'";
$result = $connection->query($sql);

while ($row = $result->fetch_assoc()) {
    $pendingUsers[] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Approval</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
html, body { height: 100%; margin: 0; }
body { display: flex; flex-direction: column; min-height: 100vh; background-color: #f8f9fa; font-family: Arial, sans-serif; }
main { flex: 1; }
footer { background-color: #8d1515; color: white; text-align: center; padding: 15px 0; width: 100%; }
.navbar-custom { background-color: #8d1515; }
.navbar-brand, .navbar-text { color: #f8cc69 !important; }

.table-container { max-width: 1400px; margin: 40px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.05); }
.table th { background-color: #8d1515; color: #fff; text-align: center; }
.table td { text-align: center; vertical-align: middle; max-width: 230px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.btn-approve { background-color: #198754; color: white; }
.btn-reject { background-color: #dc3545; color: white; }
.btn-view { background-color: #0d6efd; color: white; }
.action-icons { display: flex; justify-content: center; align-items: center; gap: 20px; }
.action-icons i { cursor: pointer; }
.action-icons i:hover { opacity: 0.8; }
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
<div class="table-container">
  <h4 class="mb-4 text-danger"><i class="bi bi-person-check me-2"></i>Pending User Approvals</h4>

  <?php if (count($pendingUsers) === 0): ?>
    <div class="alert alert-info text-center">No pending user approvals at the moment.</div>
  <?php else: ?>
    <table class="table table-striped table-hover table-bordered align-middle">
      <thead>
        <tr>
          <th>ID Number</th>
          <th>Name</th>
          <th>Email</th>
          <th>College</th>
          <th>Course</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pendingUsers as $user): ?>
          <tr>
            <td><?= htmlspecialchars($user['id_number']) ?></td>
            <td><?= htmlspecialchars($user['last_name'] . ', ' . $user['first_name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['college_name']) ?></td>
            <td><?= htmlspecialchars($user['course_name']) ?></td>
            <td class="action-icons">
              <button class="btn btn-success btn-sm approve-btn" data-id="<?= $user['id'] ?>" title="Approve">
                <i class="bi bi-check-circle"></i>
              </button>
              <button class="btn btn-danger btn-sm reject-btn" data-id="<?= $user['id'] ?>" title="Reject">
                <i class="bi bi-x-circle"></i>
              </button>
              <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal<?= $user['id'] ?>" title="View">
                <i class="bi bi-eye-fill"></i>
              </button>
            </td>
          </tr>

          <!-- View Modal -->
          <div class="modal fade" id="viewModal<?= $user['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header" style="background-color: #8d1515; color: #f8cc69;">
                  <h5 class="modal-title"><i class="bi bi-person-lines-fill me-2"></i>User Details</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <p><strong>ID Number:</strong> <?= htmlspecialchars($user['id_number']) ?></p>
                  <p><strong>Name:</strong> <?= htmlspecialchars($user['last_name'] . ', ' . $user['first_name']) ?></p>
                  <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                  <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone']) ?></p>
                  <p><strong>College:</strong> <?= htmlspecialchars($user['college_name']) ?></p>
                  <p><strong>Course:</strong> <?= htmlspecialchars($user['course_name']) ?></p>
                </div>
                <div class="modal-footer" style="background-color: #fae7e7;">
                  <button class="btn btn-success approve-btn" data-id="<?= $user['id'] ?>"><i class="bi bi-check-circle me-1"></i>Approve</button>
                  <button class="btn btn-danger reject-btn" data-id="<?= $user['id'] ?>"><i class="bi bi-x-circle me-1"></i>Reject</button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>

        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<!-- Toasts -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
  <div id="approveToast" class="toast align-items-center text-white bg-success border-0 mb-2" role="alert">
    <div class="d-flex"><div class="toast-body">✅ User Approved</div></div>
  </div>
  <div id="rejectToast" class="toast align-items-center text-white bg-danger border-0" role="alert">
    <div class="d-flex"><div class="toast-body">❌ User Rejected</div></div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.querySelectorAll('.approve-btn').forEach(button => {
    button.addEventListener('click', function () {
      const userId = this.getAttribute('data-id');
      if (confirm("Are you sure you want to approve this user?")) {
        const toast = new bootstrap.Toast(document.getElementById('approveToast'));
        toast.show();
        window.location.href = '../Admin/User/approve-user.php?id=' + userId;
      }
    });
  });

  document.querySelectorAll('.reject-btn').forEach(button => {
    button.addEventListener('click', function () {
      const userId = this.getAttribute('data-id');
      if (confirm("Are you sure you want to reject this user?")) {
        const toast = new bootstrap.Toast(document.getElementById('rejectToast'));
        toast.show();
        window.location.href = '../Admin/User/reject-user.php?id=' + userId;
      }
    });
  });
</script>
</main>

<footer>
    &copy; 2025 - Research Archive System
</footer>

</body>
</html>
