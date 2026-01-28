<?php
require_once '../db.php';
require_once '../auth.php';
require_once '../layout_header.php';

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM pricing_plans WHERE id = ?")->execute([$_GET['delete']]);
    header('Location: pricing.php'); exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $features = json_encode(array_filter(array_map('trim', explode("\n", $_POST['features']))));
    $is_popular = isset($_POST['is_popular']) ? 1 : 0;
    
    if ($_POST['id']) {
        $stmt = $pdo->prepare("UPDATE pricing_plans SET name=?, price=?, period=?, description=?, features=?, is_popular=? WHERE id=?");
        $stmt->execute([$_POST['name'], $_POST['price'], $_POST['period'], $_POST['description'], $features, $is_popular, $_POST['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO pricing_plans (name, price, period, description, features, is_popular) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['name'], $_POST['price'], $_POST['period'], $_POST['description'], $features, $is_popular]);
    }
    header('Location: pricing.php'); exit();
}

$plans = $pdo->query("SELECT * FROM pricing_plans ORDER BY price ASC")->fetchAll();
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM pricing_plans WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch();
}
?>

<div class="space-y-8 animate-in fade-in duration-500">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-black text-gray-900 uppercase tracking-tighter">Pricing Models</h1>
            <p class="text-gray-500 text-sm font-bold uppercase tracking-widest">Subscription Tiers & Service Bundles</p>
        </div>
        <a href="pricing.php?action=new" class="bg-primary text-white px-8 py-3 rounded-2xl font-black uppercase tracking-widest text-xs shadow-xl flex items-center space-x-2">
            <i data-lucide="plus" class="w-4 h-4"></i><span>Create Plan</span>
        </a>
    </div>

    <?php if (isset($_GET['action']) || $edit): ?>
    <div class="bg-white p-10 rounded-[3rem] border border-gray-100 shadow-sm max-w-2xl mx-auto">
        <h3 class="text-2xl font-black mb-8 uppercase text-primary">Plan Configuration</h3>
        <form method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="id" value="<?php echo $edit['id'] ?? ''; ?>">
            <div class="grid grid-cols-2 gap-6">
                <input type="text" name="name" value="<?php echo $edit['name'] ?? ''; ?>" placeholder="Tier Name (e.g. Startup)" required class="px-6 py-4 rounded-2xl bg-gray-50 outline-none font-bold">
                <div class="flex items-center space-x-3 px-6 py-4 bg-gray-50 rounded-2xl">
                    <input type="checkbox" name="is_popular" <?php echo ($edit['is_popular'] ?? 0) ? 'checked' : ''; ?> class="w-5 h-5 accent-primary">
                    <label class="text-[10px] font-black uppercase tracking-widest">Mark Popular</label>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-6">
                <input type="text" name="price" value="<?php echo $edit['price'] ?? ''; ?>" placeholder="Price (e.g. $500)" required class="px-6 py-4 rounded-2xl bg-gray-50 outline-none font-black text-xl">
                <input type="text" name="period" value="<?php echo $edit['period'] ?? 'mo'; ?>" placeholder="Period (e.g. mo)" required class="px-6 py-4 rounded-2xl bg-gray-50 outline-none text-xs font-bold uppercase">
            </div>
            <input type="text" name="description" value="<?php echo $edit['description'] ?? ''; ?>" placeholder="One-line Pitch" class="w-full px-6 py-4 rounded-2xl bg-gray-50 outline-none text-sm">
            <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-2">Features (One per line)</label>
                <textarea name="features" rows="6" class="w-full px-6 py-4 rounded-2xl bg-gray-50 border-none outline-none focus:ring-2 focus:ring-primary font-medium"><?php
                    if ($edit) {
                        $f = json_decode($edit['features'], true);
                        echo implode("\n", $f);
                    }
                ?></textarea>
            </div>
            <div class="flex justify-end gap-4 pt-6">
                <a href="pricing.php" class="px-8 py-3 font-bold text-gray-400 uppercase text-xs tracking-widest">Cancel</a>
                <button type="submit" class="bg-primary text-white px-10 py-3 rounded-2xl font-black shadow-xl uppercase tracking-widest text-xs">Authorize</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 pb-20">
        <?php foreach($plans as $p): ?>
        <div class="bg-white rounded-[3rem] p-8 border shadow-sm relative transition-all group <?php echo $p['is_popular'] ? 'border-primary ring-2 ring-primary/20 scale-[1.02]' : 'border-gray-100'; ?>">
            <?php if($p['is_popular']): ?>
                <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-primary text-white text-[9px] font-black uppercase px-4 py-1.5 rounded-full shadow-lg">Recommended</div>
            <?php endif; ?>
            <h3 class="text-2xl font-black text-primary mb-2 uppercase tracking-tight"><?php echo $p['name']; ?></h3>
            <p class="text-4xl font-black text-gray-900 mb-6"><?php echo $p['price']; ?><span class="text-sm text-gray-400 font-bold ml-1">/<?php echo $p['period']; ?></span></p>
            <ul class="space-y-3 mb-10 min-h-[120px]">
                <?php foreach(json_decode($p['features'], true) as $f): ?>
                    <li class="text-[10px] font-bold text-gray-500 uppercase tracking-tighter flex items-center">
                        <span class="w-1.5 h-1.5 bg-[#4DB6AC] rounded-full mr-2"></span><?php echo $f; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="flex justify-end space-x-2 pt-6 border-t border-gray-50">
               <a href="pricing.php?edit=<?php echo $p['id']; ?>" class="text-gray-300 hover:text-primary transition-all"><i data-lucide="edit-3" class="w-5 h-5"></i></a>
               <a href="pricing.php?delete=<?php echo $p['id']; ?>" onclick="return confirm('Delete?')" class="text-gray-300 hover:text-red-500 transition-all"><i data-lucide="trash-2" class="w-5 h-5"></i></a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once '../layout_footer.php'; ?>