<?php
// ডাটাবেজ কানেকশন লোড করা
require_once '../db.php';

// স্টাইল এবং হেডার (বোঝার সুবিধার জন্য)
echo '<div style="font-family: monospace; padding: 20px; background: #f4f4f4; border-radius: 10px; max-width: 800px; margin: 20px auto;">';
echo '<h2>⚡ Database Migration Tool</h2>';

try {
    // ১. কলাম এড করা (যদি না থাকে)
    // আমরা চেক করব কলামটি আগে থেকেই আছে কিনা, যাতে বারবার রান করলেও এরর না দেয়
    $checkCol = $pdo->query("SHOW COLUMNS FROM buttons LIKE 'section_name'");
    
    if ($checkCol->rowCount() == 0) {
        $sql1 = "ALTER TABLE buttons ADD COLUMN section_name VARCHAR(255) DEFAULT 'Unknown Section'";
        $pdo->exec($sql1);
        echo "<p style='color: green;'>✅ 'section_name' column added successfully.</p>";
    } else {
        echo "<p style='color: orange;'>ℹ️ 'section_name' column already exists. Skipped.</p>";
    }

    // ২. ডাটা আপডেট করা
    // আপনার দেওয়া SQL কুয়েরিগুলো এখানে রান হবে
    $updates = [
        "UPDATE buttons SET section_name = 'Hero Banner - Primary' WHERE id = 1",
        "UPDATE buttons SET section_name = 'Hero Banner - Secondary' WHERE id = 2",
        "UPDATE buttons SET section_name = 'Build to Grow - Call to Action' WHERE id = 3"
    ];

    foreach ($updates as $query) {
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        // কতগুলো রো এফেক্ট হয়েছে তা দেখানো
        $count = $stmt->rowCount();
        if($count > 0) {
            echo "<p style='color: green;'>✅ Updated row (Query: $query)</p>";
        } else {
            echo "<p style='color: gray;'>⚪ No changes needed for (Query: $query)</p>";
        }
    }

    echo "<hr><h3 style='color: green;'>🎉 Database Update Complete!</h3>";

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>❌ Error: " . $e->getMessage() . "</h3>";
}

echo '</div>';
?>