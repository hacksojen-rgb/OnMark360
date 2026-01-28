
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

require_once __DIR__ . '/../db.php';

// PIXEL YOUR SITE PRO - Advanced Hashing Logic
function signal_hash($data) {
    if (empty($data)) return null;
    $data = trim(strtolower($data));
    return hash('sha256', $data);
}

$raw_input = file_get_contents('php://input');
$data = json_decode($raw_input, true);

if (!$data || !isset($data['event_name']) || !isset($data['event_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Malformed signal']);
    exit;
}

// 1. Fetch Configuration (Production Ready)
$stmt = $pdo->query("SELECT * FROM tracking_configs WHERE id = 1");
$config = $stmt->fetch();

if (!$config || !$config['enable_server_tracking']) {
    echo json_encode(['status' => 'bypassed', 'reason' => 'Server tracking disabled']);
    exit;
}

$event_name = $data['event_name'];
$event_id = $data['event_id'];
$external_id = $data['external_id'] ?? null;
$payload = $data['payload'] ?? [];
$user_data = $data['user_data'] ?? [];
$ip = $_SERVER['REMOTE_ADDR'];
$ua = $_SERVER['HTTP_USER_AGENT'];

// 2. Build Advanced Matching Profile (The 'Power' Feature)
$hashed_profile = [
    'em' => signal_hash($user_data['email'] ?? ''),
    'ph' => signal_hash($user_data['phone'] ?? ''),
    'fn' => signal_hash($user_data['first_name'] ?? ''),
    'ln' => signal_hash($user_data['last_name'] ?? ''),
    'external_id' => $external_id,
    'client_ip_address' => $ip,
    'client_user_agent' => $ua,
];

$responses = [];

// 3. META SIGNAL ENGINE (CAPI)
if (!empty($config['meta_pixel_id']) && !empty($config['meta_access_token'])) {
    $meta_payload = [
        'data' => [[
            'event_name' => $event_name,
            'event_time' => time(),
            'event_id' => $event_id,
            'event_source_url' => $payload['page_location'] ?? '',
            'action_source' => 'website',
            'user_data' => array_filter($hashed_profile),
            'custom_data' => array_filter([
                'value' => $payload['value'] ?? null,
                'currency' => $payload['currency'] ?? 'USD',
                'page_title' => $payload['page_title'] ?? '',
                'traffic_source' => $payload['traffic_source'] ?? ''
            ]),
        ]]
    ];
    
    if ($config['enable_test_mode'] && !empty($config['meta_test_event_code'])) {
        $meta_payload['test_event_code'] = $config['meta_test_event_code'];
    }

    $ch = curl_init("https://graph.facebook.com/v18.0/{$config['meta_pixel_id']}/events?access_token={$config['meta_access_token']}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($meta_payload),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json']
    ]);
    $responses['meta'] = curl_exec($ch);
    
    // Log for Signal Pro History
    $stmt = $pdo->prepare("INSERT INTO tracking_events (event_id, event_name, platform, payload, status, last_response) VALUES (?, ?, 'meta', ?, 'sent', ?)");
    $stmt->execute([$event_id, $event_name, json_encode($meta_payload), $responses['meta']]);
    curl_close($ch);
}

// 4. TIKTOK SIGNAL ENGINE (Events API)
if (!empty($config['tiktok_pixel_id']) && !empty($config['tiktok_access_token'])) {
    $tt_payload = [
        'pixel_code' => $config['tiktok_pixel_id'],
        'event' => $event_name,
        'event_id' => $event_id,
        'context' => [
            'page' => ['url' => $payload['page_location'] ?? ''],
            'user' => [
                'email' => $hashed_profile['em'],
                'phone_number' => $hashed_profile['ph'],
                'external_id' => $external_id
            ],
            'ip' => $ip,
            'user_agent' => $ua,
        ]
    ];
    
    $ch = curl_init("https://business-api.tiktok.com/open_api/v1.3/pixel/track/");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($tt_payload),
        CURLOPT_HTTPHEADER => [
            'Access-Token: ' . $config['tiktok_access_token'],
            'Content-Type: application/json'
        ]
    ]);
    $responses['tiktok'] = curl_exec($ch);
    curl_close($ch);
}

// 5. GA4 / GOOGLE ADS SIGNAL
if (!empty($config['ga4_measurement_id']) && !empty($config['ga4_api_secret'])) {
    // GA4 Measurement Protocol Implementation...
}

echo json_encode([
    'status' => 'success', 
    'signal_id' => $event_id,
    'external_id' => $external_id,
    'diagnostics' => $responses
]);
