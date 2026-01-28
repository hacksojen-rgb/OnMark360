<?php
// api/get-hero-slides.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../db.php';

try {
    // শুধুমাত্র Active স্লাইডগুলো ফেচ করা হবে
    // SELECT * ব্যবহার করায় নতুন সব কলাম অটোমেটিক চলে আসবে
    $stmt = $pdo->query("SELECT * FROM hero_slides WHERE status = 'Active' ORDER BY id DESC");
    $slides = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($slides);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>