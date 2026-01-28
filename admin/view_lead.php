<?php
require_once '../db.php';
require_once '../auth.php';
require_once '../layout_header.php';

// ভ্যালিডেশন
if (!isset($_GET['id'])) {
    header('Location: leads.php'); exit();
}

$id = $_GET['id'];

// ১. স্ট্যাটাস আপডেট: লিড ওপেন করলেই 'Read' হয়ে যাবে
$pdo->prepare("UPDATE leads SET status = 'Read' WHERE id = ?")->execute([$id]);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    verify_csrf_token($_POST['csrf_token']);
    $pdo->prepare("DELETE FROM leads WHERE id = ?")->execute([$_POST['delete_id']]);
    header('Location: leads.php'); exit();
}

// ২. ডাটা আনা
$stmt = $pdo->prepare("SELECT * FROM leads WHERE id = ?");
$stmt->execute([$id]);
$lead = $stmt->fetch();

if (!$lead) {
    header('Location: leads.php'); exit();
}

// ৩. স্মার্ট পার্সিং (টেক্সট থেকে ডাটা আলাদা করা)
// আপনার বর্তমান সিস্টেমে সব তথ্য 'message' ফিল্ডে স্ট্রিং আকারে আছে। আমরা সেটা ভেঙে আলাদা করছি।
$full_content = $lead['message'];
$parts = explode('--- Additional Info ---', $full_content);

$main_message = trim(str_replace('Message/Details:', '', $parts[0] ?? ''));
$additional_info_str = $parts[1] ?? '';

// এডিশনাল ইনফো থেকে লাইন বাই লাইন ডাটা বের করা
$meta = [];
if (!empty($additional_info_str)) {
    $lines = explode("\n", trim($additional_info_str));
    foreach ($lines as $line) {
        $data = explode(':', $line, 2);
        if (count($data) == 2) {
            $key = trim($data[0]);
            $val = trim($data[1]);
            if (!empty($val) && $val != 'N/A') {
                $meta[$key] = $val;
            }
        }
    }
}
?>

<div class="max-w-4xl mx-auto pb-20 animate-in fade-in slide-in-from-bottom-4 duration-500">
    
    <div class="flex justify-between items-center mb-6">
        <a href="leads.php" class="flex items-center text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-[#014034] transition-colors gap-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Inbox
        </a>
        <div class="flex gap-3">
            <a href="mailto:<?php echo $lead['email']; ?>" class="bg-[#014034] text-white px-6 py-2.5 rounded-xl text-xs font-bold uppercase tracking-widest shadow-lg hover:bg-opacity-90 flex items-center gap-2">
                <i data-lucide="reply" class="w-4 h-4"></i> Reply
            </a>
            <form method="POST" onsubmit="return confirm('Permanently delete?');">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="delete_id" value="<?php echo $lead['id']; ?>">
                <button type="submit" class="bg-red-50 text-red-500 px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-red-100 flex items-center gap-2">
                <i data-lucide="trash-2" class="w-4 h-4"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        
        <div class="p-8 border-b border-gray-100 bg-gray-50/30">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-black text-gray-900 mb-2 leading-tight">
                        <?php echo htmlspecialchars($lead['subject'] ?: 'No Subject'); ?>
                    </h1>
                    <div class="flex items-center gap-2 text-sm text-gray-500 font-medium">
                        <span class="bg-[#014034]/10 text-[#014034] px-3 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest">
                            <?php echo htmlspecialchars($lead['source']); ?>
                        </span>
                        <span>•</span>
                        <span><?php echo date('F d, Y \a\t h:i A', strtotime($lead['created_at'])); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-gray-100 border-b border-gray-100">
            <div class="p-6 md:p-8">
                <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Contact Details</h3>
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-blue-50 text-blue-600 rounded-lg"><i data-lucide="user" class="w-4 h-4"></i></div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Name</p>
                            <p class="font-bold text-gray-800 text-sm"><?php echo htmlspecialchars($lead['name']); ?></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-green-50 text-green-600 rounded-lg"><i data-lucide="mail" class="w-4 h-4"></i></div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Email</p>
                            <a href="mailto:<?php echo $lead['email']; ?>" class="font-bold text-gray-800 text-sm hover:underline hover:text-blue-600 break-all">
                                <?php echo htmlspecialchars($lead['email']); ?>
                            </a>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-purple-50 text-purple-600 rounded-lg"><i data-lucide="phone" class="w-4 h-4"></i></div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Phone</p>
                            <p class="font-bold text-gray-800 text-sm"><?php echo htmlspecialchars($lead['phone'] ?: 'N/A'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6 md:p-8 col-span-2">
                <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Project Insights</h3>
                
                <?php if(!empty($meta)): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <?php foreach($meta as $key => $val): ?>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase mb-1"><?php echo htmlspecialchars($key); ?></p>
                                <?php if(strtolower($key) == 'website'): ?>
                                    <a href="<?php echo htmlspecialchars($val); ?>" target="_blank" class="text-sm font-bold text-blue-600 hover:underline flex items-center gap-1">
                                        <?php echo htmlspecialchars($val); ?> <i data-lucide="external-link" class="w-3 h-3"></i>
                                    </a>
                                <?php else: ?>
                                    <p class="text-sm font-bold text-gray-800"><?php echo htmlspecialchars($val); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-gray-400 italic">No additional project details provided.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="p-8 md:p-10 bg-white">
            <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Message Content</h3>
            <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed font-medium">
                <?php echo nl2br(htmlspecialchars($main_message)); ?>
            </div>
        </div>

    </div>
</div>

<?php require_once '../layout_footer.php'; ?>