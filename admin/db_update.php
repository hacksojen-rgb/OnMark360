<?php
// ১. এরর রিপোর্টিং এবং ডাটাবেজ কানেকশন
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../db.php';

echo '<div style="font-family: monospace; padding: 20px; background: #f4f4f4; border-radius: 10px; max-width: 800px; margin: 20px auto;">';
echo '<h2>⚡ Database Repair & Migration Tool</h2>';

try {
    // ---------------------------------------------------------
    // ধাপ ১: টেবিল তৈরি করা (যদি না থাকে)
    // আমরা 'site_buttons' নাম ব্যবহার করছি যা স্ট্যান্ডার্ড। 
    // যদি আপনার buttons.php ফাইলে 'buttons' নাম থাকে, তবে এটি আপডেট করতে হবে।
    // ---------------------------------------------------------
    
    $table_name = 'site_buttons'; 
    
    $sql_create = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        label VARCHAR(255) NOT NULL,
        url VARCHAR(255) NOT NULL,
        bg_color VARCHAR(50) DEFAULT '#014034',
        text_color VARCHAR(50) DEFAULT '#ffffff',
        border_color VARCHAR(50) DEFAULT '#014034',
        section_key VARCHAR(255) DEFAULT 'Unknown', 
        section_name VARCHAR(255) DEFAULT 'General Section'
    )";
    
    $pdo->exec($sql_create);
    echo "<p style='color: green;'>✅ Table '$table_name' checked/created successfully.</p>";

    // ---------------------------------------------------------
    // ধাপ ২: কলাম চেক করা (section_name)
    // ---------------------------------------------------------
    $checkCol = $pdo->query("SHOW COLUMNS FROM $table_name LIKE 'section_name'");
    if ($checkCol->rowCount() == 0) {
        $pdo->exec("ALTER TABLE $table_name ADD COLUMN section_name VARCHAR(255) DEFAULT 'Unknown Section'");
        echo "<p style='color: green;'>✅ Column 'section_name' added.</p>";
    }

    // ---------------------------------------------------------
    // ধাপ ৩: ডিফল্ট ডাটা ইনসার্ট/আপডেট করা
    // ---------------------------------------------------------
    
    // ডাটা আছে কিনা চেক করা
    $checkData = $pdo->query("SELECT COUNT(*) FROM $table_name")->fetchColumn();

    if ($checkData == 0) {
        // ডাটা নেই, নতুন ডাটা ইনসার্ট করা হচ্ছে
        $stmt = $pdo->prepare("INSERT INTO $table_name (label, url, section_key, section_name) VALUES (?, ?, ?, ?)");
        
        $buttons = [
            ['Get Started', '/get-quote', 'hero_primary', 'Hero Banner - Primary'],
            ['Learn More', '/services', 'hero_secondary', 'Hero Banner - Secondary'],
            ['Book Consultation', '/book', 'cta_section', 'Build to Grow - Call to Action']
        ];

        foreach ($buttons as $btn) {
            $stmt->execute($btn);
        }
        echo "<p style='color: green;'>✅ Default buttons data inserted.</p>";
    } else {
        // ডাটা আছে, শুধু নাম আপডেট করা হচ্ছে
        $updates = [
            "UPDATE $table_name SET section_name = 'Hero Banner - Primary' WHERE id = 1",
            "UPDATE $table_name SET section_name = 'Hero Banner - Secondary' WHERE id = 2",
            "UPDATE $table_name SET section_name = 'Build to Grow - Call to Action' WHERE id = 3"
        ];

        foreach ($updates as $query) {
            $pdo->exec($query);
        }
        echo "<p style='color: blue;'>ℹ️ Existing buttons updated with new section names.</p>";
    }

    echo "<hr><h3 style='color: green;'>🎉 Database Fixed Successfully!</h3>";
    echo "<p>Now you can visit <b>buttons.php</b> (Make sure buttons.php uses table name: <code>$table_name</code>)</p>";

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>❌ Error: " . $e->getMessage() . "</h3>";
}

echo '</div>';
?>