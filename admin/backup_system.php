<?php
// টাইম এবং মেমোরি লিমিট বাড়ানো (বড় ব্যাকআপের জন্য)
set_time_limit(600); // 10 minutes
ini_set('memory_limit', '1024M'); // 1GB Memory

// ১. এনভায়রনমেন্ট চেক এবং সেটআপ
$is_cli = (php_sapi_name() === 'cli');

if (!$is_cli) {
    require_once '../auth.php';
    require_once '../layout_header.php';
    // auth.php তে check_auth() কল করা থাকলে এখানে আর লাগবে না, 
    // তবে নিশ্চিত হওয়ার জন্য রাখা ভালো।
    if(function_exists('check_auth')) check_auth(); 
} else {
    require_once __DIR__ . '/../db.php';
}
// ==================================================
// ENV Loader (if .env exists)
// ==================================================
$envPath = realpath(__DIR__ . '/../.env');
if ($envPath && file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (!str_contains($line, '=')) continue;

        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if (!getenv($key)) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}


// ২. ভেরিয়েবল এবং ফোল্ডার সেটআপ
$backupDir = __DIR__ . '/../backups/';
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$date = date('Y-m-d_H-i-s');
$sqlFileName = "db_backup.sql";
$sqlFile = $backupDir . $sqlFileName;
$zipFile = $backupDir . "backup_$date.zip";
$backupCreated = false;

// CLI log helper
function cli_log($message) {
    if (php_sapi_name() === 'cli') {
        echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    }
}

// ==================================================
// ৩. PHP Native Database Dump Function
// ==================================================
function dumpDatabase($pdo, $outputFile) {
    try {
        $handle = fopen($outputFile, 'w');
        if (!$handle) return false;

        // হেডার
        fwrite($handle, "-- PHP Native Backup\n");
        fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n\n");
        fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
        fwrite($handle, "SET time_zone = \"+00:00\";\n\n");

        // টেবিল লিস্ট
        $tables = [];
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        foreach ($tables as $table) {
            // টেবিল স্ট্রাকচার
            fwrite($handle, "-- Table structure for table `$table`\n");
            fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
            
            $row2 = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
            fwrite($handle, $row2[1] . ";\n\n");

            // ডাটা ডাম্প
            fwrite($handle, "-- Dumping data for table `$table`\n");
            // Large table safe mode
            $pdo->query("SET SESSION sql_big_selects=1");

            $rows = $pdo->query("SELECT * FROM `$table`");
            
            while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                $values = array_map(function($value) use ($pdo) {
                    if ($value === null) return "NULL";
                    return $pdo->quote($value);
                }, array_values($row));
                
                fwrite($handle, "INSERT INTO `$table` VALUES (" . implode(", ", $values) . ");\n");
            }
            fwrite($handle, "\n\n");
        }

        fclose($handle);
        return true;
    } catch (Exception $e) {
    cli_log("Dump error: " . $e->getMessage());
    return false;
}
}

// ==================================================
// ৪. মেইন ব্যাকআপ লজিক
// ==================================================
if ($is_cli || ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['trigger_backup']))) {

    // 🔥 সিকিউরিটি চেক: CSRF টোকেন ভেরিফিকেশন (শুধু ব্রাউজারের জন্য)
    if (!$is_cli) {
        verify_csrf_token($_POST['csrf_token'] ?? '');
    }

    cli_log("Backup process started.");

    // ক. ডাটাবেজ ডাম্প
if (isset($pdo)) {
    $dbSuccess = dumpDatabase($pdo, $sqlFile);
    if ($dbSuccess) {
        cli_log("Database dump successful.");
    } else {
        cli_log("Database dump failed.");
        file_put_contents($sqlFile, "Error: Could not dump database via PHP.");
    }
} else {
    cli_log("PDO connection not found.");
    file_put_contents($sqlFile, "Error: PDO not available.");
}


    // খ. ZIP তৈরি
    $rootPath = realpath(__DIR__ . '/../');
    $zip = new ZipArchive();

    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {

        // ১. সাইটের সব ফাইল
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                // অনাকাঙ্ক্ষিত ফোল্ডার বাদ দেওয়া
                if (
                    strpos($relativePath, 'backups/') !== 0 &&
                    strpos($relativePath, 'error_log') === false &&
                    strpos($relativePath, '.git') === false &&
                    strpos($relativePath, 'node_modules') === false // node_modules বাদ দেওয়া ভালো (অনেক ভারী হয়)
                ) {
                    $zip->addFile($filePath, $relativePath);
                }
            }
        }

        // ২. SQL ফাইলটি জিপের ভেতর যোগ করা
        if (file_exists($sqlFile)) {
            $zip->addFile($sqlFile, $sqlFileName);
        } 
        else {
    $zip->addFromString(
        "database_missing.txt",
        "SQL dump file was not generated. Check backup logs."
    );
}

        $zip->close();

        // ৩. ক্লিনআপ: SQL ফাইল ডিলিট
        if (file_exists($sqlFile)) {
            unlink($sqlFile);
        }

        $backupCreated = true;
        cli_log("ZIP archive created successfully.");
    } else {
        cli_log("Failed to create ZIP archive.");
    }

    // গ. অটোমেটিক ক্লিনআপ (পুরনো ফাইল ডিলিট)
    $now = time();
    foreach (glob($backupDir . "*.zip") as $file) {
        if ($now - filemtime($file) >= 60 * 60 * 24 * 7) { // ৭ দিন
            unlink($file);
        }
    }
}

