<?php
require '../db.php';
require '../auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    if (login($_POST['username'], $_POST['password'], $pdo)) {
        header('Location: index.php');
        exit();
    } else {
        $error = 'Authorization failed. Please check credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authorize | On Mark</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#012a22] min-h-screen flex items-center justify-center p-6 relative overflow-hidden">
    <!-- Decor -->
    <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] bg-[#014034] rounded-full blur-[120px] opacity-50"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[400px] h-[400px] bg-[#4DB6AC] rounded-full blur-[100px] opacity-20"></div>

    <div class="w-full max-w-md relative z-10">
        <div class="bg-white/10 backdrop-blur-xl p-10 rounded-[3rem] border border-white/10 shadow-2xl">
            <div class="text-center mb-10">
                <div class="inline-flex bg-[#4DB6AC] p-4 rounded-3xl shadow-lg mb-6">
                    <i data-lucide="rocket" class="w-8 h-8 text-[#012a22]"></i>
                </div>
                <h1 class="text-3xl font-black text-white uppercase tracking-tighter">On Mark</h1>
                <p class="text-white/50 text-sm font-bold uppercase tracking-widest mt-2">Master Admin Portal</p>
            </div>

            <form method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-white/40 uppercase tracking-[0.2em] ml-2">System User</label>
                    <div class="relative">
                        <i data-lucide="user" class="absolute left-5 top-1/2 -translate-y-1/2 text-white/30 w-5 h-5"></i>
                        <input type="text" name="username" required class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 pl-14 pr-6 text-white outline-none focus:border-[#4DB6AC] transition-all" placeholder="admin">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-white/40 uppercase tracking-[0.2em] ml-2">Security Key</label>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-5 top-1/2 -translate-y-1/2 text-white/30 w-5 h-5"></i>
                        <input type="password" name="password" required class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 pl-14 pr-6 text-white outline-none focus:border-[#4DB6AC] transition-all" placeholder="••••••••">
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="p-4 bg-red-500/10 border border-red-500/20 text-red-400 text-xs font-bold rounded-xl flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <button type="submit" class="w-full bg-[#4DB6AC] text-[#012a22] py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-white transition-all shadow-xl">Authorize Access</button>
            </form>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>