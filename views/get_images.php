<?php
$dir = __DIR__ . '/../assets/img';
$files = [];

// Only allow certain image types
$allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

if (is_dir($dir)) {
    foreach (scandir($dir) as $file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $files[] = $file;
        }
    }
}

header('Content-Type: application/json');
echo json_encode($files);
