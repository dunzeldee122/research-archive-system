<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['faculty_id'])) {
    header("Location: login.php");
    exit;
}

// Get the last name from session

$lastName = isset($_SESSION['faculty_name']) ? $_SESSION['faculty_name'] : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home - Zamboanga Research Archives</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
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
    .navbar-brand, .nav-link, .navbar-text, h1 {
      color: #f8cc69 !important;
    }
    .navbar-brand img {
      height: 40px;
      margin-right: 10px;
    }
    .navbar-text {
      font-size: 28px;
      font-weight: bold;
      margin-left: 20px;
    }
    .dropdown-menu {
      min-width: 150px;
    }

    .background-section {
        background-image: url('../assets/images/background-home.jpg');
        background-size: cover;
        background-position: center;
        height: 200px;
        position: relative;
    }

    .overlay {
        background-color: rgba(0, 0, 0, 0.7);
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 100%;
    }

    .input-group .form-control:focus {
        box-shadow: none;
        border-color: #8d1515;
    }

    .container {
        max-width: none !important;
    }

    .custom-small-box {
        width: 1400px;
        padding: 1rem;
        background-color: #efdede;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .grid-box {
        background-color: white;
        border-radius: 8px;
        padding: 1rem;
        height: 420px;
        display: flex;
        flex-direction: column;
        justify-content: start;
        align-items: center;
        text-align: center;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .grid-box:hover {
      background-color: #f8dfa7;
      transition: background-color 0.3s ease;
      cursor: pointer;
    }

    .custom-sdg-text {
        font-size: 19px;
        color: #6c757d;
    }

    .btn-outline-secondary {
      border-color: #8d1515;
      color: #8d1515;
    }

    .btn-outline-secondary:hover {
      background-color: #8d1515;
      color: #f8cc69;
    }
</style>

</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom px-4">
  <a class="navbar-brand d-flex align-items-center" href="#">
    <img src="../assets/logo/ZPPSU_LOGO.png" alt="Logo" style="height: 50px; width: auto; margin-right: 10px;">
    <span class="navbar-text">Zamboanga Research Archives</span>
  </a>
  <div class="ms-auto">
    <div class="dropdown">
      <a class="nav-link dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <?= htmlspecialchars($lastName) ?>
      </a>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
        <li>
          <a class="dropdown-item" href="profile.php">
            <i class="bi bi-person-circle me-2"></i> Profile
          </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
          <a class="dropdown-item" href="../Faculty_Member/User/logout.php" onclick="return confirm('Are you sure you want to Logout?');">
            <i class="bi bi-box-arrow-right me-2" ></i> Logout
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<main>
<div class="background-section position-relative">
  <div class="overlay"></div>
  <div class="content position-absolute top-50 start-50 translate-middle text-white text-center w-100 px-4">
    <h1 class="mb-4">What are you looking for today?</h1>

    <div class="input-group mx-auto position-relative" style="max-width: 800px;">
    <span class="input-group-text bg-white">
        <i class="bi bi-search text-dark"></i>
    </span>
    <input type="text" id="searchInput" class="form-control" placeholder="Search a title, year or sdg" autocomplete="off">
    <button id="searchBtn" class="btn" style="background-color: #8d1515; color: #f8cc69;">Search</button>

    <!-- Suggestions dropdown -->
    <ul id="suggestionsList" class="list-group position-absolute w-100" style="top: 100%; z-index: 1000; display: none;"></ul>
    </div>

  </div>
</div>

<div class="container text-center" style="margin-top: 10px;">
  <img src="../assets/sdg17/sdg.png" alt="SDG Goals" class="img-fluid" style="max-width: 700px; margin-top: 10px; margin-bottom: 0;">
  <p class="fs-10 mt-0" style="color: gray;">
    These 17 Sustainable Development Goals guide global efforts toward a better world.
    <br>
    Click each goal to explore academic research from ZPPSU that contributes to real-world solutions and sustainable change.
  </p>
</div>

<div class="container d-flex justify-content-center mt-3">
<div class="custom-small-box">
  <div class="row text-center" id="sdg-container">
  </div>

  <div class="d-flex justify-content-between mt-4 px-5">
    <button id="backBtn" class="btn btn-outline-secondary" disabled>
      <i class="bi bi-arrow-left"></i> Back
    </button>
    <button id="nextBtn" class="btn btn-outline-secondary">
      Next <i class="bi bi-arrow-right"></i>
    </button>
  </div>
</div>
</main>

<footer>
    &copy; 2025 - Research Archive System
</footer>

<script>
const sdgData = [
    { goal: 0, img: "../assets/sdg17/allsdg.png", text: "View ALL SDG Research", link: "research-list.php" }, // New card
    { goal: 1, img: "../assets/sdg17/sdg1.png", text: "Explore research that aims to eradicate poverty in all its forms everywhere." },
    { goal: 2, img: "../assets/sdg17/sdg2.png", text: "Discover solutions to end hunger, improve nutrition, and promote sustainable agriculture." },
    { goal: 3, img: "../assets/sdg17/sdg3.png", text: "Access studies that promote health equity, well-being, and resilient healthcare systems." },
    { goal: 4, img: "../assets/sdg17/sdg4.png", text: "Find research focused on inclusive and equitable education for all." },
    { goal: 5, img: "../assets/sdg17/sdg5.png", text: "Explore research supporting gender equality and women's empowerment." },
    { goal: 6, img: "../assets/sdg17/sdg6.png", text: "Support studies ensuring access to water and sanitation for all." },
    { goal: 7, img: "../assets/sdg17/sdg7.png", text: "Learn about research advancing affordable and clean energy." },
    { goal: 8, img: "../assets/sdg17/sdg8.png", text: "Explore research focused on decent work and economic growth." },
    { goal: 9, img: "../assets/sdg17/sdg9.png", text: "Industry, Innovation, and Infrastructure Highlight studies that build resilient infrastructure and foster innovation." },
    { goal: 10, img: "../assets/sdg17/sdg10.png", text: "Reduced Inequalities Learn from work focused on reducing social, economic, and political inequalities." },
    { goal: 11, img: "../assets/sdg17/sdg11.png", text: "Sustainable Cities and Communities Research towards inclusive, safe, and resilient urban development." },
    { goal: 12, img: "../assets/sdg17/sdg12.png", text: "Responsible Consumption and Production Explore practices and studies that promote sustainability and reduce waste." },
    { goal: 13, img: "../assets/sdg17/sdg13.png", text: "Climate Action Access data-driven research addressing climate change and its impacts." },
    { goal: 14, img: "../assets/sdg17/sdg14.png", text: "Life Below Water Examine efforts to protect marine biodiversity and ocean ecosystems." },
    { goal: 15, img: "../assets/sdg17/sdg15.png", text: "Life on Land Research supporting biodiversity conservation and sustainable land use." },
    { goal: 16, img: "../assets/sdg17/sdg16.png", text: "Peace, Justice, and Strong Institutions Promote just, inclusive societies through governance and legal system studies." },
    { goal: 17, img: "../assets/sdg17/sdg17.png", text: "Partnerships for the Goals Highlight research that fosters global cooperation to achieve the SDGs." }
];

  let currentIndex = 0;
  const itemsPerPage = 4;
  const container = document.getElementById("sdg-container");
  const backBtn = document.getElementById("backBtn");
  const nextBtn = document.getElementById("nextBtn");

  function renderBoxes(startIndex) {
    container.innerHTML = "";
    const slice = sdgData.slice(startIndex, startIndex + itemsPerPage);
    slice.forEach(item => {
      const box = `
        <div class="col-md-3 col-sm-6 mb-3">
          <a href="research-list.php?goal=${item.goal}" class="text-decoration-none text-dark">
            <div class="grid-box text-center">
              <img src="${item.img}" alt="SDG ${item.goal}" class="img-fluid mb-2" style="max-height: 270px;">
              <p class="custom-sdg-text">${item.text}</p>
            </div>
          </a>
        </div>`;
      container.innerHTML += box;
    });

    backBtn.disabled = currentIndex === 0;
    nextBtn.disabled = currentIndex + itemsPerPage >= sdgData.length;
  }

  backBtn.addEventListener("click", () => {
    if (currentIndex >= itemsPerPage) {
      currentIndex -= itemsPerPage;
      renderBoxes(currentIndex);
    }
  });

  nextBtn.addEventListener("click", () => {
    if (currentIndex + itemsPerPage < sdgData.length) {
      currentIndex += itemsPerPage;
      renderBoxes(currentIndex);
    }
  });

  renderBoxes(currentIndex);

const searchInput = document.getElementById('searchInput');
const suggestionsList = document.getElementById('suggestionsList');
const searchBtn = document.getElementById('searchBtn');

function fetchSuggestions(query = '') {
    fetch(`search/search-suggestions.php?term=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            suggestionsList.innerHTML = '';
            if (data.length > 0) {
                data.forEach(item => {
                    const li = document.createElement('li');
                    li.classList.add('list-group-item', 'list-group-item-action');
                    li.style.textAlign = 'left'; // force text to be left-aligned
                    li.textContent = `${item.title} (${item.year}) - ${item.sdg_goal}`;
                    li.onclick = () => {
                        window.location.href = `view-research.php?id=${item.id}`;
                    };
                    suggestionsList.appendChild(li);
                });
                suggestionsList.style.display = 'block';
            } else {
                suggestionsList.style.display = 'none';
            }
        });
}


// Show suggestions on focus
searchInput.addEventListener('focus', () => fetchSuggestions(''));

// Show suggestions while typing
searchInput.addEventListener('input', () => {
    const query = searchInput.value.trim();
    fetchSuggestions(query);
});

// Hide suggestions if clicked outside
document.addEventListener('click', (e) => {
    if (!e.target.closest('#searchInput') && !e.target.closest('#suggestionsList')) {
        suggestionsList.style.display = 'none';
    }
});

// Manual search button
searchBtn.addEventListener('click', () => {
    const query = searchInput.value.trim();
    if (query) {
        window.location.href = `research-list.php?search=${encodeURIComponent(query)}`;
    }
});

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</div>

</body>
</html>
