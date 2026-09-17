<?php
// owner/add-shop.php - Register Business Profile
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../backend/config.php';
require_once __DIR__ . '/layout.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: ../login.php");
    exit();
}

$categories_res = amraj_query($conn, "SELECT * FROM categories ORDER BY name ASC");

ob_start();
?>

<!-- SweetAlert2 Core Script -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="max-w-4xl mx-auto w-full">
    <!-- Header Section -->
    <div class="mb-6">
        <h1 class="mt-1 text-2xl sm:text-3xl font-black text-stone-900 dark:text-white">Register <span class="text-olive-500">Business Profile</span></h1>
        <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">Provide accurate details about your establishment to ensure customers can reach you effectively.</p>
    </div>

    <!-- Form Container -->
    <div class="rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm p-5 sm:p-8">
        <form id="businessRegisterForm" action="../../backend/shop_handler.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <?php echo amraj_csrf_input(); ?>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] text-stone-400 uppercase font-bold tracking-wider mb-1.5">Business Name *</label>
                    <input type="text" name="title" required placeholder="e.g. Verma Electricals" class="w-full px-4 py-3 bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700 rounded-xl text-xs text-stone-900 dark:text-stone-100 placeholder-stone-400 focus:outline-none focus:border-olive-500 transition-all">
                </div>
                <div>
                    <label class="block text-[11px] text-stone-400 uppercase font-bold tracking-wider mb-1.5">Select Category *</label>
                    <select name="category_id" required class="w-full px-4 py-3 bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700 rounded-xl text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-olive-500 transition-all">
                        <?php while($cat = $categories_res->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] text-stone-400 uppercase font-bold tracking-wider mb-1.5">Calling Phone Number *</label>
                    <input type="tel" name="phone" required placeholder="e.g. 9876543210" class="w-full px-4 py-3 bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700 rounded-xl text-xs text-stone-900 dark:text-stone-100 placeholder-stone-400 focus:outline-none focus:border-olive-500 transition-all">
                </div>
                <div>
                    <label class="block text-[11px] text-stone-400 uppercase font-bold tracking-wider mb-1.5">WhatsApp Number *</label>
                    <input type="tel" name="whatsapp" required placeholder="e.g. 9876543210" class="w-full px-4 py-3 bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700 rounded-xl text-xs text-stone-900 dark:text-stone-100 placeholder-stone-400 focus:outline-none focus:border-olive-500 transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-[11px] text-stone-400 uppercase font-bold tracking-wider mb-1.5">City / Location *</label>
                    <input type="text" name="city" required placeholder="e.g. Patna" class="w-full px-4 py-3 bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700 rounded-xl text-xs text-stone-900 dark:text-stone-100 placeholder-stone-400 focus:outline-none focus:border-olive-500 transition-all">
                </div>
                <div>
                    <label class="block text-[11px] text-stone-400 uppercase font-bold tracking-wider mb-1.5">Pincode *</label>
                    <input type="text" name="pincode" required placeholder="e.g. 800001" class="w-full px-4 py-3 bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700 rounded-xl text-xs text-stone-900 dark:text-stone-100 placeholder-stone-400 focus:outline-none focus:border-olive-500 transition-all">
                </div>
            </div>

            <div>
                <label class="block text-[11px] text-stone-400 uppercase font-bold tracking-wider mb-1.5">Full Address *</label>
                <textarea name="address" rows="2" required placeholder="Shop No, Landmark, Area details..." class="w-full px-4 py-3 bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700 rounded-xl text-xs text-stone-900 dark:text-stone-100 placeholder-stone-400 focus:outline-none focus:border-olive-500 transition-all"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] text-stone-400 uppercase font-bold tracking-wider mb-1.5">Business Timings</label>
                    <input type="text" name="timing" value="09:00 AM - 08:00 PM" class="w-full px-4 py-3 bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700 rounded-xl text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-olive-500 transition-all">
                </div>
                <div>
                    <label class="block text-[11px] text-stone-400 uppercase font-bold tracking-wider mb-1.5">Shop / Banner Image</label>
                    <input type="file" name="shop_image" accept="image/*" class="w-full text-xs text-stone-500 dark:text-stone-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-stone-900 dark:file:bg-stone-700 file:text-white hover:file:bg-stone-800 cursor-pointer">
                </div>
            </div>

            <div>
                <label class="block text-[11px] text-stone-400 uppercase font-bold tracking-wider mb-1.5">Business Description</label>
                <textarea name="description" rows="3" placeholder="Describe the specialties, services, and products your business offers..." class="w-full px-4 py-3 bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700 rounded-xl text-xs text-stone-900 dark:text-stone-100 placeholder-stone-400 focus:outline-none focus:border-olive-500 transition-all"></textarea>
            </div>

            <div class="pt-2">
                <button type="submit" name="add_shop" class="w-full py-3 bg-gradient-to-r from-olive-500 to-terracotta-500 text-white font-bold text-xs rounded-xl shadow-md shadow-olive-500/15 hover:opacity-95 transition-all">Submit Profile for Verification</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('businessRegisterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const isDark = document.documentElement.classList.contains('dark');
    
    Swal.fire({
        title: 'Submit Profile?',
        text: 'Your listing details will be submitted to the administration team for review.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#c85f34',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Submit',
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
$add_shop_html = ob_get_clean();
render_owner_layout('Register Shop', $add_shop_html, 'add-shop');
?>