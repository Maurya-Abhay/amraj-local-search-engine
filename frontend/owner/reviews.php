<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../backend/config.php';
require_once __DIR__ . '/layout.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: ../login.php");
    exit();
}

$owner_id = (int) $_SESSION['user_id'];

$reviews_query = $conn->prepare("SELECT r.*, u.name as reviewer_name, b.title as shop_title FROM reviews r JOIN users u ON r.user_id = u.id JOIN businesses b ON r.shop_id = b.id WHERE b.owner_id = ? ORDER BY r.created_at DESC");
$reviews_query->bind_param("i", $owner_id);
$reviews_query->execute();
$reviews_res = $reviews_query->get_result();

ob_start();
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl sm:text-3xl font-black text-stone-900 dark:text-white">Customer <span class="text-olive-500">Feedback Desk</span></h1>
        <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">Monitor ratings, experiences, and historical testimonial comments submitted across your listed establishments.</p>
    </div>
</div>

<div class="grid grid-cols-1 gap-4">
    <?php if($reviews_res->num_rows > 0): ?>
        <?php while($rev = $reviews_res->fetch_assoc()): ?>
            <div class="p-5 rounded-xl border border-stone-200 dark:border-stone-700/80 bg-white dark:bg-stone-800/80 shadow-sm space-y-3">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h4 class="font-bold text-stone-900 dark:text-white text-sm"><?php echo htmlspecialchars($rev['reviewer_name']); ?></h4>
                        <p class="text-[10px] text-stone-500 dark:text-stone-400 font-bold uppercase tracking-wider mt-0.5">Shop: <span class="text-stone-700 dark:text-stone-200"><?php echo htmlspecialchars($rev['shop_title']); ?></span></p>
                    </div>
                    <div class="flex text-[10px] text-amber-400 gap-0.5 bg-amber-500/5 px-2 py-1 rounded border border-amber-500/10">
                        <?php for($i=1; $i<=5; $i++): ?>
                            <i class="<?php echo $i <= $rev['rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                        <?php endfor; ?>
                    </div>
                </div>

                <p class="text-xs text-stone-600 dark:text-stone-300 leading-relaxed bg-stone-50 dark:bg-stone-900/50 p-3 rounded-lg border border-stone-200 dark:border-stone-700/80"><?php echo htmlspecialchars($rev['comment'] ?? ''); ?></p>
                <div class="text-[10px] text-stone-400 dark:text-stone-500 text-right font-mono"><?php echo date("d M Y - h:i A", strtotime($rev['created_at'])); ?></div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="p-12 text-center rounded-xl border border-dashed border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800/60 text-stone-500 dark:text-stone-400 text-xs">
            No customer reviews have been published for your businesses yet.
        </div>
    <?php endif; ?>
</div>

<?php
$reviews_html = ob_get_clean();
render_owner_layout('Customer Reviews', $reviews_html, 'reviews');
?>