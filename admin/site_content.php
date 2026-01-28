<?php
ob_start();
require_once '../db.php';
require_once '../auth.php';
require_once '../layout_header.php';

// ডাটা সেভ হ্যান্ডলার
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token']); 
    
    // 🟢 ২.1 About List (JSON)
    $about_list = [];
    for($i=0; $i<3; $i++) {
        if(!empty($_POST["about_list_$i"])) {
            $about_list[] = $_POST["about_list_$i"];
        }
    }
    $about_list_json = json_encode($about_list);

    
    // ১. বাজেট ও ফর্ম কনফিগ
    $budgets = array_filter(explode("\n", $_POST['budget_ranges']));
    $budget_json = json_encode(array_values(array_map('trim', $budgets))); 
    
    $form_config = json_encode([
        'is_phone_required' => isset($_POST['req_phone']) ? true : false,
        'is_website_required' => isset($_POST['req_website']) ? true : false
    ]);

    $about_content = json_encode([
        'title' => $_POST['about_title'],
        'subtitle' => $_POST['about_subtitle'],
        'description' => $_POST['about_desc']
    ]);
    // 🆕 About Page Extra Data (with icon)
        $about_features = [];
        for($i=0; $i<4; $i++) {
            $about_features[] = [
                'title' => $_POST["about_feat_title_$i"] ?? '',
                'desc'  => $_POST["about_feat_desc_$i"] ?? '',
                'icon'  => $_POST["about_feat_icon_$i"] ?? 'Target'
            ];
        }
        $about_features_json = json_encode($about_features);

    
    $contact_content = json_encode([
        'title' => $_POST['contact_title'],
        'subtitle' => $_POST['contact_subtitle'],
        'email' => $_POST['contact_email'], 
        'phone' => $_POST['contact_phone'] 
    ]);

    // ২. Advantage Section Features
    $adv_features = [];
    for($i=0; $i<3; $i++) {
        $adv_features[] = [
            'title' => $_POST["adv_feat_title_$i"],
            'desc' => $_POST["adv_feat_desc_$i"],
            'icon' => $_POST["adv_feat_icon_$i"]
        ];
    }
    $adv_features_json = json_encode($adv_features);
    // === Universal Image Processor (NEW) ===
        $upload_dir = '../uploads/';
        function processImg($fileKey, $hiddenKey, $existingVal) {
            global $upload_dir;
        
            // 1. New Upload
            if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] == 0) {
                $ext = pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION);
                $allowed = ['jpg','jpeg','png','webp'];
if (!in_array(strtolower($ext), $allowed)) {
    return $existingVal;
}

                $fileName = $fileKey . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $upload_dir . $fileName)) {
                    return $fileName;
                }
            }
            // 2. Media Library Selection
            if (!empty($_POST[$hiddenKey])) {
                return $_POST[$hiddenKey];
            }
            // 3. Keep Existing
            return $existingVal;
        }


// 🟢 ৩.1 About Image 1
$about_img1 = processImageInput(
    'about_image1',
    'selected_about_image1',
    $_POST['existing_about_image1']
);

