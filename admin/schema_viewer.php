<?php
require_once '../db.php';
require_once '../auth.php';
require_once '../layout_header.php';

check_auth();

// ১. সব টেবিলের লিস্ট আনা
$tables = [];
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $error = $e->getMessage();
}

// ২. নির্দিষ্ট টেবিলের স্ট্রাকচার আনা (যদি সিলেক্ট করা থাকে)
$selected_table = $_GET['table'] ?? ($tables[0] ?? null);
$columns = [];
$indexes = [];

if ($selected_table && in_array($selected_table, $tables)) {
    try {
        // কলাম ডিটেইলস
        $stmt = $pdo->query("SHOW FULL COLUMNS FROM `$selected_table`");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ইনডেক্স ডিটেইলস (Primary/Foreign keys)
        $stmt = $pdo->query("SHOW INDEX FROM `$selected_table`");
        $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Error loading table: " . $e->getMessage();
    }
}
?>

<div class="max-w-7xl mx-auto pb-24 animate-in fade-in duration-500">
    
    <div class="flex space-x-1 bg-gray-100 p-1 rounded-xl mb-8 w-fit mx-auto md:mx-0">
        <a href="sql_tool.php" class="flex items-center gap-2 px-6 py-2.5 text-gray-500 hover:text-[#014034] hover:bg-gray-200 rounded-lg text-xs font-bold uppercase tracking-widest transition-all">
            <i data-lucide="terminal-square" class="w-4 h-4"></i> SQL Console
        </a>
        <a href="schema_viewer.php" class="flex items-center gap-2 px-6 py-2.5 bg-white text-[#014034] shadow-sm rounded-lg text-xs font-bold uppercase tracking-widest transition-all">
            <i data-lucide="table-properties" class="w-4 h-4"></i> Schema Viewer
        </a>
    </div>

    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-black text-[#014034] uppercase flex items-center gap-3">
                <i data-lucide="table-properties" class="w-8 h-8"></i> Schema Viewer
            </h1>
            <p class="text-gray-500 text-xs font-bold uppercase tracking-widest mt-1">
                Database Structure Overview
            </p>
        </div>
        <div class="text-right">
            <p class="text-xs font-bold text-gray-400 uppercase">Current Database</p>
            <p class="font-mono text-lg font-bold text-[#014034]"><?php echo $dbname ?? 'futureba_OnMark'; ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm sticky top-24">
                <h3 class="text-sm font-black text-[#014034] mb-4 uppercase flex items-center gap-2 border-b border-gray-100 pb-2">
                    <i data-lucide="list" class="w-4 h-4"></i> Tables (<?php echo count($tables); ?>)
                </h3>
                <div class="space-y-1 max-h-[70vh] overflow-y-auto pr-2 custom-scrollbar">
                    <?php foreach ($tables as $table): ?>
                        <a href="?table=<?php echo $table; ?>" 
                           class="block px-4 py-3 rounded-xl text-xs font-bold uppercase transition-all flex items-center gap-2 
                           <?php echo $selected_table === $table ? 'bg-[#014034] text-white shadow-lg' : 'text-gray-500 hover:bg-gray-50 hover:text-[#014034]'; ?>">
                           <i data-lucide="table" class="w-3 h-3"></i>
                           <?php echo $table; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="lg:col-span-3">
            <?php if ($selected_table): ?>
                
                <div class="flex justify-between items-end mb-6">
                    <h2 class="text-2xl font-black text-gray-800 flex items-center gap-2">
                        <span class="text-gray-400 text-sm font-normal">Table:</span> 
                        <?php echo $selected_table; ?>
                    </h2>
                    <div class="flex gap-2">
                        <a href="sql_tool.php?query=SELECT * FROM `<?php echo $selected_table; ?>`" class="bg-blue-50 text-blue-600 px-4 py-2 rounded-lg text-xs font-bold uppercase hover:bg-blue-100 transition-colors flex items-center gap-2">
                            <i data-lucide="search" class="w-3 h-3"></i> Browse Data
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden mb-8">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-bold text-gray-700 uppercase text-xs flex items-center gap-2">
                            <i data-lucide="columns" class="w-4 h-4"></i> Columns Structure
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-white text-gray-400 text-[10px] uppercase tracking-wider border-b border-gray-100">
                                    <th class="p-4 font-bold">Field</th>
                                    <th class="p-4 font-bold">Type</th>
                                    <th class="p-4 font-bold">Null</th>
                                    <th class="p-4 font-bold">Key</th>
                                    <th class="p-4 font-bold">Default</th>
                                    <th class="p-4 font-bold">Extra</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-xs font-mono">
                                <?php foreach ($columns as $col): ?>
                                <tr class="hover:bg-blue-50/30 transition-colors">
                                    <td class="p-4 font-bold text-gray-800"><?php echo $col['Field']; ?></td>
                                    <td class="p-4 text-blue-600"><?php echo $col['Type']; ?></td>
                                    <td class="p-4 <?php echo $col['Null']=='YES'?'text-gray-400':'text-gray-800 font-bold'; ?>">
                                        <?php echo $col['Null']; ?>
                                    </td>
                                    <td class="p-4">
                                        <?php if($col['Key'] == 'PRI'): ?>
                                            <span class="text-[10px] bg-yellow-100 text-yellow-700 px-2 py-1 rounded font-bold">PRIMARY</span>
                                        <?php elseif($col['Key'] == 'UNI'): ?>
                                            <span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-1 rounded font-bold">UNIQUE</span>
                                        <?php elseif($col['Key'] == 'MUL'): ?>
                                            <span class="text-[10px] bg-gray-100 text-gray-600 px-2 py-1 rounded font-bold">INDEX</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-gray-500"><?php echo $col['Default'] === null ? 'NULL' : $col['Default']; ?></td>
                                    <td class="p-4 text-gray-400 italic"><?php echo $col['Extra']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if(!empty($indexes)): ?>
                <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-bold text-gray-700 uppercase text-xs flex items-center gap-2">
                            <i data-lucide="key" class="w-4 h-4"></i> Indexes & Keys
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-white text-gray-400 text-[10px] uppercase tracking-wider border-b border-gray-100">
                                    <th class="p-4 font-bold">Key Name</th>
                                    <th class="p-4 font-bold">Column</th>
                                    <th class="p-4 font-bold">Unique</th>
                                    <th class="p-4 font-bold">Type</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-xs font-mono">
                                <?php foreach ($indexes as $idx): ?>
                                <tr class="hover:bg-yellow-50/30 transition-colors">
                                    <td class="p-4 font-bold text-gray-800"><?php echo $idx['Key_name']; ?></td>
                                    <td class="p-4 text-gray-600"><?php echo $idx['Column_name']; ?></td>
                                    <td class="p-4">
                                        <?php echo $idx['Non_unique'] == 0 ? '<span class="text-green-600 font-bold">YES</span>' : '<span class="text-gray-400">NO</span>'; ?>
                                    </td>
                                    <td class="p-4 text-gray-500"><?php echo $idx['Index_type']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="bg-white p-12 rounded-[2rem] border border-gray-100 text-center">
                    <div class="inline-flex p-4 bg-gray-50 rounded-full mb-4">
                        <i data-lucide="database" class="w-8 h-8 text-gray-300"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-400">Select a table to view structure</h3>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../layout_footer.php'; ?>