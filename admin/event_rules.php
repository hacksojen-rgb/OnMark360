<?php
// 1. ERROR REPORTING ON (যাতে সাদা পেজ না আসে, এরর দেখা যায়)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. SMART FILE INCLUSION (ফাইল যেখানেই থাক, খুঁজে নেবে)
$header_found = false;

// চেক ১: একই ফোল্ডারে আছে কি না?
if (file_exists(__DIR__ . '/layout_header.php')) {
    require_once __DIR__ . '/layout_header.php';
    $header_found = true;
} 
// চেক ২: এক ফোল্ডার পেছনে আছে কি না?
elseif (file_exists(__DIR__ . '/../layout_header.php')) {
    require_once __DIR__ . '/../layout_header.php';
    $header_found = true;
} 

if (!$header_found) {
    die("<div style='color:red; padding:20px; font-weight:bold;'>Error: layout_header.php ফাইলটি খুঁজে পাওয়া যাচ্ছে না। দয়া করে পাথ চেক করুন।</div>");
}

// 3. DATABASE CONNECTION CHECK
// যদি হেডার থেকে $pdo কানেকশন না আসে, তবে ম্যানুয়ালি কানেক্ট করার চেষ্টা
if (!isset($pdo)) {
    if (file_exists(__DIR__ . '/../db.php')) {
        require_once __DIR__ . '/../db.php';
    } elseif (file_exists(__DIR__ . '/db.php')) {
        require_once __DIR__ . '/db.php';
    }
}

if (!isset($pdo)) {
    die("<div style='color:red; padding:20px; font-weight:bold;'>Error: Database connection ($pdo) মিসিং। db.php ফাইলটি লোড হচ্ছে না।</div>");
}

// --- 4. Handle Form Submission (Add/Delete/Toggle) ---
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ADD NEW RULE
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $selector = isset($_POST['selector']) ? trim($_POST['selector']) : '';
        $event_type = isset($_POST['event_type']) ? trim($_POST['event_type']) : 'click';
        $event_name = isset($_POST['event_name']) ? trim($_POST['event_name']) : '';
        $parameters = isset($_POST['parameters']) ? trim($_POST['parameters']) : '{}';
        
        if (empty($selector) || empty($event_name)) {
            $error = "Selector and Event Name are required.";
        } else {
            // Validate JSON
            if (!empty($parameters)) {
                json_decode($parameters);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $error = "Invalid JSON format in parameters.";
                }
            } else {
                $parameters = '{}';
            }

            if (empty($error)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO custom_event_rules (selector, event_type, event_name, parameters, status) VALUES (?, ?, ?, ?, 'Active')");
                    $stmt->execute([$selector, $event_type, $event_name, $parameters]);
                    $message = "New event rule added successfully!";
                } catch (PDOException $e) {
                    $error = "Database Error: " . $e->getMessage();
                }
            }
        }
    }

    // DELETE RULE
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['rule_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM custom_event_rules WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Rule deleted successfully.";
        } catch (PDOException $e) {
            $error = "Error deleting rule.";
        }
    }

    // TOGGLE STATUS
    if (isset($_POST['action']) && $_POST['action'] === 'toggle') {
        $id = $_POST['rule_id'];
        $current_status = $_POST['current_status'];
        $new_status = ($current_status === 'Active') ? 'Inactive' : 'Active';
        
        try {
            $stmt = $pdo->prepare("UPDATE custom_event_rules SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $id]);
            $message = "Status updated.";
        } catch (PDOException $e) {
            $error = "Error updating status.";
        }
    }
}

// --- 5. Fetch All Rules ---
try {
    // টেবিল না থাকলে এরর হ্যান্ডেল করা
    $stmt = $pdo->query("SHOW TABLES LIKE 'custom_event_rules'");
    if ($stmt->rowCount() == 0) {
        $error = "Error: 'custom_event_rules' টেবিলটি ডাটাবেজে নেই। দয়া করে phpMyAdmin থেকে টেবিলটি তৈরি করুন।";
        $rules = [];
    } else {
        $stmt = $pdo->query("SELECT * FROM custom_event_rules ORDER BY id DESC");
        $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error = "Error fetching rules: " . $e->getMessage();
    $rules = [];
}
?>

<div class="max-w-7xl mx-auto space-y-8">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Event Manager</h1>
            <p class="text-gray-500 mt-1">Configure click tracking without coding.</p>
        </div>
        <?php if ($message): ?>
            <div class="bg-green-100 text-green-700 px-4 py-2 rounded-lg font-medium text-sm">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 px-4 py-2 rounded-lg font-medium text-sm">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-5 h-5 text-primary"></i>
                    Add New Trigger
                </h3>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="add">
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">CSS Selector</label>
                        <input type="text" name="selector" placeholder=".btn-book-now" required 
                               class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 text-sm font-mono text-blue-600">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Event Trigger</label>
                        <select name="event_type" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm">
                            <option value="click">Click Element</option>
                            <option value="submit">Form Submit</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Pixel Event Name</label>
                        <input type="text" name="event_name" list="standard_events" placeholder="Lead" required 
                               class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg font-bold text-gray-800 text-sm">
                        <datalist id="standard_events">
                            <option value="Lead">
                            <option value="Purchase">
                            <option value="AddToCart">
                            <option value="Contact">
                        </datalist>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Custom Data (JSON)</label>
                        <textarea name="parameters" rows="3" placeholder='{"value": 100}'
                                  class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-mono"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-[#014034] hover:bg-[#012a22] text-white font-bold py-3 rounded-xl transition-all shadow-lg flex justify-center items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Save Rule
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">Active Triggers</h3>
                    <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2 py-1 rounded-md"><?php echo isset($rules) ? count($rules) : 0; ?> Rules</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="p-4 font-bold">Selector</th>
                                <th class="p-4 font-bold">Event</th>
                                <th class="p-4 font-bold">Status</th>
                                <th class="p-4 text-right font-bold">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <?php if (empty($rules)): ?>
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-gray-400">No tracking rules found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($rules as $rule): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="p-4 font-mono text-blue-600 font-medium"><?php echo htmlspecialchars($rule['selector']); ?></td>
                                        <td class="p-4 font-bold"><?php echo htmlspecialchars($rule['event_name']); ?></td>
                                        <td class="p-4">
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="action" value="toggle">
                                                <input type="hidden" name="rule_id" value="<?php echo $rule['id']; ?>">
                                                <input type="hidden" name="current_status" value="<?php echo $rule['status']; ?>">
                                                <button type="submit" class="text-xs font-bold px-3 py-1 rounded-full <?php echo $rule['status'] === 'Active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'; ?>">
                                                    <?php echo $rule['status']; ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="p-4 text-right">
                                            <form method="POST" onsubmit="return confirm('Delete this rule?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="rule_id" value="<?php echo $rule['id']; ?>">
                                                <button type="submit" class="text-gray-400 hover:text-red-500 p-2">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
if (file_exists(__DIR__ . '/layout_footer.php')) {
    require_once __DIR__ . '/layout_footer.php';
} elseif (file_exists(__DIR__ . '/../layout_footer.php')) {
    require_once __DIR__ . '/../layout_footer.php';
}
?>