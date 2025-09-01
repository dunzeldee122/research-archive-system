<?php
session_start();
include '../db/db_connect.php'; // Adjust path as needed

if (!isset($_SESSION['faculty_id'])) {
    header("Location: login.php");
    exit;
}

$faculty_id = $_SESSION['faculty_id'];
$error = '';
$success = '';

// Fetch current user data with college and course names
$stmt = $conn->prepare("
    SELECT fm.id, fm.id_number, fm.first_name, fm.last_name, fm.email, fm.phone, 
           fm.college_id, fm.course_id, c.college_name, cr.course_name
    FROM faculty_members fm
    LEFT JOIN colleges c ON fm.college_id = c.id
    LEFT JOIN courses cr ON fm.course_id = cr.id
    WHERE fm.id = ?
");
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) die("User not found.");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $college_id = $_POST['college'];
    $course_id = $_POST['course'];
    $password = trim($_POST['password']);

    if (!$first_name || !$last_name || !$email || !$phone || !$college_id || !$course_id) {
        $error = "Please fill in all required fields.";
    } else {
        // Check for email/phone duplicates
        $check = $conn->prepare("SELECT id FROM faculty_members WHERE (email=? OR phone=?) AND id!=?");
        $check->bind_param("ssi", $email, $phone, $faculty_id);
        $check->execute();
        $result = $check->get_result();
        if ($result && $result->num_rows > 0) {
            $error = "Email or Phone number already exists. Please use a different one.";
            $check->close();
        } else {
            $check->close();

            // Update
            if ($password !== '') {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE faculty_members 
                                          SET first_name=?, last_name=?, email=?, phone=?, college_id=?, course_id=?, password=? 
                                          WHERE id=?");
                $update->bind_param("sssssssi", $first_name, $last_name, $email, $phone, $college_id, $course_id, $hashed_password, $faculty_id);
            } else {
                $update = $conn->prepare("UPDATE faculty_members 
                                          SET first_name=?, last_name=?, email=?, phone=?, college_id=?, course_id=? 
                                          WHERE id=?");
                $update->bind_param("ssssssi", $first_name, $last_name, $email, $phone, $college_id, $course_id, $faculty_id);
            }

            if ($update->execute()) {
              // ✅ Update session immediately so home.php shows new name
              $_SESSION['faculty_name'] = $first_name . ' ' . $last_name;
          
              // Refresh page with success message
              header("Location: profile.php?success=1");
              exit;
          } else {
              $error = "Failed to update profile.";
          }          

            $update->close();
        }
    }
}

// Fetch all colleges for dropdown
$collegesResult = $conn->query("SELECT id, college_name FROM colleges ORDER BY college_name ASC");
$colleges = [];
while ($row = $collegesResult->fetch_assoc()) {
    $colleges[] = $row;
}

