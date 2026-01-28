<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../db.php';

try {
    $buttons = $pdo->query("SELECT * FROM site_buttons")->fetchAll();
    
    // ডাটাকে একটি সুন্দর Key-Value অবজেক্টে রূপান্তর করা হচ্ছে যাতে ফ্রন্টএন্ডে সহজে ধরা যায়
    // যেমন: buttons.nav_quote.label বা buttons.footer_cta.url
    $formatted_buttons = [];
    foreach ($buttons as $btn) {
        $formatted_buttons[$btn['section_key']] = [
            'label' => $btn['label'],
            'url' => $btn['url'],
            'style' => [
                'backgroundColor' => $btn['bg_color'],
                'color' => $btn['text_color'],
                'borderColor' => $btn['border_color'],
                'borderWidth' => '2px'
            ]
        ];
    }
    
    echo json_encode($formatted_buttons);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>