


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Skylanders Collection Vault | Images</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .image-card {
            width: 150px;
            margin: 10px;
            text-align: center;
        }
        .image-card img {
            max-width: 100%;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }
        .image-card label {
            display: block;
            word-break: break-word;
        }
    </style>
</head>

<body class="bg-info">
<div class="container my-4">
    <h1 class="mb-4">Skylanders Image Library</h1>

    <div class="container my-4">
        <input type="text" id="searchInput" class="form-control mb-4" placeholder="Search Skylander...">
    </div>

    <a href="../index.php" class="btn btn-primary mb-3">
        <i class="bi bi-archive"></i> Back to Checklist
    </a>

    <div class="d-flex flex-wrap" id="imageContainer">
        <?php
        function getImages($dir) {
            $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
            $files = [];
            foreach ($rii as $file) {
                if ($file->isDir()) continue;
                if (in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png'])) {
                    $relativePath = str_replace('\\', '/', substr($file->getPathname(), strlen(__DIR__) + 1));
                    $files[] = $relativePath;
                }
            }
            return $files;
        }

        $images = getImages(__DIR__ . '/img');
        $hidden = ['1', 'final', 'wm', 'actual', 'series', '2', '3'];

        foreach ($images as $img) {
            $basename = pathinfo($img, PATHINFO_FILENAME);
            $cleanName = $basename;
            foreach ($hidden as $item) {
                $cleanName = str_ireplace($item, '', $cleanName);
            }
            $formattedName = wordwrap(htmlspecialchars($cleanName), 17, "-<br>", true);

            echo "
            <div class='image-card' data-name='" . strtolower($cleanName) . "'>
                <img src='$img' alt='$basename'>
                <div><label for='$basename'>$formattedName</label></div>
            </div>";
        }
        ?>
    </div>
</div>

<script>
    document.getElementById('searchInput').addEventListener('input', function () {
        const query = this.value.toLowerCase();
        document.querySelectorAll('.image-card').forEach(card => {
            const name = card.getAttribute('data-name');
            card.style.display = name.includes(query) ? '' : 'none';
        });


    });



    document.querySelectorAll('.image-card img').forEach(img => {
        img.addEventListener('click', function () {
            const modalImage = document.getElementById('modalImage');
            modalImage.src = this.src;
            const modal = new bootstrap.Modal(document.getElementById('imageModal'));
            modal.show();
        });
    });
</script>

<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-body p-0">
                <img src="" id="modalImage" class="img-fluid w-100" alt="Enlarged Skylander">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
