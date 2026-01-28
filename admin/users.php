<?php
require_once '../db.php';
require_once '../auth.php';
require_once '../layout_header.php';

if (isset($_GET['delete'])) {
    // ১. নিজের আইডি ডিলিট চেক (এটা আপনার আগে থেকেই ছিল)
    if ($_GET['delete'] == $_SESSION['btg_admin_id']) {
        header('Location: users.php?err=self'); exit();
    }

    // ২. 🔥 নতুন সিকিউরিটি কোড: সুপার এডমিন (ID 1) ডিলিট প্রটেকশন 🔥
    if ($_GET['delete'] == 1) {
        // অ্যালার্ট দিয়ে আটকে দেওয়া হবে
        echo "<script>alert('Security Alert: Super Admin cannot be deleted.'); window.location.href='users.php';</script>";
        exit();
    }

    // ৩. ডিলিট কমান্ড
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$_GET['delete']]);
    header('Location: users.php'); exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['id']) {
        if (!empty($_POST['password'])) {
            $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, role=?, password=? WHERE id=?");
            $stmt->execute([$_POST['username'], $_POST['email'], $_POST['role'], $pass, $_POST['id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, role=? WHERE id=?");
            $stmt->execute([$_POST['username'], $_POST['email'], $_POST['role'], $_POST['id']]);
        }
    } else {
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, role, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['username'], $_POST['email'], $_POST['role'], $pass]);
    }
    header('Location: users.php'); exit();
}

$users = $pdo->query("SELECT * FROM users")->fetchAll();
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch();
}
?>

<div class="space-y-8 animate-in fade-in duration-500">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-4xl font-black text-gray-900 uppercase tracking-tighter">System Personnel</h1>
            <p class="text-gray-500 text-sm font-bold uppercase tracking-widest">Administrative Authorization Directory</p>
        </div>
        <a href="users.php?action=new" class="bg-primary text-white px-8 py-3 rounded-2xl font-black uppercase tracking-widest text-xs shadow-xl flex items-center space-x-2">
            <i data-lucide="user-plus" class="w-4 h-4"></i><span>Invite Admin</span>
        </a>
    </div>

    <?php if (isset($_GET['err']) && $_GET['err'] == 'self'): ?>
        <div class="bg-red-500 text-white p-4 rounded-2xl text-xs font-black uppercase tracking-widest text-center">Security Block: Cannot terminate own active session.</div>
    <?php endif; ?>

    <?php if (isset($_GET['action']) || $edit): ?>
    <div class="bg-white p-10 rounded-[3rem] border border-gray-100 shadow-sm max-w-xl mx-auto">
        <h3 class="text-2xl font-black mb-8 uppercase text-primary">Credential Builder</h3>
        <form method="POST" class="space-y-6">
            <input type="hidden" name="id" value="<?php echo $edit['id'] ?? ''; ?>">
            <div class="space-y-2">
                <input type="text" name="username" value="<?php echo $edit['username'] ?? ''; ?>" placeholder="System Nickname" required class="w-full px-6 py-4 rounded-2xl bg-gray-50 outline-none font-bold">
            </div>
            <div class="space-y-2">
                <input type="email" name="email" value="<?php echo $edit['email'] ?? ''; ?>" placeholder="Contact Email" required class="w-full px-6 py-4 rounded-2xl bg-gray-50 outline-none text-sm">
            </div>
            <div class="space-y-2">
                <select name="role" class="w-full px-6 py-4 rounded-2xl bg-gray-50 outline-none text-xs font-black uppercase tracking-widest">
                    <option <?php echo ($edit['role'] ?? '') == 'Administrator' ? 'selected' : ''; ?>>Administrator</option>
                    <option <?php echo ($edit['role'] ?? '') == 'Editor' ? 'selected' : ''; ?>>Editor</option>
                    <option <?php echo ($edit['role'] ?? '') == 'Manager' ? 'selected' : ''; ?>>Manager</option>
                </select>
            </div>
            <div class="space-y-2">
                <input type="password" name="password" placeholder="<?php echo $edit ? 'Leave blank to retain current' : 'Define Security Key'; ?>" <?php echo $edit ? '' : 'required'; ?> class="w-full px-6 py-4 rounded-2xl bg-gray-50 outline-none font-bold">
            </div>
            <div class="flex justify-end gap-4 pt-6">
                <a href="users.php" class="px-8 py-3 font-bold text-gray-400 uppercase text-xs tracking-widest">Cancel</a>
                <button type="submit" class="bg-primary text-white px-10 py-3 rounded-2xl font-black shadow-xl uppercase tracking-widest text-xs">Grant Access</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 pb-20">
        <?php foreach($users as $u): ?>
        <div class="bg-white p-8 rounded-[3rem] border border-gray-100 shadow-sm group relative overflow-hidden">
             <div class="flex items-center space-x-4 mb-8 relative z-10">
                <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center text-primary font-black text-xl">
                   <?php echo strtoupper($u['username'][0]); ?>
                </div>
                <div>
                   <h3 class="font-black text-gray-900 uppercase tracking-tighter flex items-center">
                     <?php echo $u['username']; ?> 
                     <i data-lucide="badge-check" class="ml-2 text-[#4DB6AC] w-4 h-4"></i>
                   </h3>
                   <div class="flex items-center space-x-1 text-gray-400 text-[10px] font-bold uppercase tracking-widest">
                      <i data-lucide="shield" class="w-3 h-3"></i>
                      <span><?php echo $u['role']; ?></span>
                   </div>
                </div>
             </div>
             <div class="space-y-3 mb-8 text-xs font-medium text-gray-500">
                <div class="flex items-center gap-2"><i data-lucide="mail" class="w-4 h-4 text-gray-300"></i> <?php echo $u['email']; ?></div>
                <div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-green-500"></div> Active Status</div>
             </div>
             <div class="flex justify-between items-center border-t border-gray-50 pt-6">
                <div class="flex space-x-2">
                   <a href="users.php?edit=<?php echo $u['id']; ?>" class="text-gray-300 hover:text-primary transition-all"><i data-lucide="edit-2" class="w-4 h-4"></i></a>
                   <a href="users.php?delete=<?php echo $u['id']; ?>" onclick="return confirm('Erase Personnel?')" class="text-gray-300 hover:text-red-500 transition-all"><i data-lucide="trash-2" class="w-4 h-4"></i></a>
                </div>
                <button class="text-[9px] font-black uppercase text-primary underline underline-offset-4 tracking-widest">Secret Reset</button>
             </div>
             <div class="absolute -top-10 -right-10 w-24 h-24 bg-primary/5 rounded-full blur-2xl group-hover:scale-150 transition-all duration-1000"></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once '../layout_footer.php'; ?>