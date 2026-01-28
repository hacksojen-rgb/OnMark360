<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../db.php';

try {
    $plans = $pdo->query("SELECT * FROM pricing_plans ORDER BY id ASC")->fetchAll();
    foreach ($plans as &$p) {
        if (is_string($p['features'])) {
            $p['features'] = json_decode($p['features'], true);
        }
        $p['is_popular'] = (bool)$p['is_popular'];
    }
    echo json_encode($plans);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>