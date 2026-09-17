<?php
// owner/dashboard.php - Owner Workspace Dashboard
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../backend/config.php';
require_once __DIR__ . '/layout.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: ../login.php");
    exit();
}

$owner_id = (int) $_SESSION['user_id'];

$stats = [
    'total' => 0,
    'approved' => 0,
    'pending' => 0,
    'rejected' => 0,
];

// Fetch business statistics safely
$stats_stmt = amraj_prepare_execute($conn, "SELECT status, COUNT(*) as total FROM businesses WHERE owner_id = ? GROUP BY status", 'i', [$owner_id]);
if ($stats_stmt) {
    $stats_result = $stats_stmt->get_result();
    while ($row = $stats_result->fetch_assoc()) {
        if (isset($stats[$row['status']])) {
            $stats[$row['status']] = (int) $row['total'];
        }
        $stats['total'] += (int) $row['total'];
    }
    $stats_stmt->close();
}

// Fetch shops list
$shops_stmt = $conn->prepare("SELECT b.*, c.name as category_name FROM businesses b JOIN categories c ON b.category_id = c.id WHERE b.owner_id = ? ORDER BY b.created_at DESC");
$shops_stmt->bind_param("i", $owner_id);
$shops_stmt->execute();
$shops_res = $shops_stmt->get_result();

// Handle SweetAlert message triggers based on URL parameter
$swal_script = '';
if (isset($_GET['msg']) && $_GET['msg'] === 'updated') {
    $swal_script = "
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Updated!',
                    text: 'Business profile update submitted for re-verification.',
                    icon: 'success',
                    confirmButtonColor: '#c85f34',
                    customClass: { popup: 'rounded-xl' }
                });
            });
        </script>
    ";
} elseif (isset($_GET['listing']) && $_GET['listing'] === 'submitted') {
    $swal_script = "
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Success!',
                    text: 'New shop submitted successfully.',
                    icon: 'success',
                    confirmButtonColor: '#c85f34',
                    customClass: { popup: 'rounded-xl' }
                });
            });
        </script>
    ";
}

ob_start();
?>

<!-- Inject SweetAlert if triggered -->
<?php echo $swal_script; ?>

<!-- Top Workspace Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-stone-900 dark:text-white">Managed <span class="text-terracotta-700 dark:text-terracotta-400">Businesses</span></h1>
        <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">Manage your shops, track status, and perform quick actions in one central hub.</p>
    </div>
    <a href="add-shop.php" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-olive-500 to-terracotta-500 text-white text-xs font-bold shadow-md shadow-olive-500/15 hover:opacity-95 transition-all">
        <i class="fas fa-plus-circle text-[10px]"></i> Add Business
    </a>
</div>

<!-- Statistics Cards Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="p-4 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm">
        <p class="text-[10px] uppercase tracking-wider text-stone-400 dark:text-stone-500 font-bold">Total Listings</p>
        <h3 class="mt-1 text-2xl font-black text-stone-900 dark:text-white"><?php echo number_format($stats['total']); ?></h3>
    </div>
    <div class="p-4 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm">
        <p class="text-[10px] uppercase tracking-wider text-stone-400 dark:text-stone-500 font-bold">Approved</p>
        <h3 class="mt-1 text-2xl font-black text-emerald-600 dark:text-emerald-400"><?php echo number_format($stats['approved']); ?></h3>
    </div>
    <div class="p-4 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm">
        <p class="text-[10px] uppercase tracking-wider text-stone-400 dark:text-stone-500 font-bold">Pending</p>
        <h3 class="mt-1 text-2xl font-black text-amber-600 dark:text-amber-400"><?php echo number_format($stats['pending']); ?></h3>
    </div>
    <div class="p-4 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm">
        <p class="text-[10px] uppercase tracking-wider text-stone-400 dark:text-stone-500 font-bold">Rejected</p>
        <h3 class="mt-1 text-2xl font-black text-red-600 dark:text-red-400"><?php echo number_format($stats['rejected']); ?></h3>
    </div>
</div>

