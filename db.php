<?php
// ob_start() আমরা রাখছি কারণ আপনি বলেছেন এটি রিডাইরেক্ট এরর ফিক্স করে, যদিও এটি বেস্ট প্র্যাকটিস না, আপাতত সাইট সচল রাখতে এটি থাক।
ob_start(); 

$host = 'localhost';
$db   = 'princepanjabibd_agency_db'; 
$user = 'princepanjabibd_princepanjabibd';  
$pass = 'IPM&~#nYJhx';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // পরিবর্তন: সরাসরি এরর না দেখিয়ে একটি জেনেরিক মেসেজ দেওয়া এবং আসল এরর লগ ফাইলে রাখা
     error_log("Database Connection Error: " . $e->getMessage()); // এটি সার্ভারের error_log ফাইলে সেভ হবে
     header('Content-Type: application/json');
     http_response_code(500);
     echo json_encode(["error" => "Database connection failed"]);
     exit; 
}


// AUTOMATIC DATABASE MIGRATION SYSTEM
function checkAndAddColumn($pdo, $table, $col, $type) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$col'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$col` $type");
            error_log("Auto-Migrate: Added `$col` to `$table`");
        }
    } catch (PDOException $e) {
        // Ignore if table doesn't exist
    }
}

if (!defined('AUTO_MIGRATION_RAN')) {
    define('AUTO_MIGRATION_RAN', true);

// আপনার প্রয়োজনীয় সব কলাম এখানে ডিফাইন করুন
    checkAndAddColumn($pdo, 'site_settings', 'footer_privacy_text', "VARCHAR(255) DEFAULT 'Privacy Policy'");
    checkAndAddColumn($pdo, 'site_settings', 'footer_terms_text', "VARCHAR(255) DEFAULT 'Terms of Service'");
    checkAndAddColumn($pdo, 'site_settings', 'company_address', "TEXT");
    checkAndAddColumn($pdo, 'site_settings', 'platform_name', "VARCHAR(255)");
    checkAndAddColumn($pdo, 'portfolio', 'client_name', "VARCHAR(255)");
    checkAndAddColumn($pdo, 'portfolio', 'video_url', "VARCHAR(255)");
    // ভবিষ্যতে নতুন কিছু লাগলে শুধু এখানে একটা লাইন বাড়িয়ে দিলেই হবে।
}
?>