// Fetch courses for the current selected college
$coursesResult = $conn->query("SELECT id, course_name FROM courses WHERE college_id={$user['college_id']} ORDER BY course_name ASC");
$courses = [];
while ($row = $coursesResult->fetch_assoc()) {
    $courses[] = $row;
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    html, body {
      height: 100%;
      margin: 0;
    }
      body {
      background-color: #f0f0f0;
      font-family: Arial, sans-serif;
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
    .profile-card {
      max-width: 700px;
      margin: 40px auto;
      background-color: #fff;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
      padding: 30px;
    }
    .profile-title {
      color: #8d1515;
      font-weight: bold;
    }
    .save-btn {
      background-color: #8d1515;
      color: #f8cc69;
    }
    .save-btn:hover {
      background-color: #a61b1b;
      color: white;
    }
    label {
      font-weight: bold;
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
<div class="container">
  <?php if ($error): ?>
    <div class="alert alert-danger mt-3"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="alert alert-success mt-3">Profile updated successfully!</div>
  <?php endif; ?>

  <form class="profile-card" method="POST" action="">
    <h4 class="profile-title mb-4"><i class="bi bi-person-circle me-2"></i>My Profile</h4>

    <div class="mb-3">
      <label>ID Number</label>
      <input type="text" name="id_number" class="form-control" value="<?= htmlspecialchars($user['id_number']) ?>" readonly>
    </div>

    <div class="row mb-3">
      <div class="col-md-6">
        <label>First Name</label>
        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
      </div>
      <div class="col-md-6">
        <label>Last Name</label>
        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-6">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
      </div>
      <div class="col-md-6">
        <label>Phone</label>
        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required maxlength="11" required minlength="11" required>
      </div>
    </div>

    <div class="mb-3">
  <label>College</label>
  <select name="college" id="collegeSelect" class="form-select" required>
    <option value="">Select a college</option>
    <?php foreach($colleges as $college): ?>
      <option value="<?= $college['id'] ?>" <?= $college['id'] == $user['college_id'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($college['college_name']) ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>

<div class="mb-3">
  <label>Course</label>
  <select name="course" id="courseSelect" class="form-select" required>
    <option value="">Select a course</option>
    <?php foreach($courses as $course): ?>
      <option value="<?= $course['id'] ?>" <?= $course['id'] == $user['course_id'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($course['course_name']) ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>


    <!-- Password Fields -->
    <div class="mb-3">
      <label>New Password</label>
      <div class="input-group">
        <input type="password" id="password" name="password" class="form-control" placeholder="Enter new password if you want to change" autocomplete="new-password" minlength="8">
        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password', this)">
          <i class="bi bi-eye"></i>
        </button>
      </div>
    </div>

    <div class="mb-4">
      <label>Re-Enter Password</label>
      <div class="input-group">
        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter new password" autocomplete="new-password" minlength="8">
        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('confirm_password', this)">
          <i class="bi bi-eye"></i>
        </button>
      </div>
    </div>


    <div class="text-end">
    <button type="submit" class="btn" style="background-color: #8d1515; color: #f8cc69;" id="saveBtn" disabled>Save Changes</button>
    </div>
  </form>
</div>

</main>

<footer>
    &copy; 2025 - Research Archive System
</footer>


<script>
const collegeSelect = document.getElementById('collegeSelect');
const courseSelect = document.getElementById('courseSelect');

collegeSelect.addEventListener('change', function() {
  const collegeId = this.value;
  fetch(`api/get-courses.php?college_id=${collegeId}`)
    .then(res => res.json())
    .then(data => {
      courseSelect.innerHTML = '<option value="">Select a course</option>';
      data.forEach(course => {
        const opt = document.createElement('option');
        opt.value = course.id;
        opt.textContent = course.course_name;
        courseSelect.appendChild(opt);
      });
    });
});


  function togglePassword(inputId, btn) {
  const input = document.getElementById(inputId);
  const icon = btn.querySelector('i');
  if (input.type === "password") {
    input.type = "text";
    icon.classList.remove('bi-eye');
    icon.classList.add('bi-eye-slash');
  } else {
    input.type = "password";
    icon.classList.remove('bi-eye-slash');
    icon.classList.add('bi-eye');
  }
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const saveBtn = document.getElementById('saveBtn');

    // Store original values
    const originalValues = {};
    form.querySelectorAll('input, select, textarea').forEach(input => {
        originalValues[input.name] = input.value;
    });

    // Check if any field changed
    function checkChanges() {
        let changed = false;
        form.querySelectorAll('input, select, textarea').forEach(input => {
            if (input.value !== originalValues[input.name]) {
                changed = true;
            }
        });
        saveBtn.disabled = !changed;
    }

    // Listen for any change in form
    form.addEventListener('input', checkChanges);

    // On submit validation + confirm dialog
    form.addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (password !== '' || confirmPassword !== '') {
            if (password.length < 8) {
                alert('Password must be at least 8 characters.');
                e.preventDefault();
                return false;
            }
            if (password !== confirmPassword) {
                alert('Passwords do not match.');
                e.preventDefault();
                return false;
            }
        }

        const confirmUpdate = confirm("Are you sure you want to update your profile?");
        if (!confirmUpdate) {
            e.preventDefault();
            return false;
        }
    });
});


</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
