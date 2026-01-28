<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../db.php';

try {
    $leads = $pdo->query("SELECT * FROM leads ORDER BY created_at DESC")->fetchAll();
    echo json_encode($leads);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>