<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../db.php';

$slug = $_GET['slug'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT * FROM blogs WHERE slug = ?");
    $stmt->execute([$slug]);
    $post = $stmt->fetch();
    echo json_encode($post);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>