// ==================================================
// ৫. UI (Browser only)
// ==================================================
if (!$is_cli) {
?>

<div class="max-w-4xl mx-auto pb-24 animate-in fade-in duration-500">
    
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-black text-[#014034] uppercase flex items-center gap-3">
                <i data-lucide="database-backup" class="w-8 h-8"></i> Backup System
            </h1>
            <p class="text-gray-500 text-xs font-bold uppercase tracking-widest mt-1">
                Full Site & Database Archive
            </p>
        </div>
    </div>

    <?php if ($backupCreated): ?>
        <div class="bg-white p-8 rounded-[2rem] border border-green-100 shadow-xl text-center">
            <div class="inline-flex p-5 bg-green-100 text-green-600 rounded-full mb-6">
                <i data-lucide="check-circle" class="w-16 h-16"></i>
            </div>
            <h1 class="text-3xl font-black text-[#014034] mb-3 uppercase">Backup Ready!</h1>
            <p class="text-gray-500 mb-8 font-medium">Full backup generated successfully at <?php echo date('H:i:s'); ?></p>

            <div class="flex justify-center gap-4">
                <a href="../backups/<?php echo basename($zipFile); ?>" class="bg-[#014034] text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest hover:scale-105 transition-transform shadow-lg flex items-center gap-3">
                    <i data-lucide="download" class="w-5 h-5"></i> Download ZIP
                </a>
                <a href="backup_system.php" class="bg-gray-100 text-gray-500 px-10 py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-gray-200 transition-colors">
                    Back
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white p-10 rounded-[2rem] border border-gray-100 shadow-xl text-center">
            <div class="inline-flex p-6 bg-green-50 rounded-full mb-6">
                <i data-lucide="server" class="w-12 h-12 text-[#014034]"></i>
            </div>
            
            <h2 class="text-2xl font-black text-[#014034] mb-4">Generate Full Backup</h2>
            <p class="text-gray-500 text-sm mb-8 max-w-lg mx-auto leading-relaxed">
                This process will create a <b>ZIP archive</b> containing:
                <br>✅ All Website Files (Images, PHP, CSS, JS)
                <br>✅ Full Database Dump (.sql)
            </p>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="trigger_backup" value="1">
                
                <button type="submit" onclick="this.innerHTML='Processing...'; this.disabled=true; this.form.submit();" class="bg-[#014034] text-white px-12 py-5 rounded-2xl font-black uppercase tracking-widest hover:shadow-2xl hover:scale-105 transition-all flex items-center gap-3 mx-auto cursor-pointer">
                    <i data-lucide="save" class="w-5 h-5"></i> Start Backup Process
                </button>
            </form>
            
            <p class="mt-8 text-[10px] text-gray-400 font-bold uppercase tracking-widest bg-gray-50 inline-block px-4 py-2 rounded-lg">
                <i data-lucide="clock" class="w-3 h-3 inline mr-1"></i> Auto-cleanup: Backups older than 7 days are deleted.
            </p>
        </div>
    <?php endif; ?>

</div>

<?php 
    require_once '../layout_footer.php'; 
}
?>