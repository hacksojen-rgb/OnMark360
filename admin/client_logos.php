<?php
require_once '../db.php';
require_once '../auth.php';
require_once '../layout_header.php';

// ১. লোগো আপলোড লজিক
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo_file'])) {
    $targetDir = "../uploads/";
    $fileName = "client_" . time() . "_" . basename($_FILES["logo_file"]["name"]);
    if (move_uploaded_file($_FILES["logo_file"]["tmp_name"], $targetDir . $fileName)) {
        $url = "uploads/" . $fileName;
        $pdo->prepare("INSERT INTO client_logos (image_url) VALUES (?)")->execute([$url]);
        header('Location: client_logos.php?success=1'); exit();
    }
}

// ২. লোগো ডিলিট লজিক
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM client_logos WHERE id = ?")->execute([$_GET['delete']]);
    header('Location: client_logos.php'); exit();
}

$logos = $pdo->query("SELECT * FROM client_logos ORDER BY id DESC")->fetchAll();
?>

<div class="max-w-5xl mx-auto pb-24 animate-in fade-in duration-500">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-900 uppercase">Client Logos</h1>
            <p class="text-gray-500 text-xs font-bold uppercase tracking-widest">Manage Trusted Company Logos</p>
        </div>
    </div>

    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm mb-12">
        <h3 class="text-lg font-black text-primary mb-6 uppercase border-b pb-2">Add New Logo</h3>
        <form method="POST" enctype="multipart/form-data" class="flex gap-4 items-end">
            <div class="w-full">
                <label class="text-[10px] font-bold text-gray-400 uppercase">Select Image (PNG/JPG)</label>
                <input type="file" name="logo_file" required class="w-full p-3 bg-gray-50 rounded-xl text-sm border border-gray-200">
            </div>
            <button type="submit" class="bg-primary text-white px-8 py-4 rounded-xl font-bold uppercase text-xs tracking-widest hover:bg-black transition-all">Upload</button>
        </form>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
        <?php foreach($logos as $logo): ?>
        <div class="group relative bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md flex items-center justify-center h-32">
            <img src="../<?php echo $logo['image_url']; ?>" class="max-h-16 max-w-full opacity-60 group-hover:opacity-100 transition-opacity grayscale group-hover:grayscale-0">
            <a href="?delete=<?php echo $logo['id']; ?>" onclick="return confirm('Are you sure?')" 
               class="absolute top-2 right-2 bg-red-100 text-red-500 p-2 rounded-full opacity-0 group-hover:opacity-100 transition-all hover:bg-red-500 hover:text-white">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if(count($logos) == 0): ?>
        <p class="text-center text-gray-400 font-bold uppercase text-sm py-12">No logos uploaded yet.</p>
    <?php endif; ?>
</div>
<?php require_once '../layout_footer.php'; ?>