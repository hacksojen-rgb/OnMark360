<?php
require_once '../db.php';
require_once '../auth.php';
require_once '../layout_header.php';

// আপডেট লজিক
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $stmt = $pdo->prepare("UPDATE site_buttons SET label=?, url=?, bg_color=?, text_color=?, border_color=? WHERE id=?");
    $stmt->execute([$_POST['label'], $_POST['url'], $_POST['bg_color'], $_POST['text_color'], $_POST['border_color'], $_POST['id']]);
    header('Location: buttons.php?success=1'); exit();
}

$buttons = $pdo->query("SELECT * FROM site_buttons ORDER BY id ASC")->fetchAll();
?>

<div class="max-w-6xl mx-auto pb-24 animate-in fade-in duration-500">
    <div class="flex justify-between items-center mb-10">
        <div>
            <h1 class="text-4xl font-black text-gray-900 uppercase tracking-tighter">Buttons & Links</h1>
            <p class="text-gray-500 text-sm font-bold uppercase tracking-widest">Global Control Center</p>
        </div>
    </div>

    <?php if(count($buttons) == 0): ?>
        <div class="bg-red-50 text-red-500 p-8 rounded-3xl text-center font-bold">
            No buttons found! Please run the <a href="setup_fix.php" class="underline">Setup Fix</a> script.
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-8">
            <?php foreach($buttons as $btn): ?>
            <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-md transition-all">
                <div class="flex justify-between items-center mb-6 border-b border-gray-50 pb-4">
                    <span class="bg-gray-100 text-gray-500 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest"><?php echo $btn['section_key']; ?></span>
                    <a href="#" class="px-4 py-2 rounded-lg font-bold text-xs uppercase pointer-events-none shadow-sm"
                       style="background-color: <?php echo $btn['bg_color']; ?>; color: <?php echo $btn['text_color']; ?>; border: 2px solid <?php echo $btn['border_color']; ?>;">
                       Preview
                    </a>
                </div>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="id" value="<?php echo $btn['id']; ?>">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[9px] font-bold text-gray-400 uppercase ml-1">Label</label>
                            <input type="text" name="label" value="<?php echo $btn['label']; ?>" class="w-full p-3 bg-gray-50 rounded-xl text-sm font-bold">
                        </div>
                        <div>
                            <label class="text-[9px] font-bold text-gray-400 uppercase ml-1">URL</label>
                            <input type="text" name="url" value="<?php echo $btn['url']; ?>" class="w-full p-3 bg-gray-50 rounded-xl text-sm font-mono text-gray-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div><label class="text-[9px] font-bold text-gray-400 uppercase ml-1">Background</label><div class="flex items-center bg-gray-50 rounded-xl p-1"><input type="color" name="bg_color" value="<?php echo $btn['bg_color']; ?>" class="h-8 w-8 rounded cursor-pointer border-none"></div></div>
                        <div><label class="text-[9px] font-bold text-gray-400 uppercase ml-1">Text</label><div class="flex items-center bg-gray-50 rounded-xl p-1"><input type="color" name="text_color" value="<?php echo $btn['text_color']; ?>" class="h-8 w-8 rounded cursor-pointer border-none"></div></div>
                        <div><label class="text-[9px] font-bold text-gray-400 uppercase ml-1">Border</label><div class="flex items-center bg-gray-50 rounded-xl p-1"><input type="color" name="border_color" value="<?php echo $btn['border_color']; ?>" class="h-8 w-8 rounded cursor-pointer border-none"></div></div>
                    </div>
                    <button type="submit" class="w-full bg-primary text-white py-3 rounded-xl font-bold uppercase text-[10px] tracking-widest hover:bg-black transition-all">Update</button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php require_once '../layout_footer.php'; ?>