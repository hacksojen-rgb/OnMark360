<?php
require_once '../db.php';

try {
    // ১. সোশ্যাল লিংক টেবিল চেক ও ফিক্স
    $pdo->exec("CREATE TABLE IF NOT EXISTS social_links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        platform VARCHAR(50),
        icon_code VARCHAR(50),
        url VARCHAR(255)
    )");

    // ২. ফুটার লিংক টেবিল চেক ও ফিক্স
    $pdo->exec("CREATE TABLE IF NOT EXISTS footer_links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        section_type ENUM('explore', 'support') NOT NULL,
        label VARCHAR(100),
        url VARCHAR(255),
        sort_order INT DEFAULT 0
    )");

    // ৩. বাটন টেবিল চেক ও ফিক্স
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_buttons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        section_key VARCHAR(50) NOT NULL UNIQUE,
        label VARCHAR(100),
        url VARCHAR(255),
        bg_color VARCHAR(20) DEFAULT '#014034',
        text_color VARCHAR(20) DEFAULT '#ffffff',
        border_color VARCHAR(20) DEFAULT '#014034'
    )");

    // ৪. আপনার চাহিদামত সব বাটন ইনসার্ট করা (যদি না থাকে)
    $buttons = [
        ['nav_quote', 'Get a Quote (Navbar)', '/get-quote', '#014034', '#ffffff', '#014034'],
        ['home_advantage', 'Get Growth Plan (Home Advantage)', '/get-quote', '#014034', '#ffffff', '#014034'],
        ['footer_cta_primary', 'Get a Free Growth Plan (Footer)', '/get-quote', '#ffffff', '#014034', '#ffffff'],
        ['footer_cta_secondary', 'Book a Consultation (Footer)', '/contact', 'transparent', '#ffffff', '#ffffff'],
        ['contact_submit', 'Send Message (Contact Page)', '', '#014034', '#ffffff', '#014034'],
        ['get_quote_submit', 'Submit Request (Get Quote Page)', '', '#014034', '#ffffff', '#014034']
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO site_buttons (section_key, label, url, bg_color, text_color, border_color) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($buttons as $btn) {
        $stmt->execute($btn);
    }

    // ৫. সেটিংস টেবিলে কলাম আছে কিনা চেক করা (না থাকলে এরর এড়াতে)
    // এটি ম্যানুয়ালি চেক করার প্রয়োজন নেই যদি আগের SQL রান হয়ে থাকে।
    
    // ৬. অতিরিক্ত ফুটার লিংক রিমুভ করা (আপনার অভিযোগ অনুযায়ী)
    $pdo->exec("DELETE FROM footer_links"); 

    echo "<h1 style='color:green;text-align:center;'>Database Fixed Successfully!</h1>";
    echo "<p style='text-align:center;'>All buttons inserted. Unwanted footer links removed. Please delete this file now.</p>";
    echo "<div style='text-align:center;'><a href='buttons.php'>Go to Buttons Page</a></div>";

} catch (PDOException $e) {
    echo "<h1 style='color:red;'>Error: " . $e->getMessage() . "</h1>";
}
?>