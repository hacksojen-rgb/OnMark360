<?php
require_once '../db.php';
require_once '../auth.php';
require_once '../layout_header.php';

// ১. এডিট ডাটা আনা
$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editing = $stmt->fetch();
}

// ২. ডিলিট লজিক
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: pages.php?deleted=1'); exit();
}

// ৩. সেভ বা আপডেট লজিক
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $title = $_POST['title'];
    // স্লাগ তৈরি: ইউজার দিলে সেটা, না দিলে টাইটেল থেকে অটো
    $slug = !empty($_POST['slug']) ? $_POST['slug'] : $title;
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $slug)));
    $content = $_POST['content'];
    $status = $_POST['status'];

    if (!empty($_POST['id'])) {
        // আপডেট
        $stmt = $pdo->prepare("UPDATE pages SET title=?, slug=?, content=?, status=? WHERE id=?");
        $stmt->execute([$title, $slug, $content, $status, $_POST['id']]);
    } else {
        // নতুন তৈরি
        $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $content, $status]);
    }
    header('Location: pages.php?success=1'); exit();
}

// ৪. পেজ লিস্ট
$pages = $pdo->query("SELECT * FROM pages ORDER BY id DESC")->fetchAll();
?>

<div class="max-w-6xl mx-auto space-y-10 animate-in fade-in duration-500">
    <div class="bg-white p-10 rounded-[3rem] shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-black text-[#014034] uppercase"><?php echo $editing ? 'Edit Page' : 'Create New Page'; ?></h1>
            <?php if(isset($_GET['success'])): ?>
                <span class="text-green-600 font-bold bg-green-50 px-4 py-2 rounded-lg">Saved Successfully!</span>
            <?php endif; ?>
        </div>

        <form method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="id" value="<?php echo $editing['id'] ?? ''; ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase ml-2 mb-1 block">Page Title</label>
                    <input type="text" name="title" required value="<?php echo $editing['title'] ?? ''; ?>" class="w-full p-4 bg-gray-50 rounded-2xl outline-none border-none focus:ring-2 focus:ring-[#014034]" placeholder="e.g. Privacy Policy">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase ml-2 mb-1 block">Slug (URL)</label>
                    <input type="text" name="slug" value="<?php echo $editing['slug'] ?? ''; ?>" class="w-full p-4 bg-gray-50 rounded-2xl outline-none border-none focus:ring-2 focus:ring-[#014034]" placeholder="auto-generated-if-empty">
                </div>
            </div>

            <div>
                <label class="text-xs font-bold text-gray-400 uppercase ml-2 mb-1 block">Page Content (HTML Allowed)</label>
                <textarea name="content" rows="12" required class="w-full p-6 bg-gray-50 rounded-2xl outline-none border-none focus:ring-2 focus:ring-[#014034] font-mono text-sm" placeholder="<p>Write your page content here...</p>"><?php echo $editing['content'] ?? ''; ?></textarea>
                
                <!-- TinyMCE Editor -->
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

<p class="text-[10px] text-gray-400 mt-2 ml-2">
  * Pro Tip: You can use standard HTML tags like &lt;h1&gt;, &lt;p&gt;, &lt;ul&gt; for formatting.
</p>
                
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <select name="status" class="p-4 bg-gray-100 rounded-2xl font-bold text-sm outline-none">
                        <option value="published" <?php echo ($editing['status'] ?? '') == 'published' ? 'selected' : ''; ?>>Published</option>
                        <option value="draft" <?php echo ($editing['status'] ?? '') == 'draft' ? 'selected' : ''; ?>>Draft</option>
                    </select>
                </div>
                <div class="flex items-center gap-4">
                    <?php if($editing): ?>
                        <a href="pages.php" class="text-gray-400 font-bold uppercase text-xs hover:text-[#014034]">Cancel</a>
                    <?php endif; ?>
                    <button type="submit" class="bg-[#014034] text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest shadow-xl hover:scale-105 transition-transform">
                        <?php echo $editing ? 'Update Page' : 'Publish Page'; ?>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="space-y-4 pb-20">
        <h3 class="text-xl font-black uppercase text-gray-400 ml-4">All Pages</h3>
        <div class="grid grid-cols-1 gap-4">
            <?php foreach($pages as $p): ?>
            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm flex items-center justify-between group hover:shadow-md transition-all">
                <div class="flex items-center gap-6">
                    <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center text-[#014034]">
                        <i data-lucide="file-text" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-black text-[#014034] text-lg"><?php echo htmlspecialchars($p['title']); ?></h4>
                        <div class="flex items-center gap-2 text-xs text-gray-400 font-bold mt-1">
                            <span class="bg-gray-100 px-2 py-0.5 rounded text-[10px] uppercase">/<?php echo $p['slug']; ?></span>
                            <span>•</span>
                            <span class="<?php echo $p['status'] == 'published' ? 'text-green-500' : 'text-orange-500'; ?> uppercase"><?php echo $p['status']; ?></span>
                        </div>
                    </div>
                </div>
                <div class="flex space-x-2 opacity-50 group-hover:opacity-100 transition-opacity">
                    <a href="/#/<?php echo $p['slug']; ?>" target="_blank" class="p-3 bg-gray-50 text-gray-400 hover:text-blue-500 rounded-xl" title="View">
                        <i data-lucide="eye" class="w-5 h-5"></i>
                    </a>
                    <a href="pages.php?edit=<?php echo $p['id']; ?>" class="p-3 bg-gray-50 text-gray-400 hover:text-[#014034] rounded-xl" title="Edit">
                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                    </a>
                    <a href="pages.php?delete=<?php echo $p['id']; ?>" onclick="return confirm('Delete this page?')" class="p-3 bg-gray-50 text-gray-400 hover:text-red-500 rounded-xl" title="Delete">
                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once '../layout_footer.php'; ?>