<!-- Quick Action Shortcuts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    <a href="add-shop.php" class="p-5 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm hover:border-olive-300 dark:hover:border-olive-500/40 transition-all">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-[10px] uppercase tracking-widest text-stone-400 dark:text-stone-500 font-bold">Quick Action</p>
                <h3 class="mt-1 text-sm font-bold text-stone-900 dark:text-white">Register New Shop</h3>
                <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">Open the registration form to create a new business listing.</p>
            </div>
            <div class="h-10 w-10 rounded-xl bg-olive-500/10 text-olive-500 flex items-center justify-center shrink-0">
                <i class="fas fa-plus-circle"></i>
            </div>
        </div>
    </a>
    <a href="reviews.php" class="p-5 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm hover:border-terracotta-300 dark:hover:border-terracotta-500/40 transition-all">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-[10px] uppercase tracking-widest text-stone-400 dark:text-stone-500 font-bold">Quick Action</p>
                <h3 class="mt-1 text-sm font-bold text-stone-900 dark:text-white">Customer Reviews</h3>
                <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">View and respond to feedback left by your customers.</p>
            </div>
            <div class="h-10 w-10 rounded-xl bg-terracotta-500/10 text-terracotta-500 flex items-center justify-center shrink-0">
                <i class="fas fa-star"></i>
            </div>
        </div>
    </a>
</div>

<!-- Business Listings Section -->
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="text-sm font-black uppercase tracking-wider text-stone-700 dark:text-stone-300">Your Listings</h2>
        <span class="text-xs font-semibold text-stone-400"><?php echo $shops_res->num_rows; ?> Total Found</span>
    </div>

    <?php if($shops_res->num_rows > 0): ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <?php while($shop = $shops_res->fetch_assoc()): ?>
                <div class="rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm p-4 sm:p-5 flex flex-col justify-between">
                    <div class="flex gap-4">
                        <div class="w-20 h-20 rounded-xl overflow-hidden bg-stone-100 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 shrink-0">
                            <?php if(!empty($shop['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($shop['image_url']); ?>" alt="<?php echo htmlspecialchars($shop['title']); ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-stone-300 dark:text-stone-600"><i class="fas fa-store text-2xl"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-[10px] uppercase tracking-widest text-stone-400 dark:text-stone-500 font-bold truncate"><?php echo htmlspecialchars($shop['category_name']); ?></p>
                                    <h3 class="mt-0.5 text-sm font-bold text-stone-900 dark:text-white truncate"><?php echo htmlspecialchars($shop['title']); ?></h3>
                                    <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400 truncate"><i class="fas fa-map-marker-alt text-[10px] mr-1 text-olive-500"></i><?php echo htmlspecialchars($shop['city']); ?></p>
                                </div>
                                <?php if($shop['status'] == 'approved'): ?>
                                    <span class="px-2.5 py-0.5 text-[10px] font-black uppercase text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-full shrink-0">Live</span>
                                <?php elseif($shop['status'] == 'rejected'): ?>
                                    <span class="px-2.5 py-0.5 text-[10px] font-black uppercase text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-full shrink-0">Rejected</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-0.5 text-[10px] font-black uppercase text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-full shrink-0">Pending</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4 pt-3 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <a href="edit-shop.php?id=<?php echo $shop['id']; ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-stone-900 dark:bg-stone-100 text-white dark:text-stone-950 text-[11px] font-bold hover:opacity-90 transition-all">
                                <i class="fas fa-pen-to-square text-[10px]"></i> Edit
                            </a>
                            <a href="../shop-details.php?id=<?php echo $shop['id']; ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-stone-200 dark:border-stone-700 text-stone-600 dark:text-stone-300 text-[11px] font-bold hover:bg-stone-50 dark:hover:bg-stone-800 transition-all">
                                <i class="fas fa-arrow-up-right-from-square text-[10px]"></i> View
                            </a>
                        </div>
                        <span class="text-[10px] text-stone-400 font-medium">Added: <?php echo date('M d, Y', strtotime($shop['created_at'])); ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="p-12 text-center rounded-2xl border border-dashed border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 text-stone-500 dark:text-stone-400 text-xs">
            <i class="fas fa-store-slash text-3xl mb-2 text-stone-300 dark:text-stone-600 block"></i>
            You haven't listed any businesses yet. Use the button above to register your first shop.
        </div>
    <?php endif; ?>
</div>

<?php
$dashboard_html = ob_get_clean();
render_owner_layout('Dashboard', $dashboard_html, 'dashboard');
?>