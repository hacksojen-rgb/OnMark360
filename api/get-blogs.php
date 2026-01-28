<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../db.php';

try {
    $blogs = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC")->fetchAll();
    echo json_encode($blogs);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>