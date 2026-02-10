<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';


// Ensure only logged-in users can view
requireLogin();

$username = getUsername($pdo, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Skylanders Collection Vault</title>

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#00bcd4">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/logoimg/collectionvault.png">
    <link rel="apple-touch-icon" sizes="167x167" href="/assets/logoimg/collectionvault.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/assets/logoimg/collectionvault.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/assets/logoimg/collectionvault.png">
    <link rel="icon" type="image/png" href="/assets/logoimg/collectionvault.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="bg-info">

<!-- Fixed Header -->
<header class="fixed-header d-flex justify-content-between align-items-center px-3 py-2 shadow-sm">
    <div class="d-flex align-items-center gap-3">
        <h1 class="fw-bold m-0 text-white">Skylanders Checklist</h1>
        <div class="welcome-box d-none d-md-inline-flex align-items-center px-3 py-2 border rounded-pill bg-light text-dark fw-semibold">
            Welcome, <strong class="ms-1"><?= htmlspecialchars($username) ?></strong>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
        <button id="toggleAllBtn" class="btn btn-primary">
            <i class="bi bi-arrows-expand"></i> Expand All
        </button>
        <button id="toggleAllImagesBtn" class="btn btn-success">
            <i class="bi bi-images"></i> Show All Images
        </button>
        <button id="themeToggle" class="btn btn-outline-light" title="Toggle Dark Mode">
            <i class="bi bi-moon"></i>
        </button>
        <a href="../auth/logout.php" class="btn btn-danger">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</header>

<div class="container py-4 mt-header">



    <div id="statsPanel" class="stats-panel ">
        <h5>Progress</h5>
        <p><strong>Have:</strong> <span id="checkedCount">0</span></p>
        <p><strong>Left:</strong> <span id="uncheckedCount">0</span></p>
        <p><strong>Visible:</strong> <span id="visibleCount">0</span></p>
    </div>
    <div class="container my-4">
        <input type="text" id="searchInput" class="form-control mb-4" placeholder="Search for a Skylander...">
    </div>


    <div id="checklist-container"></div>
</div>









<!-- Pass image data from PHP -->
<script>
    const imageFiles = <?php
        $images = [];
        $dir = __DIR__ . '/../assets/img';
        if (is_dir($dir)) {
            foreach (scandir($dir) as $file) {
                if (preg_match('/\.(jpg|jpeg|png|webp)$/i', $file)) {
                    $images[] = $file;
                }
            }
        }
        echo json_encode($images);
        ?>;
</script>

<!-- JS Imports -->
<script type="module" src="../assets/js/utils.js"></script>
<script type="module" src="../assets/js/images.js"></script>
<script type="module" src="../assets/js/checklist.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Theme Toggle Script -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const body = document.body;
        const toggle = document.getElementById("themeToggle");
        const icon = toggle.querySelector("i");

        const saved = localStorage.getItem("theme");
        if (saved === "dark") {
            body.classList.add("dark-mode");
            icon.className = "bi bi-sun";
        }

        toggle.addEventListener("click", () => {
            const dark = body.classList.toggle("dark-mode");
            icon.className = dark ? "bi bi-sun" : "bi bi-moon";
            localStorage.setItem("theme", dark ? "dark" : "light");
        });
    });
</script>
</body>
</html>
