<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../backend/config.php';
require_once __DIR__ . '/layout.php';

$user_id = $_SESSION['user_id'];

// Handle Deletion Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_review') {
    $review_id = intval($_POST['review_id']);
    
    $stmt_del = $conn->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
    $stmt_del->bind_param("ii", $review_id, $user_id);
    if ($stmt_del->execute()) {
        $_SESSION['flash_success'] = "Review testimonial successfully removed.";
    }
    $stmt_del->close();
    
    header("Location: reviews-manager.php");
    exit();
}

// Query Structured User Feedback mapping business context titles
$query = "
    SELECT r.id as review_id, r.rating, r.comment as review_text, r.created_at, bs.id as shop_id, bs.title as shop_title, bs.address
    FROM reviews r
    JOIN businesses bs ON r.shop_id = bs.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reviews_res = $stmt->get_result();

ob_start();
?>

<!-- ================= COMPACT HEADER SECTION ================= -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 mb-6 border-b border-stone-200/80 dark:border-stone-800/80">
    <div>
        <h1 class="text-xl sm:text-2xl font-black text-stone-900 dark:text-white tracking-tight">
            Your Written <span class="text-terracotta-700 dark:text-terracotta-400">Reviews</span>
        </h1>
        <p class="text-stone-500 dark:text-stone-400 text-[11px] font-medium mt-0.5">
            Track and manage your feedback testimonials distributed across platform listings.
        </p>
    </div>
    <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 px-3 py-1.5 rounded-xl flex items-center gap-2 text-xs shadow-sm self-start sm:self-auto shrink-0">
        <i class="fas fa-star text-olive-500 text-xs"></i>
        <span class="font-bold text-stone-700 dark:text-stone-300 text-[11px]"><?php echo $reviews_res->num_rows; ?> Contributions</span>
    </div>
</div>

<!-- ================= REVIEW ITEMS DATA PIPELINE ================= -->
<?php if ($reviews_res && $reviews_res->num_rows > 0): ?>
    <div class="space-y-4 max-w-4xl">
        <?php while ($row = $reviews_res->fetch_assoc()): ?>
            <div class="bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800/80 p-5 rounded-2xl shadow-sm space-y-3.5 group transition-all duration-200 hover:border-stone-300 dark:hover:border-stone-700">
                
                <!-- Context Meta Data Line -->
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    <div class="space-y-0.5">
                        <h3 class="text-sm font-bold text-stone-900 dark:text-white flex items-center gap-2">
                            <a href="../shop-detail.php?id=<?php echo $row['shop_id']; ?>" class="hover:text-terracotta-600 dark:hover:text-terracotta-400 transition-colors truncate">
                                <?php echo htmlspecialchars($row['shop_title']); ?>
                            </a>
                        </h3>
                        <p class="text-[10px] text-stone-400 font-medium flex items-center gap-1">
                            <i class="fas fa-location-dot text-[9px]"></i> <?php echo htmlspecialchars($row['address']); ?>
                        </p>
                    </div>

                    <!-- Structured Star Visualization Blocks -->
                    <div class="flex items-center gap-3 shrink-0">
                        <div class="flex items-center gap-0.5 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-lg border border-amber-200/50 dark:border-amber-900/40">
                            <?php 
                            $stars = intval($row['rating']);
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $stars) {
                                    echo '<i class="fas fa-star text-[10px] text-amber-500"></i>';
                                } else {
                                    echo '<i class="far fa-star text-[10px] text-stone-300 dark:text-stone-700"></i>';
                                }
                            }
                            ?>
                        </div>
                        <span class="text-[10px] text-stone-400 font-semibold">
                            <?php echo date("M d, Y", strtotime($row['created_at'])); ?>
                        </span>
                    </div>
                </div>

                <!-- Testimonial Copy Output block -->
                <div class="bg-stone-50 dark:bg-stone-800/40 border border-stone-100 dark:border-stone-800/60 p-3.5 rounded-xl">
                    <p class="text-[11px] text-stone-600 dark:text-stone-300 leading-relaxed font-medium italic">
                        "<?php echo htmlspecialchars($row['review_text']); ?>"
                    </p>
                </div>

                <!-- Component Control Interfaces -->
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-stone-100 dark:border-stone-800/60">
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this review?');">
                        <input type="hidden" name="action" value="delete_review">
                        <input type="hidden" name="review_id" value="<?php echo $row['review_id']; ?>">
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[11px] font-bold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 transition-all">
                            <i class="far fa-trash-can text-[10px]"></i> Delete Review
                        </button>
                    </form>
                </div>

            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <!-- Empty Activity Log Block -->
    <div class="bg-white dark:bg-stone-900 border border-dashed border-stone-300 dark:border-stone-800 p-12 text-center rounded-2xl max-w-lg mx-auto space-y-3 shadow-sm">
        <div class="w-14 h-14 bg-stone-100 dark:bg-stone-800 text-stone-400 rounded-2xl flex items-center justify-center text-xl mx-auto shadow-inner">
            <i class="far fa-star"></i>
        </div>
        <div class="space-y-1">
            <h3 class="text-sm font-bold text-stone-900 dark:text-white">No Reviews Found</h3>
            <p class="text-[11px] text-stone-500 dark:text-stone-400 leading-relaxed font-medium">
                You haven't written any reviews yet. Share your feedback on marketplace listings.
            </p>
        </div>
        <div class="pt-2">
            <a href="../index.php" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-olive-500 to-red-500 text-white font-bold text-xs rounded-xl shadow-sm hover:opacity-95 transition-all">
                <i class="fas fa-magnifying-glass text-[10px]"></i> Explore Marketplace
            </a>
        </div>
    </div>
<?php endif; ?>

<?php
$stmt->close();
$content_html = ob_get_clean();
amraj_render_user_layout('Your Written Reviews', $content_html, 'reviews');
?>