<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../db.php';
require_once '../layout_header.php';

// ১. সেটিংস আপডেট হ্যান্ডলার
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_tracking'])) {
    try {
        // চেকবক্স হ্যান্ডলিং (Checkboxes return 'on' or nothing)
        $browser = isset($_POST['enable_browser_tracking']) ? 1 : 0;
        $server = isset($_POST['enable_server_tracking']) ? 1 : 0;
        $test_mode = isset($_POST['enable_test_mode']) ? 1 : 0;
        $consent = isset($_POST['enable_consent_mode']) ? 1 : 0;

        $stmt = $pdo->prepare("UPDATE tracking_configs SET 
            meta_pixel_id = ?, 
            meta_access_token = ?, 
            meta_test_event_code = ?, 
            ga4_measurement_id = ?, 
            ga4_api_secret = ?, 
            gtm_container_id = ?, 
            google_ads_conversion_id = ?, 
            google_ads_label = ?, 
            tiktok_pixel_id = ?, 
            tiktok_access_token = ?,
            enable_browser_tracking = ?,
            enable_server_tracking = ?,
            enable_test_mode = ?,
            enable_consent_mode = ?
            WHERE id = 1");

        $stmt->execute([
            $_POST['meta_pixel_id'],
            $_POST['meta_access_token'],
            $_POST['meta_test_event_code'],
            $_POST['ga4_measurement_id'],
            $_POST['ga4_api_secret'],
            $_POST['gtm_container_id'],
            $_POST['google_ads_conversion_id'],
            $_POST['google_ads_label'],
            $_POST['tiktok_pixel_id'],
            $_POST['tiktok_access_token'],
            $browser,
            $server,
            $test_mode,
            $consent
        ]);

        $success_msg = "Tracking configuration updated successfully!";
        
        // রিফ্রেশ করে নতুন ডাটা দেখানো
        header("Location: tracking.php?success=1");
        exit;

    } catch (PDOException $e) {
        $error_msg = "Database Error: " . $e->getMessage();
    }
}

// ২. বর্তমান কনফিগারেশন লোড করা
$config = $pdo->query("SELECT * FROM tracking_configs WHERE id = 1")->fetch(PDO::FETCH_ASSOC);

