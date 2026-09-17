<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../backend/config.php';
require_once __DIR__ . '/layout.php';

$user_id = $_SESSION['user_id'];

// 1. Fetch Saved Bookmarks Count
$stmt_saved = $conn->prepare("SELECT COUNT(*) as total FROM bookmarks WHERE user_id = ?");
$stmt_saved->bind_param("i", $user_id);
$stmt_saved->execute();
$saved_count = $stmt_saved->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_saved->close();

// 2. Fetch User Reviews Count
$stmt_reviews = $conn->prepare("SELECT COUNT(*) as total FROM reviews WHERE user_id = ?");
$stmt_reviews->bind_param("i", $user_id);
$stmt_reviews->execute();
$review_count = $stmt_reviews->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_reviews->close();

// 3. Fetch Recent Activity Feed Timeline
$timeline_query = "
    (SELECT 'bookmark' as type, bs.title as meta_title, b.created_at as action_date, bs.address as meta_desc
     FROM bookmarks b JOIN businesses bs ON b.shop_id = bs.id WHERE b.user_id = ?)
    UNION ALL
    (SELECT 'review' as type, bs.title as meta_title, r.created_at as action_date, r.comment as meta_desc
     FROM reviews r JOIN businesses bs ON r.shop_id = bs.id WHERE r.user_id = ?)
    ORDER BY action_date DESC LIMIT 4";
$timeline_stmt = amraj_prepare_execute($conn, $timeline_query, 'ii', [$user_id, $user_id]);
$timeline_res = $timeline_stmt ? $timeline_stmt->get_result() : false;

ob_start();
?>

<!-- ================= COMPACT HEADER SECTION ================= -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 mb-6 border-b border-stone-200/80 dark:border-stone-800/80">
    <div>
        <h1 class="text-xl sm:text-2xl font-black text-stone-900 dark:text-white tracking-tight">
            Dashboard <span class="text-terracotta-700 dark:text-terracotta-400">Overview</span>
        </h1>
        <p class="text-stone-500 dark:text-stone-400 text-[11px] font-medium mt-0.5">
            Monitor your bookmarked store listings, submitted reviews, and portal activity.
        </p>
    </div>
    <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 px-3 py-1.5 rounded-xl flex items-center gap-2 text-xs shadow-sm self-start sm:self-auto shrink-0">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        <span class="font-bold text-stone-700 dark:text-stone-300 text-[11px]">System Online</span>
    </div>
</div>

<!-- ================= METRIC STATISTICS GRID ================= -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
    
    <!-- Saved Stores Card -->
    <div class="bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800/80 p-5 rounded-2xl flex items-center justify-between shadow-sm hover:border-terracotta-500/40 transition-all">
        <div>
            <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">Saved Stores</p>
            <h3 class="text-3xl font-black text-stone-900 dark:text-white mt-0.5"><?php echo $saved_count; ?></h3>
            <span class="text-[10px] text-stone-400 font-medium">Active verified bookmarks</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-terracotta-50 dark:bg-terracotta-950/40 text-terracotta-600 dark:text-terracotta-400 flex items-center justify-center text-lg shrink-0">
            <i class="fas fa-bookmark"></i>
        </div>
    </div>

    <!-- Total Reviews Card -->
    <div class="bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800/80 p-5 rounded-2xl flex items-center justify-between shadow-sm hover:border-olive-500/40 transition-all">
        <div>
            <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">Total Reviews</p>
            <h3 class="text-3xl font-black text-stone-900 dark:text-white mt-0.5"><?php echo $review_count; ?></h3>
            <span class="text-[10px] text-stone-400 font-medium">Public testimonials</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-olive-50 dark:bg-olive-950/40 text-olive-600 dark:text-olive-400 flex items-center justify-center text-lg shrink-0">
            <i class="fas fa-star"></i>
        </div>
    </div>

    <!-- Security Tier Card -->
    <div class="bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800/80 p-5 rounded-2xl flex items-center justify-between shadow-sm sm:col-span-2 lg:col-span-1">
        <div>
            <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">Security Status</p>
            <div class="mt-1">
                <span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-md text-[11px] font-bold inline-block">Verified Account</span>
            </div>
            <span class="text-[10px] text-stone-400 font-medium mt-1 block">Encrypted Protected</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg shrink-0">
            <i class="fas fa-shield-alt"></i>
        </div>
    </div>
