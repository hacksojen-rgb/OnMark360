<?php
require_once '../db.php';
require_once '../auth.php';
require_once '../layout_header.php';

// ১. সিকিউরিটি চেক: লগড ইন কিনা
check_auth();

// ২. অডিট লগ টেবিল অটোমেটিক তৈরি (যদি না থাকে)
$pdo->exec("CREATE TABLE IF NOT EXISTS sql_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    query_text TEXT,
    query_type VARCHAR(20),
    status VARCHAR(20),
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$msg = '';
$msg_type = '';
$results = [];
$query_history = '';

// ৩. নিষিদ্ধ কমান্ড লিস্ট (Cross-DB Protection)
$forbidden_commands = ['USE', 'GRANT', 'REVOKE', 'CREATE USER', 'DROP USER', 'FLUSH PRIVILEGES'];

// ৪. বিপদজনক কমান্ড লিস্ট (সতর্কতার জন্য)
$dangerous_commands = ['DROP', 'TRUNCATE', 'DELETE', 'UPDATE', 'ALTER'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    $raw_sql = trim($_POST['sql_query']);
    $query_history = $raw_sql;
    $username = $_SESSION['btg_admin_user'] ?? 'Unknown';
    $ip = $_SERVER['REMOTE_ADDR'];

    // 🛡️ রুল ১: খালি কুয়েরি আটকানো
    if (empty($raw_sql)) {
        $msg = "Please enter a SQL query.";
        $msg_type = "error";
    } else {
        // SQL আপারকেস করে চেক করা
        $upper_sql = strtoupper($raw_sql);
        $is_blocked = false;

        // 🛡️ রুল ২: নিষিদ্ধ কমান্ড চেক
        foreach ($forbidden_commands as $bad) {
            if (strpos($upper_sql, $bad) !== false) {
                $msg = "🚫 Security Alert: Command '$bad' is blocked in this console.";
                $msg_type = "error";
                $is_blocked = true;
                
                // ফেইল্ড লগ সেভ
                $log = $pdo->prepare("INSERT INTO sql_audit_log (username, query_text, query_type, status, ip_address) VALUES (?, ?, 'BLOCKED', 'FAIL', ?)");
                $log->execute([$username, $raw_sql, $ip]);
                break;
            }
        }

        // 🛡️ রুল ৩: মাল্টিপল কুয়েরি আটকানো (সাধারণত সেমিকোলন দিয়ে আলাদা করা হয়)
        if (!$is_blocked && substr_count($raw_sql, ';') > 1) {
             // সেমিকোলন স্ট্রিংয়ের ভেতরে থাকতে পারে, তাই এটি বেসিক চেক। 
             // অ্যাডভান্সড ইউজের জন্য এটি অফ রাখতে পারেন, তবে নিরাপত্তার জন্য অন রাখা ভালো।
             // $msg = "⚠️ Multiple statements are disabled for safety.";
             // $msg_type = "error";
             // $is_blocked = true;
        }

        if (!$is_blocked) {
            try {
                // কুয়েরি রান করার আগে টাইপ ডিটেক্ট করা
                $query_type = 'SELECT';
                foreach ($dangerous_commands as $danger) {
                    if (strpos($upper_sql, $danger) === 0) {
                        $query_type = $danger;
                        break;
                    }
                }

                // 🔥 অডিট লগ: রান করার আগেই সেভ করা (যাতে ক্র্যাশ করলেও রেকর্ড থাকে)
                $log = $pdo->prepare("INSERT INTO sql_audit_log (username, query_text, query_type, status, ip_address) VALUES (?, ?, ?, 'PENDING', ?)");
                $log->execute([$username, $raw_sql, $query_type, $ip]);
                $log_id = $pdo->lastInsertId();

                // 🚀 কুয়েরি এক্সিকিউশন
                if (strpos($upper_sql, 'SELECT') === 0 || strpos($upper_sql, 'SHOW') === 0 || strpos($upper_sql, 'DESCRIBE') === 0) {
                    $stmt = $pdo->query($raw_sql);
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $count = count($results);
                    $msg = "✅ Query executed successfully. $count rows returned.";
                    $msg_type = "success";
                } else {
                    // Action Query (UPDATE, DELETE, ALTER etc.)
                    $affected = $pdo->exec($raw_sql);
                    $msg = "✅ Query executed successfully. Schema/Data updated.";
                    $msg_type = "success";
                }

                // লগ আপডেট (SUCCESS)
                $pdo->prepare("UPDATE sql_audit_log SET status = 'SUCCESS' WHERE id = ?")->execute([$log_id]);

            } catch (PDOException $e) {
                $msg = "❌ SQL Error: " . $e->getMessage();
                $msg_type = "error";
                
                // লগ আপডেট (ERROR)
                if(isset($log_id)) {
                    $pdo->prepare("UPDATE sql_audit_log SET status = 'ERROR' WHERE id = ?")->execute([$log_id]);
                }
            }
        }
    }
}

// রিসেন্ট লগ ফেচ করা
$logs = $pdo->query("SELECT * FROM sql_audit_log ORDER BY id DESC LIMIT 5")->fetchAll();
?>

<div class="max-w-7xl mx-auto pb-24 animate-in fade-in duration-500">

    <div class="flex space-x-1 bg-gray-100 p-1 rounded-xl mb-8 w-fit mx-auto md:mx-0">
        <a href="sql_tool.php" class="flex items-center gap-2 px-6 py-2.5 bg-white text-[#014034] shadow-sm rounded-lg text-xs font-bold uppercase tracking-widest transition-all">
            <i data-lucide="terminal-square" class="w-4 h-4"></i> SQL Console
        </a>
        <a href="schema_viewer.php" class="flex items-center gap-2 px-6 py-2.5 text-gray-500 hover:text-[#014034] hover:bg-gray-200 rounded-lg text-xs font-bold uppercase tracking-widest transition-all">
            <i data-lucide="table-properties" class="w-4 h-4"></i> Schema Viewer
        </a>
    </div>
    
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-black text-[#014034] uppercase flex items-center gap-3">
                <i data-lucide="terminal-square" class="w-8 h-8"></i> SQL Console
            </h1>
            <p class="text-gray-500 text-xs font-bold uppercase tracking-widest mt-1">
                Super Admin Database Access
            </p>
        </div>
        <div class="flex gap-2">
             <span class="bg-red-100 text-red-600 px-3 py-1 rounded-lg text-xs font-bold uppercase">
                <i data-lucide="shield-alert" class="w-3 h-3 inline mr-1"></i> production mode
             </span>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="mb-6 p-4 rounded-xl border-l-4 font-bold text-sm <?php echo $msg_type === 'success' ? 'bg-green-50 border-green-500 text-green-700' : 'bg-red-50 border-red-500 text-red-700'; ?>">
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white p-1 rounded-[2rem] border border-gray-100 shadow-sm">
                <div class="bg-[#1e1e1e] p-6 rounded-[1.8rem]">
                    <form method="POST" id="sqlForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="flex justify-between items-center mb-4">
                            <label class="text-xs font-bold text-gray-400 uppercase">SQL Command Editor</label>
                            <span class="text-[10px] text-gray-500 font-mono">Connected: futureba_OnMark</span>
                        </div>
                        
                        <textarea name="sql_query" rows="8" 
                            class="w-full p-4 bg-[#2d2d2d] text-green-400 font-mono text-sm rounded-xl outline-none focus:ring-2 ring-green-500/50 border border-transparent placeholder-gray-600 leading-relaxed"
                            placeholder="SELECT * FROM users WHERE id = 1;"><?php echo htmlspecialchars($query_history); ?></textarea>
                        
                        <div class="flex justify-between items-center mt-6">
                            <div class="flex gap-2">
                                <button type="button" onclick="insertTemplate('SELECT * FROM ')" class="px-3 py-1 bg-white/10 text-gray-300 rounded text-[10px] font-bold hover:bg-white/20">SELECT</button>
                                <button type="button" onclick="insertTemplate('ALTER TABLE ')" class="px-3 py-1 bg-white/10 text-gray-300 rounded text-[10px] font-bold hover:bg-white/20">ALTER</button>
                                <button type="button" onclick="insertTemplate('UPDATE ')" class="px-3 py-1 bg-white/10 text-gray-300 rounded text-[10px] font-bold hover:bg-white/20">UPDATE</button>
                            </div>
                            <div class="flex gap-3">
                                <button type="button" onclick="document.querySelector('textarea').value=''" class="px-4 py-2 text-gray-400 font-bold text-xs uppercase hover:text-red-500">Clear</button>
                                <button type="submit" onclick="return confirm('⚠️ Are you sure you want to execute this query? This action cannot be undone.')" class="bg-green-600 text-white px-8 py-3 rounded-xl font-bold uppercase text-xs shadow-lg hover:bg-green-500 transition-all flex items-center gap-2">
                                    <i data-lucide="play" class="w-4 h-4"></i> Execute
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (!empty($results)): ?>
            <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden animate-in slide-in-from-bottom-4">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-700 uppercase text-xs">Query Results</h3>
                    <span class="text-[10px] bg-gray-200 px-2 py-1 rounded font-mono"><?php echo count($results); ?> rows</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-[10px] uppercase tracking-wider">
                                <?php foreach (array_keys($results[0]) as $col): ?>
                                    <th class="p-4 font-black border-b border-gray-200 whitespace-nowrap"><?php echo htmlspecialchars($col); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs font-mono">
                            <?php foreach ($results as $row): ?>
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <?php foreach ($row as $val): ?>
                                    <td class="p-4 text-gray-700 border-r border-gray-50 last:border-0 max-w-xs truncate" title="<?php echo htmlspecialchars($val ?? 'NULL'); ?>">
                                        <?php echo htmlspecialchars($val ?? 'NULL'); ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm sticky top-24">
                <h3 class="text-sm font-black text-[#014034] mb-4 uppercase flex items-center gap-2">
                    <i data-lucide="history" class="w-4 h-4"></i> Audit Trail
                </h3>
                <div class="space-y-4">
                    <?php foreach($logs as $log): ?>
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-[10px] font-bold <?php echo $log['status']=='SUCCESS'?'text-green-600':'text-red-500'; ?> bg-white px-2 py-0.5 rounded border border-gray-100">
                                <?php echo $log['status']; ?>
                            </span>
                            <span class="text-[9px] text-gray-400 font-mono"><?php echo date('H:i:s', strtotime($log['created_at'])); ?></span>
                        </div>
                        <code class="block text-[10px] text-gray-600 font-mono break-all bg-white p-2 rounded border border-gray-100 mb-1">
                            <?php echo htmlspecialchars(substr($log['query_text'], 0, 100)) . (strlen($log['query_text'])>100?'...':''); ?>
                        </code>
                        <div class="flex justify-between items-center text-[9px] text-gray-400 uppercase font-bold">
                            <span>User: <?php echo htmlspecialchars($log['username']); ?></span>
                            <span><?php echo htmlspecialchars($log['query_type']); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function insertTemplate(text) {
    const textarea = document.querySelector('textarea[name="sql_query"]');
    textarea.value = text + textarea.value;
    textarea.focus();
}
</script>

<?php require_once '../layout_footer.php'; ?>