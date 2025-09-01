<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../Faculty_Member/login.php");
    exit();
}

include '../db/db_connect.php'; // DB connection

if (!isset($_GET['id'])) {
    header('Location: manage-user.php');
    exit;
}

$id = intval($_GET['id']);
$error = '';
$success = '';

// Function to generate random password
function generateRandomPassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    return substr(str_shuffle($chars), 0, $length);
}

$generatedPassword = generateRandomPassword();

// Fetch user data
$stmt = $conn->prepare("
    SELECT fm.id, fm.first_name, fm.last_name, fm.email, fm.phone, fm.id_number,
           fm.college_id, fm.course_id, c.college_name, co.course_name
    FROM faculty_members fm
    LEFT JOIN colleges c ON fm.college_id = c.id
    LEFT JOIN courses co ON fm.course_id = co.id
    WHERE fm.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: manage-user.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $college_id = intval($_POST['college']);
    $course_id = intval($_POST['course']);
    $password = trim($_POST['password']);

    if (!$first_name || !$last_name || !$email || !$phone || !$college_id || !$course_id) {
        $error = "Please fill in all required fields.";
    } else {
        $check = $conn->prepare("SELECT id FROM faculty_members WHERE (email=? OR phone=?) AND id!=?");
        $check->bind_param("ssi", $email, $phone, $id);
        $check->execute();
        $resultCheck = $check->get_result();
        if ($resultCheck->num_rows > 0) {
            $error = "Email or phone is already used by another account.";
        } else {
            if ($password !== '') {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE faculty_members SET first_name=?, last_name=?, email=?, phone=?, college_id=?, course_id=?, password=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssi", $first_name, $last_name, $email, $phone, $college_id, $course_id, $passwordHash, $id);
            } else {
                $sql = "UPDATE faculty_members SET first_name=?, last_name=?, email=?, phone=?, college_id=?, course_id=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssi", $first_name, $last_name, $email, $phone, $college_id, $course_id, $id);
            }
            if ($stmt->execute()) {
                $success = "User information updated successfully.";
                $user['first_name'] = $first_name;
                $user['last_name'] = $last_name;
                $user['email'] = $email;
                $user['phone'] = $phone;
                $user['college_id'] = $college_id;
                $user['course_id'] = $course_id;
            } else {
                $error = "Failed to update user: " . $conn->error;
            }
            $stmt->close();
        }
        $check->close();
    }
}

// Fetch all colleges
$colleges = $conn->query("SELECT id, college_name FROM colleges ORDER BY college_name ASC")->fetch_all(MYSQLI_ASSOC);

// Determine which college to fetch courses from
$selectedCollegeId = $_SERVER['REQUEST_METHOD'] === 'POST' ? intval($_POST['college']) : $user['college_id'];

// Fetch courses for the selected college
$stmt = $conn->prepare("SELECT id, course_name FROM courses WHERE college_id = ? ORDER BY course_name ASC");
$stmt->bind_param("i", $selectedCollegeId);
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Edit User Account</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
<style>
body { background-color: #f8f9fa; font-family: Arial, sans-serif; display: flex; flex-direction: column; min-height: 100vh; }
footer { background-color: #8d1515; color: white; text-align: center; padding: 15px 0; width: 100%; }
.navbar-custom { background-color: #8d1515; }
.navbar-brand, .navbar-text { color: #f8cc69 !important; }
.form-container { max-width: 700px; margin: 40px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.05); }
.form-title { color: #8d1515; font-weight: bold; }
.btn-update { background-color: #8d1515; color: #f8cc69; }
.btn-update:hover { background-color: #a61b1b; color: white; }
.btn-generate { background-color: #ffc107; color: black; }
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
  <a href="manage-user.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Manage Users</a>
</div>

<main>
<div class="form-container">
  <h4 class="form-title mb-4"><i class="bi bi-person-lines-fill me-2"></i>Edit User Information</h4>

  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

  <form method="POST" action="" onsubmit="return confirm('Are you sure you want to update this user?');">
    <input type="hidden" name="id" value="<?= (int)$user['id'] ?>" />

    <div class="mb-3">
      <label class="form-label">ID Number</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($user['id_number']) ?>" disabled />
    </div>

    <div class="mb-3">
      <label class="form-label">First Name</label>
      <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required />
    </div>

    <div class="mb-3">
      <label class="form-label">Last Name</label>
      <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required />
    </div>

    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required />
    </div>

    <div class="mb-3">
      <label class="form-label">Phone</label>
      <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" maxlength="11" minlength="11" required />
    </div>

    <div class="mb-3">
      <label class="form-label">College</label>
      <select name="college" id="collegeSelect" class="form-select" required>
        <option value="">Select a college</option>
        <?php foreach($colleges as $college): ?>
          <option value="<?= $college['id'] ?>" <?= $college['id']==$selectedCollegeId ? 'selected' : '' ?>>
            <?= htmlspecialchars($college['college_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Course</label>
      <select name="course" id="courseSelect" class="form-select" required>
        <option value="">Select a course</option>
        <?php foreach($courses as $course): ?>
          <option value="<?= $course['id'] ?>" <?= ($course['id']==($_POST['course'] ?? $user['course_id'])) ? 'selected' : '' ?>>
            <?= htmlspecialchars($course['course_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">New Password (Optional)</label>
      <div class="input-group">
        <input type="text" name="password" class="form-control" id="passwordInput" placeholder="Leave blank to keep old password" autocomplete="new-password" />
        <button type="button" class="btn btn-generate" onclick="document.getElementById('passwordInput').value='<?= $generatedPassword ?>'">
          <i class="bi bi-shuffle"></i> Generate Random
        </button>
      </div>
    </div>

    <div class="text-end">
      <button type="submit" class="btn btn-update"><i class="bi bi-save me-1"></i>Update Account</button>
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
    courseSelect.innerHTML = '<option value="">Loading courses...</option>';
    fetch(`api/get-courses.php?college_id=${collegeId}`)
      .then(res => res.json())
      .then(data => {
          courseSelect.innerHTML = '<option value="">Select a course</option>';
          data.forEach(course => {
              const option = document.createElement('option');
              option.value = course.id;
              option.textContent = course.course_name;
              courseSelect.appendChild(option);
          });
      });
});
</script>

</body>
</html>
