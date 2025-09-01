<?php
session_start();
require '../db/db_connect.php'; // Adjust path if needed

// LOGIN PROCESS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $userType = $_POST['user_type'] ?? '';
    $password = trim($_POST['password']);

    if ($userType === 'admin') {
        $username = trim($_POST['username']);
        if (empty($username) || empty($password)) {
            $_SESSION['error'] = "Please enter username and password.";
            header("Location: login.php");
            exit;
        }
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
    } else { // faculty
        $idNumber = trim($_POST['idNumber']);
        if (empty($idNumber) || empty($password)) {
            $_SESSION['error'] = "Please enter ID Number and password.";
            header("Location: login.php");
            exit;
        }
        $stmt = $conn->prepare("SELECT * FROM faculty_members WHERE id_number = ?");
        $stmt->bind_param("s", $idNumber);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        if ($userType === 'faculty' && $user['status'] !== 'approved') {
            $_SESSION['error'] = "Your account is still pending approval.";
            header("Location: login.php");
            exit;
        }

        if (password_verify($password, $user['password'])) {
            if ($userType === 'admin') {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['first_name'] . ' ' . $user['last_name'];
                header("Location: ../Admin/admin-dashboard.php");
                exit;
            } else { // faculty
                $_SESSION['faculty_id'] = $user['id'];
                $_SESSION['faculty_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['faculty_department'] = $user['department'];
                header("Location: home.php"); 
                exit;
            }
        } else {
            $_SESSION['error'] = "Invalid password.";
        }
    } else {
        $_SESSION['error'] = ($userType === 'admin') ? "Username not found." : "ID Number not found.";
    }

    header("Location: login.php");
    exit;
}

