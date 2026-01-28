<?php
require_once '../db.php';
require_once '../auth.php';
require_once '../layout_header.php';

// ১. ইমেজ প্রসেসিং ফাংশন (আপলোড এবং অপ্টিমাইজেশন)
function processImage($file) {
    if (!isset($file) || $file['error'] !== 0) return null;
    $targetDir = "../uploads/";
    $fileName = time() . ".webp";
    $targetFile = $targetDir . $fileName;
    
    $info = getimagesize($file['tmp_name']);
    $sourceImg = null;
    if ($info['mime'] == 'image/jpeg') $sourceImg = imagecreatefromjpeg($file['tmp_name']);
    elseif ($info['mime'] == 'image/png') $sourceImg = imagecreatefrompng($file['tmp_name']);
    elseif ($info['mime'] == 'image/webp') $sourceImg = imagecreatefromwebp($file['tmp_name']);

    if ($sourceImg) {
        $width = imagesx($sourceImg);
        $height = imagesy($sourceImg);
        $newWidth = 1280;
        $newHeight = ($height / $width) * $newWidth;
        $optimizedImg = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($optimizedImg, $sourceImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagewebp($optimizedImg, $targetFile, 80);
        imagedestroy($sourceImg);
        imagedestroy($optimizedImg);
        return "uploads/" . $fileName;
    }
    return null;
}

// ২. এডিট করার জন্য ডাটা আনা
$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editing = $stmt->fetch();
}

// ৩. ডিলিট হ্যান্ডেল করা
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: manage_blogs.php?deleted=1');
    exit();
}

// ৪. নতুন ব্লগ পাবলিশ বা আপডেট করা
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    // ইমেজ লজিক: নতুন আপলোড হলে সেটা নেবে, নাহলে লিংকের ইনপুট নেবে, আর কিছু না দিলে আগেরটাই থাকবে
    $image = processImage($_FILES['blog_image']);
    if (!$image) {
        $image = !empty($_POST['image_link']) ? $_POST['image_link'] : $_POST['old_image'];
    }
    
    $slug = strtolower(str_replace(' ', '-', $_POST['title']));

    if (!empty($_POST['id'])) {
        // আপডেট লজিক
        $stmt = $pdo->prepare("UPDATE blogs SET title=?, slug=?, content=?, image_url=?, category=? WHERE id=?");
        $stmt->execute([$_POST['title'], $slug, $_POST['content'], $image, $_POST['category'], $_POST['id']]);
    } else {
        // নতুন ইনসার্ট লজিক
        $stmt = $pdo->prepare("INSERT INTO blogs (title, slug, content, image_url, category, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$_POST['title'], $slug, $_POST['content'], $image, $_POST['category']]);
    }
    header('Location: manage_blogs.php?success=1');
    exit();
}

// ৫. সব ব্লগ ডাটাবেজ থেকে আনা
$blogs = $pdo->query("SELECT * FROM blogs ORDER BY id DESC")->fetchAll();
?>

<div class="max-w-6xl mx-auto space-y-10 animate-in fade-in duration-500">
    <div class="bg-white p-10 rounded-[3rem] shadow-sm border border-gray-100">
        <h1 class="text-3xl font-black text-primary mb-8 uppercase"><?php echo $editing ? 'Edit' : 'Create New'; ?> Post</h1>
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="id" value="<?php echo $editing['id'] ?? ''; ?>">
            <input type="hidden" name="old_image" value="<?php echo $editing['image_url'] ?? ''; ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <input type="text" name="title" required value="<?php echo $editing['title'] ?? ''; ?>" class="w-full p-4 bg-gray-50 rounded-2xl outline-none border-none focus:ring-2 focus:ring-primary" placeholder="Blog Title">
                <input type="text" name="category" required value="<?php echo $editing['category'] ?? ''; ?>" class="w-full p-4 bg-gray-50 rounded-2xl outline-none border-none focus:ring-2 focus:ring-primary" placeholder="Category">
            </div>
            
            <textarea name="content" rows="6" required class="w-full p-4 bg-gray-50 rounded-2xl outline-none border-none focus:ring-2 focus:ring-primary" placeholder="Write your content here..."><?php echo $editing['content'] ?? ''; ?></textarea>
            
            <script src="https://cdn.tiny.cloud/1/y0jbtdw12tfykhlayy3ltzsjb0td7c3cuj3d3s9y48t49ec7/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
  tinymce.init({
    selector: 'textarea[name="content"]',
    height: 500,
    plugins: 'image link lists table code help wordcount',
    toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | image link | bullist numlist | code',

    file_picker_callback: function (callback, value, meta) {
        if (meta.filetype === 'image') {
            var input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');

            input.onchange = function () {
                var file = this.files[0];
                var reader = new FileReader();
                reader.onload = function () {
                    var id = 'blobid' + (new Date()).getTime();
                    var blobCache = tinymce.activeEditor.editorUpload.blobCache;
                    var base64 = reader.result.split(',')[1];
                    var blobInfo = blobCache.create(id, file, base64);
                    blobCache.add(blobInfo);
                    callback(blobInfo.blobUri(), { title: file.name });
                };
                reader.readAsDataURL(file);
            };
            input.click();
        }
    }
  });
</script>

            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-6 rounded-3xl">
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 ml-2">Option A: Upload Image (WebP Optimization)</label>
                    <input type="file" name="blog_image" class="w-full p-3 text-xs">
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 ml-2">Option B: Image URL (Link)</label>
                    <input id="blog_image_input" type="text" name="image_link" value="<?php echo (isset($editing['image_url']) && strpos($editing['image_url'], 'http') === 0) ? $editing['image_url'] : ''; ?>" class="w-full p-3 bg-white rounded-xl outline-none border-none text-xs" placeholder="https://example.com/image.jpg">
                    <button type="button" onclick="openMediaManager('blog_image_input')" class="bg-gray-200 px-4 py-2 rounded font-bold text-xs">Media</button>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <button type="submit" class="bg-primary text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest shadow-xl">
                    <?php echo $editing ? 'Update Post' : 'Publish Post'; ?>
                </button>
                <?php if($editing): ?>
                    <a href="manage_blogs.php" class="text-gray-400 font-bold uppercase text-xs hover:text-primary transition-colors">Cancel Edit</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="space-y-4 pb-20">
        <h3 class="text-xl font-black uppercase text-gray-400 ml-4">Existing Posts (<?php echo count($blogs); ?>)</h3>
        <div class="grid grid-cols-1 gap-4">
            <?php foreach($blogs as $b): ?>
            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm flex items-center justify-between group">
                <div class="flex items-center space-x-6">
                    <img src="<?php echo (strpos($b['image_url'], 'http') === 0) ? $b['image_url'] : '../' . $b['image_url']; ?>" class="w-16 h-16 rounded-2xl object-cover bg-gray-100">
                    <div>
                        <h4 class="font-black text-primary uppercase text-sm"><?php echo htmlspecialchars($b['title']); ?></h4>
                        <p class="text-[10px] text-gray-400 font-bold"><?php echo htmlspecialchars($b['category']); ?> • <?php echo $b['created_at']; ?></p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <a href="manage_blogs.php?edit=<?php echo $b['id']; ?>" class="p-3 bg-gray-50 text-gray-400 hover:text-primary rounded-xl transition-colors">
                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                    </a>
                    <a href="manage_blogs.php?delete=<?php echo $b['id']; ?>" onclick="return confirm('Delete this post?')" class="p-3 bg-gray-50 text-gray-400 hover:text-red-500 rounded-xl transition-colors">
                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once '../layout_footer.php'; ?>