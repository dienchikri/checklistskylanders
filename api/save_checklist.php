<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json; charset=utf-8');
requireLogin();

// Reads JSON from frontend
$input = json_decode(file_get_contents('php://input'), true);


if (!isset($input['game'], $input['category'], $input['character'], $input['have'])) {
    echo json_encode(["error" => "Invalid input", "received" => $input]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        INSERT INTO user_checklist (user_id, game, category, character_name, have)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE have = VALUES(have)
    ");
    $stmt->execute([
        $user_id,
        $input['game'],
        $input['category'],
        $input['character'],
        $input['have'] ? 1 : 0
    ]);
    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}


