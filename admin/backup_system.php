<?php
// টাইম এবং মেমোরি লিমিট বাড়ানো (বড় ব্যাকআপের জন্য)
set_time_limit(300);
ini_set('memory_limit', '512M');

// ১. এনভায়রনমেন্ট চেক এবং সেটআপ
$is_cli = (php_sapi_name() === 'cli');

if (!$is_cli) {
    require_once '../auth.php';
    check_auth();
    require_once '../layout_header.php';
} else {
    require_once __DIR__ . '/../db.php';
}

// ২. ENV Loader (যদি থাকে)
$envPath = realpath(__DIR__ . '/../.env');
if ($envPath && file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if (!getenv($key)) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// ৩. ভেরিয়েবল এবং ফোল্ডার সেটআপ
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
// ৪. PHP Native Database Dump Function (No mysqldump required)
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
            // টেবিল ড্রপ এবং ক্রিয়েট স্ট্রাকচার
            fwrite($handle, "-- Table structure for table `$table`\n");
            fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
            
            $row2 = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
            fwrite($handle, $row2[1] . ";\n\n");

            // ডাটা ডাম্প
            fwrite($handle, "-- Dumping data for table `$table`\n");
            $rows = $pdo->query("SELECT * FROM `$table`");
            
            while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                $values = array_map(function($value) use ($pdo) {
                    if ($value === null) return "NULL";
                    // PDO::quote ব্যবহার করে সেফ করা
                    return $pdo->quote($value);
                }, array_values($row));
                
                fwrite($handle, "INSERT INTO `$table` VALUES (" . implode(", ", $values) . ");\n");
            }
            fwrite($handle, "\n\n");
        }

        fclose($handle);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// ==================================================
// ৫. মেইন ব্যাকআপ লজিক
// ==================================================
if ($is_cli || ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['trigger_backup']))) {

    cli_log("Backup process started.");

    // ক. ডাটাবেজ ডাম্প (PHP দিয়ে)
    // $pdo অবজেক্টটি db.php বা layout_header.php থেকে আসছে
    if (isset($pdo)) {
        $dbSuccess = dumpDatabase($pdo, $sqlFile);
        if ($dbSuccess) {
            cli_log("Database dump successful (PHP Mode).");
        } else {
            cli_log("Database dump failed.");
            file_put_contents($sqlFile, "Error: Could not dump database via PHP.");
        }
    } else {
        cli_log("PDO connection not found.");
        file_put_contents($sqlFile, "Error: Database connection not active.");
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

                // অনাকাঙ্ক্ষিত ফোল্ডার বাদ দেওয়া
                if (
                    strpos($relativePath, 'backups/') !== 0 &&
                    strpos($relativePath, 'error_log') === false &&
                    strpos($relativePath, '.git') === false
                ) {
                    $zip->addFile($filePath, $relativePath);
                }
            }
        }

        // ২. SQL ফাইলটি জিপের ভেতর যোগ করা
        if (file_exists($sqlFile)) {
            $zip->addFile($sqlFile, $sqlFileName);
        } else {
            $zip->addFromString("database_missing.txt", "SQL file was not generated.");
        }

        $zip->close();

        // ৩. বাইরের SQL ফাইল ডিলিট (ক্লিনআপ)
        if (file_exists($sqlFile)) {
            unlink($sqlFile);
        }

        $backupCreated = true;
        cli_log("ZIP archive created successfully.");
    } else {
        cli_log("Failed to create ZIP archive.");
    }

    // গ. অটোমেটিক ক্লিনআপ (৭ দিনের পুরনো ডিলিট)
    $now = time();
    foreach (glob($backupDir . "*.zip") as $file) {
        if ($now - filemtime($file) >= 60 * 60 * 24 * 7) {
            unlink($file);
            cli_log("Old backup removed: " . basename($file));
        }
    }

    cli_log("Backup process finished.");
}

// ==================================================
// ৬. UI (Browser only)
// ==================================================
if (!$is_cli) {
?>

<div class="max-w-4xl mx-auto mt-10 p-10 bg-white rounded-[3rem] shadow-sm border border-gray-100 text-center animate-in fade-in duration-500">

<?php if ($backupCreated): ?>
    <div class="inline-flex p-5 bg-green-100 text-green-600 rounded-full mb-6">
        <i data-lucide="check-circle" class="w-16 h-16"></i>
    </div>
    <h1 class="text-4xl font-black text-[#014034] mb-3 uppercase tracking-tight">Backup Ready!</h1>
    <p class="text-gray-500 mb-10 font-medium">Full site and database backup generated successfully.</p>

    <div class="flex justify-center gap-4">
        <a href="../backups/<?php echo basename($zipFile); ?>" class="bg-[#014034] text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest hover:scale-105 transition-transform shadow-xl inline-flex items-center gap-3">
            <i data-lucide="download"></i> Download Backup
        </a>
        <a href="backup_system.php" class="bg-gray-100 text-gray-500 px-10 py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-gray-200 transition-colors">
            Back
        </a>
    </div>
<?php else: ?>
    <div class="inline-flex p-5 bg-[#014034]/10 text-[#014034] rounded-full mb-6">
        <i data-lucide="database" class="w-16 h-16"></i>
    </div>
    <h1 class="text-4xl font-black text-[#014034] mb-3 uppercase tracking-tight">System Backup</h1>
    <p class="text-gray-500 mb-10 font-medium max-w-lg mx-auto">
        Create a complete archive of your website files and database. 
        <br><span class="text-xs text-orange-500 font-bold">(Using PHP Native Dumper - Safe for Special Characters)</span>
    </p>

    <form method="POST">
        <input type="hidden" name="trigger_backup" value="1">
        <button type="submit" class="bg-[#014034] text-white px-12 py-5 rounded-2xl font-black uppercase tracking-widest hover:bg-[#00332a] hover:shadow-2xl transition-all inline-flex items-center gap-3 cursor-pointer">
            <i data-lucide="save"></i> Take Backup Now
        </button>
    </form>
    
    <p class="mt-8 text-xs text-gray-400 font-bold uppercase tracking-widest">
        * Older backups (7+ days) are automatically removed.
    </p>
<?php endif; ?>

</div>

<?php 
    require_once '../layout_footer.php'; 
}
?>