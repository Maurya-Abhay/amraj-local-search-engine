<?php 
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/public_layout.php';

$shop_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch Shop Details along with Category and Owner Info
$stmt = $conn->prepare("SELECT b.*, c.name as category_name, u.name as owner_name FROM businesses b 
                        JOIN categories c ON b.category_id = c.id 
                        JOIN users u ON b.owner_id = u.id 
                        WHERE b.id = ? AND b.status = 'approved'");
$stmt->bind_param("i", $shop_id);
$stmt->execute();
$shop = $stmt->get_result()->fetch_assoc();

if (!$shop) {
    echo "<div class='min-h-screen bg-stone-950 text-white flex items-center justify-center font-bold tracking-tight'>The requested business could not be found.</div>";
    exit();
}

// Fetch Reviews for this shop
$reviews_stmt = amraj_prepare_execute($conn, "SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.shop_id = ? ORDER BY r.created_at DESC", 'i', [$shop_id]);
$reviews_res = $reviews_stmt ? $reviews_stmt->get_result() : false;
?>
<?php amraj_render_public_header($shop['title'], '', false); ?>

<!-- Breadcrumb -->
<section class="border-b border-stone-200 dark:border-stone-800 px-4 sm:px-6 py-3 bg-white dark:bg-stone-950">
    <div class="max-w-6xl mx-auto w-full">
        <a href="search.php" class="text-xs font-medium text-stone-500 hover:text-stone-900 dark:hover:text-white inline-flex items-center gap-2 transition-colors">
            <i class="fas fa-arrow-left text-[10px]"></i> Back to search
        </a>
    </div>
</section>

<!-- Main Container -->
<main class="max-w-6xl mx-auto w-full p-4 sm:p-6 space-y-6">
    
    <!-- Success / Error Alerts -->
    <?php if(isset($_SESSION['success'])): ?>
        <div class="rounded-xl border border-emerald-500/20 bg-emerald-50 px-4 py-3 text-xs font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 flex items-center gap-2.5">
            <i class="fas fa-check-circle text-sm shrink-0"></i> 
            <span><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="rounded-xl border border-rose-500/20 bg-rose-50 px-4 py-3 text-xs font-medium text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 flex items-center gap-2.5">
            <i class="fas fa-exclamation-circle text-sm shrink-0"></i> 
            <span><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>
    
    <!-- Business Profile Header Card (Solid White/Dark background, no blur) -->
    <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-2xl overflow-hidden p-5 sm:p-8 flex flex-col md:flex-row gap-6 sm:gap-8 shadow-sm">
        
        <!-- Image Box -->
        <div class="w-full md:w-60 h-52 bg-stone-100 dark:bg-stone-950 rounded-xl overflow-hidden border border-stone-200 dark:border-stone-800 shrink-0 flex items-center justify-center">
            <?php if(!empty($shop['image_url'])): ?>
                <img src="<?php echo htmlspecialchars($shop['image_url']); ?>" alt="<?php echo htmlspecialchars($shop['title']); ?>" class="w-full h-full object-cover">
            <?php else: ?>
                <div class="text-stone-400"><i class="fas fa-store text-3xl"></i></div>
            <?php endif; ?>
        </div>

        <!-- Meta Details -->
        <div class="flex-1 flex flex-col justify-between space-y-4">
            <div class="space-y-3">
                <span class="px-2.5 py-1 bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 text-[11px] font-medium rounded-md w-fit inline-block">
                    <?php echo htmlspecialchars($shop['category_name']); ?>
                </span>
                
                <h1 class="text-2xl sm:text-3xl font-bold text-stone-900 dark:text-white tracking-tight">
                    <?php echo htmlspecialchars($shop['title']); ?>
                </h1>
                
                <p class="text-stone-600 dark:text-stone-400 text-xs sm:text-sm leading-relaxed max-w-3xl">
                    <?php echo nl2br(htmlspecialchars($shop['description'])); ?>
                </p>
                
                <div class="pt-3 space-y-2 text-xs text-stone-600 dark:text-stone-400 border-t border-stone-100 dark:border-stone-800">
                    <div class="flex items-start gap-2.5">
                        <i class="fas fa-map-marker-alt text-stone-400 text-xs mt-0.5 w-4 shrink-0"></i>
                        <span><?php echo htmlspecialchars($shop['address'] . ', ' . $shop['city'] . ' - ' . $shop['pincode']); ?></span>
                    </div>
                    <?php if(!empty($shop['timing'])): ?>
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-clock text-stone-400 text-xs w-4 shrink-0"></i>
                            <span><?php echo htmlspecialchars($shop['timing']); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="flex items-center gap-2.5">
                        <i class="fas fa-user text-stone-400 text-xs w-4 shrink-0"></i>
                        <span>Owner: <strong class="text-stone-800 dark:text-stone-200 font-medium"><?php echo htmlspecialchars($shop['owner_name']); ?></strong></span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 max-w-md">
                <?php if(!empty($shop['phone'])): ?>
                    <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $shop['phone']); ?>" class="py-2.5 bg-stone-900 dark:bg-white text-white dark:text-stone-900 text-xs font-medium text-center rounded-xl hover:opacity-90 transition-opacity flex items-center justify-center gap-2">
                        <i class="fas fa-phone-alt text-[11px]"></i> Call Business
                    </a>
                <?php endif; ?>
                
                <?php if(!empty($shop['whatsapp'])): ?>
                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $shop['whatsapp']); ?>" target="_blank" class="py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium text-center rounded-xl transition-colors flex items-center justify-center gap-2">
                        <i class="fab fa-whatsapp text-sm"></i> WhatsApp Chat
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Write Review Form -->
        <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-2xl p-5 sm:p-6 shadow-sm">
            <h3 class="font-bold text-sm text-stone-900 dark:text-white mb-4 border-b border-stone-100 dark:border-stone-800 pb-3">Write a Review</h3>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <form action="../backend/review_handler.php" method="POST" class="space-y-4">
                    <?php echo amraj_csrf_input(); ?>
                    <input type="hidden" name="shop_id" value="<?php echo $shop_id; ?>">
                    
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-stone-700 dark:text-stone-300">Rating</label>
                        <select name="rating" required class="w-full bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 rounded-xl px-3 py-2.5 text-xs text-stone-800 dark:text-white focus:outline-none focus:border-stone-400">
                            <option value="5">5 Stars - Excellent</option>
                            <option value="4">4 Stars - Very Good</option>
                            <option value="3">3 Stars - Average</option>
                            <option value="2">2 Stars - Poor</option>
                            <option value="1">1 Star - Terrible</option>
                        </select>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-stone-700 dark:text-stone-300">Your Feedback</label>
                        <textarea name="comment" rows="4" required placeholder="Share your experience with this business..." class="w-full bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 rounded-xl p-3 text-xs text-stone-800 dark:text-white placeholder-stone-400 focus:outline-none focus:border-stone-400 resize-none"></textarea>
                    </div>
                    
                    <button type="submit" name="submit_review" class="w-full py-2.5 bg-stone-900 dark:bg-white text-white dark:text-stone-900 text-xs font-medium rounded-xl hover:opacity-90 transition-opacity">
                        Post Review
                    </button>
                </form>
            <?php else: ?>
                <p class="text-xs text-stone-500 dark:text-stone-400 text-center py-6 px-3 bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 rounded-xl leading-relaxed">
                    Please <a href="login.php" class="text-stone-900 dark:text-white font-semibold underline">log in</a> to post a review for this business.
                </p>
            <?php endif; ?>
        </div>

        <!-- Reviews List -->
        <div class="lg:col-span-2 space-y-4">
            <h3 class="font-bold text-base text-stone-900 dark:text-white tracking-tight flex items-center gap-2">
                Customer Reviews 
                <span class="px-2 py-0.5 bg-stone-100 dark:bg-stone-800 text-xs rounded-md text-stone-600 dark:text-stone-400 font-medium">
                    <?php echo $reviews_res ? $reviews_res->num_rows : 0; ?>
                </span>
            </h3>
            
            <?php if($reviews_res && $reviews_res->num_rows > 0): ?>
                <div class="space-y-3">
                    <?php while($rev = $reviews_res->fetch_assoc()): ?>
                        <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-xl p-4 space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-semibold text-stone-800 dark:text-stone-200 flex items-center gap-2">
                                    <i class="fas fa-user-circle text-stone-400 text-sm"></i> 
                                    <?php echo htmlspecialchars($rev['user_name']); ?>
                                </span>
                                <span class="text-xs text-amber-500 font-medium">
                                    <?php echo str_repeat('★', intval($rev['rating'])) . str_repeat('☆', 5 - intval($rev['rating'])); ?>
                                </span>
                            </div>
                            <p class="text-xs text-stone-600 dark:text-stone-400 leading-relaxed pl-6">
                                <?php echo nl2br(htmlspecialchars($rev['comment'])); ?>
                            </p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="p-10 text-center border border-stone-200 dark:border-stone-800 rounded-2xl text-stone-400 text-xs bg-white dark:bg-stone-900 flex flex-col items-center gap-2 justify-center">
                    <i class="fas fa-comments text-2xl text-stone-300 dark:text-stone-800"></i>
                    <span>No reviews yet. Be the first to leave feedback for this business!</span>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>
<?php amraj_render_public_footer(); ?>