<?php
// owner/edit-shop.php - Edit Business Profile
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../backend/config.php';
require_once __DIR__ . '/layout.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: ../login.php");
    exit();
}

$owner_id = (int) $_SESSION['user_id'];
$shop_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Fetch existing metadata state safely
$shop_stmt = $conn->prepare("SELECT * FROM businesses WHERE id = ? AND owner_id = ?");
$shop_stmt->bind_param("ii", $shop_id, $owner_id);
$shop_stmt->execute();
$shop = $shop_stmt->get_result()->fetch_assoc();
$shop_stmt->close();

if (!$shop) {
    header("Location: dashboard.php");
    exit();
}

$categories_res = amraj_query($conn, "SELECT * FROM categories ORDER BY name ASC");

// Handle form processing override
if (isset($_POST['update_shop'])) {
    if (!amraj_validate_csrf($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Invalid form submission.';
        header('Location: dashboard.php'); 
        exit();
    }
    
    $title = trim((string)($_POST['title'] ?? ''));
    $category_id = intval($_POST['category_id']);
    $phone = trim((string)($_POST['phone'] ?? ''));
    $whatsapp = trim((string)($_POST['whatsapp'] ?? ''));
    $city = trim((string)($_POST['city'] ?? ''));
    $pincode = trim((string)($_POST['pincode'] ?? ''));
    $address = trim((string)($_POST['address'] ?? ''));
    $timing = trim((string)($_POST['timing'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    
    $image_url = $shop['image_url']; // Preserve legacy image state

    if (isset($_FILES['shop_image']) && $_FILES['shop_image']['error'] == 0) {
        $upload_dir = dirname(__DIR__) . '/uploads/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }
        if (amraj_is_valid_upload($_FILES['shop_image'])) {
            $uploaded_name = amraj_local_upload_image($_FILES['shop_image'], $upload_dir);
            if ($uploaded_name !== false) {
                $image_url = 'uploads/' . $uploaded_name;
            } else {
                // Try ImageKit fallback
                $uploaded_name = amraj_get_unique_filename($_FILES['shop_image']['name'], 'shop_');
                $remote_url = uploadToImageKit($_FILES['shop_image']['tmp_name'], $uploaded_name);
                if ($remote_url) {
                    $image_url = $remote_url;
                } else {
                    $_SESSION['error'] = 'Image must be a valid image and compressible to 500KB or less.';
                    header('Location: edit-shop.php?id=' . $shop_id);
                    exit();
                }
            }
        } else {
            $_SESSION['error'] = 'Invalid image upload.';
            header('Location: edit-shop.php?id=' . $shop_id);
            exit();
        }
    }

    // Set listing state back to 'pending' upon edit to enforce validation review cycle
    $update_stmt = $conn->prepare("UPDATE businesses SET category_id=?, title=?, description=?, address=?, city=?, pincode=?, phone=?, whatsapp=?, timing=?, image_url=?, status='pending' WHERE id=? AND owner_id=?");
    $update_stmt->bind_param("isssssssssii", $category_id, $title, $description, $address, $city, $pincode, $phone, $whatsapp, $timing, $image_url, $shop_id, $owner_id);
    if ($update_stmt->execute()) {
        header("Location: dashboard.php?msg=updated");
        exit();
    }
    $update_stmt->close();
}

ob_start();
?>

<div class="max-w-4xl mx-auto w-full">
    <!-- Header Section -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-3">
        <div>
            <p class="text-[10px] uppercase tracking-widest text-stone-400 dark:text-stone-500 font-bold">Owner Management</p>
            <h1 class="mt-1 text-2xl sm:text-3xl font-black text-stone-900 dark:text-white">Edit <span class="text-olive-500">Business Profile</span></h1>
            <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">Modifying your establishment profile will place your listings back into pending verification mode.</p>
        </div>
        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 text-xs font-semibold text-stone-600 dark:text-stone-300 shadow-sm self-start sm:self-auto shrink-0">
            <i class="fas fa-hourglass-half text-amber-500"></i> Re-verification Required
        </span>
    </div>

    <!-- Form Container -->
    <div class="rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm p-5 sm:p-8">
        <form id="editBusinessForm" action="" method="POST" enctype="multipart/form-data" class="space-y-4">
            <?php echo amraj_csrf_input(); ?>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] text-stone-400 font-bold uppercase mb-1.5">Business Name *</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($shop['title']); ?>" required class="w-full px-4 py-3 bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700 rounded-xl text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-olive-500 transition-all">
                </div>
                <div>
                    <label class="block text-[11px] text-stone-400 font-bold uppercase mb-1.5">Category Mapping *</label>
                    <select name="category_id" required class="w-full px-4 py-3 bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700 rounded-xl text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-olive-500 transition-all">
                        <?php while($cat = $categories_res->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $shop['category_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] text-stone-400 font-bold uppercase mb-1.5">Phone Number *</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($shop['phone']); ?>" required class="w-full px-4 py-3 bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700 rounded-xl text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-olive-500 transition-all">
                </div>
                <div>
                    <label class="block text-[11px] text-stone-400 font-bold uppercase mb-1.5">WhatsApp *</label>
                    <input type="tel" name="whatsapp" value="<?php echo htmlspecialchars($shop['whatsapp']); ?>" required class="w-full px-4 py-3 bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700 rounded-xl text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-olive-500 transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-[11px] text-stone-400 font-bold uppercase mb-1.5">City *</label>
                    <input type="text" name="city" value="<?php echo htmlspecialchars($shop['city']); ?>" required class="w-full px-4 py-3 bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700 rounded-xl text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-olive-500 transition-all">
                </div>
                <div>
                    <label class="block text-[11px] text-stone-400 font-bold uppercase mb-1.5">Pincode *</label>
                    <input type="text" name="pincode" value="<?php echo htmlspecialchars($shop['pincode']); ?>" required class="w-full px-4 py-3 bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700 rounded-xl text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-olive-500 transition-all">
                </div>
            </div>

            <div>
                <label class="block text-[11px] text-stone-400 font-bold uppercase mb-1.5">Full Address *</label>
                <textarea name="address" rows="2" required class="w-full px-4 py-3 bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700 rounded-xl text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-olive-500 transition-all"><?php echo htmlspecialchars($shop['address']); ?></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] text-stone-400 font-bold uppercase mb-1.5">Business Timings</label>
                    <input type="text" name="timing" value="<?php echo htmlspecialchars($shop['timing']); ?>" class="w-full px-4 py-3 bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700 rounded-xl text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-olive-500 transition-all">
                </div>
                <div>
                    <label class="block text-[11px] text-stone-400 font-bold uppercase mb-1.5">Banner Image</label>
                    <input type="file" name="shop_image" accept="image/*" class="w-full text-xs text-stone-500 dark:text-stone-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-stone-900 dark:file:bg-stone-700 file:text-white hover:file:bg-stone-800 cursor-pointer">
                </div>
            </div>

            <div>
                <label class="block text-[11px] text-stone-400 font-bold uppercase mb-1.5">Description</label>
                <textarea name="description" rows="3" class="w-full px-4 py-3 bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700 rounded-xl text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-olive-500 transition-all"><?php echo htmlspecialchars($shop['description']); ?></textarea>
            </div>

            <div class="flex items-center justify-between gap-3 pt-4 border-t border-stone-100 dark:border-stone-800">
                <a href="dashboard.php" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-xs font-bold text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-700 transition-all">
                    <i class="fas fa-arrow-left text-[10px]"></i> Back
                </a>
                <button type="submit" name="update_shop" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-olive-500 to-terracotta-500 text-white text-xs font-bold shadow-md shadow-olive-500/15 hover:opacity-95 transition-all">
                    <i class="fas fa-floppy-disk text-[10px]"></i> Save Updates
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('editBusinessForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const isDark = document.documentElement.classList.contains('dark');
    
    Swal.fire({
        title: 'Save Profile Changes?',
        text: 'Submitting updates will change your business status back to pending approval.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#c85f34',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Update It',
        background: isDark ? '#241f18' : '#fff',
        color: isDark ? '#f8fafc' : '#1e293b',
        customClass: {
            popup: 'rounded-2xl'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            this.submit();
        }
    });
});
</script>

<?php
$edit_shop_html = ob_get_clean();
render_owner_layout('Edit Shop', $edit_shop_html, 'dashboard');
?>