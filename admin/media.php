<?php
// ১. এরর রিপোর্টিং
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ২. স্মার্ট ফাইল ইনক্লুশন
$auth_path = file_exists(__DIR__ . '/auth.php') ? __DIR__ . '/auth.php' : __DIR__ . '/../auth.php';
$header_path = file_exists(__DIR__ . '/layout_header.php') ? __DIR__ . '/layout_header.php' : __DIR__ . '/../layout_header.php';

// ৩. Auth লোড (সেশন এখান থেকেই স্টার্ট হবে, তাই আলাদা session_start() দরকার নেই)
if (file_exists($auth_path)) {
    require_once $auth_path;
} else {
    die("<div style='color:red;padding:20px;'>Error: auth.php not found.</div>");
}

// CSRF চেক
if (!function_exists('generate_csrf_token')) {
    die("<div style='color:red;padding:20px;'>Error: CSRF function missing in auth.php</div>");
}

$is_popup = isset($_GET['popup']);

// ৪. হেডার লোড (যদি পপআপ না হয়)
if (!$is_popup) {
    if (file_exists($header_path)) {
        require_once $header_path;
    }
}

$upload_dir = '../uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
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
    }
    header("Location: media.php" . ($is_popup ? "?popup=1" : ""));
    exit;
}

/* ===============================
   UPLOAD LOGIC
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['new_media'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $file = $_FILES['new_media'];
    // ===== Security validation START =====

        // File size limit (2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            header("Location: media.php" . ($is_popup ? "?popup=1" : ""));
            exit;
        }

        // Extension whitelist
        $allowed_ext = ['jpg','jpeg','png','webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_ext)) {
            header("Location: media.php" . ($is_popup ? "?popup=1" : ""));
            exit;
        }

        // MIME type validation
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowed_mime = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];
        if (!in_array($mime, $allowed_mime)) {
            header("Location: media.php" . ($is_popup ? "?popup=1" : ""));
            exit;
        }

        // ===== Security validation END =====


    if ($file['error'] === 0) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_name = 'media_' . time() . rand(100,999) . '.' . $ext;
        
        // ফাইল আপলোড
        move_uploaded_file($file['tmp_name'], $upload_dir . $new_name);
        
        header("Location: media.php" . ($is_popup ? "?popup=1" : ""));
        exit;
    }
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

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Media Library</title>

<?php if($is_popup): ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style> body { font-family: 'Inter', sans-serif; } </style>
<?php endif; ?>
</head>

<body class="<?php echo $is_popup ? 'bg-gray-50 p-6' : ''; ?>">

<div class="max-w-6xl mx-auto pb-20 <?php echo $is_popup ? '' : 'animate-in fade-in duration-500'; ?>">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-[#014034]">Media Library</h2>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <label class="bg-[#014034] text-white px-4 py-2 rounded-lg cursor-pointer hover:bg-[#00332a] text-sm font-bold flex items-center gap-2 shadow-lg hover:scale-105 transition-all">
                <i data-lucide="upload" class="w-4 h-4"></i>
                Upload New
                <input type="file" name="new_media" class="hidden" onchange="this.form.submit()">
            </label>
        </form>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <?php foreach ($images as $img):
            $url = "../uploads/".$img;
        ?>
        <div class="group bg-white p-2 rounded-xl shadow-sm border hover:shadow-md transition-all hover:-translate-y-1">

            <div class="aspect-square rounded-lg overflow-hidden bg-gray-100 relative cursor-pointer border border-gray-100"
                 onclick="<?php echo $is_popup ? "selectAndClose('$img','$url')" : "openPreview('$url')"; ?>">
                
                <img src="<?= $url ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                    <?php if($is_popup): ?>
                        <span class="text-white text-xs font-bold bg-[#014034] px-3 py-1 rounded shadow-lg">Select</span>
                    <?php else: ?>
                        <i data-lucide="eye" class="text-white w-6 h-6"></i>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex justify-between items-center mt-2 px-1 gap-2">
                
                <p class="text-[10px] text-gray-500 truncate flex-1 cursor-pointer hover:text-[#014034]"
                   onclick="navigator.clipboard.writeText('<?= $url ?>'); alert('URL Copied!')"
                   title="<?= $img ?>">
                    <?= $img ?>
                </p>

                <form method="POST" onsubmit="return confirm('Are you sure?');">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="delete_image" value="<?= $img ?>">
                    
                    <button type="submit" class="bg-red-50 text-red-500 p-1.5 rounded-lg hover:bg-red-500 hover:text-white transition-all shadow-sm">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    </button>
                </form>
            </div>

        </div>
        <?php endforeach; ?>
    </div>
    
    <?php if(empty($images)): ?>
        <div class="text-center py-20 text-gray-400">
            <i data-lucide="image-off" class="w-12 h-12 mx-auto mb-2 opacity-50"></i>
            <p class="text-sm font-bold uppercase">No media files found</p>
        </div>
    <?php endif; ?>

</div>

<?php if(!$is_popup): ?>
<div id="imageModal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-[60] p-4 backdrop-blur-sm" onclick="closePreview()">
    <div class="relative max-w-5xl w-full" onclick="event.stopPropagation()">
        <button onclick="closePreview()" class="absolute -top-10 right-0 text-white hover:text-red-400 transition-colors">
            <i data-lucide="x" class="w-8 h-8"></i>
        </button>
        <img id="modalImage" src="" class="w-full max-h-[85vh] object-contain rounded-lg shadow-2xl bg-black">
    </div>
</div>
<?php endif; ?>

<script>
    lucide.createIcons();

    function openPreview(url){
        const modal = document.getElementById('imageModal');
        const img = document.getElementById('modalImage');
        if(modal && img) {
            img.src = url;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closePreview(){
        const modal = document.getElementById('imageModal');
        if(modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function selectAndClose(filename, url) {
        if (window.opener && !window.opener.closed) {
            if (window.opener.updateImageInput) {
                window.opener.updateImageInput(filename, url);
            } else if (window.opener.tinymce || window.opener.tinyMCE) {
                window.opener.postMessage({ mceAction:'insert', content:url }, '*');
            }
            window.close();
        } else {
            alert('Parent window disconnected.');
        }
    }
</script>

</body>
</html>

<?php 
$footer_path = file_exists(__DIR__ . '/layout_footer.php') ? __DIR__ . '/layout_footer.php' : __DIR__ . '/../layout_footer.php';
if(!$is_popup && file_exists($footer_path)) {
    require_once $footer_path; 
}
?>