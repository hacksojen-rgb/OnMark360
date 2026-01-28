<?php
require_once '../db.php';
require_once '../auth.php';
require_once '../layout_header.php';

// =======================
// Image Upload Function
// =======================
function uploadPortfolioImg($file) {
    if (isset($file) && $file['error'] === 0) {
        $targetDir = "../uploads/";
        $fileName = "p_" . time() . "_" . basename($file["name"]);
        if (move_uploaded_file($file["tmp_name"], $targetDir . $fileName)) {
            return "uploads/" . $fileName;
        }
    }
    return null;
}

// =======================
// Edit Fetch
// =======================
$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM portfolio WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editing = $stmt->fetch();
}

// =======================
// Delete Logic
// =======================
// =======================
// Delete Logic (with image unlink)
// =======================
if (isset($_GET['delete'])) {

    // 1️⃣ image_url বের করি
    $stmt = $pdo->prepare("SELECT image_url FROM portfolio WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $img = $stmt->fetchColumn();

    // 2️⃣ শুধু uploads এর image হলে delete
    if ($img && strpos($img, 'uploads/') === 0) {
        $filePath = '../' . $img;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // 3️⃣ DB row delete
    $stmt = $pdo->prepare("DELETE FROM portfolio WHERE id = ?");
    $stmt->execute([$_GET['delete']]);

    header('Location: portfolio.php?deleted=1');
    exit();
}


// =======================
// Save / Update Logic
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $uploaded  = uploadPortfolioImg($_FILES['p_file']);
    $final_img = $uploaded 
    ?: ($_POST['p_link'] ?? '') 
    ?: ($_POST['old_img'] ?? null);

    if (!empty($_POST['id'])) {
        // UPDATE QUERY (Added video_url)
        $stmt = $pdo->prepare("
            UPDATE portfolio 
            SET title=?, category=?, subcategory=?, video_url=?, image_url=?, client=?, content=? 
            WHERE id=?
        ");
        $stmt->execute([
            $_POST['title'],
            $_POST['category'],
            $_POST['subcategory'],
            $_POST['video_url'], // ✅ New Field
            $final_img,
            $_POST['client'],
            $_POST['content'],
            $_POST['id']
        ]);

    } else {
        // INSERT QUERY (Added video_url)
        $stmt = $pdo->prepare("
            INSERT INTO portfolio (title, category, subcategory, video_url, image_url, client, content) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['title'],
            $_POST['category'],
            $_POST['subcategory'],
            $_POST['video_url'], // ✅ New Field
            $final_img,
            $_POST['client'],
            $_POST['content']
        ]);
    }

    header('Location: portfolio.php?success=1');
    exit();
}

// =======================
// Fetch Data
// =======================
$projects = $pdo->query("SELECT * FROM portfolio ORDER BY id DESC")->fetchAll();
$services = $pdo->query("SELECT title FROM services ORDER BY title ASC")->fetchAll();
?>

<div class="max-w-6xl mx-auto space-y-10 pb-20">

    <div class="bg-white p-8 rounded-[2rem] shadow-sm border">
        <h2 class="text-2xl font-bold text-primary mb-6 uppercase">
            <?php echo $editing ? 'Edit' : 'New'; ?> Project
        </h2>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="id" value="<?php echo $editing['id'] ?? ''; ?>">
            <input type="hidden" name="old_img" value="<?php echo $editing['image_url'] ?? ''; ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="title" required placeholder="Title"
                    value="<?php echo $editing['title'] ?? ''; ?>"
                    class="w-full p-3 bg-gray-50 rounded-xl border outline-none font-bold">

                <input type="text" name="client" required placeholder="Client Name"
                    value="<?php echo $editing['client'] ?? ''; ?>"
                    class="w-full p-3 bg-gray-50 rounded-xl border outline-none">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Main Service</label>
                    <select name="category" required
                        class="w-full p-3 bg-gray-50 rounded-xl border outline-none font-bold text-sm">
                        <option value="">Select Service</option>
                        <?php foreach($services as $s): ?>
                            <option value="<?= htmlspecialchars($s['title']) ?>"
                                <?= (isset($editing['category']) && $editing['category'] == $s['title']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
           
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase">
                        Portfolio Label (Filter Name)
                    </label>
                    <input type="text" name="subcategory"
                           placeholder="e.g. Video Editing"
                           value="<?= $editing['subcategory'] ?? '' ?>"
                           class="w-full p-3 bg-gray-50 rounded-xl border outline-none font-bold text-sm">
                </div>

                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase text-blue-500">
                        Video Link (Optional)
                    </label>
                    <input type="text" name="video_url"
                           placeholder="YouTube / Vimeo / Drive Link"
                           value="<?= $editing['video_url'] ?? '' ?>"
                           class="w-full p-3 bg-blue-50 rounded-xl border border-blue-100 outline-none text-blue-600 font-bold text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex gap-2 items-end">
                    <div class="w-1/2">
                        <span class="text-[10px] font-bold text-gray-400 block mb-1 uppercase tracking-widest">
                            Upload New
                        </span>
                        <input type="file" name="p_file" class="text-xs w-full p-2 bg-white rounded-lg border">
                    </div>
                    <div class="w-1/2 flex flex-col">
                        <span class="text-[10px] font-bold text-gray-400 block mb-1 uppercase tracking-widest">
                            Select From Media
                        </span>
                        <button type="button" onclick="openMediaManager('portfolio_image_path')"
                            class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg text-xs font-bold hover:bg-gray-100 transition-all">
                            Media Library
                        </button>
                    </div>
                </div>
               
                <div>
                    <span class="text-[10px] font-bold text-gray-400 block mb-1 uppercase tracking-widest">
                        Image Path
                    </span>
                    <input type="text" id="portfolio_image_path" name="p_link"
                        placeholder="Or Image URL"
                        value="<?php echo $editing['image_url'] ?? ''; ?>"
                        class="w-full p-2 rounded-lg border text-xs outline-none bg-white">
                </div>
            </div>

            <textarea name="content" rows="3" placeholder="Description"
                class="w-full p-3 bg-gray-50 rounded-xl border outline-none text-sm"><?php echo $editing['content'] ?? ''; ?></textarea>

            <button type="submit"
                class="bg-[#014034] text-white px-8 py-3 rounded-xl font-bold uppercase shadow-lg hover:shadow-xl transition-all w-full md:w-auto">
                Save Project
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach ($projects as $p): ?>
            <div class="bg-white p-4 rounded-3xl border shadow-sm group hover:shadow-md transition-all">
                <div class="relative">
                    <?php 
                        // থাম্বনেইল লজিক
                        $thumb = '';
                        // ১. ইমেজ থাকলে সেটা সেট করো
                        if (!empty($p['image_url'])) {
                            $thumb = (strpos($p['image_url'], 'http') === 0) ? $p['image_url'] : '../' . $p['image_url'];
                        } 
                        // ২. ইমেজ না থাকলে এবং ভিডিও থাকলে ইউটিউব থাম্বনেইল বানাও
                        elseif (!empty($p['video_url'])) {
                            if (preg_match('/(?:v=|\/)([0-9A-Za-z_-]{11})/', $p['video_url'], $matches)) {
                                $thumb = "https://img.youtube.com/vi/" . $matches[1] . "/hqdefault.jpg";
                            } else {
                                // ভিডিও আছে কিন্তু ইউটিউব না (প্লেসহোল্ডার)
                                $thumb = "https://placehold.co/600x400/014034/FFF?text=Video";
                            }
                        } 
                        // ৩. কিছুই না থাকলে ডিফল্ট
                        else {
                            $thumb = "https://placehold.co/600x400?text=No+Image";
                        }
                    ?>
                    <img src="<?php echo $thumb; ?>" class="w-full h-40 object-cover rounded-2xl mb-4 border border-gray-100">
                    
                    <?php if(!empty($p['video_url'])): ?>
                    <div class="absolute top-2 right-2 bg-red-600 text-white p-1.5 rounded-full shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                    </div>
                    <?php endif; ?>
                </div>

                <h3 class="font-bold text-sm uppercase truncate">
                    <?php echo htmlspecialchars($p['title']); ?>
                </h3>

                <p class="text-[10px] text-gray-500 mt-1 uppercase tracking-wider font-bold">
                    <?php echo htmlspecialchars($p['category']); ?> 
                    <?php if($p['subcategory']) echo ' / ' . htmlspecialchars($p['subcategory']); ?>
                </p>

                <div class="flex justify-end gap-3 mt-4 border-t pt-3">
                    <a href="portfolio.php?edit=<?php echo $p['id']; ?>"
                        class="text-blue-600 text-xs font-bold hover:underline">Edit</a>

                    <a href="portfolio.php?delete=<?php echo $p['id']; ?>"
                        onclick="return confirm('Are you sure you want to delete this project?')"
                        class="text-red-600 text-xs font-bold hover:underline">Delete</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<?php require_once '../layout_footer.php'; ?>