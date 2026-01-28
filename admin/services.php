<?php
ob_start(); 
require '../layout_header.php';

// ১. ডিলিট হ্যান্ডেল করা
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: services.php');
    exit(); 
}

// ২. অ্যাড বা এডিট হ্যান্ডেল করা
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $features = json_encode(array_filter(array_map('trim', explode("\n", $_POST['features']))));
    $icon = $_POST['icon'] ?? 'zap';

    if (!empty($_POST['id'])) {
        $stmt = $pdo->prepare("UPDATE services SET title=?, description=?, features=?, icon=? WHERE id=?");
        $stmt->execute([$_POST['title'], $_POST['description'], $features, $icon, $_POST['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO services (title, description, features, icon) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['title'], $_POST['description'], $features, $icon]);
    }
    header('Location: services.php');
    exit(); 
}

// ৩. ডাটাবেজ থেকে ডাটা আনা
$services = $pdo->query("SELECT title FROM services ORDER BY title ASC")->fetchAll();
$services = $pdo->query("SELECT * FROM services ORDER BY id DESC")->fetchAll();
$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editing = $stmt->fetch();
}
?>

<div class="animate-in fade-in duration-500">
    <div class="flex justify-between items-center mb-10">
        <div>
            <h1 class="text-4xl font-black text-gray-900 uppercase tracking-tighter">Service Catalog</h1>
            <p class="text-gray-500">Manage digital products and solutions offered.</p>
        </div>
        <a href="services.php?action=new" class="bg-primary text-white px-8 py-3 rounded-2xl font-bold shadow-xl flex items-center space-x-2">
            <i data-lucide="plus"></i><span>Add Service</span>
        </a>
    </div>

    <?php if (isset($_GET['action']) || $editing): ?>
    <div class="bg-white p-10 rounded-[3rem] border border-gray-100 shadow-sm mb-10">
        <h3 class="text-2xl font-black mb-6 uppercase text-primary"><?php echo $editing ? 'Edit' : 'New'; ?> Service</h3>
        <form method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="id" value="<?php echo $editing['id'] ?? ''; ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-2">Service Title</label>
                    <input type="text" name="title" required value="<?php echo $editing['title'] ?? ''; ?>" class="w-full p-4 bg-gray-50 rounded-2xl border-none outline-none focus:ring-2 focus:ring-primary font-bold" placeholder="e.g. Web Development">
                </div>
                
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-2">Visual Icon</label>
                    <select name="icon" class="w-full p-4 bg-gray-50 rounded-2xl border-none outline-none focus:ring-2 focus:ring-primary font-bold appearance-none">
                        <option value="code-2" <?php echo ($editing['icon'] ?? '') == 'code-2' ? 'selected' : ''; ?>>Code (Web Dev)</option>
                        <option value="target" <?php echo ($editing['icon'] ?? '') == 'target' ? 'selected' : ''; ?>>Target (Marketing)</option>
                        <option value="search" <?php echo ($editing['icon'] ?? '') == 'search' ? 'selected' : ''; ?>>Search (SEO)</option>
                        <option value="pen-tool" <?php echo ($editing['icon'] ?? '') == 'pen-tool' ? 'selected' : ''; ?>>Pen Tool (Design)</option>
                        <option value="megaphone" <?php echo ($editing['icon'] ?? '') == 'megaphone' ? 'selected' : ''; ?>>Megaphone (Content)</option>
                        <option value="bar-chart-3" <?php echo ($editing['icon'] ?? '') == 'bar-chart-3' ? 'selected' : ''; ?>>Chart (Analytics)</option>
                        <option value="zap" <?php echo ($editing['icon'] ?? '') == 'zap' || !isset($editing['icon']) ? 'selected' : ''; ?>>Zap (Performance)</option>
                    </select>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-2">Detailed Description</label>
                <textarea name="description" rows="3" class="w-full p-4 bg-gray-50 rounded-2xl border-none outline-none focus:ring-2 focus:ring-primary" placeholder="What is this service about?"><?php echo $editing['description'] ?? ''; ?></textarea>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-2">Key Features (One per line)</label>
                <textarea name="features" rows="5" class="w-full p-4 bg-gray-50 rounded-2xl border-none outline-none focus:ring-2 focus:ring-primary" placeholder="Enter features..."><?php
                    if ($editing) {
                        $f = json_decode($editing['features'], true);
                        echo is_array($f) ? implode("\n", $f) : $editing['features'];
                    }
                ?></textarea>
            </div>

            <div class="flex justify-end gap-4 pt-4">
                <a href="services.php" class="px-8 py-3 font-bold text-gray-400">Cancel</a>
                <button type="submit" class="bg-primary text-white px-10 py-3 rounded-2xl font-black shadow-xl uppercase tracking-widest text-xs">Finalize Service</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 gap-6">
    <?php foreach($services as $s): ?>
    <div class="bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm flex items-center group">
        
        <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center text-primary shrink-0 mr-6 transition-all group-hover:bg-primary group-hover:text-white">
            <i data-lucide="<?php echo htmlspecialchars(strtolower($s['icon'] ?: 'zap')); ?>" class="w-8 h-8"></i>
        </div>

        <div class="flex-grow flex items-center">
            <h3 class="text-2xl font-black text-primary uppercase tracking-tight shrink-0 mr-4">
                <?php echo htmlspecialchars($s['title']); ?>
            </h3>
            
            <p class="text-gray-500 text-sm hidden md:block border-l border-gray-200 pl-4 line-clamp-1">
                <?php echo htmlspecialchars($s['description']); ?>
            </p>
        </div>

        <div class="flex space-x-2 shrink-0 ml-4">
            <a href="services.php?edit=<?php echo $s['id']; ?>" class="p-3 bg-gray-50 text-gray-400 hover:text-primary rounded-xl">
                <i data-lucide="edit-3" class="w-5 h-5"></i>
            </a>
            <a href="services.php?delete=<?php echo $s['id']; ?>" onclick="return confirm('Delete?')" class="p-3 bg-gray-50 text-gray-400 hover:text-red-500 rounded-xl">
                <i data-lucide="trash-2" class="w-5 h-5"></i>
            </a>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>

<?php require '../layout_footer.php'; ?>