<?php
session_start();
include '../db/db_connect.php';

// Fetch all colleges and their courses
$collegesQuery = $conn->query("SELECT * FROM colleges ORDER BY college_name ASC");
$colleges = [];

while ($row = $collegesQuery->fetch_assoc()) {
    $collegeId = $row['id'];
    $coursesQuery = $conn->query("SELECT id, course_name FROM courses WHERE college_id = $collegeId ORDER BY course_name ASC");
    $row['courses'] = $coursesQuery->fetch_all(MYSQLI_ASSOC);
    $colleges[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $phone      = trim($_POST['phone']);
    $id_number  = trim($_POST['id_number']);
    $college_id = intval($_POST['college_id']);
    $course_id  = intval($_POST['course_id']); // Must be numeric ID
    $password   = $_POST['password'];
    $re_password = $_POST['re_password'];

    $_SESSION['form_data'] = [
        'first_name'=>$first_name,
        'last_name'=>$last_name,
        'email'=>$email,
        'phone'=>$phone,
        'id_number'=>$id_number,
        'college_id'=>$college_id,
        'course_id'=>$course_id,
    ];

    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters.";
        header("Location: register.php"); exit();
    }
    if ($password !== $re_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: register.php"); exit();
    }

    $check = $conn->prepare("SELECT * FROM faculty_members WHERE email=? OR phone=? OR id_number=?");
    $check->bind_param("sss", $email, $phone, $id_number);
    $check->execute();
    $result = $check->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email, Phone, or ID already exists.";
        header("Location: register.php"); exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO faculty_members 
        (first_name, last_name, email, phone, id_number, college_id, course_id, password, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("ssssiiis", $first_name, $last_name, $email, $phone, $id_number, $college_id, $course_id, $hashed_password);

    if ($stmt->execute()) {
        unset($_SESSION['form_data']);
        $_SESSION['success'] = "Registration successful! Await admin approval.";
        header("Location: register.php");
    } else {
        $_SESSION['error'] = "Something went wrong. Try again.";
        header("Location: register.php");
    }
    exit();
}
?>



?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Register</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
    html, body {
      height: 100%;
      margin: 0;
    }
    body {
      margin: 0;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      background-image: url('../assets/images/background-register.png');
      background-size: 100% 100%;
      background-position: center;
      background-repeat: no-repeat;
      font-family: Arial, sans-serif;
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
    .register-container {
      background-color: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.3);
      width: 100%;
      max-width: 600px;
      transform: translateX(-100px);
      margin-top: 30px;
      
    }
    .form-control { 
      border: 1px solid #8d1515 !important;
      height: 50px;
      font-size: 16px;
    }
    .form-control:focus {
      outline: none;
      border-color: #8d1515;
      box-shadow: 0 0 5px #8d1515;
    }
    .custom-select-arrow {
      background-image: url('data:image/svg+xml;utf8,<svg fill="black" height="20" viewBox="0 0 24 24" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>');
      background-repeat: no-repeat;
      background-position: right 10px center;
      background-size: 16px;
      padding-right: 30px;
    }
    .custom-btn {
      background-color: #8d1515;
      color: #f8cc69;
      border: none;
    }
    .custom-btn:hover {
      background-color: #a61b1b;
      color: white;
    }
    .link-blue {
      color: #38b6ff;
      text-decoration: none;
    }
    .link-blue:hover {
      text-decoration: underline;
    }
</style>
</head>
<body>
<main>
  <div class="container-fluid h-100">
    <div class="row h-100">
      <div class="col-md-7"></div>
      <div class="col-md-5 d-flex align-items-center justify-content-center">
        <div class="register-container">

          <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
          <?php endif; ?>
          <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
          <?php endif; ?>

          <form method="POST" action="">
            <div class="row mb-3">
              <div class="col">
                <input type="text" name="first_name" class="form-control" placeholder="First Name" required
                      value="<?= isset($_SESSION['form_data']['first_name']) ? htmlspecialchars($_SESSION['form_data']['first_name']) : '' ?>">
              </div>
              <div class="col">
                <input type="text" name="last_name" class="form-control" placeholder="Last Name" required
                      value="<?= isset($_SESSION['form_data']['last_name']) ? htmlspecialchars($_SESSION['form_data']['last_name']) : '' ?>">
              </div>
            </div>

            <div class="row mb-3">
              <div class="col">
                <input type="email" name="email" class="form-control" placeholder="Email" required
                      value="<?= isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : '' ?>">
              </div>
            </div>

            <div class="row mb-3">
              <div class="col">
                <input type="text" 
                      name="phone" 
                      class="form-control" 
                      placeholder="09xxxxxxxxx" 
                      required 
                      maxlength="11" 
                      minlength="11"
                      pattern="^09[0-9]{9}$"
                      title="Phone number must start with 09 and contain 11 digits only"
                      value="<?= isset($_SESSION['form_data']['phone']) ? htmlspecialchars($_SESSION['form_data']['phone']) : '' ?>">
              </div>
              <div class="col">
                <input type="text" name="id_number" class="form-control" placeholder="ID Number" required
                      value="<?= isset($_SESSION['form_data']['id_number']) ? htmlspecialchars($_SESSION['form_data']['id_number']) : '' ?>">
              </div>
            </div>

            <!-- Department & Course Dropdowns -->
            <div class="row mb-3">
              <div class="col">
                  <select name="college_id" id="collegeSelect" class="form-control" required>
                      <option value="" disabled selected>Select College</option>
                      <?php foreach($colleges as $c): ?>
                          <option value="<?= $c['id'] ?>" <?= isset($_SESSION['form_data']['college_id']) && $_SESSION['form_data']['college_id']==$c['id'] ? 'selected' : '' ?>>
                              <?= htmlspecialchars($c['college_name']) ?>
                          </option>
                      <?php endforeach; ?>
                  </select>
              </div>

              <div class="col">
                  <select name="course_id" id="courseSelect" class="form-control" required>
                      <option value="" disabled selected>Select Course</option>
                  </select>
              </div>
          </div>


            <div class="row mb-3">
              <div class="col">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
              </div>
              <div class="col">
                <input type="password" name="re_password" class="form-control" placeholder="Re-enter Password" required>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
              <div>
                <p class="mb-1">Already have an account?</p>
                <a href="login.php" class="link-blue">Click here!</a>
              </div>
              <button type="submit" class="btn custom-btn px-4">Register</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>

<footer>
    &copy; 2025 - Research Archive System
</footer>

<script>
const colleges = <?= json_encode($colleges) ?>;
const collegeSelect = document.getElementById('collegeSelect');
const courseSelect = document.getElementById('courseSelect');

function populateCourses() {
    const selectedCollegeId = collegeSelect.value;
    courseSelect.innerHTML = '<option value="" disabled selected>Select Course</option>';

    if (!selectedCollegeId) return;

    const college = colleges.find(c => c.id == selectedCollegeId);
    if (college && college.courses) {
        college.courses.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;       // Important: send course ID
            opt.textContent = c.course_name;
            // Preserve selected course if page reloads
            if (<?= json_encode($_SESSION['form_data']['course_id'] ?? 0) ?> == c.id) {
                opt.selected = true;
            }
            courseSelect.appendChild(opt);
        });
    }
}

collegeSelect.addEventListener('change', populateCourses);
window.addEventListener('DOMContentLoaded', populateCourses);

</script>

</body>
</html>
