<?php
// ১. এরর রিপোর্টিং চালু করা (ডিবাগিংয়ের জন্য)
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ২. স্মার্ট ফাইল ইনক্লুশন
$auth_path = file_exists(__DIR__ . '/auth.php') ? __DIR__ . '/auth.php' : __DIR__ . '/../auth.php';
$header_path = file_exists(__DIR__ . '/layout_header.php') ? __DIR__ . '/layout_header.php' : __DIR__ . '/../layout_header.php';

// ৩. Auth load
if (file_exists($auth_path)) {
    require_once $auth_path;
} else {
    die("<div style='color:red;padding:20px;'>auth.php পাওয়া যায়নি</div>");
}

if (!function_exists('generate_csrf_token')) {
    die("<div style='color:red;padding:20px;'>CSRF function missing</div>");
}

$is_popup = isset($_GET['popup']);

if (!$is_popup && file_exists($header_path)) {
    require_once $header_path;
}

$upload_dir = '../uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

/* ===============================
   Flash Message Helpers
================================ */
function set_flash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
function show_flash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $color = $f['type'] === 'error' ? 'red' : 'green';
        echo "<div class='mb-4 p-3 rounded-lg bg-{$color}-50 text-{$color}-600 text-sm font-bold'>{$f['msg']}</div>";
    }
}

/* ===============================
   DELETE LOGIC
================================ */
if (isset($_POST['delete_image'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $file = basename($_POST['delete_image']);
    $path = $upload_dir . $file;

    if (is_file($path)) {
        unlink($path);
        set_flash('success', 'Image deleted successfully');
    } else {
        set_flash('error', 'File not found');
    }

    header("Location: media.php" . ($is_popup ? "?popup=1" : ""));
    exit;
}

/* ===============================
   UPLOAD LOGIC (SECURED)
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['new_media'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $file = $_FILES['new_media'];

    // Size limit (2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        set_flash('error', 'File too large. Max 2MB allowed.');
        header("Location: media.php" . ($is_popup ? "?popup=1" : ""));
        exit;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Extension whitelist
    $allowed_ext = ['jpg','jpeg','png','webp'];
    if (!in_array($ext, $allowed_ext)) {
        set_flash('error', 'Invalid file type');
        header("Location: media.php" . ($is_popup ? "?popup=1" : ""));
        exit;
    }

    // MIME validation
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed_mime = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    if (!in_array($mime, $allowed_mime)) {
        set_flash('error', 'Invalid image MIME type');
        header("Location: media.php" . ($is_popup ? "?popup=1" : ""));
        exit;
    }

    if ($file['error'] === 0) {
        $new_name = 'media_' . time() . rand(100,999) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_name)) {
            set_flash('success', 'Image uploaded successfully');
        } else {
            set_flash('error', 'Upload failed. Check permissions.');
        }
    }

    header("Location: media.php" . ($is_popup ? "?popup=1" : ""));
    exit;
}

/* ===============================
   LOAD IMAGES
================================ */
$files = scandir($upload_dir);
$images = array_filter($files, function($file) use ($upload_dir) {
    return is_file($upload_dir . $file) && preg_match('/\.(jpg|jpeg|png|webp)$/i', $file);
});
rsort($images);
?>