</div>

<!-- ================= MAIN CONTENT SPLIT GRID ================= -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Left 2 Cols: Activity Timeline Feed -->
    <div class="lg:col-span-2 space-y-3">
        <h3 class="text-xs font-bold text-stone-800 dark:text-stone-200 uppercase tracking-wider flex items-center gap-2">
            <i class="fas fa-history text-terracotta-600"></i> Recent Activity Feed
        </h3>
        
        <div class="bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800/80 p-5 rounded-2xl shadow-sm space-y-3">
            <?php if($timeline_res && $timeline_res->num_rows > 0): ?>
                <div class="space-y-2.5">
                    <?php while($log = $timeline_res->fetch_assoc()): ?>
                        <div class="flex items-start gap-3 p-3.5 rounded-xl bg-stone-50 dark:bg-stone-800/40 border border-stone-100 dark:border-stone-800/60">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs shrink-0 <?php echo $log['type'] === 'bookmark' ? 'bg-terracotta-600 text-white' : 'bg-olive-500 text-white'; ?>">
                                <i class="fas <?php echo $log['type'] === 'bookmark' ? 'fa-bookmark' : 'fa-star'; ?>"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-3 mb-0.5">
                                    <h4 class="text-xs font-bold text-stone-800 dark:text-stone-200 truncate"><?php echo htmlspecialchars($log['meta_title']); ?></h4>
                                    <span class="text-[10px] font-medium text-stone-400 shrink-0"><?php echo date("M d, Y", strtotime($log['action_date'])); ?></span>
                                </div>
                                <p class="text-[11px] text-stone-500 dark:text-stone-400 truncate font-medium">
                                    <?php echo $log['type'] === 'bookmark' ? 'Saved storefront location' : 'Reviewed: "' . htmlspecialchars($log['meta_desc']) . '"'; ?>
                                </p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="py-10 text-center text-stone-400 text-xs space-y-2">
                    <i class="fas fa-inbox text-2xl text-stone-300 dark:text-stone-700 block"></i>
                    <p class="font-medium">No activity logs recorded yet. Start exploring listings!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Col: Portal Guidelines & Tips -->
    <div class="space-y-3">
        <h3 class="text-xs font-bold text-stone-800 dark:text-stone-200 uppercase tracking-wider flex items-center gap-2">
            <i class="fas fa-lightbulb text-olive-500"></i> Portal Guidelines
        </h3>
        
        <div class="bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800/80 p-5 rounded-2xl shadow-sm space-y-3.5">
            <!-- Tip 1 -->
            <div class="p-3.5 rounded-xl bg-terracotta-50/50 dark:bg-terracotta-950/20 border border-terracotta-100 dark:border-terracotta-900/30">
                <h4 class="text-xs font-bold text-terracotta-700 dark:text-terracotta-400 flex items-center gap-2 mb-1">
                    <i class="fas fa-phone-alt text-xs"></i> Quick Call Routing
                </h4>
                <p class="text-[11px] text-stone-600 dark:text-stone-400 leading-relaxed font-medium">
                    All bookmarked store profiles feature single-click phone dialers for secure communication.
                </p>
            </div>

            <!-- Tip 2 -->
            <div class="p-3.5 rounded-xl bg-olive-50/50 dark:bg-olive-950/20 border border-olive-100 dark:border-olive-900/30">
                <h4 class="text-xs font-bold text-olive-700 dark:text-olive-400 flex items-center gap-2 mb-1">
                    <i class="fas fa-shield-alt text-xs"></i> Review Compliance
                </h4>
                <p class="text-[11px] text-stone-600 dark:text-stone-400 leading-relaxed font-medium">
                    Ensure ratings remain factual and authentic to maintain active account status.
                </p>
            </div>
        </div>
    </div>
</div>

<?php
$content_html = ob_get_clean();
amraj_render_user_layout('Dashboard Overview', $content_html, 'dashboard');
?>