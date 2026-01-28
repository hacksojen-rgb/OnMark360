<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 
require_once '../db.php';

$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    echo json_encode(['success' => false, 'message' => 'No slug provided']);
    exit;
}

$stmt = $pdo->prepare("SELECT title, content, updated_at FROM pages WHERE slug = ? AND status = 'published'");
$stmt->execute([$slug]);
$page = $stmt->fetch(PDO::FETCH_ASSOC);

if ($page) {
    echo json_encode(['success' => true, 'data' => $page]);
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Page not found']);
}
?>