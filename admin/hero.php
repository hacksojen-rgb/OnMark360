<?php
// 1. Error Reporting ON
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. File Inclusion
$header_path = file_exists(__DIR__ . '/layout_header.php') ? __DIR__ . '/layout_header.php' : __DIR__ . '/../layout_header.php';
$db_path = file_exists(__DIR__ . '/db.php') ? __DIR__ . '/db.php' : __DIR__ . '/../db.php';
$auth_path = file_exists(__DIR__ . '/auth.php') ? __DIR__ . '/auth.php' : __DIR__ . '/../auth.php';

if (file_exists($db_path)) require_once $db_path;
if (file_exists($auth_path)) { require_once $auth_path; if (function_exists('check_auth')) check_auth(); }
if (file_exists($header_path)) require_once $header_path;

$message = '';
$error = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add' || $action === 'edit') {
            $title = trim($_POST['title'] ?? '');
            $subtitle = trim($_POST['subtitle'] ?? '');
            
            // Colors: যদি কালার ফিল্ড খালি থাকে বা ডিজেবল থাকে, তবে NULL বা ডিফল্ট হবে
            $title_color = !empty($_POST['title_color']) ? $_POST['title_color'] : '#ffffff';
            // চেকবাক্স না থাকলে bg_color NULL হবে (ট্রান্সপারেন্ট)
            $title_bg_color = (isset($_POST['enable_title_bg']) && !empty($_POST['title_bg_color'])) ? $_POST['title_bg_color'] : null;
            
            $subtitle_color = !empty($_POST['subtitle_color']) ? $_POST['subtitle_color'] : '#e5e7eb';
            $subtitle_bg_color = (isset($_POST['enable_sub_bg']) && !empty($_POST['subtitle_bg_color'])) ? $_POST['subtitle_bg_color'] : null;

            $cta_primary = trim($_POST['cta_primary'] ?? '');
            $cta_primary_url = trim($_POST['cta_primary_url'] ?? '');
            $cta_secondary = trim($_POST['cta_secondary'] ?? '');
            $cta_secondary_url = trim($_POST['cta_secondary_url'] ?? '');
            $image_url = trim($_POST['image_url'] ?? '');
            // Status Check Fix
            $status = isset($_POST['status']) ? $_POST['status'] : 'Active';

            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO hero_slides (title, subtitle, title_color, title_bg_color, subtitle_color, subtitle_bg_color, cta_primary, cta_primary_url, cta_secondary, cta_secondary_url, image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $subtitle, $title_color, $title_bg_color, $subtitle_color, $subtitle_bg_color, $cta_primary, $cta_primary_url, $cta_secondary, $cta_secondary_url, $image_url, $status]);
                echo "<script>window.location.href='hero.php?msg=added';</script>";
            } else {
                $id = intval($_POST['id']);
                $stmt = $pdo->prepare("UPDATE hero_slides SET title=?, subtitle=?, title_color=?, title_bg_color=?, subtitle_color=?, subtitle_bg_color=?, cta_primary=?, cta_primary_url=?, cta_secondary=?, cta_secondary_url=?, image_url=?, status=? WHERE id=?");
                $stmt->execute([$title, $subtitle, $title_color, $title_bg_color, $subtitle_color, $subtitle_bg_color, $cta_primary, $cta_primary_url, $cta_secondary, $cta_secondary_url, $image_url, $status, $id]);
                echo "<script>window.location.href='hero.php?msg=updated';</script>";
            }
            exit();
        }
        
        if ($action === 'delete') {
            $id = intval($_POST['id']);
            $pdo->prepare("DELETE FROM hero_slides WHERE id = ?")->execute([$id]);
            echo "<script>window.location.href='hero.php?msg=deleted';</script>";
            exit();
        }

    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'added') $message = "Slide added!";
    if ($_GET['msg'] === 'updated') $message = "Slide updated!";
    if ($_GET['msg'] === 'deleted') $message = "Slide deleted!";
}

