<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
require_once '../db.php';

try {
    // ডাটাবেজ থেকে সব সার্ভিস আনা হচ্ছে
    $stmt = $pdo->query("SELECT * FROM services ORDER BY id DESC");
    $services = $stmt->fetchAll();

    foreach ($services as &$service) {
        // ১. যদি ডাটাবেজে আইকন না থাকে, তবে একটি ডিফল্ট আইকন (Zap) সেট করা
        if (empty($service['icon'])) {
            $service['icon'] = 'Zap';
        }

        // ২. ফিচারগুলো যদি ডাটাবেজে JSON ফরম্যাটে থাকে, তবে সেটিকে অ্যারেতে রূপান্তর করা
        if (isset($service['features']) && is_string($service['features'])) {
            $decoded = json_decode($service['features'], true);
            // যদি ডিকোড সফল হয় তবে অ্যারে নেবে, নাহলে লাইন ব্রেক দিয়ে ভাগ করবে
            $service['features'] = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode("\n", $service['features'])));
        } else {
            $service['features'] = [];
        }
    }

    // সবশেষে JSON ডাটা আউটপুট দেওয়া
    echo json_encode($services);

} catch (Exception $e) {
    // কোনো ভুল হলে এরর মেসেজ পাঠানো
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>