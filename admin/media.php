<?php
require_once '../auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_popup = isset($_GET['popup']);

if (!$is_popup) {
    require_once '../layout_header.php';
}

$upload_dir = '../uploads/';

/* -------------------------
   Ensure upload dir exists
-------------------------- */
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

/* -------------------------
   Flash helpers
-------------------------- */
$flash_error   = $_SESSION['flash_error']   ?? null;
$flash_success = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

/* -------------------------
   Delete Logic (UNCHANGED)
-------------------------- */
if (isset($_POST['delete_image'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    $file_path = $upload_dir . basename($_POST['delete_image']);
    if (file_exists($file_path)) {
        unlink($file_path);
        $_SESSION['flash_success'] = 'Image deleted successfully.';
    } else {
        $_SESSION['flash_error'] = 'File not found.';
    }

    header("Location: media.php" . ($is_popup ? "?popup=1" : ""));
    exit;
}

/* -------------------------
   Upload Logic (EXTENDED)
-------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['new_media'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $file = $_FILES['new_media'];

    if ($file['error'] !== 0) {
        $_SESSION['flash_error'] = 'Upload failed. Please try again.';
    } else {

        /* ---- Added from old behaviour ---- */
        $allowed_ext  = ['jpg','jpeg','png','webp','svg'];
        $allowed_mime = ['image/jpeg','image/png','image/webp','image/svg+xml'];
        $max_size     = 2 * 1024 * 1024; // 2MB

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext)) {
            $_SESSION['flash_error'] = 'Invalid file type.';
        }
        elseif ($file['size'] > $max_size) {
            $_SESSION['flash_error'] = 'File size exceeds limit (2MB).';
        }
        else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $allowed_mime)) {
                $_SESSION['flash_error'] = 'Invalid image format.';
            } else {
                $new_name = 'media_' . time() . rand(100,999) . '.' . $ext;

                if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_name)) {
                    $_SESSION['flash_success'] = 'Image uploaded successfully.';
                } else {
                    $_SESSION['flash_error'] = 'Could not save uploaded file.';
                }
            }
        }
    }

    header("Location: media.php" . ($is_popup ? "?popup=1" : ""));
    exit;
}

/* -------------------------
   Load images (UNCHANGED)
-------------------------- */
$files = scandir($upload_dir);
$images = array_filter($files, function($file) use ($upload_dir) {
    return is_file($upload_dir . $file) && preg_match('/\.(jpg|jpeg|png|webp|svg)$/i', $file);
});
rsort($images);
?>
