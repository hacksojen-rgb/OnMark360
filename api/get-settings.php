<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../db.php';

try {
    // ১. মেইন সেটিংস
    $stmt = $pdo->query("SELECT * FROM site_settings WHERE id = 1");
    $settings = $stmt->fetch();
    
    // JSON ডিকোড করা
    $settings['header_nav'] = json_decode($settings['header_nav'], true) ?: [];
    
    // ২. সোশ্যাল লিংক
    $socials = $pdo->query("SELECT * FROM social_links ORDER BY sort_order ASC")->fetchAll();
    
    // ৩. ফুটার লিংক (গ্রুপ করা)
    $footer_links_raw = $pdo->query("SELECT * FROM footer_links ORDER BY sort_order")->fetchAll();
    $footer_links = [
        'explore' => [],
        'support' => []
    ];
    foreach ($footer_links_raw as $link) {
        if (isset($footer_links[$link['section_type']])) {
            $footer_links[$link['section_type']][] = [
                'label' => $link['label'],
                'path' => $link['url']
            ];
        }
    }

    // ৪. সব ডাটা মার্জ করে পাঠানো
    $response = [
        'settings' => $settings,
        'social_links' => $socials,
        'footer_links' => $footer_links
    ];
    
    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>