// 🟢 ৩.2 About Image 2
$about_img2 = processImageInput(
    'about_image2',
    'selected_about_image2',
    $_POST['existing_about_image2']
);
// 🟢 3.3 About Page Image
$about_page_img = processImageInput(
    'about_page_image',
    'selected_about_page_image',
    $_POST['existing_about_page_image']
);


    // ৩. ইমেজ আপলোড (SECURED)
    $adv_image_path = processImageInput(
    'adv_image',
    'selected_adv_image',
    $_POST['existing_adv_image']
);

    // ৪. ডাটাবেজ আপডেট (সব ফিল্ড একসাথে)
    $stmt = $pdo->prepare("UPDATE site_settings SET 
        quote_budget_ranges = ?, 
        quote_privacy_text = ?, 
        quote_form_config = ?,
        about_page_content = ?,
        contact_page_content = ?,
        home_cta_title = ?,
        home_cta_subtitle = ?,
        home_about_title = ?, 
        home_about_subtitle = ?, 
        home_about_desc = ?, 
        home_about_list = ?, 
        home_about_image1 = ?, 
        home_about_image2 = ?, 
        home_about_year = ?,
        footer_copyright_text = ?,
        footer_privacy_text = ?,
        footer_terms_text = ?,
        footer_privacy_url = ?,
        footer_terms_url = ?,
        home_adv_subtitle = ?,
        home_adv_title = ?,
        home_adv_image = ?,
        home_adv_stat_num = ?,
        home_adv_stat_text = ?,
        home_adv_features = ?,
        google_font_url = ?, 
        font_family = ?, 
        about_image = ?,
        about_motto = ?,
        about_stat_projects = ?,
        about_stat_satisfaction = ?,
        about_features = ?
        
WHERE id = 1");

        
    $stmt->execute([
        // Quote & Form
        $budget_json,
        $_POST['privacy_text'],
        $form_config,
    
        // Pages
        $about_content,
        $contact_content,
    
        // Home CTA
        $_POST['home_cta_title'],
        $_POST['home_cta_subtitle'],
    
        // Home About
        $_POST['home_about_title'],
        $_POST['home_about_subtitle'],
        $_POST['home_about_desc'],
        $about_list_json,
        $about_img1,
        $about_img2,
        $_POST['home_about_year'],
    
        // Footer
        $_POST['footer_copyright_text'],
        $_POST['footer_privacy_text'],
        $_POST['footer_terms_text'],
        $_POST['footer_privacy_url'],
        $_POST['footer_terms_url'],
    
        // Advantage
        $_POST['home_adv_subtitle'],
        $_POST['home_adv_title'],
        $adv_image_path,
        $_POST['home_adv_stat_num'],
        $_POST['home_adv_stat_text'],
        $adv_features_json,
    
        // Typography
        $_POST['google_font_url'],
        $_POST['font_family'],
    
        // 🆕 About Page Advanced
        $about_page_img,
        $_POST['about_motto'] ?? '',
        $_POST['about_stat_projects'] ?? '',
        $_POST['about_stat_satisfaction'] ?? '',
        $about_features_json
    ]);
    
    header('Location: site_content.php?success=1'); 
    exit();
}

// ডাটা লোড
$settings = $pdo->query("SELECT * FROM site_settings WHERE id = 1")->fetch();

// JSON Decode
$budgets = json_decode($settings['quote_budget_ranges'] ?? '[]', true);
$budget_text = implode("\n", $budgets);
$config = json_decode($settings['quote_form_config'] ?? '{}', true);
$about = json_decode($settings['about_page_content'] ?? '{}', true);
$about_features = json_decode($settings['about_features'] ?? '[]', true);
$contact = json_decode($settings['contact_page_content'] ?? '{}', true);
$adv_features = json_decode($settings['home_adv_features'] ?? '[]', true);

if(empty($adv_features)) {
    $adv_features = [
        ['title' => 'Strategy-First Approach', 'desc' => 'Every pixel aligned with your bottom line.', 'icon' => 'Target'],
        ['title' => 'Everything Under One Roof', 'desc' => 'Unified design, dev, and growth team.', 'icon' => 'Zap'],
        ['title' => 'Business Results Over Vanity', 'desc' => 'Leads and sales over likes and clicks.', 'icon' => 'TrendingUp']
    ];
}
?>

