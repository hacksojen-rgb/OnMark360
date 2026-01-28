<?php
// admin/api/get-tracking-config.php

header('Content-Type: application/json');
// নিরাপত্তা: লাইভ সাইটে '*' এর বদলে আপনার ডোমেইন দেওয়াই ভালো
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET');

require_once '../db.php';

try {
    // ডাটাবেজ থেকে কনফিগারেশন আনা
    $stmt = $pdo->query("SELECT * FROM tracking_configs WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$config) {
        // যদি কনফিগ না থাকে, ডিফল্ট ভ্যালু পাঠানো
        echo json_encode([
            'enable_browser_tracking' => 0,
            'enable_server_tracking' => 0,
            'meta_pixel_id' => null,
            'tiktok_pixel_id' => null
        ]);
        exit;
    }

    // সিকিউরিটি: সেনসিটিভ ডাটা (যেমন API Token) ফ্রন্টএন্ডে পাঠানো যাবে না
    // আমরা শুধু পাবলিক আইডিগুলো পাঠাবো
    $publicConfig = [
        'meta_pixel_id' => $config['meta_pixel_id'],
        'tiktok_pixel_id' => $config['tiktok_pixel_id'],
        'ga4_measurement_id' => $config['ga4_measurement_id'],
        'gtm_container_id' => $config['gtm_container_id'],
        'enable_browser_tracking' => (bool)$config['enable_browser_tracking'],
        'enable_server_tracking' => (bool)$config['enable_server_tracking'],
        'enable_test_mode' => (bool)$config['enable_test_mode'],
        'enable_consent_mode' => (bool)$config['enable_consent_mode']
    ];

    echo json_encode($publicConfig);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>