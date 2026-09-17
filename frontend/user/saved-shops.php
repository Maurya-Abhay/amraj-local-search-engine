<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../backend/config.php';
require_once __DIR__ . '/layout.php';

$user_id = $_SESSION['user_id'];

// Handle Un-bookmark Action Securely via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_bookmark') {
    $bookmark_id = intval($_POST['bookmark_id']);
    
    $stmt_del = $conn->prepare("DELETE FROM bookmarks WHERE id = ? AND user_id = ?");
    $stmt_del->bind_param("ii", $bookmark_id, $user_id);
    if ($stmt_del->execute()) {
        $_SESSION['flash_success'] = "Listing successfully removed from your bookmarks.";
    }
    $stmt_del->close();
    
    header("Location: saved-shops.php");
    exit();
}

// Fetch Bookmarked Businesses with category metadata
$query = "
    SELECT b.id as bookmark_id, b.created_at as saved_date, bs.id as shop_id, bs.title, bs.address, bs.phone, c.name AS category 
    FROM bookmarks b
    JOIN businesses bs ON b.shop_id = bs.id
    LEFT JOIN categories c ON bs.category_id = c.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookmarks_res = $stmt->get_result();

ob_start();
?>

<!-- ================= COMPACT HEADER SECTION ================= -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 mb-6 border-b border-stone-200/80 dark:border-stone-800/80">
    <div>
        <h1 class="text-xl sm:text-2xl font-black text-stone-900 dark:text-white tracking-tight">
            Bookmarked <span class="text-terracotta-700 dark:text-terracotta-400">Stores</span>
        </h1>
        <p class="text-stone-500 dark:text-stone-400 text-[11px] font-medium mt-0.5">
            Manage your curated collection of favorite verified commercial listings.
        </p>
    </div>
    <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 px-3 py-1.5 rounded-xl flex items-center gap-2 text-xs shadow-sm self-start sm:self-auto shrink-0">
        <i class="fas fa-bookmark text-terracotta-600 dark:text-terracotta-400 text-xs"></i>
        <span class="font-bold text-stone-700 dark:text-stone-300 text-[11px]"><?php echo $bookmarks_res->num_rows; ?> Saved Items</span>
    </div>
</div>

<!-- ================= MAIN CONTENT INTERFACE GRID ================= -->
<?php if ($bookmarks_res && $bookmarks_res->num_rows > 0): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        <?php while ($row = $bookmarks_res->fetch_assoc()): ?>
            <div class="bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800/80 rounded-2xl shadow-sm flex flex-col justify-between overflow-hidden group hover:border-stone-300 dark:hover:border-stone-700 transition-all duration-200">
                
                <!-- Card Body -->
                <div class="p-5 space-y-3.5">
                    <div class="flex items-start justify-between gap-3">
                        <span class="px-2.5 py-1 bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400 rounded-lg text-[10px] font-bold uppercase tracking-wider">
                            <?php echo htmlspecialchars($row['category'] ?? 'General'); ?>
                        </span>
                        <span class="text-[10px] text-stone-400 font-semibold">
                            Saved <?php echo date("M d, Y", strtotime($row['saved_date'])); ?>
                        </span>
                    </div>

                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-stone-900 dark:text-white group-hover:text-terracotta-600 dark:group-hover:text-terracotta-400 transition-colors truncate">
                            <?php echo htmlspecialchars($row['title']); ?>
                        </h3>
                        <p class="text-[11px] text-stone-500 dark:text-stone-400 font-medium flex items-start gap-2 line-clamp-2">
                            <i class="fas fa-location-dot text-stone-400 mt-0.5 shrink-0"></i>
                            <span><?php echo htmlspecialchars($row['address']); ?></span>
                        </p>
                    </div>

                    <!-- Communication Action Trigger -->
                    <?php if (!empty($row['phone'])): ?>
                        <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $row['phone']); ?>" class="inline-flex items-center gap-2 text-xs font-semibold text-stone-700 dark:text-stone-300 bg-stone-50 dark:bg-stone-800/50 hover:bg-stone-100 dark:hover:bg-stone-800 px-3 py-2 rounded-xl transition-all border border-stone-100 dark:border-stone-800">
                            <i class="fas fa-phone text-[10px] text-emerald-500"></i>
                            <span><?php echo htmlspecialchars($row['phone']); ?></span>
                        </a>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-2 text-xs font-medium text-stone-400 bg-stone-50 dark:bg-stone-800/30 px-3 py-2 rounded-xl italic select-none">
                            <i class="fas fa-phone-slash text-[10px]"></i> No Phone Listed
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Card Footer Controls -->
                <div class="px-5 py-3.5 bg-stone-50/50 dark:bg-stone-900/40 border-t border-stone-100 dark:border-stone-800/60 flex items-center justify-between gap-4">
                    <a href="../shop-detail.php?id=<?php echo $row['shop_id']; ?>" class="text-xs font-bold text-terracotta-600 dark:text-terracotta-400 hover:text-terracotta-700 dark:hover:text-terracotta-300 transition-colors inline-flex items-center gap-1.5">
                        View Profile <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                    </a>
                    
                    <form method="POST" onsubmit="return confirm('Are you sure you want to remove this bookmark?');">
                        <input type="hidden" name="action" value="remove_bookmark">
                        <input type="hidden" name="bookmark_id" value="<?php echo $row['bookmark_id']; ?>">
                        <button type="submit" class="text-stone-400 hover:text-red-500 dark:text-stone-500 dark:hover:text-red-400 p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/20 transition-all" title="Delete Bookmark">
                            <i class="far fa-trash-can text-xs"></i>
                        </button>
                    </form>
                </div>

            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <!-- Empty State Component fallback -->
    <div class="bg-white dark:bg-stone-900 border border-dashed border-stone-300 dark:border-stone-800 p-12 text-center rounded-2xl max-w-lg mx-auto space-y-3 shadow-sm">
        <div class="w-14 h-14 bg-stone-100 dark:bg-stone-800 text-stone-400 rounded-2xl flex items-center justify-center text-xl mx-auto shadow-inner">
            <i class="far fa-bookmark"></i>
        </div>
        <div class="space-y-1">
            <h3 class="text-sm font-bold text-stone-900 dark:text-white">No Bookmarks Found</h3>
            <p class="text-[11px] text-stone-500 dark:text-stone-400 leading-relaxed font-medium">
                You haven't bookmarked any business listings yet. Browse marketplace directories to add favorites.
            </p>
        </div>
        <div class="pt-2">
            <a href="../index.php" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-terracotta-600 to-olive-500 text-white font-bold text-xs rounded-xl shadow-sm hover:opacity-95 transition-all">
                <i class="fas fa-magnifying-glass text-[10px]"></i> Browse Marketplace
            </a>
        </div>
    </div>
<?php endif; ?>

<?php
$stmt->close();
$content_html = ob_get_clean();
amraj_render_user_layout('Bookmarked Stores', $content_html, 'saved-shops');
?>