<div class="max-w-5xl mx-auto pb-24 animate-in fade-in duration-500">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-black text-[#014034] uppercase">Manage Content</h1>
        <?php if(isset($_GET['success'])): ?>
            <span class="bg-green-500 text-white px-4 py-2 rounded-lg text-xs font-bold uppercase shadow-lg flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4"></i> Saved Successfully!
            </span>
        <?php endif; ?>
    </div>

    <form method="POST" enctype="multipart/form-data" class="space-y-8">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

        <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <h3 class="text-lg font-black text-[#014034] mb-6 uppercase border-b pb-4 flex items-center gap-2">
                <i data-lucide="type" class="w-5 h-5 text-indigo-500"></i> Typography Settings
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Google Font URL</label>
                    <input type="text" name="google_font_url" value="<?php echo htmlspecialchars($settings['google_font_url'] ?? ''); ?>" class="w-full p-4 bg-gray-50 rounded-xl font-medium mt-2 border border-transparent focus:border-[#014034] focus:bg-white transition-all outline-none">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Font Family Name (CSS)</label>
                    <input type="text" name="font_family" value="<?php echo htmlspecialchars($settings['font_family'] ?? ''); ?>" class="w-full p-4 bg-gray-50 rounded-xl font-bold mt-2 border border-transparent focus:border-[#014034] focus:bg-white transition-all outline-none" placeholder="'Poppins', sans-serif">
                </div>
            </div>
        </div>
        
        <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <h3 class="text-lg font-black text-[#014034] mb-6 uppercase border-b pb-4 flex items-center gap-2">
                <i data-lucide="trophy" class="w-5 h-5 text-yellow-500"></i> Advantage Section (Home)
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div class="space-y-4">
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Section Subtitle</label>
                        <input type="text" name="home_adv_subtitle" value="<?php echo htmlspecialchars($settings['home_adv_subtitle'] ?? 'The Build to Grow Advantage'); ?>" class="w-full p-4 bg-gray-50 rounded-xl font-bold mt-1 border-transparent focus:border-[#014034] focus:bg-white transition-all">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Main Title</label>
                        <input type="text" name="home_adv_title" value="<?php echo htmlspecialchars($settings['home_adv_title'] ?? 'Strategy-First Execution'); ?>" class="w-full p-4 bg-gray-50 rounded-xl font-bold mt-1 border-transparent focus:border-[#014034] focus:bg-white transition-all">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Stat Number</label>
                            <input type="text" name="home_adv_stat_num" value="<?php echo htmlspecialchars($settings['home_adv_stat_num'] ?? '140%'); ?>" class="w-full p-4 bg-gray-50 rounded-xl font-bold mt-1 text-green-600">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Stat Text</label>
                            <input type="text" name="home_adv_stat_text" value="<?php echo htmlspecialchars($settings['home_adv_stat_text'] ?? 'Average Growth Increase'); ?>" class="w-full p-4 bg-gray-50 rounded-xl font-bold mt-1">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Section Image</label>
                    <div class="mt-2 relative group">
                        <?php 
                            $imgUrl = $settings['home_adv_image'];
                            if(!filter_var($imgUrl, FILTER_VALIDATE_URL)) {
                                $imgUrl = "../uploads/" . $imgUrl;
                            }
                        ?>
                        <img src="<?php echo $imgUrl; ?>" class="w-full h-48 object-cover rounded-xl border-2 border-dashed border-gray-200 mb-4">
                        <input type="hidden" name="existing_adv_image" value="<?php echo $settings['home_adv_image']; ?>">
                        <input type="file" name="adv_image" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-[#014034]/10 file:text-[#014034] hover:file:bg-[#014034]/20">
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2">Key Features (3 Items)</label>
                <?php for($i=0; $i<3; $i++): ?>
                <div class="flex gap-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="w-1/4">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Icon Name</label>
                        <select name="adv_feat_icon_<?php echo $i; ?>" class="w-full p-3 rounded-lg font-bold text-sm mt-1 bg-white">
                            <?php 
                                $icons = ['Target', 'Zap', 'TrendingUp', 'ShieldCheck', 'Award', 'BarChart', 'Users'];
                                $current = $adv_features[$i]['icon'] ?? 'Target';
                                foreach($icons as $icon) {
                                    echo "<option value='$icon' " . ($current == $icon ? 'selected' : '') . ">$icon</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="w-1/3">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Feature Title</label>
                        <input type="text" name="adv_feat_title_<?php echo $i; ?>" value="<?php echo htmlspecialchars($adv_features[$i]['title'] ?? ''); ?>" class="w-full p-3 rounded-lg font-bold text-sm mt-1 bg-white border border-gray-200">
                    </div>
                    <div class="w-full">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Description</label>
                        <input type="text" name="adv_feat_desc_<?php echo $i; ?>" value="<?php echo htmlspecialchars($adv_features[$i]['desc'] ?? ''); ?>" class="w-full p-3 rounded-lg font-medium text-sm mt-1 bg-white border border-gray-200">
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <h3 class="text-lg font-black text-[#014034] mb-6 uppercase border-b pb-4 flex items-center gap-2">
                <i data-lucide="rocket" class="w-5 h-5 text-[#4DB6AC]"></i> Get a Quote Configuration
            </h3>
            
            <div class="mb-6">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Budget Ranges (One per line)</label>
                <textarea name="budget_ranges" rows="5" class="w-full p-4 bg-gray-50 rounded-xl text-sm font-bold mt-2 font-mono border-transparent focus:border-[#014034] focus:bg-white transition-all"><?php echo htmlspecialchars($budget_text); ?></textarea>
            </div>

            <div class="mb-6">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Privacy Policy Text (HTML Allowed)</label>
                <textarea name="privacy_text" rows="3" class="w-full p-4 bg-gray-50 rounded-xl text-sm font-medium mt-2 border-transparent focus:border-[#014034] focus:bg-white transition-all"><?php echo htmlspecialchars($settings['quote_privacy_text'] ?? ''); ?></textarea>
            </div>
            
            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100">
                <h4 class="text-xs font-black text-gray-400 uppercase mb-4 tracking-widest">Form Requirements</h4>
                <div class="flex gap-8">
                    <label class="flex items-center cursor-pointer gap-3">
                        <input type="checkbox" name="req_phone" class="w-5 h-5 accent-[#014034]" <?php echo ($config['is_phone_required'] ?? true) ? 'checked' : ''; ?>>
                        <span class="text-xs font-bold uppercase text-gray-600">Phone Required</span>
                    </label>
                    <label class="flex items-center cursor-pointer gap-3">
                        <input type="checkbox" name="req_website" class="w-5 h-5 accent-[#014034]" <?php echo ($config['is_website_required'] ?? false) ? 'checked' : ''; ?>>
                        <span class="text-xs font-bold uppercase text-gray-600">Website URL Required</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <h3 class="text-lg font-black text-[#014034] mb-6 uppercase border-b pb-4 flex items-center gap-2">
                <i data-lucide="layout" class="w-5 h-5 text-purple-500"></i> Homepage & Footer
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 border-b border-gray-100 pb-8">
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Home CTA Title</label>
                    <input type="text" name="home_cta_title" value="<?php echo htmlspecialchars($settings['home_cta_title'] ?? 'Ready to Grow Your Business?'); ?>" class="w-full p-4 bg-gray-50 rounded-xl font-bold mt-2 border-transparent focus:border-[#014034] focus:bg-white transition-all">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Home CTA Subtitle</label>
                    <input type="text" name="home_cta_subtitle" value="<?php echo htmlspecialchars($settings['home_cta_subtitle'] ?? ''); ?>" class="w-full p-4 bg-gray-50 rounded-xl font-bold mt-2 border-transparent focus:border-[#014034] focus:bg-white transition-all">
                </div>
            </div>

            <div class="space-y-6">
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Footer Copyright Text</label>
                    <input type="text" name="footer_copyright_text" value="<?php echo htmlspecialchars($settings['footer_copyright_text'] ?? '© 2026 Build to Grow. All rights reserved.'); ?>" class="w-full p-4 bg-gray-50 rounded-xl font-bold mt-2 border-transparent focus:border-[#014034] focus:bg-white transition-all">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Privacy Link Label & URL</label>
                        <div class="flex gap-2 mt-1">
                            <input type="text" name="footer_privacy_text" value="<?php echo htmlspecialchars($settings['footer_privacy_text'] ?? 'Privacy'); ?>" class="w-1/3 p-3 bg-white rounded-lg font-bold border border-gray-200">
                            <input type="text" name="footer_privacy_url" value="<?php echo htmlspecialchars($settings['footer_privacy_url'] ?? '/privacy'); ?>" class="w-2/3 p-3 bg-white rounded-lg font-medium border border-gray-200">
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Terms Link Label & URL</label>
                        <div class="flex gap-2 mt-1">
                            <input type="text" name="footer_terms_text" value="<?php echo htmlspecialchars($settings['footer_terms_text'] ?? 'Terms'); ?>" class="w-1/3 p-3 bg-white rounded-lg font-bold border border-gray-200">
                            <input type="text" name="footer_terms_url" value="<?php echo htmlspecialchars($settings['footer_terms_url'] ?? '/terms'); ?>" class="w-2/3 p-3 bg-white rounded-lg font-medium border border-gray-200">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 🟢 HOME ABOUT SECTION (NEW) -->
<div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm mt-8">
    <h3 class="text-lg font-black text-[#014034] mb-6 uppercase border-b pb-4 flex items-center gap-2">
        <i data-lucide="info" class="w-5 h-5 text-blue-500"></i> Home About Section
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="space-y-4">
            <input type="text" name="home_about_title" value="<?php echo htmlspecialchars($settings['home_about_title'] ?? ''); ?>" class="w-full p-4 bg-gray-50 rounded-2xl font-black" placeholder="Main Title">
            <input type="text" name="home_about_subtitle" value="<?php echo htmlspecialchars($settings['home_about_subtitle'] ?? ''); ?>" class="w-full p-4 bg-gray-50 rounded-2xl font-bold" placeholder="Subtitle / Lower Text">
            <textarea name="home_about_desc" rows="6" class="w-full p-4 bg-gray-50 rounded-2xl text-sm"><?php echo htmlspecialchars($settings['home_about_desc'] ?? ''); ?></textarea>
            
            <label class="text-[10px] font-bold uppercase block mt-2">Bullet Points</label>
            <?php 
                $list = json_decode($settings['home_about_list'] ?? '[]', true);
                for($i=0; $i<3; $i++): 
            ?>
                <input type="text" name="about_list_<?php echo $i; ?>" value="<?php echo htmlspecialchars($list[$i] ?? ''); ?>" class="w-full p-3 bg-white border border-gray-200 rounded-xl text-sm mb-2" placeholder="List Item <?php echo $i+1; ?>">
            <?php endfor; ?>
            
            <input type="text" name="home_about_year" value="<?php echo htmlspecialchars($settings['home_about_year'] ?? ''); ?>" class="w-1/3 p-3 bg-gray-50 rounded-xl font-bold text-center" placeholder="Since Year (e.g. 2008)">
        </div>

        <div class="space-y-6">
            <div>
                <label class="text-[10px] font-bold uppercase mb-2 block">Image 1 (Top)</label>
                <div class="relative group w-full h-40 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 overflow-hidden">
                    <img src="<?php echo !empty($settings['home_about_image1']) ? '../uploads/'.$settings['home_about_image1'] : ''; ?>" class="w-full h-full object-cover">
                </div>
                <input type="file" name="about_image1" class="mt-2 w-full text-xs">
                <input type="hidden" name="existing_about_image1" value="<?php echo $settings['home_about_image1'] ?? ''; ?>">
            </div>

            <div>
                <label class="text-[10px] font-bold uppercase mb-2 block">Image 2 (Bottom)</label>
                <div class="relative group w-full h-40 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 overflow-hidden">
                    <img src="<?php echo !empty($settings['home_about_image2']) ? '../uploads/'.$settings['home_about_image2'] : ''; ?>" class="w-full h-full object-cover">
                </div>
                <input type="file" name="about_image2" class="mt-2 w-full text-xs">
                <input type="hidden" name="existing_about_image2" value="<?php echo $settings['home_about_image2'] ?? ''; ?>">
            </div>
        </div>
    </div>
</div>


        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
                <h3 class="text-lg font-black text-[#014034] mb-6 uppercase border-b pb-4 flex items-center gap-2">
                    <i data-lucide="info" class="w-5 h-5 text-blue-500"></i> About Page
                </h3>
                
                <input type="hidden" name="existing_about_page_image"
                       value="<?php echo $settings['about_image'] ?? ''; ?>">
                <input type="hidden" name="selected_about_page_image"
                       id="selected_about_page_image">

                <div class="space-y-4">
                    <input type="text" name="about_title" value="<?php echo htmlspecialchars($about['title'] ?? ''); ?>" class="w-full p-4 bg-gray-50 rounded-xl font-bold border-transparent focus:border-[#014034] focus:bg-white transition-all" placeholder="Title">
                    <input type="text" name="about_subtitle" value="<?php echo htmlspecialchars($about['subtitle'] ?? ''); ?>" class="w-full p-4 bg-gray-50 rounded-xl font-bold border-transparent focus:border-[#014034] focus:bg-white transition-all" placeholder="Subtitle">
                    <textarea name="about_desc" rows="4" class="w-full p-4 bg-gray-50 rounded-xl text-sm font-medium border-transparent focus:border-[#014034] focus:bg-white transition-all"><?php echo htmlspecialchars($about['description'] ?? ''); ?></textarea>
                </div>
                <div class="mt-6">
                    <label class="text-[10px] font-bold uppercase mb-2 block">About Page Image</label>
                
                    <div class="flex items-center gap-4 bg-gray-50 p-4 rounded-xl border border-dashed border-gray-300">
                        <img id="prev_about_pg"
                             src="<?php echo !empty($settings['about_image']) ? '../uploads/'.$settings['about_image'] : ''; ?>"
                             class="w-24 h-24 object-cover rounded-lg bg-white">
                
                        <div class="flex gap-2">
                            <button type="button"
                                onclick="openMediaLibrary('selected_about_page_image','prev_about_pg')"
                                class="bg-[#014034]/10 text-[#014034] px-3 py-2 rounded-lg text-xs font-bold">
                                Media Library
                            </button>
                
                            <label class="cursor-pointer bg-white border border-gray-200 px-3 py-2 rounded-lg text-xs font-bold">
                                Upload
                                <input type="file" name="about_page_image" class="hidden"
                                       onchange="previewUpload(this,'prev_about_pg')">
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 space-y-4">
                    <label class="text-[10px] font-bold uppercase tracking-widest">
                        About Key Features
                    </label>
                
                    <?php 
                    $defaults = ['Our Mission', 'Our Team', 'Our Speed', 'Our Reach'];
                    for($i=0; $i<4; $i++): 
                    ?>
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <input type="text"
                                   name="about_feat_title_<?php echo $i; ?>"
                                   value="<?php echo htmlspecialchars($about_features[$i]['title'] ?? $defaults[$i]); ?>"
                                   class="w-full mb-2 p-3 bg-white rounded-lg text-xs font-bold"
                                   placeholder="Title">
                
                            <input type="text"
                                   name="about_feat_desc_<?php echo $i; ?>"
                                    <label class="text-[10px] font-bold text-gray-400 mt-2 block">
                                        Icon (Lucide Name)
                                    </label>
                                    <select name="about_feat_icon_<?php echo $i; ?>"
                                            class="w-full p-2 bg-white rounded-lg text-xs">
                                    <?php
                                    $icons = ['Target','Users','Zap','Globe','Award','Shield'];
                                    $current = $about_features[$i]['icon'] ?? 'Target';
                                    foreach($icons as $ic){
                                        echo "<option value='$ic' ".($current==$ic?'selected':'').">$ic</option>";
                                    }
                                    ?>
                                    </select>
                                   
                                   value="<?php echo htmlspecialchars($about_features[$i]['desc'] ?? ''); ?>"
                                   class="w-full p-3 bg-white rounded-lg text-xs"
                                   placeholder="Description">
                        </div>
                    <?php endfor; ?>
                </div>


            </div>

            <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
                <h3 class="text-lg font-black text-[#014034] mb-6 uppercase border-b pb-4 flex items-center gap-2">
                    <i data-lucide="mail" class="w-5 h-5 text-orange-500"></i> Contact Page
                </h3>
                <div class="space-y-4">
                    <input type="text" name="contact_title" value="<?php echo htmlspecialchars($contact['title'] ?? ''); ?>" class="w-full p-4 bg-gray-50 rounded-xl font-bold border-transparent focus:border-[#014034] focus:bg-white transition-all" placeholder="Title">
                    <input type="text" name="contact_subtitle" value="<?php echo htmlspecialchars($contact['subtitle'] ?? ''); ?>" class="w-full p-4 bg-gray-50 rounded-xl font-bold border-transparent focus:border-[#014034] focus:bg-white transition-all" placeholder="Subtitle">
                    <div class="grid grid-cols-2 gap-4">
                        <input type="text" name="contact_email" value="<?php echo htmlspecialchars($contact['email'] ?? ''); ?>" class="bg-gray-50 border-transparent rounded-xl p-4 font-bold focus:bg-white border focus:border-[#014034]" placeholder="Email">
                        <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($contact['phone'] ?? ''); ?>" class="bg-gray-50 border-transparent rounded-xl p-4 font-bold focus:bg-white border focus:border-[#014034]" placeholder="Phone">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end fixed bottom-8 right-8 z-50">
            <button type="submit" class="bg-[#014034] text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest shadow-2xl hover:scale-105 transition-transform flex items-center gap-3 border-4 border-white">
                <i data-lucide="save" class="w-5 h-5"></i> Save All Content
            </button>
        </div>
    </form>
</div>

<?php require_once '../layout_footer.php'; ?>