// যদি কনফিগ না থাকে, ডিফল্ট রো তৈরি করা
if (!$config) {
    $pdo->query("INSERT INTO tracking_configs (id) VALUES (1)");
    $config = $pdo->query("SELECT * FROM tracking_configs WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="max-w-6xl mx-auto pb-24">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-black text-[#014034] uppercase">Tracking & Analytics (PSY Pro)</h1>
        <?php if (isset($_GET['success'])): ?>
            <span class="bg-green-500 text-white px-4 py-2 rounded-lg text-xs font-bold uppercase shadow-lg animate-pulse">Saved Successfully!</span>
        <?php endif; ?>
    </div>

    <form method="POST" class="space-y-8">
        <input type="hidden" name="update_tracking" value="1">

        <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
            <h3 class="text-lg font-black text-[#014034] mb-6 uppercase flex items-center gap-2">
                🚀 Global Control Center
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                    <input type="checkbox" name="enable_browser_tracking" class="w-5 h-5 accent-[#014034]" <?= $config['enable_browser_tracking'] ? 'checked' : '' ?>>
                    <div>
                        <span class="font-bold block text-sm">Browser Tracking</span>
                        <span class="text-xs text-gray-400">Pixel / Gtag.js</span>
                    </div>
                </label>
                
                <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                    <input type="checkbox" name="enable_server_tracking" class="w-5 h-5 accent-[#014034]" <?= $config['enable_server_tracking'] ? 'checked' : '' ?>>
                    <div>
                        <span class="font-bold block text-sm">Server Tracking (CAPI)</span>
                        <span class="text-xs text-gray-400">100% Accuracy</span>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                    <input type="checkbox" name="enable_test_mode" class="w-5 h-5 accent-[#014034]" <?= $config['enable_test_mode'] ? 'checked' : '' ?>>
                    <div>
                        <span class="font-bold block text-sm">Test Mode</span>
                        <span class="text-xs text-gray-400">For Debugging</span>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                    <input type="checkbox" name="enable_consent_mode" class="w-5 h-5 accent-[#014034]" <?= $config['enable_consent_mode'] ? 'checked' : '' ?>>
                    <div>
                        <span class="font-bold block text-sm">Consent Mode</span>
                        <span class="text-xs text-gray-400">GDPR Compliance</span>
                    </div>
                </label>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <div class="bg-white p-8 rounded-[2rem] border border-blue-100 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 bg-blue-600 text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl">META / FACEBOOK</div>
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase">Pixel ID</label>
                        <input type="text" name="meta_pixel_id" value="<?= htmlspecialchars($config['meta_pixel_id'] ?? '') ?>" class="w-full p-4 bg-blue-50/50 rounded-xl font-bold mt-1" placeholder="e.g. 1234567890">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase">Conversion API Access Token</label>
                        <textarea name="meta_access_token" rows="3" class="w-full p-4 bg-blue-50/50 rounded-xl font-mono text-xs mt-1" placeholder="Paste huge token string here..."><?= htmlspecialchars($config['meta_access_token'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase">Test Event Code (Optional)</label>
                        <input type="text" name="meta_test_event_code" value="<?= htmlspecialchars($config['meta_test_event_code'] ?? '') ?>" class="w-full p-4 bg-blue-50/50 rounded-xl font-bold mt-1" placeholder="e.g. TEST59238">
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 rounded-[2rem] border border-orange-100 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 bg-orange-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl">GOOGLE ANALYTICS 4</div>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase">Measurement ID</label>
                            <input type="text" name="ga4_measurement_id" value="<?= htmlspecialchars($config['ga4_measurement_id'] ?? '') ?>" class="w-full p-4 bg-orange-50/50 rounded-xl font-bold mt-1" placeholder="G-XXXXXXXXXX">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase">GTM Container ID</label>
                            <input type="text" name="gtm_container_id" value="<?= htmlspecialchars($config['gtm_container_id'] ?? '') ?>" class="w-full p-4 bg-orange-50/50 rounded-xl font-bold mt-1" placeholder="GTM-XXXXXX">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase">Measurement Protocol API Secret</label>
                        <input type="text" name="ga4_api_secret" value="<?= htmlspecialchars($config['ga4_api_secret'] ?? '') ?>" class="w-full p-4 bg-orange-50/50 rounded-xl font-bold mt-1" placeholder="Generated from GA4 Admin">
                    </div>
                </div>
                
                <div class="mt-8 border-t pt-6 border-orange-100">
                    <h4 class="text-xs font-black text-gray-400 uppercase mb-4">Google Ads</h4>
                    <div class="grid grid-cols-2 gap-4">
                         <div>
                            <label class="text-xs font-bold text-gray-400 uppercase">Conversion ID</label>
                            <input type="text" name="google_ads_conversion_id" value="<?= htmlspecialchars($config['google_ads_conversion_id'] ?? '') ?>" class="w-full p-4 bg-orange-50/50 rounded-xl font-bold mt-1" placeholder="AW-XXXXXXXX">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase">Conversion Label</label>
                            <input type="text" name="google_ads_label" value="<?= htmlspecialchars($config['google_ads_label'] ?? '') ?>" class="w-full p-4 bg-orange-50/50 rounded-xl font-bold mt-1" placeholder="AbC_xYz123">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 rounded-[2rem] border border-gray-900 shadow-sm relative overflow-hidden lg:col-span-2">
                <div class="absolute top-0 right-0 bg-black text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl">TIKTOK PIXEL</div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase">Pixel ID</label>
                        <input type="text" name="tiktok_pixel_id" value="<?= htmlspecialchars($config['tiktok_pixel_id'] ?? '') ?>" class="w-full p-4 bg-gray-50 rounded-xl font-bold mt-1" placeholder="CXXXXXXXXXXXXXX">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase">Events API Access Token</label>
                        <input type="text" name="tiktok_access_token" value="<?= htmlspecialchars($config['tiktok_access_token'] ?? '') ?>" class="w-full p-4 bg-gray-50 rounded-xl font-bold mt-1" placeholder="Huge token string...">
                    </div>
                </div>
            </div>

        </div>

        <div class="fixed bottom-0 left-0 w-full bg-white/80 backdrop-blur-md border-t p-4 flex justify-end z-50">
             <div class="max-w-6xl w-full mx-auto flex justify-end">
                <button type="submit" class="bg-[#014034] hover:bg-[#012a22] text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest shadow-2xl transition-all hover:scale-105 flex items-center gap-3">
                    <span>Save All Settings</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                </button>
             </div>
        </div>
        
    </form>
    
    <div class="mt-16 bg-gray-900 text-white p-8 rounded-[2rem]">
        <h3 class="text-lg font-black uppercase mb-6 flex justify-between items-center">
            <span>Server Event Logs (Last 5)</span>
            <a href="#" class="text-xs text-gray-400 hover:text-white border-b border-gray-600">View All Logs</a>
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-400">
                <thead class="text-xs uppercase bg-gray-800 text-gray-200">
                    <tr>
                        <th class="p-4 rounded-l-xl">Time</th>
                        <th class="p-4">Event</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 rounded-r-xl">Response</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    <?php
                    $logs = $pdo->query("SELECT * FROM tracking_events ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
                    if ($logs):
                        foreach($logs as $log): 
                    ?>
                    <tr class="hover:bg-gray-800/50 transition">
                        <td class="p-4 font-mono text-xs"><?= date('M d, H:i:s', strtotime($log['created_at'])) ?></td>
                        <td class="p-4 font-bold text-white"><?= htmlspecialchars($log['event_name']) ?></td>
                        <td class="p-4">
                            <?php if($log['status'] == 'sent'): ?>
                                <span class="bg-green-500/20 text-green-400 px-2 py-1 rounded text-xs font-bold">SENT</span>
                            <?php else: ?>
                                <span class="bg-red-500/20 text-red-400 px-2 py-1 rounded text-xs font-bold">FAILED</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 max-w-xs truncate font-mono text-xs opacity-50"><?= htmlspecialchars(substr($log['response_log'] ?? '', 0, 50)) ?>...</td>
                    </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="4" class="p-8 text-center opacity-30">No events tracked yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../layout_footer.php'; ?>