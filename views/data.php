<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $imgFolder = __DIR__ . "/img/";
    $checklists = [];

    $games = $pdo->query("SELECT * FROM games")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($games as $game) {
        $game_id = $game['id'];
        $checklists[$game['name']] = [];

        $stmt = $pdo->prepare("SELECT * FROM categories WHERE game_id = ?");
        $stmt->execute([$game_id]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($categories as $cat) {
            $cat_id = $cat['id'];

            $stmt2 = $pdo->prepare("SELECT name FROM characters WHERE category_id = ?");
            $stmt2->execute([$cat_id]);
            $chars = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            foreach ($chars as &$char) {
                $baseName = strtolower(str_replace([' ', '-', '’'], '_', $char['name']));
                $found = false;
                foreach (['png', 'jpg', 'jpeg', 'webp'] as $ext) {
                    $file = $imgFolder . $baseName . '.' . $ext;
                    if (file_exists($file)) {
                        $char['image_filename'] = $baseName . '.' . $ext;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $char['image_filename'] = null;
                }
            }

            $checklists[$game['name']][$cat['name']] = $chars;
        }
    }

    echo json_encode($checklists, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>

