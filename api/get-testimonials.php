<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../db.php';

try {
    // Note: Ensure your DB has a `testimonials` table.
    $stmt = $pdo->query("SELECT * FROM testimonials ORDER BY id DESC");
    $items = $stmt->fetchAll();
    echo json_encode($items);
} catch (Exception $e) {
    // If table doesn't exist yet, return empty array instead of crashing
    echo json_encode([]);
}
?>