// FORGOT PASSWORD REQUEST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot_password'])) {
    $idNumber = trim($_POST['reset_idNumber']);

    if (!empty($idNumber)) {
        $stmt = $conn->prepare("SELECT first_name, last_name FROM faculty_members WHERE id_number = ?");
        $stmt->bind_param("s", $idNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        $faculty = $result->fetch_assoc();

        if ($faculty) {
            $message = "wants to reset password!";
            $stmt = $conn->prepare("INSERT INTO notifications (id_number, message, created_at) VALUES (?, ?, NOW())");
            $stmt->bind_param("ss", $idNumber, $message);
            $stmt->execute();

            $_SESSION['success'] = "Successfully Submitted!";
        } else {
            $_SESSION['error'] = "ID Number not found.";
        }
    } else {
        $_SESSION['error'] = "Please enter your ID Number.";
    }

    header("Location: login.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body, html {
      height: 100%;
      margin: 0;
      background-image: url('../assets/images/background-login.png');
      background-size: 100% 100%;
      background-position: center;
      background-repeat: no-repeat;
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


    /* Dropdown styling */
    select.transparent-select {
        width: 220px; /* adjust as needed */
        background-color: rgba(0,0,0,0); /* transparent background */
        color: white; /* white text */
        border: 1px solid white;
        padding: 5px 2rem 5px 8px; /* space for arrow */
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }

    /* White arrow */
    select.transparent-select::-ms-expand {
        display: none;
    }
    select.transparent-select {
        background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="white"><polygon points="0,0 14,0 7,7"/></svg>');
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 10px;
    }

    /* Option text */
    select.transparent-select option {
        background-color: rgba(0,0,0,0.5); /* semi-transparent options */
        color: white; /* white text */
    }

    .login-container {
      background-color: rgba(0, 0, 0, 0.7);
      padding: 50px;
      border-radius: 10px;
      color: white;
      max-width: 500px;
      position: relative;
      right: 30px;
      margin-top: 30px;
    }
    .form-control.transparent-input {
      background-color: transparent;
      border: 1px solid white;
      color: white;
    }
    .input-group-text.transparent-icon {
      background-color: rgba(0, 0, 0, 0.5);
      color: white;
      border: 1px solid white;
    }
    .form-control::placeholder {
      color: #ccc;
    }
    .custom-check:checked {
      background-color: #38b6ff;
      border-color: #38b6ff;
    }
    .custom-check {
      accent-color: #38b6ff; 
    }
    .forgot-link, .register-link {
      color: #38b6ff;
      text-decoration: none;
    }
    .forgot-link:hover, .register-link:hover {
      text-decoration: underline;
    }
  </style>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
  </head>
  <body>
  <main>
    <div class="container-fluid h-100">
      <div class="row h-100">
        <div class="col-md-7"></div>
        <div class="col-md-5 d-flex align-items-center justify-content-center">
  <div class="login-container">

  <h3 class="mb-4 text-center" style="font-size: 22px;">Login to your account</h3>


  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
  <?php endif; ?>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
  <?php endif; ?>

  <form action="login.php" method="POST">
    <!-- User Type -->
  <div class="mb-3 d-flex align-items-center justify-content-center">
  <div class="input-group">
  <span class="input-group-text transparent-icon"><i class="bi bi-person-fill-gear"></i></span>
    <select class="form-select transparent-select" name="user_type" id="userType" required>
      <option value="admin" <?= (isset($_POST['user_type']) && $_POST['user_type']=='admin') ? 'selected' : '' ?>>Admin</option>
      <option value="faculty" <?= (isset($_POST['user_type']) && $_POST['user_type']=='faculty') ? 'selected' : (!isset($_POST['user_type']) ? 'selected' : '') ?>>Faculty Member</option>
    </select>
    </div> 
  </div>

    <!-- ID Number (Faculty) -->
    <div class="mb-3" id="idNumberField">
      <div class="input-group">
        <span class="input-group-text transparent-icon"><i class="bi bi-person-badge"></i></span>
        <input type="text" class="form-control transparent-input" name="idNumber" placeholder="Enter your ID Number">
      </div>
    </div>

    <!-- Username (Admin) -->
    <div class="mb-3" id="usernameField" style="display:none;">
      <div class="input-group">
        <span class="input-group-text transparent-icon"><i class="bi bi-person"></i></span>
        <input type="text" class="form-control transparent-input" name="username" placeholder="Enter your username">
      </div>
    </div>

    <!-- Password -->
    <div class="mb-3"> 
      <div class="input-group"> 
        <span class="input-group-text transparent-icon"> <i class="bi bi-lock-fill"></i> </span> 
        <input type="password" class="form-control transparent-input" id="password" name="password" placeholder="Enter your password" required> 
        <span class="input-group-text" style="background: transparent; border-left: none; cursor: pointer;" onclick="togglePassword('password', this)"> 
          <i class="bi bi-eye-fill" style="color: white;"></i> 
        </span> 
      </div> 
    </div>

  <div class="d-flex justify-content-end align-items-center mb-3">
    
    <button type="submit" name="login" class="btn" style="background-color: #38b6ff; border: none; color: white;">
      Login <i class="bi bi-box-arrow-in-right ms-2"></i>
    </button>
  </div>
</form>
          <div class="mt-3">
            <p class="text-white mb-0">Forgot your password?</p>
            <a href="#" class="forgot-link" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Please click here</a>
          </div>

          <div class="text-center mt-4">
            <p class="text-white mb-1">Don't have an account yet?</p>
            <a href="register.php" class="btn" style="background-color: #38b6ff; border: none; color: white;">
              Click here to create an account!
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Forgot Password Modal -->
  <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form action="login.php" method="POST">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="forgotPasswordModalLabel">Reset Password Request</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <label for="reset_idNumber" class="form-label">Enter your ID Number</label>
            <input type="text" name="reset_idNumber" id="reset_idNumber" class="form-control" placeholder="Enter your ID Number" required>
          </div>
          <div class="modal-footer">
            <button type="submit" name="forgot_password" class="btn btn-primary">Submit Reset Password</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</main>
  <footer>
    &copy; 2025 - Research Archive System
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
function togglePassword(inputId, iconElement) {
    const input = document.getElementById(inputId);
    const icon = iconElement.querySelector('i');

    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bi-eye-fill");
        icon.classList.add("bi-eye-slash-fill");
    } else {
        input.type = "password";
        icon.classList.remove("bi-eye-slash-fill");
        icon.classList.add("bi-eye-fill");
    }
}

const userType = document.getElementById('userType');
const idField = document.getElementById('idNumberField');
const usernameField = document.getElementById('usernameField');

userType.addEventListener('change', function() {
  if (this.value === 'admin') {
    idField.style.display = 'none';
    usernameField.style.display = 'block';
  } else if (this.value === 'faculty') {
    idField.style.display = 'block';
    usernameField.style.display = 'none';
  } else {
    idField.style.display = 'none';
    usernameField.style.display = 'none';
  }
});
</script>
</body>
</html>
