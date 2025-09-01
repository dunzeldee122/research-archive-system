<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../Faculty_Member/login.php");
    exit();
}

$connection = new mysqli("localhost", "root", "", "research_archive");

// Fetch all colleges with their courses
$collegesQuery = $connection->query("SELECT * FROM colleges ORDER BY college_name ASC");
$colleges = [];
while ($row = $collegesQuery->fetch_assoc()) {
    $collegeId = $row['id'];
    $coursesQuery = $connection->query("SELECT * FROM courses WHERE college_id = $collegeId ORDER BY course_name ASC");
    $row['courses'] = $coursesQuery->fetch_all(MYSQLI_ASSOC);
    $colleges[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Colleges & Courses</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<style>
           html, body {
        height: 100%;
        margin: 0;
      }

    body {
      background-color: #f8f9fa;
      font-family: Arial, sans-serif;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }
    main { flex: 1; }

    footer {
      background-color: #8d1515;
      color: white;
      text-align: center;
      padding: 15px 0;
      width: 100%;
      margin-top: 20px;
    }
        .navbar-custom {
      background-color: #8d1515;
    }
    .navbar-brand, .navbar-text {
      color: #f8cc69 !important;
    }
    .container{
        margin-top: 20px;
    }
</style>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-custom px-3">
  <a class="navbar-brand d-flex align-items-center" href="admin-dashboard.php">
    <img src="../assets/logo/ZPPSU_LOGO.png" alt="Logo" style="height: 40px; margin-right: 10px;" />
    <span class="navbar-text">ZPPSU Admin Panel</span>
  </a>
</nav>

<div class="container mt-3">
  <a href="admin-dashboard.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Manage Users</a>
</div>

<main>
<div class="container">
  <!-- Add College and Courses -->
  <div class="row">
  <!-- Add College and Courses -->
  <div class="col-md-6">
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title">Add College and Courses</h5>
        <form action="colleges-course/save-college-course.php" method="POST" id="addCollegeForm">
          <div class="mb-3">
            <label class="form-label">College Name</label>
            <input type="text" class="form-control" name="college_name" required>
          </div>

          <div id="courseFields">
            <div class="mb-2 d-flex">
              <input type="text" class="form-control me-2" name="courses[]" placeholder="Course Name" required>
              <button type="button" class="btn btn-danger removeCourse" style="display:none;">X</button>
            </div>
          </div>
          <button type="button" class="btn btn-secondary mb-3" id="addCourseField">+ Add Course</button>
          <br>
          <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure want to add this college and course?')">Submit</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Add Course to Existing College -->
  <div class="col-md-6">
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title">Add Course to Existing College</h5>
        <form action="colleges-course/add-course.php" method="POST" id="addCourseForm">
          <div class="mb-3">
            <label class="form-label">Select College</label>
            <select class="form-control" name="college_id" required>
              <option value="">-- Select College --</option>
              <?php foreach ($colleges as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['college_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div id="newCourseFields">
            <div class="mb-2 d-flex">
              <input type="text" class="form-control me-2" name="new_courses[]" placeholder="Course Name" required>
              <button type="button" class="btn btn-danger removeNewCourse" style="display:none;">X</button>
            </div>
          </div>
          <button type="button" class="btn btn-secondary mb-3" id="addNewCourseField">+ Add Another Course</button>
          <br>
          <button type="submit" class="btn btn-success" onclick="return confirm('Add course(s) to this college?')">Save</button>
        </form>
      </div>
    </div>
  </div>
</div>




  <!-- Colleges Table -->
  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Colleges List</h5>

    <div class="mb-3">
            <input type="text" class="form-control" id="collegeSearch" placeholder="Search College...">
    </div>
    <table id="collegesTable" class="table table-bordered table-striped">
        <thead class="table-danger">
          <tr>
            <th>College</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($colleges as $c): ?>
          <tr>
            <td><?= htmlspecialchars($c['college_name']) ?></td>
            <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $c['id'] ?>">Edit</button>
              <form action="colleges-course/delete-college.php" method="POST" style="display:inline;" onsubmit="return confirm('Delete this college and all its courses?')">
                <input type="hidden" name="college_id" value="<?= $c['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
            </td>
          </tr>
            <!-- Edit Modal -->
            <div class="modal fade" id="editModal<?= $c['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                <form action="colleges-course/update-college.php" method="POST">
                    <div class="modal-header">
                    <h5 class="modal-title">Edit College & Courses</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                    <input type="hidden" name="college_id" value="<?= $c['id'] ?>">

                    <!-- College Name -->
                    <div class="mb-3">
                        <label class="form-label">College Name</label>
                        <input type="text" class="form-control" name="college_name" value="<?= htmlspecialchars($c['college_name']) ?>" required>
                    </div>
                    <hr>

                    <!-- Existing Courses -->
                    <h6>Existing Courses</h6>
                    <?php foreach ($c['courses'] as $course): ?>
                        <div class="d-flex mb-2">
                        <input type="hidden" name="course_ids[]" value="<?= $course['id'] ?>">
                        <input type="text" class="form-control me-2" name="course_names[]" value="<?= htmlspecialchars($course['course_name']) ?>" required>
                        <button type="button" class="btn btn-danger btn-sm delete-course-btn" 
                                data-course-id="<?= $course['id'] ?>" 
                                data-college-id="<?= $c['id'] ?>">
                            Delete
                        </button>
                        </div>
                    <?php endforeach; ?>

                    <!-- Add New Courses -->
                    <hr>
                    <h6>Add New Courses (Optional)</h6>
                    <div id="newCourseFields<?= $c['id'] ?>">
                        <div class="mb-2 d-flex">
                        <input type="text" class="form-control me-2" name="new_courses[]" placeholder="Course Name">
                        <button type="button" class="btn btn-danger removeNewCourse" style="display:none;">X</button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" onclick="addNewCourseField(<?= $c['id'] ?>)">+ Add Another Course</button>
                    </div>

                    <div class="modal-footer">
                    <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure want to update?')">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
                </div>
            </div>
            </div>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</main>

<footer>
    &copy; 2025 - Research Archive System
</footer>
<script>
// Add dynamic course field when adding new college

function addNewCourseField(collegeId) {
  const container = document.getElementById("newCourseFields" + collegeId);
  const div = document.createElement("div");
  div.classList.add("mb-2", "d-flex");
  div.innerHTML = `
    <input type="text" class="form-control me-2" name="new_courses[]" placeholder="Course Name" required>
    <button type="button" class="btn btn-danger removeNewCourse">X</button>
  `;
  container.appendChild(div);

  // remove button
  div.querySelector(".removeNewCourse").addEventListener("click", function() {
    div.remove();
  });
}


document.querySelectorAll(".delete-course-btn").forEach(btn => {
    btn.addEventListener("click", function() {
        if (confirm("Delete this course?")) {
            const form = document.createElement("form");
            form.method = "POST";
            form.action = "/research-archive-system/Admin/colleges-course/delete-course.php";

            const courseInput = document.createElement("input");
            courseInput.type = "hidden";
            courseInput.name = "course_id";
            courseInput.value = btn.dataset.courseId;

            const collegeInput = document.createElement("input");
            collegeInput.type = "hidden";
            collegeInput.name = "college_id";
            collegeInput.value = btn.dataset.collegeId;

            form.appendChild(courseInput);
            form.appendChild(collegeInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
});

// Add dynamic course field for "Add College and Courses"
document.getElementById("addCourseField").addEventListener("click", function(){
  const container = document.getElementById("courseFields");
  const div = document.createElement("div");
  div.classList.add("mb-2","d-flex");
  div.innerHTML = `<input type="text" class="form-control me-2" name="courses[]" placeholder="Course Name" required>
                   <button type="button" class="btn btn-danger removeCourse">X</button>`;
  container.appendChild(div);

  div.querySelector(".removeCourse").addEventListener("click", function(){ div.remove(); });
});

// Add dynamic course field for "Add Course to Existing College"
document.getElementById("addNewCourseField").addEventListener("click", function(){
  const container = document.getElementById("newCourseFields");
  const div = document.createElement("div");
  div.classList.add("mb-2","d-flex");
  div.innerHTML = `<input type="text" class="form-control me-2" name="new_courses[]" placeholder="Course Name" required>
                   <button type="button" class="btn btn-danger removeNewCourse">X</button>`;
  container.appendChild(div);

  div.querySelector(".removeNewCourse").addEventListener("click", function(){ div.remove(); });
});


document.getElementById("collegeSearch").addEventListener("keyup", function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll("#collegesTable tbody tr");

    rows.forEach(row => {
        const collegeName = row.cells[0].textContent.toLowerCase();
        if (collegeName.includes(filter)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
});



</script>

</body>
</html>
