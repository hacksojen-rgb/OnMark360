<?php
header('Content-Type: application/json');

// ==============================
// CORS CONFIG
// ==============================
$allowed_origins = [
    'https://onmark360.com',
    'http://localhost:5173',
    'http://localhost:3000'
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}

header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../db.php';

// Safe buffer clean
if (ob_get_length()) {
    ob_clean();
}

// ==============================
// MAIN HANDLER
// ==============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {

        // ------------------------------
        // INPUT DETECTION (JSON OR POST)
        // ------------------------------
        $rawInput = file_get_contents('php://input');
        $jsonData = json_decode($rawInput, true);

        $data = is_array($jsonData) ? $jsonData : $_POST;

        // ------------------------------
        // BASIC REQUIRED FIELDS
        // ------------------------------
        $name  = $data['name']  ?? '';
        $email = $data['email'] ?? '';

        if (empty($name) || empty($email)) {
            throw new Exception('Name and Email are required');
        }

        // ------------------------------
        // COMMON FIELDS
        // ------------------------------
        $subject = $data['subject'] ?? 'New Inquiry';
        $phone   = $data['phone']   ?? 'N/A';
        
        // 🔥 ক্রিটিক্যাল লাইন: সোর্স হ্যান্ডেল করা (Consultation ট্যাবের জন্য জরুরি) 🔥
        $source  = $data['source']  ?? 'Contact Form';

        // ------------------------------
        // EXTENDED FIELDS (OLD SYSTEM SUPPORT)
        // ------------------------------
        $company        = $data['company']        ?? 'N/A';
        $website        = $data['website']        ?? 'N/A';
        $budget         = $data['budget']         ?? 'N/A';
        $decisionMaker  = $data['decision_maker'] ?? 'N/A';
        $challenge      = $data['challenge']      ?? 'N/A';
        // Consultation পেজ থেকে 'message' ফিল্ডে বিস্তারিত তথ্য আসে, তাই এটিই মেইন মেসেজ
        $main_message   = $data['message']        ?? '';

        // ------------------------------
        // MESSAGE BUILDER
        // ------------------------------
        // সব তথ্য সুন্দর করে সাজিয়ে একটি মেসেজ বানানো হচ্ছে
        $combined_message = "";
        
        if (!empty($main_message)) {
            $combined_message .= "Message/Details:\n$main_message\n\n";
        }
        
        $combined_message .= "--- Additional Info ---\n";
        $combined_message .= "Company: $company\n";
        $combined_message .= "Website: $website\n";
        $combined_message .= "Budget: $budget\n";
        $combined_message .= "Decision Maker: $decisionMaker\n";
        $combined_message .= "Challenge: $challenge\n";

        // ------------------------------
        // DATABASE INSERT
        // ------------------------------
        // 'source' কলামটি এখানে যোগ করা হয়েছে
        $stmt = $pdo->prepare("INSERT INTO leads (name, email, subject, phone, message, status, source) VALUES (?, ?, ?, ?, ?, 'New', ?)");
        $result = $stmt->execute([$name, $email, $subject, $phone, $combined_message, $source]);

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Database execution failed');
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
?>