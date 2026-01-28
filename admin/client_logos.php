<?php
require_once '../db.php';
require_once '../auth.php';
require_once '../layout_header.php';

// ==============================
// ১. লোগো আপলোড এবং সেভ লজিক (Validated)
// ==============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_logo'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $finalUrl = null;

    // ক. যদি নতুন ফাইল আপলোড করা হয়
    if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === 0) {

        // 🔒 File validation
        $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if (!in_array($_FILES['logo_file']['type'], $allowedTypes)) {
            die('Invalid file type. Only PNG and JPG allowed.');
        }

        if ($_FILES['logo_file']['size'] > $maxSize) {
            die('File size too large. Max 2MB allowed.');
        }

        $targetDir = "../uploads/";
        $fileName = "client_" . time() . "_" . basename($_FILES["logo_file"]["name"]);

        if (move_uploaded_file($_FILES["logo_file"]["tmp_name"], $targetDir . $fileName)) {
            $finalUrl = "uploads/" . $fileName;
        }

    }
    // খ. যদি ফাইল না থাকে, কিন্তু টেক্সট লিংক দেওয়া থাকে (Media / External)
    elseif (!empty($_POST['logo_url_text'])) {
        $finalUrl = trim($_POST['logo_url_text']);
    }

    // ডাটাবেজে সেভ
    if ($finalUrl) {
        $pdo->prepare("INSERT INTO client_logos (image_url) VALUES (?)")
            ->execute([$finalUrl]);

        header('Location: client_logos.php?success=1');
        exit();
    }
}

// ==============================
// ২. লোগো ডিলিট লজিক (with unlink)
// ==============================
if (isset($_GET['delete'])) {

    // আগে image_url বের করি
    $stmt = $pdo->prepare("SELECT image_url FROM client_logos WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $imageUrl = $stmt->fetchColumn();

    // যদি local upload হয়, তাহলে ফাইল ডিলিট
    if ($imageUrl && strpos($imageUrl, 'uploads/') === 0) {
        $filePath = "../" . $imageUrl;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // DB row delete
    $pdo->prepare("DELETE FROM client_logos WHERE id = ?")
        ->execute([$_GET['delete']]);

    header('Location: client_logos.php');
    exit();
}

// ==============================
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

        <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="add_logo" value="1">

            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase block mb-2">
                    Option 1: Upload File (PNG/JPG, max 2MB)
                </label>
                <input type="file" name="logo_file"
                       class="w-full p-3 bg-gray-50 rounded-xl text-sm border border-gray-200">
            </div>

            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase block mb-2">
                    Option 2: Media / Link
                </label>
                <div class="flex gap-2">
                    <input type="text" id="client_logo_input" name="logo_url_text"
                           placeholder="Image URL or Select from Media"
                           class="w-full p-3 bg-gray-50 rounded-xl text-sm border border-gray-200">
                    <button type="button"
                            onclick="openMediaManager('client_logo_input')"
                            class="bg-gray-200 text-gray-700 px-4 py-3 rounded-xl font-bold text-xs hover:bg-gray-300">
                        Media
                    </button>
                </div>
            </div>

            <div class="md:col-span-2">
                <button type="submit"
                        class="bg-primary text-white px-8 py-4 rounded-xl font-bold uppercase text-xs tracking-widest hover:bg-black transition-all w-full md:w-auto">
                    Save Logo
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
        <?php foreach ($logos as $logo): ?>
            <div class="group relative bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md flex items-center justify-center h-32">
                <?php
                $imgSrc = (strpos($logo['image_url'], 'http') === 0)
                    ? $logo['image_url']
                    : '../' . $logo['image_url'];
                ?>
                <img src="<?php echo $imgSrc; ?>"
                     class="max-h-16 max-w-full opacity-60 group-hover:opacity-100 transition-opacity grayscale group-hover:grayscale-0">

                <a href="?delete=<?php echo $logo['id']; ?>"
                   onclick="return confirm('Are you sure?')"
                   class="absolute top-2 right-2 bg-red-100 text-red-500 p-2 rounded-full opacity-0 group-hover:opacity-100 transition-all hover:bg-red-500 hover:text-white">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (count($logos) == 0): ?>
        <p class="text-center text-gray-400 font-bold uppercase text-sm py-12">
            No logos uploaded yet.
        </p>
    <?php endif; ?>
</div>

<?php require_once '../layout_footer.php'; ?>
