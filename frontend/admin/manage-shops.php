<?php
// manage-shops.php - Verification Queue Matrix
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../backend/config.php';
require_once __DIR__ . '/layout.php'; // Include Master Layout Base

// Strict Admin Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch Pending Counter globally for layout context badge
$pending_result = amraj_query($conn, "SELECT COUNT(*) as total FROM businesses WHERE status='pending'");
$pending_shops = 0;
if ($pending_result) {
    $row = $pending_result->fetch_assoc();
    $pending_shops = isset($row['total']) ? (int)$row['total'] : 0;
}

// --- PAGINATION LOGIC (10 Records Per Page) ---
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }
$offset = ($page - 1) * $limit;

// Total counts for pagination navigation
$total_rows_result = amraj_query($conn, "SELECT COUNT(*) as total FROM businesses");
$total_rows = 0;
if ($total_rows_result) {
    $row = $total_rows_result->fetch_assoc();
    $total_rows = isset($row['total']) ? (int)$row['total'] : 0;
}
$total_pages = $total_rows > 0 ? ceil($total_rows / $limit) : 1;

// Fetch businesses with categories and owner details safely using Prepared Statement
$query = "SELECT b.*, c.name as category_name, u.name as owner_name 
          FROM businesses b 
          JOIN categories c ON b.category_id = c.id 
          JOIN users u ON b.owner_id = u.id 
          ORDER BY FIELD(b.status, 'pending', 'approved', 'rejected'), b.created_at DESC 
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Output buffer initialization for content pipeline injection
ob_start();
?>

