<?php
require_once '../auth.php';

$is_popup = isset($_GET['popup']);

if (!$is_popup) {
    require_once '../layout_header.php';
}

$upload_dir = '../uploads/';

// -------- Delete --------
if (isset($_POST['delete_image'])) {
    $file_path = $upload_dir . basename($_POST['delete_image']);
    if (file_exists($file_path)) unlink($file_path);
    header("Location: media.php" . ($is_popup ? "?popup=1" : ""));
    exit;
}

// -------- Upload --------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['new_media'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $file = $_FILES['new_media'];
    if ($file['error'] === 0) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_name = 'media_' . time() . rand(100,999) . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $upload_dir . $new_name);
        header("Location: media.php" . ($is_popup ? "?popup=1" : ""));
        exit;
    }
}

// -------- Load images --------
$files = scandir($upload_dir);
$images = array_filter($files, function($file) use ($upload_dir) {
    return is_file($upload_dir . $file) && preg_match('/\.(jpg|jpeg|png|webp|svg)$/i', $file);
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

<div class="aspect-square rounded-lg overflow-hidden bg-gray-100 relative cursor-pointer"
onclick="<?php echo $is_popup ? "selectAndClose('$img','$url')" : "openPreview('$url')"; ?>">

<img src="<?= $url ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">

<div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
<?php if($is_popup): ?>
<span class="text-white text-xs font-bold bg-[#014034] px-2 py-1 rounded">Select</span>
<?php else: ?>
<i data-lucide="eye" class="text-white w-6 h-6"></i>
<?php endif; ?>
</div>
</div>

<div class="flex justify-between items-center mt-2 px-1">
<p class="text-[10px] text-gray-500 truncate w-20 cursor-pointer select-all"
onclick="navigator.clipboard.writeText('<?= $url ?>'); alert('Copied!')"
title="<?= $img ?>">
<?= $img ?>
</p>

<form method="POST" onsubmit="return confirm('Delete permanently?');">
<input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
<input type="hidden" name="delete_image" value="<?= $img ?>">
<button class="text-gray-400 hover:text-red-500 p-1">
<i data-lucide="trash-2" class="w-3 h-3"></i>
</button>
</form>
</div>

</div>
<?php endforeach; ?>
</div>
</div>

<!-- IMAGE PREVIEW POPUP (non-popup mode only) -->
<?php if(!$is_popup): ?>
<div id="imageModal" class="fixed inset-0 bg-black/70 hidden items-center justify-center z-50">
<div class="relative max-w-4xl w-full p-4">
<button onclick="closePreview()" class="absolute -top-3 -right-3 bg-white rounded-full p-2 shadow">
✕
</button>
<img id="modalImage" src="" class="w-full max-h-[85vh] object-contain rounded-lg bg-white">
</div>
</div>
<?php endif; ?>

<script>
lucide.createIcons();

function openPreview(url){
    const modal = document.getElementById('imageModal');
    const img = document.getElementById('modalImage');
    img.src = url;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closePreview(){
    const modal = document.getElementById('imageModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
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
        alert('Parent window not found.');
    }
}
</script>

</body>
</html>

<?php if(!$is_popup) require_once '../layout_footer.php'; ?>
