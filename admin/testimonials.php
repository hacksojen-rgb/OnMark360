<?php
require_once '../db.php';
require_once '../auth.php';
require_once '../layout_header.php';

// ১. নতুন টেস্টিমোনিয়াল সেভ করা (আপডেটেড)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_testimonial'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $avatarUrl = null;

    // ক. ফাইল আপলোড চেক
    if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === 0) {
        $targetDir = "../uploads/";
        $fileName = "review_" . time() . "_" . basename($_FILES["avatar_file"]["name"]);
        if (move_uploaded_file($_FILES["avatar_file"]["tmp_name"], $targetDir . $fileName)) {
            $avatarUrl = "uploads/" . $fileName;
        }
    }
    // খ. টেক্সট লিংক / মিডিয়া চেক
    elseif (!empty($_POST['avatar_url_text'])) {
        $avatarUrl = $_POST['avatar_url_text'];
    }

    // ডাটাবেজ ইনসার্ট (যদি ছবি ছাড়াও এলাউ করতে চান তবে if শর্ত সরাতে পারেন)
    $stmt = $pdo->prepare("INSERT INTO testimonials (name, role, company, content, avatar_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['name'], 
        $_POST['role'], 
        $_POST['company'], 
        $_POST['content'], 
        $avatarUrl
    ]);
    header('Location: testimonials.php?success=1'); exit();
}

// ২. ডিলিট লজিক
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM testimonials WHERE id = ?")->execute([$_GET['delete']]);
    header('Location: testimonials.php'); exit();
}

$reviews = $pdo->query("SELECT * FROM testimonials ORDER BY id DESC")->fetchAll();
?>

<div class="max-w-6xl mx-auto pb-24 animate-in fade-in duration-500">
    <div class="flex justify-between items-center mb-10">
        <div>
            <h1 class="text-4xl font-black text-gray-900 uppercase tracking-tighter">Testimonials</h1>
            <p class="text-gray-500 text-sm font-bold uppercase tracking-widest">Manage Client Feedback</p>
        </div>
    </div>

    <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm mb-12">
        <h3 class="text-lg font-black text-primary mb-6 uppercase border-b pb-2">Add New Review</h3>
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="add_testimonial" value="1">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase ml-1">Client Name</label>
                    <input type="text" name="name" required class="w-full p-4 bg-gray-50 rounded-2xl text-sm font-bold border-none focus:ring-2 focus:ring-primary/20">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase ml-1">Role (e.g. CEO)</label>
                    <input type="text" name="role" required class="w-full p-4 bg-gray-50 rounded-2xl text-sm font-bold border-none focus:ring-2 focus:ring-primary/20">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase ml-1">Company Name</label>
                    <input type="text" name="company" required class="w-full p-4 bg-gray-50 rounded-2xl text-sm font-bold border-none focus:ring-2 focus:ring-primary/20">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase ml-1">Review Content</label>
                    <textarea name="content" rows="5" required class="w-full p-4 bg-gray-50 rounded-2xl text-sm font-medium border-none focus:ring-2 focus:ring-primary/20"></textarea>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase ml-1">Option 1: Upload Photo</label>
                        <input type="file" name="avatar_file" class="w-full p-3 bg-gray-50 rounded-2xl text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary file:text-white hover:file:bg-black transition-all">
                    </div>
                    
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase ml-1">Option 2: Media / Link</label>
                        <div class="flex gap-2">
                            <input type="text" id="testimonial_img_input" name="avatar_url_text" placeholder="Image URL" class="w-full p-4 bg-gray-50 rounded-2xl text-sm font-bold border-none focus:ring-2 focus:ring-primary/20">
                            <button type="button" onclick="openMediaManager('testimonial_img_input')" class="bg-gray-200 text-gray-700 px-6 rounded-2xl font-bold text-xs hover:bg-gray-300">Media</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-primary text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest shadow-lg hover:scale-105 transition-transform">
                    Publish Review
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach($reviews as $rev): ?>
        <div class="relative bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-md transition-all">
            <div class="absolute top-8 right-8 text-primary/10">
                <i data-lucide="quote" class="w-12 h-12"></i>
            </div>
            
            <p class="text-gray-600 mb-8 italic text-sm leading-relaxed min-h-[80px]">"<?php echo $rev['content']; ?>"</p>
            
            <div class="flex items-center gap-4">
                <?php 
                    $avatar = $rev['avatar_url'];
                    if ($avatar && strpos($avatar, 'http') !== 0) {
                        $avatar = '../' . $avatar;
                    }
                    if (!$avatar) $avatar = 'https://placehold.co/100x100?text=User';
                ?>
                <img src="<?php echo $avatar; ?>" class="w-12 h-12 rounded-full object-cover bg-gray-100">
                <div>
                    <h4 class="font-bold text-gray-900 text-sm"><?php echo $rev['name']; ?></h4>
                    <p class="text-[10px] text-primary font-black uppercase"><?php echo $rev['role']; ?>, <?php echo $rev['company']; ?></p>
                </div>
            </div>

            <a href="?delete=<?php echo $rev['id']; ?>" onclick="return confirm('Delete this review?')" 
               class="absolute top-4 right-4 bg-red-50 text-red-500 p-2 rounded-xl hover:bg-red-500 hover:text-white transition-all">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require_once '../layout_footer.php'; ?>