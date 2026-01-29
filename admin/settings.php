<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../db.php';
require_once '../layout_header.php';

// === অটোমেটিক ডাটাবেজ ফিক্স (Sort Order কলাম না থাকলে যুক্ত করবে) ===
try {
    try {
    $pdo->query("SELECT sort_order FROM social_links LIMIT 1");
} catch(Exception $e) {
    $pdo->exec("ALTER TABLE social_links ADD sort_order INT DEFAULT 0");
}

} catch (Exception $e) { /* Ignore if exists */ }

// === ডাটা রিমুভ হ্যান্ডলার ===
if (isset($_GET['delete_social'])) {
    $pdo->prepare("DELETE FROM social_links WHERE id = ?")->execute([$_GET['delete_social']]);
    header('Location: settings.php#social');
    exit();
}
if (isset($_GET['delete_link'])) {
    $pdo->prepare("DELETE FROM footer_links WHERE id = ?")->execute([$_GET['delete_link']]);
    header('Location: settings.php#footer');
    exit();
}

// === সেটিংস সেভ হ্যান্ডলার ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    // ১. মেইন সেটিংস আপডেট
    if (isset($_POST['action']) && $_POST['action'] == 'main_settings') {
        $upload_dir = '../uploads/';
        
        function getImagePath($fileKey, $hiddenKey, $existingVal) {
            global $upload_dir;
            if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === 0) {
                $ext = pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION);
                $newName = 'brand_' . time() . rand(10,99) . '.' . $ext;
                move_uploaded_file($_FILES[$fileKey]['tmp_name'], $upload_dir . $newName);
                return $newName;
            } elseif (!empty($_POST[$hiddenKey])) {
                return basename($_POST[$hiddenKey]);
            }
            return $existingVal;
        }

        $logo = getImagePath('logo_upload', 'selected_logo', $_POST['existing_logo']);
        $footer_logo = getImagePath('footer_logo_upload', 'selected_footer_logo', $_POST['existing_footer_logo']);
        $favicon = getImagePath('favicon_upload', 'selected_favicon', $_POST['existing_favicon']);
        
        $theme_color = $_POST['theme_color'] ?? '#014034';
        $about_text = $_POST['about_text'] ?? '';
        $show_footer_links = isset($_POST['show_footer_links']) ? 1 : 0;

        // মেনু আইটেম প্রসেসিং
        $nav_items = [];
        if(isset($_POST['nav_label'])){
            for($i=0; $i<count($_POST['nav_label']); $i++){
                if(!empty($_POST['nav_label'][$i])){
                    $nav_items[] = [
                        'label' => $_POST['nav_label'][$i],
                        'path' => $_POST['nav_path'][$i]
                    ];
                }
            }
        }
        $header_nav_json = json_encode($nav_items);

        try {
            $stmt = $pdo->prepare("UPDATE site_settings SET company_name = ?, address = ?, phone = ?, email = ?, logo_url = ?, footer_logo_url = ?, site_favicon = ?, theme_color = ?, header_nav = ?, show_footer_links = ?, about_text = ? WHERE id = 1");
            $stmt->execute([
                $_POST['company_name'], $_POST['address'], $_POST['phone'], $_POST['email'], 
                $logo, $footer_logo, $favicon, $theme_color, $header_nav_json, $show_footer_links, $about_text
            ]);
            header('Location: settings.php?success=1');
            exit();
        } catch (PDOException $e) {
            die("Database Error: " . $e->getMessage());
        }
    }

    // ২. সোশ্যাল মিডিয়া অর্ডার আপডেট (NEW)
    if (isset($_POST['action']) && $_POST['action'] == 'update_social_order') {
        if(isset($_POST['social_id'])) {
            foreach ($_POST['social_id'] as $index => $id) {
                $stmt = $pdo->prepare("UPDATE social_links SET sort_order = ? WHERE id = ?");
                $stmt->execute([$index, $id]);
            }
        }
        header('Location: settings.php#social');
        exit();
    }

    // ৩. ফুটার স্ট্যাটাস আপডেট
    if (isset($_POST['action']) && $_POST['action'] == 'update_footer_status') {
        $status = isset($_POST['show_footer_links']) ? 1 : 0;
        $pdo->prepare("UPDATE site_settings SET show_footer_links = ? WHERE id = 1")->execute([$status]);
        header('Location: settings.php?success=1#footer');
        exit();
    }

    // ৪. সোশ্যাল মিডিয়া অ্যাড
    if (isset($_POST['action']) && $_POST['action'] == 'add_social') {
        $stmt = $pdo->prepare("INSERT INTO social_links (platform, icon_code, url, sort_order) VALUES (?, ?, ?, 99)");
        $stmt->execute([$_POST['platform'], $_POST['icon'], $_POST['url']]);
        header('Location: settings.php#social');
        exit();
    }

    // ৫. ফুটার লিংক অ্যাড
    if (isset($_POST['action']) && $_POST['action'] == 'add_footer_link') {
        $stmt = $pdo->prepare("INSERT INTO footer_links (section_type, label, url) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['section_type'], $_POST['label'], $_POST['url']]);
        header('Location: settings.php#footer');
        exit();
    }
}