<div class="space-y-6 animate-fade-in">
    <!-- Section Interactive Header Structure -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-200 dark:border-stone-800 pb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-stone-900 dark:text-white">
                Business <span class="text-terracotta-700 dark:text-terracotta-400">Approval Queue</span>
            </h1>
            <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">Sabhie listing registration requests ko verify karein, live approve ya permanently reject karein.</p>
        </div>
    </div>

    <!-- Modern Responsive Data Grid Table Framework Container -->
    <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-stone-50/80 dark:bg-stone-800/50 border-b border-stone-200 dark:border-stone-800 text-[11px] text-stone-500 dark:text-stone-400 font-bold tracking-wider uppercase">
                        <th class="py-3.5 px-4">Business / Category</th>
                        <th class="py-3.5 px-4">Owner Identity</th>
                        <th class="py-3.5 px-4">Contact Gateway</th>
                        <th class="py-3.5 px-4">Location</th>
                        <th class="py-3.5 px-4">System Status</th>
                        <th class="py-3.5 px-4 text-center">Operation Control</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 dark:divide-stone-800/80 text-xs text-stone-700 dark:text-stone-300">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-stone-50/60 dark:hover:bg-stone-800/40 transition-colors">
                                <!-- Business Meta Column -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-stone-100 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl overflow-hidden shrink-0 flex items-center justify-center">
                                            <?php if (!empty($row['image_url'])): ?>
                                                <img src="<?php echo htmlspecialchars($row['image_url']); ?>" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="hidden w-full h-full items-center justify-center text-stone-400 dark:text-stone-500"><i class="fas fa-store text-xs"></i></div>
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center text-stone-400 dark:text-stone-500"><i class="fas fa-store text-xs"></i></div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div class="font-bold text-stone-900 dark:text-white"><?php echo htmlspecialchars($row['title']); ?></div>
                                            <div class="text-[10px] text-stone-400 dark:text-stone-500 font-bold uppercase tracking-wider mt-0.5"><?php echo htmlspecialchars($row['category_name']); ?></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Owner Context Column -->
                                <td class="py-3.5 px-4 font-semibold text-stone-800 dark:text-stone-300">
                                    <?php echo htmlspecialchars($row['owner_name']); ?>
                                </td>

                                <!-- Contact Comms Column -->
                                <td class="py-3.5 px-4 font-mono text-[11px] space-y-1 text-stone-600 dark:text-stone-400">
                                    <div class="flex items-center gap-1.5"><i class="fas fa-phone text-[10px] text-stone-400 w-3.5"></i><?php echo htmlspecialchars($row['phone']); ?></div>
                                    <div class="text-stone-400 dark:text-stone-500"><i class="fab fa-whatsapp text-[11px] text-emerald-500 w-3.5"></i><?php echo htmlspecialchars($row['whatsapp']); ?></div>
                                </td>

                                <!-- Physical Mapping Address Column -->
                                <td class="py-3.5 px-4 max-w-[200px]">
                                    <div class="font-semibold text-stone-800 dark:text-stone-300"><?php echo htmlspecialchars($row['city']); ?></div>
                                    <div class="text-[11px] text-stone-400 dark:text-stone-500 truncate mt-0.5" title="<?php echo htmlspecialchars($row['address']); ?>">
                                        <?php echo htmlspecialchars($row['address']); ?>
                                    </div>
                                </td>

                                <!-- Context Dynamic Status Badges -->
                                <td class="py-3.5 px-4">
                                    <?php if ($row['status'] == 'approved'): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-lg">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Live
                                        </span>
                                    <?php elseif ($row['status'] == 'rejected'): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-lg">
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> Rejected
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-lg animate-pulse">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Pending
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Actions Decision Core Row UI -->
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ($row['status'] == 'pending'): ?>
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="../../backend/admin_handler.php?action=approve&id=<?php echo $row['id']; ?>" class="inline-flex items-center justify-center h-8 px-3 bg-emerald-600 hover:bg-emerald-700 font-bold text-[11px] rounded-lg text-white transition-all shadow-sm active:scale-95">
                                                <i class="fas fa-check mr-1 text-[9px]"></i> Approve
                                            </a>
                                            <a href="../../backend/admin_handler.php?action=reject&id=<?php echo $row['id']; ?>" class="inline-flex items-center justify-center h-8 px-3 bg-white dark:bg-stone-800 border border-red-200 dark:border-red-900/50 hover:bg-red-50 dark:hover:bg-red-950 text-red-600 dark:text-red-400 font-bold text-[11px] rounded-lg transition-all shadow-sm active:scale-95">
                                                <i class="fas fa-times mr-1 text-[9px]"></i> Reject
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-[11px] text-stone-400 dark:text-stone-600 font-bold uppercase tracking-wider">
                                            <i class="fas fa-lock text-[10px] mr-1"></i> Locked
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="p-12 text-center text-stone-400 dark:text-stone-600">
                                <div class="space-y-2">
                                    <i class="fas fa-folder-open text-2xl block text-stone-300 dark:text-stone-700"></i>
                                    <span class="text-xs font-medium">No active verification requests found.</span>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- --- PAGINATION NAVIGATION FOOTER --- -->
        <?php if ($total_pages > 1): ?>
            <div class="px-5 py-3.5 bg-stone-50/80 dark:bg-stone-800/50 border-t border-stone-200 dark:border-stone-800 flex items-center justify-between gap-4">
                <div class="text-xs text-stone-500 dark:text-stone-400">
                    Showing <span class="font-bold text-stone-700 dark:text-stone-300"><?php echo $offset + 1; ?></span> to 
                    <span class="font-bold text-stone-700 dark:text-stone-300"><?php echo min($offset + $limit, $total_rows); ?></span> of 
                    <span class="font-bold text-stone-700 dark:text-stone-300"><?php echo $total_rows; ?></span> entries
                </div>
                <div class="flex items-center gap-1.5">
                    <!-- Previous Button -->
                    <a href="<?php echo $page > 1 ? '?page=' . ($page - 1) : '#'; ?>" 
                       class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-xs text-stone-600 dark:text-stone-400 font-bold hover:bg-stone-50 dark:hover:bg-stone-700/50 transition-colors <?php if($page <= 1) echo 'opacity-50 pointer-events-none'; ?>">
                        Previous
                    </a>
                    
                    <!-- Page Numbers -->
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" 
                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-bold transition-all <?php echo $page == $i ? 'bg-gradient-to-r from-brand-start to-brand-end text-white shadow-sm shadow-red-500/20' : 'border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-700/50'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Next Button -->
                    <a href="<?php echo $page < $total_pages ? '?page=' . ($page + 1) : '#'; ?>" 
                       class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-xs text-stone-600 dark:text-stone-400 font-bold hover:bg-stone-50 dark:hover:bg-stone-700/50 transition-colors <?php if($page >= $total_pages) echo 'opacity-50 pointer-events-none'; ?>">
                        Next
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$shops_html = ob_get_clean();

// Execute layout render compiler
render_admin_layout("Verification Queue", $shops_html, 'manage-shops');
?>