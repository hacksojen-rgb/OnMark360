<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../db.php';

try {
    $projects = $pdo->query("SELECT * FROM portfolio ORDER BY id DESC")->fetchAll();
    echo json_encode($projects);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>