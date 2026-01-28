<?php
require '../layout_header.php';

// Fetch stats
$leadsCount = $pdo->query("SELECT count(*) FROM leads")->fetchColumn();
$newLeads = $pdo->query("SELECT count(*) FROM leads WHERE status = 'New'")->fetchColumn();
$servicesCount = $pdo->query("SELECT count(*) FROM services")->fetchColumn();
$usersCount = $pdo->query("SELECT count(*) FROM users")->fetchColumn();
?>

<div class="space-y-8">
    <div class="flex justify-between items-end">
        <div>
            <h1 class="text-4xl font-black text-gray-900 mb-2 uppercase tracking-tighter">Command Center</h1>
            <p class="text-gray-500">Global system intelligence and conversion funnel.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 group transition-all">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 rounded-2xl bg-[#014034] text-white"><i data-lucide="inbox"></i></div>
                <div class="flex items-center text-green-600 text-xs font-bold bg-green-50 px-2 py-1 rounded-full">+<?php echo $newLeads; ?> new</div>
            </div>
            <h3 class="text-gray-500 text-sm font-medium mb-1 uppercase tracking-tighter">Conversion Funnel</h3>
            <p class="text-3xl font-black text-gray-900"><?php echo $leadsCount; ?></p>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 group transition-all">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 rounded-2xl bg-[#00695c] text-white"><i data-lucide="zap"></i></div>
                <div class="flex items-center text-green-600 text-xs font-bold bg-green-50 px-2 py-1 rounded-full">Active</div>
            </div>
            <h3 class="text-gray-500 text-sm font-medium mb-1 uppercase tracking-tighter">Growth Index</h3>
            <p class="text-3xl font-black text-gray-900">Steady</p>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 group transition-all">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 rounded-2xl bg-[#4DB6AC] text-white"><i data-lucide="activity"></i></div>
            </div>
            <h3 class="text-gray-500 text-sm font-medium mb-1 uppercase tracking-tighter">Catalog Items</h3>
            <p class="text-3xl font-black text-gray-900"><?php echo $servicesCount; ?></p>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 group transition-all">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 rounded-2xl bg-[#012a22] text-white"><i data-lucide="users"></i></div>
            </div>
            <h3 class="text-gray-500 text-sm font-medium mb-1 uppercase tracking-tighter">Admins</h3>
            <p class="text-3xl font-black text-gray-900"><?php echo $usersCount; ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white rounded-[3rem] p-10 border border-gray-100 shadow-sm relative overflow-hidden">
             <div class="flex justify-between items-center mb-10 relative z-10">
                <div>
                   <h3 className="text-2xl font-black text-gray-900 font-bold">Traffic Velocity</h3>
                   <p class="text-gray-400 text-sm font-bold uppercase tracking-tighter">Global acquisition stats</p>
                </div>
                <button class="bg-primary text-white p-3 rounded-2xl"><i data-lucide="arrow-up-right"></i></button>
             </div>
             <div class="h-[280px] flex items-end justify-between px-4 relative z-10">
                <?php for($i=0; $i<12; $i++): $h = rand(30, 100); ?>
                    <div class="w-full mx-1 bg-primary/5 hover:bg-primary transition-all rounded-t-2xl" style="height: <?php echo $h; ?>%"></div>
                <?php endfor; ?>
             </div>
        </div>
        <div class="bg-primary text-white rounded-[3rem] p-10 shadow-2xl flex flex-col justify-between">
            <div>
               <i data-lucide="check-circle" class="w-12 h-12 text-[#4DB6AC] mb-6"></i>
               <h3 class="text-3xl font-black mb-4 leading-tight">System Status: Optimal</h3>
               <p class="text-white/60 text-sm leading-relaxed">Infrastructure is optimized for global content delivery.</p>
            </div>
            <button class="w-full py-4 bg-white/10 hover:bg-white/20 transition-all rounded-2xl font-black text-xs uppercase tracking-[0.2em] mt-8 border border-white/5">Run Diagnostics</button>
        </div>
    </div>
</div>

<?php require 'layout_footer.php'; ?>