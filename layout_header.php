<?php

require_once 'db.php';
require_once 'auth.php';
check_auth();

$current_page = basename($_SERVER['PHP_SELF']);

function is_active($page, $current) {
    return $page === $current
        ? 'bg-white/10 text-white shadow-lg'
        : 'text-gray-400 hover:text-white hover:bg-white/5';
}
function icon_color($page, $current) {
    return $page === $current ? 'text-[#4DB6AC]' : 'text-gray-500';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Console | OnMark360</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-sidebar { background-color: #012a22; }
        .bg-primary { background-color: #014034; }
        .text-primary { color: #014034; }
    </style>
</head>
<script>
    lucide.createIcons();
</script>
<body class="bg-gray-50 text-gray-900">

<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-72 bg-sidebar fixed h-full z-50 flex flex-col border-r border-white/5">

        <!-- Logo -->
        <div class="p-6 flex items-center space-x-3">
            <div class="bg-[#4DB6AC] p-2 rounded-lg">
                <i data-lucide="rocket" class="w-5 h-5 text-[#012a22]"></i>
            </div>
            <span class="text-white font-black text-xl tracking-tight">
                Master<span class="text-[#4DB6AC]">Admin</span>
            </span>
        </div>

        <!-- Navigation -->
        <nav class="flex-grow px-4 space-y-1 overflow-y-auto pb-10">

            <!-- Overview -->
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= is_active('index.php',$current_page) ?>">
                <i data-lucide="layout-dashboard" class="w-5 h-5 <?= icon_color('index.php',$current_page) ?>"></i>
                <span class="font-semibold text-sm">Overview</span>
            </a>

            <!-- Management -->
            <div class="pt-4 pb-2">
                <span class="text-[10px] uppercase font-bold text-gray-500 px-4 tracking-widest">Management</span>
            </div>

            <?php
            $management = [
                'pages.php' => ['file-text','Pages'],
                'media.php' => ['image','Media Library'],
                'leads.php' => ['inbox','Inquiries'],
            ];
            foreach ($management as $file=>$data):
            ?>
            <a href="<?= $file ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= is_active($file,$current_page) ?>">
                <i data-lucide="<?= $data[0] ?>" class="w-5 h-5 <?= icon_color($file,$current_page) ?>"></i>
                <span class="font-semibold text-sm"><?= $data[1] ?></span>
            </a>
            <?php endforeach; ?>

            <!-- Site Content -->
            <div class="pt-4 pb-2">
                <span class="text-[10px] uppercase font-bold text-gray-500 px-4 tracking-widest">Site Content</span>
            </div>

            <?php
            $content = [
                'site_content.php' => ['layout-template', 'Page Content'],
                'hero.php' => ['layers','Hero Slider'],
                'client_logos.php' => ['gem','Client Logos'],
                'services.php' => ['zap','Services'],
                'portfolio.php' => ['briefcase','Portfolio'],
                'testimonials.php' => ['message-square-quote','Testimonials'],
                'pricing.php' => ['credit-card','Pricing'],
                'manage_blogs.php' => ['pen-tool','Blogs'],
            ];
            foreach ($content as $file=>$data):
            ?>
            <a href="<?= $file ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= is_active($file,$current_page) ?>">
                <i data-lucide="<?= $data[0] ?>" class="w-5 h-5 <?= icon_color($file,$current_page) ?>"></i>
                <span class="font-semibold text-sm"><?= $data[1] ?></span>
            </a>
            <?php endforeach; ?>

            <!-- Configuration -->
            <div class="pt-4 pb-2">
                <span class="text-[10px] uppercase font-bold text-gray-500 px-4 tracking-widest">Configuration</span>
            </div>

            <?php
            $config = [
                'settings.php' => ['settings','Settings'],
                'buttons.php' => ['mouse-pointer-2','Buttons & Links'],
                'sql_tool.php' => ['terminal-square','Database Manager'],
                // 'schema_viewer.php' => ['table-properties','Schema Viewer'],
                'tracking.php' => ['bar-chart-2','Tracking'],
                'event_rules.php' => ['mouse-pointer-click','Event Manager'],
                'backup_system.php' => ['database','Backup System'],
                'users.php' => ['users','Users'],
            ];
            foreach ($config as $file=>$data):
            ?>
            <a href="<?= $file ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= is_active($file,$current_page) ?>">
                <i data-lucide="<?= $data[0] ?>" class="w-5 h-5 <?= icon_color($file,$current_page) ?>"></i>
                <span class="font-semibold text-sm"><?= $data[1] ?></span>
            </a>
            <?php endforeach; ?>

        </nav>

        <!-- Logout -->
        <div class="p-4">
            <a href="logout.php" class="flex items-center justify-center gap-2 bg-white/5 hover:bg-red-500/10 text-gray-400 hover:text-red-400 py-3 rounded-xl transition-all font-bold text-xs uppercase tracking-widest">
                <i data-lucide="log-out" class="w-4 h-4"></i>
                <span>Terminate</span>
            </a>
        </div>

    </aside>

    <!-- Main -->
    <main class="ml-72 flex-grow min-h-screen">

        <header class="bg-white border-b border-gray-200 py-4 px-8 sticky top-0 z-40 flex justify-between items-center">
            <h2 class="text-lg font-bold uppercase tracking-tight">OnMark360 Console</h2>
            <div class="flex items-center gap-4">
                <div class="text-right hidden md:block">
                    <p class="text-sm font-bold"><?= $_SESSION['btg_admin_user']; ?></p>
                    <p class="text-[10px] text-green-500 font-bold uppercase">System Live</p>
                </div>
                <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white font-bold shadow-lg ring-4 ring-primary/10">A</div>
            </div>
        </header>

        <div class="p-8">
