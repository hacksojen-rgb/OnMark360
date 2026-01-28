<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../db.php';

try {
    $logos = $pdo->query("SELECT * FROM client_logos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($logos);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>