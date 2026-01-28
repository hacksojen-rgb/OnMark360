<?php
require_once '../db.php';
require_once '../auth.php';
require_once '../layout_header.php';

// ১. ট্যাব লজিক
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'service';

// ২. ডিলিট লজিক (SECURED POST METHOD)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_lead_id'])) {
    verify_csrf_token($_POST['csrf_token']);
    $pdo->prepare("DELETE FROM leads WHERE id = ?")->execute([$_POST['delete_lead_id']]);
    header("Location: leads.php?tab=$active_tab"); exit();
}

// ৩. স্ট্যাটাস আপডেট (Mark as Read/Unread)
if (isset($_GET['status']) && isset($_GET['id'])) {
    $status = $_GET['status'] == 'read' ? 'Read' : 'New';
    $pdo->prepare("UPDATE leads SET status = ? WHERE id = ?")->execute([$status, $_GET['id']]);
    header("Location: leads.php?tab=$active_tab"); exit();
}

// ৪. ফিল্টারিং লজিক
$query = "SELECT * FROM leads WHERE ";
if ($active_tab == 'consultation') {
    $query .= "source LIKE '%Consultation%'";
    $title = "Consultation Requests";
} elseif ($active_tab == 'inbox') {
    $query .= "(source = 'Contact Form' OR source IS NULL)";
    $title = "Inbox Messages";
} else {
    $query .= "source IN ('Service Form', 'Get A Quote', 'Free Growth Plan')";
    $title = "Service Leads";
    $active_tab = 'service';
}
$query .= " ORDER BY id DESC";

$stmt = $pdo->query($query);
$leads = $stmt->fetchAll();
?>

<div class="animate-in fade-in duration-500 pb-20">
    <div class="flex flex-col md:flex-row justify-between items-center gap-6 mb-8">
        <div>
            <h1 class="text-3xl font-black text-[#014034] uppercase tracking-tighter"><?php echo $title; ?></h1>
            <p class="text-gray-500 text-xs font-bold uppercase tracking-widest"><?php echo count($leads); ?> conversations found</p>
        </div>
        
        <div class="flex bg-white p-1 rounded-xl shadow-sm border border-gray-100">
            <?php 
            $tabs = [
                'service' => ['label' => 'Service Leads', 'icon' => 'briefcase'],
                'inbox' => ['label' => 'Inbox', 'icon' => 'inbox'],
                'consultation' => ['label' => 'Consultations', 'icon' => 'calendar']
            ];
            foreach($tabs as $key => $tab): 
                $isActive = $active_tab == $key;
            ?>
            <a href="leads.php?tab=<?php echo $key; ?>" 
               class="px-5 py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all flex items-center gap-2 <?php echo $isActive ? 'bg-[#014034] text-white shadow-md' : 'text-gray-400 hover:text-[#014034] hover:bg-gray-50'; ?>">
                <i data-lucide="<?php echo $tab['icon']; ?>" class="w-4 h-4"></i>
                <?php echo $tab['label']; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="bg-white rounded-[1.5rem] border border-gray-200 shadow-sm overflow-hidden">
        <?php if(count($leads) > 0): ?>
            <div class="divide-y divide-gray-100">
                <?php foreach($leads as $lead): 
                    $isUnread = $lead['status'] == 'New';
                    // Parse snippet from message
                    $full_msg = $lead['message'] ?? '';
                    $clean_msg = explode('--- Additional Info ---', $full_msg)[0];
                    $clean_msg = str_replace('Message/Details:', '', $clean_msg);
                    $snippet = substr(trim(strip_tags($clean_msg)), 0, 60) . '...';
                ?>
                <div class="group hover:bg-gray-50 transition-colors cursor-pointer relative <?php echo $isUnread ? 'bg-blue-50/30' : ''; ?>">
                    
                    <a href="view_lead.php?id=<?php echo $lead['id']; ?>" class="flex items-center gap-4 p-4 sm:px-6">
                        
                        <div class="flex-shrink-0">
                            <?php if($isUnread): ?>
                                <div class="w-3 h-3 bg-blue-500 rounded-full shadow-sm" title="New Message"></div>
                            <?php else: ?>
                                <div class="w-3 h-3 border-2 border-gray-200 rounded-full" title="Read"></div>
                            <?php endif; ?>
                        </div>

                        <div class="w-1/4 min-w-[150px]">
                            <h4 class="text-sm truncate <?php echo $isUnread ? 'font-black text-gray-900' : 'font-medium text-gray-600'; ?>">
                                <?php echo htmlspecialchars($lead['name']); ?>
                            </h4>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mt-0.5">
                                <?php echo htmlspecialchars($lead['source']); ?>
                            </span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-sm truncate <?php echo $isUnread ? 'font-bold text-gray-800' : 'font-medium text-gray-500'; ?>">
                                    <?php echo htmlspecialchars($lead['subject'] ?: '(No Subject)'); ?>
                                </span>
                                <?php if(!empty($lead['budget']) && $lead['budget'] != 'N/A'): ?>
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 text-[10px] font-bold rounded-full uppercase tracking-wide">
                                        <?php echo htmlspecialchars($lead['budget']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-gray-400 truncate font-medium">
                                <?php echo htmlspecialchars($snippet); ?>
                            </p>
                        </div>

                        <div class="text-right w-24 flex-shrink-0">
                            <span class="text-xs font-bold text-gray-400 <?php echo $isUnread ? 'text-blue-500' : ''; ?>">
                                <?php echo date('M d', strtotime($lead['created_at'])); ?>
                            </span>
                        </div>
                    </a>

                    <div class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity bg-white/80 backdrop-blur-sm p-1 rounded-lg shadow-sm border border-gray-100">
                        <?php if($isUnread): ?>
                            <a href="leads.php?tab=<?php echo $active_tab; ?>&status=read&id=<?php echo $lead['id']; ?>" class="p-2 text-gray-400 hover:text-[#014034]" title="Mark as Read"><i data-lucide="check" class="w-4 h-4"></i></a>
                        <?php else: ?>
                            <a href="leads.php?tab=<?php echo $active_tab; ?>&status=unread&id=<?php echo $lead['id']; ?>" class="p-2 text-gray-400 hover:text-blue-500" title="Mark as Unread"><i data-lucide="mail" class="w-4 h-4"></i></a>
                        <?php endif; ?>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this lead?');" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="delete_lead_id" value="<?php echo $lead['id']; ?>">
                            <button type="submit" class="p-2 text-gray-400 hover:text-red-500" title="Delete">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-24 bg-gray-50/50">
                <div class="bg-white p-4 rounded-full shadow-sm w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="inbox" class="w-8 h-8 text-gray-300"></i>
                </div>
                <h3 class="text-lg font-black text-gray-800 uppercase tracking-tight">No messages yet</h3>
                <p class="text-sm text-gray-400 font-medium">New inquiries will appear here.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../layout_footer.php'; ?>