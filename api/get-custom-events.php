<?php
// api/get-custom-events.php

header('Content-Type: application/json');
// নিরাপত্তা: '*' এর বদলে আপনার ডোমেইন দেওয়াই ভালো, তবে টেস্ট করার জন্য '*' ঠিক আছে
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../db.php';

try {
    // 1. ডাটাবেজ থেকে অ্যাক্টিভ রুলসগুলো আনা
    $stmt = $pdo->query("SELECT selector, event_type, event_name, parameters FROM custom_event_rules WHERE status = 'Active'");
    $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. যদি কোনো রুল না থাকে, খালি অ্যারে পাঠানো
    if (!$rules) {
        echo json_encode([]);
    } else {
        echo json_encode($rules);
    }

} catch (PDOException $e) {
    // 3. এরর হ্যান্ডলিং (সাইট ক্র্যাশ করবে না)
    echo json_encode([]);
}
?>