// ডাটা লোড
$settings = $pdo->query("SELECT * FROM site_settings WHERE id = 1")->fetch();
$socials = $pdo->query("SELECT * FROM social_links ORDER BY sort_order ASC")->fetchAll(); // অর্ডার অনুযায়ী ফেচ
$footer_links = $pdo->query("SELECT * FROM footer_links ORDER BY section_type DESC")->fetchAll();
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>

<div class="max-w-6xl mx-auto pb-24">
    
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-black text-[#014034] uppercase">Site Settings</h1>
        <?php if(isset($_GET['success'])): ?>
            <span class="bg-green-500 text-white px-4 py-2 rounded-lg text-xs font-bold uppercase shadow-lg">Saved!</span>
        <?php endif; ?>
    </div>

    <div class="flex space-x-6 border-b border-gray-200 mb-8" id="settingsTabs">
        <a href="#general" class="pb-4 border-b-4 border-[#014034] font-bold text-[#014034]">General & Logos</a>
        <a href="#social" class="pb-4 border-b-4 border-transparent text-gray-400 font-bold">Social Media</a>
        <a href="#footer" class="pb-4 border-b-4 border-transparent text-gray-400 font-bold">Footer Links</a>
    </div>

    <div id="general" class="tab-content">
        <form method="POST" enctype="multipart/form-data" class="space-y-8">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="action" value="main_settings">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
                    <h3 class="text-lg font-black text-[#014034] mb-6 uppercase">Company Info</h3>
                    <div class="space-y-4">
                        <input type="text" name="company_name" value="<?php echo htmlspecialchars($settings['company_name'] ?? ''); ?>" class="w-full p-4 bg-gray-50 rounded-xl font-bold" placeholder="Company Name">
                        <input type="text" name="address" value="<?php echo htmlspecialchars($settings['address'] ?? ''); ?>" class="w-full p-4 bg-gray-50 rounded-xl font-bold" placeholder="Address">
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($settings['phone'] ?? ''); ?>" class="p-4 bg-gray-50 rounded-xl font-bold" placeholder="Phone">
                            <input type="text" name="email" value="<?php echo htmlspecialchars($settings['email'] ?? ''); ?>" class="p-4 bg-gray-50 rounded-xl font-bold" placeholder="Email">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase">Theme Color</label>
                            <input type="color" name="theme_color" value="<?php echo $settings['theme_color'] ?? '#014034'; ?>" class="h-10 w-20 block mt-1">
                        </div>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
                    <h3 class="text-lg font-black text-[#014034] mb-6 uppercase">Branding</h3>
                    
                    <div class="mb-6">
                        <label class="text-xs font-bold text-gray-400 uppercase block mb-2">Main Logo</label>
                        <div class="flex items-center gap-4">
                            <div class="w-20 h-20 bg-gray-100 rounded-xl flex items-center justify-center p-2 border">
                                <img id="preview_logo" src="<?php echo !empty($settings['logo_url']) ? '../uploads/'.$settings['logo_url'] : ''; ?>" class="max-w-full max-h-full">
                            </div>
                            <div class="flex-1 space-y-2">
                                <input type="hidden" name="existing_logo" value="<?php echo $settings['logo_url']; ?>">
                                <input type="hidden" name="selected_logo" id="selected_logo">
                                <div class="flex gap-2">
                                    <button type="button" onclick="openMediaManager('selected_logo', 'preview_logo')" class="bg-gray-100 px-3 py-1 rounded text-xs font-bold">Media</button>
                                    <label class="bg-gray-100 px-3 py-1 rounded text-xs font-bold cursor-pointer">
                                        Upload <input type="file" name="logo_upload" class="hidden" onchange="previewUpload(this, 'preview_logo')">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="text-xs font-bold text-gray-400 uppercase block mb-2">Footer Logo</label>
                        <div class="flex items-center gap-4">
                            <div class="w-20 h-20 bg-[#014034] rounded-xl flex items-center justify-center p-2">
                                <img id="preview_f_logo" src="<?php echo !empty($settings['footer_logo_url']) ? '../uploads/'.$settings['footer_logo_url'] : ''; ?>" class="max-w-full max-h-full">
                            </div>
                            <div class="flex-1 space-y-2">
                                <input type="hidden" name="existing_footer_logo" value="<?php echo $settings['footer_logo_url']; ?>">
                                <input type="hidden" name="selected_footer_logo" id="selected_footer_logo">
                                <div class="flex gap-2">
                                    <button type="button" onclick="openMediaManager('selected_footer_logo', 'preview_f_logo')" class="bg-gray-100 px-3 py-1 rounded text-xs font-bold">Media</button>
                                    <label class="bg-gray-100 px-3 py-1 rounded text-xs font-bold cursor-pointer">
                                        Upload <input type="file" name="footer_logo_upload" class="hidden" onchange="previewUpload(this, 'preview_f_logo')">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase block mb-2">Favicon</label>
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-gray-50 rounded flex items-center justify-center border border-dashed">
                                <img id="preview_favicon" src="<?php echo !empty($settings['site_favicon']) ? '../uploads/'.$settings['site_favicon'] : ''; ?>" class="max-w-full max-h-full">
                            </div>
                            <div class="flex-1 space-y-2">
                                <input type="hidden" name="existing_favicon" value="<?php echo $settings['site_favicon']; ?>">
                                <input type="hidden" name="selected_favicon" id="selected_favicon">
                                <div class="flex gap-2">
                                    <button type="button" onclick="openMediaManager('selected_favicon', 'preview_favicon')" class="bg-gray-100 px-3 py-1 rounded text-xs font-bold">Media</button>
                                    <label class="bg-gray-100 px-3 py-1 rounded text-xs font-bold cursor-pointer">
                                        Upload <input type="file" name="favicon_upload" class="hidden" onchange="previewUpload(this, 'preview_favicon')">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 rounded-2xl border border-gray-100">
                <h3 class="font-black uppercase mb-4 text-[#014034]">Header Menu Builder (Drag to Sort)</h3>
                
                <div id="nav-container" class="space-y-3">
                    <?php 
                    $navs = json_decode($settings['header_nav'] ?? '[]', true);
                    if (!is_array($navs)) $navs = [];
                    foreach($navs as $index => $nav): 
                    ?>
                    <div class="flex gap-2 items-center bg-gray-50 p-2 rounded-xl border border-gray-200 cursor-move">
                        <span class="text-gray-400 font-bold px-2 handle-icon">☰</span>
                        <input type="text" name="nav_label[]" value="<?php echo htmlspecialchars($nav['label'] ?? ''); ?>" class="w-1/3 p-2 bg-white rounded-lg font-bold border-none outline-none focus:ring-2 focus:ring-primary/20" placeholder="Label">
                        <input type="text" name="nav_path[]" value="<?php echo htmlspecialchars($nav['path'] ?? ''); ?>" class="w-1/2 p-2 bg-white rounded-lg border-none outline-none focus:ring-2 focus:ring-primary/20 text-blue-600 font-mono text-sm" placeholder="URL Path">
                        <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:bg-red-50 p-2 rounded-lg transition-colors">✕</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <button type="button" onclick="addNavItem()" class="mt-4 flex items-center gap-2 bg-[#014034]/10 text-[#014034] px-4 py-2 rounded-lg font-bold text-sm hover:bg-[#014034] hover:text-white transition-all">
                    + Add New Menu Item
                </button>
            </div>

            <div class="bg-white p-8 rounded-2xl border border-gray-100">
                <label class="text-xs font-bold text-gray-400 uppercase">Footer About Text</label>
                <textarea name="about_text" rows="4" class="w-full p-4 bg-gray-50 rounded-xl font-medium mt-2"><?php echo htmlspecialchars($settings['about_text'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="bg-[#014034] text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest shadow-xl">Save All General Settings</button>
        </form>
    </div>

    <div id="social" class="tab-content hidden">
        <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
            <h3 class="text-lg font-black text-[#014034] mb-6 uppercase">Social Accounts (Drag to Reorder)</h3>
            
            <form method="POST" id="socialSortForm">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="action" value="update_social_order">
                
                <div id="social-container" class="space-y-3 mb-8">
                    <?php foreach($socials as $social): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl cursor-move border border-transparent hover:border-gray-200">
                        <div class="flex items-center gap-4 flex-1">
                            <span class="text-gray-400 font-bold handle-social">☰</span>
                            <input type="hidden" name="social_id[]" value="<?php echo $social['id']; ?>">
                            
                            <i data-lucide="<?php echo htmlspecialchars($social['icon_code']); ?>" class="w-5 h-5 text-gray-500"></i>
                            <div class="flex flex-col">
                                <span class="font-bold"><?php echo htmlspecialchars($social['platform']); ?></span>
                                <span class="text-xs text-gray-400 truncate max-w-[200px]"><?php echo htmlspecialchars($social['url']); ?></span>
                            </div>
                        </div>
                        <a href="?delete_social=<?php echo $social['id']; ?>" class="text-red-500 font-bold px-3 py-1 bg-red-50 rounded-lg" onclick="return confirm('Delete?')">Delete</a>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <button type="submit" class="bg-[#014034] text-white px-6 py-2 rounded-lg font-bold text-sm mb-8">Save New Order</button>
            </form>

            <form method="POST" class="bg-gray-50 p-6 rounded-2xl border-t border-gray-200">
                <h4 class="font-bold text-sm text-gray-500 uppercase mb-4">Add New Social Link</h4>
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="action" value="add_social">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="text" name="platform" placeholder="Platform (e.g. Facebook)" required class="p-3 rounded-xl border font-bold">
                    <input type="text" name="icon" placeholder="Lucide Icon (e.g. Facebook)" required class="p-3 rounded-xl border font-bold">
                    <input type="url" name="url" placeholder="Full URL" required class="p-3 rounded-xl border font-bold">
                    <button type="submit" class="bg-[#014034] text-white p-3 rounded-xl font-bold uppercase">Add Social</button>
                </div>
            </form>
        </div>
    </div>

    <div id="footer" class="tab-content hidden">
        <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="action" value="update_footer_status">
                
                <h3 class="text-lg font-black text-[#014034] mb-6 uppercase">Footer Visibility</h3>
                <label class="flex items-center gap-3 cursor-pointer mb-6">
                    <input type="checkbox" name="show_footer_links" value="1" <?php if(($settings['show_footer_links'] ?? 1) == 1) echo 'checked'; ?>>
                    <span class="text-sm font-bold">Show Footer Explore & Support Links</span>
                </label>
                <button type="submit" class="bg-[#014034] text-white px-6 py-2 rounded-lg font-bold">Save Link Status</button>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
            <div class="space-y-6">
                <?php foreach(['explore' => 'Explore', 'support' => 'Support'] as $key => $label): ?>
                <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm">
                    <h3 class="text-sm font-black text-[#014034] mb-4 uppercase"><?php echo $label; ?> Links</h3>
                    <div class="space-y-2">
                        <?php foreach($footer_links as $link): ?>
                            <?php if($link['section_type'] === $key): ?>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl">
                                <span class="font-bold text-sm"><?php echo htmlspecialchars($link['label']); ?></span>
                                <a href="?delete_link=<?php echo $link['id']; ?>" class="text-red-500 text-sm font-bold">✕</a>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm h-fit">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="add_footer_link">
                    
                    <h3 class="text-lg font-black text-[#014034] mb-6 uppercase">Add New Footer Link</h3>
                    <div class="space-y-4">
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2"><input type="radio" name="section_type" value="explore" checked> Explore</label>
                            <label class="flex items-center gap-2"><input type="radio" name="section_type" value="support"> Support</label>
                        </div>
                        <input type="text" name="label" placeholder="Label (e.g. Terms)" required class="w-full p-4 bg-gray-50 rounded-xl border">
                        <input type="text" name="url" placeholder="Path (e.g. /terms)" required class="w-full p-4 bg-gray-50 rounded-xl border">
                        <button type="submit" class="w-full bg-[#014034] text-white p-4 rounded-xl font-black uppercase">Add Link</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
// Tab Logic
document.querySelectorAll('#settingsTabs a').forEach(tab => {
    tab.addEventListener('click', (e) => {
        e.preventDefault();
        const target = tab.getAttribute('href').substring(1);
        document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
        document.getElementById(target).classList.remove('hidden');
        
        document.querySelectorAll('#settingsTabs a').forEach(t => {
            t.classList.remove('border-[#014034]', 'text-[#014034]');
            t.classList.add('border-transparent', 'text-gray-400');
        });
        tab.classList.remove('border-transparent', 'text-gray-400');
        tab.classList.add('border-[#014034]', 'text-[#014034]');
    });
});

// Menu Builder Add Item
function addNavItem(){
    const div = document.createElement('div');
    div.className = 'flex gap-2 items-center bg-gray-50 p-2 rounded-xl border border-gray-200 animate-in fade-in slide-in-from-top-2 duration-300 cursor-move';
    div.innerHTML = `
        <span class="text-gray-400 font-bold px-2 handle-icon">☰</span>
        <input type="text" name="nav_label[]" class="w-1/3 p-2 bg-white rounded-lg font-bold border-none outline-none focus:ring-2 focus:ring-primary/20" placeholder="Label">
        <input type="text" name="nav_path[]" class="w-1/2 p-2 bg-white rounded-lg border-none outline-none focus:ring-2 focus:ring-primary/20 text-blue-600 font-mono text-sm" placeholder="URL Path">
        <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:bg-red-50 p-2 rounded-lg transition-colors">✕</button>
    `;
    document.getElementById('nav-container').appendChild(div);
}

// Local Upload Preview
function previewUpload(input, previewId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Initialize Sorting
document.addEventListener('DOMContentLoaded', function() {
    // 1. Navbar Sorting
    var navEl = document.getElementById('nav-container');
    if(navEl) {
        new Sortable(navEl, {
            animation: 150,
            handle: '.handle-icon', // শুধু আইকন ধরলেই সরবে
            ghostClass: 'bg-blue-50'
        });

    // Menu Sorting
    var navEl = document.getElementById('nav-container');
    new Sortable(navEl, {
        animation: 150,
        handle: '.cursor-move', // হ্যান্ডেল আইকন ধরে মুভ হবে
        ghostClass: 'bg-blue-100'
    });    
    }

    // 2. Social Media Sorting
    var socialEl = document.getElementById('social-container');
    if(socialEl) {
        new Sortable(socialEl, {
            animation: 150,
            handle: '.handle-social',
            ghostClass: 'bg-blue-50'
        });
    }
});
</script>

<?php require_once '../layout_footer.php'; ?>