$slides = $pdo->query("SELECT * FROM hero_slides ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Edit Mode Logic
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM hero_slides WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="max-w-7xl mx-auto space-y-8 pb-20">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-black uppercase text-[#014034]">Hero Engine</h1>
        <?php if(!$edit): ?>
        <a href="hero.php?action=new" class="bg-[#014034] text-white px-6 py-3 rounded-xl font-bold uppercase text-xs shadow-lg">+ New Slide</a>
        <?php endif; ?>
    </div>

    <?php if($message) echo "<div class='bg-green-100 text-green-700 p-4 rounded-xl font-bold'>$message</div>"; ?>
    <?php if($error) echo "<div class='bg-red-100 text-red-700 p-4 rounded-xl font-bold'>$error</div>"; ?>

    <?php if((isset($_GET['action']) && $_GET['action'] == 'new') || $edit): ?>
    <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm">
        <h3 class="text-xl font-black uppercase mb-6"><?php echo $edit ? 'Edit Slide' : 'Create Slide'; ?></h3>
        <form method="POST" class="space-y-6">
            <input type="hidden" name="action" value="<?php echo $edit ? 'edit' : 'add'; ?>">
            <input type="hidden" name="id" value="<?php echo $edit['id'] ?? ''; ?>">
            
            <div class="bg-gray-50 p-6 rounded-xl space-y-4">
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase">Main Headline</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($edit['title'] ?? ''); ?>" required class="w-full p-3 rounded-lg border-none focus:ring-2 bg-white font-bold text-lg">
                </div>
                <div class="flex gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Text Color</label>
                        <input type="color" name="title_color" value="<?php echo htmlspecialchars($edit['title_color'] ?? '#ffffff'); ?>" class="h-10 w-16 p-0 border rounded cursor-pointer">
                    </div>
                    <div>
                        <label class="flex items-center gap-2 text-xs font-bold text-gray-400 uppercase mb-1">
                            <input type="checkbox" name="enable_title_bg" value="1" <?php echo !empty($edit['title_bg_color']) ? 'checked' : ''; ?> onchange="document.getElementById('tBgColor').disabled = !this.checked">
                            Background Color
                        </label>
                        <input type="color" id="tBgColor" name="title_bg_color" value="<?php echo htmlspecialchars($edit['title_bg_color'] ?? '#000000'); ?>" class="h-10 w-16 p-0 border rounded cursor-pointer" <?php echo empty($edit['title_bg_color']) ? 'disabled' : ''; ?>>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 p-6 rounded-xl space-y-4">
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase">Supporting Text</label>
                    <textarea name="subtitle" rows="2" class="w-full p-3 rounded-lg border-none focus:ring-2 bg-white font-medium"><?php echo htmlspecialchars($edit['subtitle'] ?? ''); ?></textarea>
                </div>
                <div class="flex gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Text Color</label>
                        <input type="color" name="subtitle_color" value="<?php echo htmlspecialchars($edit['subtitle_color'] ?? '#e5e7eb'); ?>" class="h-10 w-16 p-0 border rounded cursor-pointer">
                    </div>
                    <div>
                        <label class="flex items-center gap-2 text-xs font-bold text-gray-400 uppercase mb-1">
                            <input type="checkbox" name="enable_sub_bg" value="1" <?php echo !empty($edit['subtitle_bg_color']) ? 'checked' : ''; ?> onchange="document.getElementById('sBgColor').disabled = !this.checked">
                            Background Color
                        </label>
                        <input type="color" id="sBgColor" name="subtitle_bg_color" value="<?php echo htmlspecialchars($edit['subtitle_bg_color'] ?? '#000000'); ?>" class="h-10 w-16 p-0 border rounded cursor-pointer" <?php echo empty($edit['subtitle_bg_color']) ? 'disabled' : ''; ?>>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                     <label class="text-xs font-bold text-gray-400 uppercase">Primary Button</label>
                     <input type="text" name="cta_primary" value="<?php echo htmlspecialchars($edit['cta_primary'] ?? 'Get Started'); ?>" class="w-full p-3 rounded-lg border bg-white text-sm">
                     <input type="text" name="cta_primary_url" value="<?php echo htmlspecialchars($edit['cta_primary_url'] ?? '#'); ?>" placeholder="URL" class="w-full p-3 mt-2 rounded-lg border bg-white text-xs font-mono text-blue-600">
                </div>
                <div>
                     <label class="text-xs font-bold text-gray-400 uppercase">Secondary Button</label>
                     <input type="text" name="cta_secondary" value="<?php echo htmlspecialchars($edit['cta_secondary'] ?? 'Learn More'); ?>" class="w-full p-3 rounded-lg border bg-white text-sm">
                     <input type="text" name="cta_secondary_url" value="<?php echo htmlspecialchars($edit['cta_secondary_url'] ?? '#'); ?>" placeholder="URL" class="w-full p-3 mt-2 rounded-lg border bg-white text-xs font-mono text-blue-600">
                </div>
            </div>

            <div>
                <label class="text-xs font-bold text-gray-400 uppercase">Image URL</label>
                <div class="flex gap-2">
                    <input type="text" name="image_url" value="<?php echo htmlspecialchars($edit['image_url'] ?? ''); ?>" required class="w-full p-3 rounded-lg border bg-white text-xs font-mono">
                    <a href="media.php" target="_blank" class="bg-gray-200 px-4 py-3 rounded-lg text-xs font-bold">Media</a>
                </div>
            </div>
            
            <div>
                <label class="text-xs font-bold text-gray-400 uppercase">Status</label>
                <select name="status" class="p-2 border rounded">
                    <option value="Active" <?php echo (isset($edit['status']) && $edit['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                    <option value="Inactive" <?php echo (isset($edit['status']) && $edit['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div class="flex justify-end gap-4 border-t pt-4">
                <a href="hero.php" class="px-6 py-3 font-bold text-gray-400 text-xs uppercase">Cancel</a>
                <button type="submit" class="bg-[#014034] text-white px-8 py-3 rounded-xl font-bold uppercase text-xs shadow-lg">Save Slide</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php foreach($slides as $s): ?>
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm group">
            <div class="relative h-40 rounded-xl overflow-hidden mb-4">
                <img src="<?php echo htmlspecialchars($s['image_url']); ?>" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                    <h3 class="text-white font-bold text-center px-4" 
                        style="color: <?php echo htmlspecialchars($s['title_color']); ?>; background: <?php echo htmlspecialchars($s['title_bg_color'] ?? 'transparent'); ?>;">
                        <?php echo htmlspecialchars($s['title']); ?>
                    </h3>
                </div>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-[10px] font-bold bg-gray-100 px-2 py-1 rounded uppercase"><?php echo htmlspecialchars($s['status'] ?? 'Active'); ?></span>
                <div class="flex gap-2">
                    <a href="hero.php?edit=<?php echo $s['id']; ?>" class="text-blue-600 font-bold text-xs uppercase">Edit</a>
                    <form method="POST" onsubmit="return confirm('Delete?')" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                        <button class="text-red-600 font-bold text-xs uppercase">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php 
// Footer Include
$footer_path = file_exists(__DIR__ . '/layout_footer.php') ? __DIR__ . '/layout_footer.php' : __DIR__ . '/../layout_footer.php';
if (file_exists($footer_path)) require_once $footer_path;
?>