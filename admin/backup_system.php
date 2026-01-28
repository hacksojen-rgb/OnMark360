<?php
// অটোমেটিক রানের জন্য সেশন চেক বাদ দিতে হবে যদি এটি CLI (Command Line) থেকে রান হয়
if (php_sapi_name() !== 'cli') {
    require_once '../auth.php';
    check_auth();
    require_once '../layout_header.php'; // স্টাইলের জন্য
} else {
    // CLI মোডে থাকলে ডাটাবেজ কানেকশন ম্যানুয়ালি ইনক্লুড করতে হবে
    require_once __DIR__ . '/../db.php';
}

// কনফিগারেশন
$db_host = 'localhost';
$db_name = 'princepanjabibd_agency_db';
$db_user = 'princepanjabibd_princepanjabibd';
$db_pass = 'IPM&~#nYJhx';

$backupDir = __DIR__ . '/../backups/';
if (!file_exists($backupDir)) { mkdir($backupDir, 0755, true); }

// ফাইলের নাম
$date = date('Y-m-d_H-i-s');
$sqlFile = $backupDir . "db_$date.sql";
$zipFile = $backupDir . "backup_$date.zip";

// ১. ডাটাবেজ ব্যাকআপ
$command = "mysqldump --opt -h $db_host -u $db_user -p'$db_pass' $db_name > $sqlFile";
system($command);

// ২. ফাইল জিপ করা
$rootPath = realpath(__DIR__ . '/../');
$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($rootPath),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($rootPath) + 1);
            // backups ফোল্ডার এবং error_log বাদ দেওয়া
            if (strpos($filePath, 'backups') === false && strpos($filePath, 'error_log') === false) {
                $zip->addFile($filePath, $relativePath);
            }
        }
    }
    $zip->close();
    // SQL ফাইলটি জিপের ভেতর থাকলে বাইরে রাখার দরকার নেই, ডিলিট করে দিই
    unlink($sqlFile);
}

// ৩. পুরনো ব্যাকআপ ডিলিট করা (৭ দিনের বেশি পুরনো ফাইল ডিলিট হবে - সার্ভার স্পেস বাঁচাতে)
$files = glob($backupDir . "*.zip");
$now   = time();
foreach ($files as $file) {
    if (is_file($file)) {
        if ($now - filemtime($file) >= 60 * 60 * 24 * 7) { // 7 days
            unlink($file);
        }
    }
}

// ব্রাউজারে আউটপুট দেখানো
if (php_sapi_name() !== 'cli') {
    echo '<div class="max-w-4xl mx-auto mt-10 p-8 bg-white rounded-3xl shadow-lg border border-gray-100 text-center animate-in fade-in zoom-in duration-500">';
    echo '<div class="inline-flex p-4 bg-green-100 text-green-600 rounded-full mb-4"><i data-lucide="check-circle" class="w-12 h-12"></i></div>';
    echo '<h1 class="text-3xl font-black text-[#014034] mb-2 uppercase">Backup Successful!</h1>';
    echo '<p class="text-gray-500 mb-8">Full site and database backup created successfully.</p>';
    echo '<a href="../backups/backup_'.$date.'.zip" class="bg-[#014034] text-white px-8 py-4 rounded-xl font-bold uppercase tracking-widest hover:scale-105 transition-transform shadow-xl inline-flex items-center gap-2"><i data-lucide="download"></i> Download Backup</a>';
    echo '</div>';
    require_once '../layout_footer.php';
}
?>