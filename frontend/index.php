<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/public_layout.php';

$categories_res = amraj_query($conn, "SELECT * FROM categories ORDER BY name ASC LIMIT 12");

$premium_query = "
    SELECT b.*, c.name as category_name 
    FROM businesses b 
    LEFT JOIN categories c ON b.category_id = c.id 
    WHERE b.is_premium = 1 AND b.status = 'approved' 
    ORDER BY b.id DESC LIMIT 8";
$premium_res = amraj_query($conn, $premium_query);

$site_counts = amraj_get_site_counts($conn);
$visit_counts = amraj_track_visit($conn);
$total_biz = $site_counts['businesses'];
$total_cats = $site_counts['categories'];
$total_users = $site_counts['users'];

amraj_render_public_header('AMRAJ - Find Local Businesses Near You', 'home', false);
?>

<!-- Hero Search Section -->
<section class="py-16 sm:py-24 px-4 sm:px-6 flex flex-col items-center justify-center text-center border-b border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-950">
    <div class="max-w-4xl mx-auto space-y-6">
        
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-stone-100 dark:bg-stone-900 text-stone-600 dark:text-stone-300 text-xs font-medium border border-stone-200 dark:border-stone-800 mx-auto">
            <i class="fas fa-shield-alt text-[11px] text-stone-400"></i> Trusted by Local Shoppers
        </div>

        <h1 class="text-3xl sm:text-5xl font-bold tracking-tight text-stone-900 dark:text-white leading-tight">
            Find Local Businesses & Services <br class="hidden sm:inline">
            <span class="text-stone-500 dark:text-stone-400">In Your City Instantly</span>
        </h1>
        
        <p class="text-stone-600 dark:text-stone-400 text-sm sm:text-base max-w-xl mx-auto">
            Connect quickly with verified local stores, medical shops, repair services, and institutes near you.
        </p>
        
        <!-- Search Form -->
        <form action="search.php" method="GET" class="w-full max-w-3xl mt-6 p-2 bg-stone-50 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-2xl sm:rounded-full flex flex-col sm:flex-row items-center gap-2 shadow-sm">
            <div class="flex items-center w-full px-4 gap-3 border-b sm:border-b-0 sm:border-r border-stone-200 dark:border-stone-800 pb-3 sm:pb-0">
                <i class="fas fa-search text-stone-400 text-sm shrink-0"></i>
                <input type="text" name="query" placeholder="What are you looking for? (e.g. Doctor, Salon)" required class="w-full bg-transparent border-none text-stone-800 dark:text-white placeholder-stone-400 focus:outline-none text-sm">
            </div>
            <div class="flex items-center w-full px-4 gap-3">
                <i class="fas fa-map-marker-alt text-stone-400 text-sm shrink-0"></i>
                <input type="text" name="location" placeholder="City or Pincode" class="w-full bg-transparent border-none text-stone-800 dark:text-white placeholder-stone-400 focus:outline-none text-sm">
            </div>
            <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-stone-900 dark:bg-white text-white dark:text-stone-900 font-medium rounded-xl sm:rounded-full hover:opacity-90 transition-colors text-sm shrink-0">
                Search
            </button>
        </form>

        <!-- Platform Stats -->
        <div class="flex flex-wrap items-center justify-center gap-6 pt-4 text-stone-500 dark:text-stone-400 text-xs">
            <div>Businesses: <strong class="text-stone-800 dark:text-white font-semibold"><?php echo $total_biz > 0 ? $total_biz . '+' : 'Growing'; ?></strong></div>
            <div>Categories: <strong class="text-stone-800 dark:text-white font-semibold"><?php echo $total_cats; ?></strong></div>
            <div>Users: <strong class="text-stone-800 dark:text-white font-semibold"><?php echo $total_users; ?></strong></div>
            <div>Today Visits: <strong class="text-stone-800 dark:text-white font-semibold"><?php echo $visit_counts['today_visits']; ?></strong></div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-12 px-4 sm:px-6 max-w-7xl mx-auto w-full space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 border-b border-stone-200 dark:border-stone-800 pb-4">
        <div>
            <h2 class="text-xl font-bold text-stone-900 dark:text-white tracking-tight">Browse Categories</h2>
            <p class="text-xs text-stone-500 dark:text-stone-400">Explore popular business categories to find what you need.</p>
        </div>
        <a href="categories.php" class="text-xs font-medium text-stone-700 dark:text-stone-300 hover:underline shrink-0">
            View all categories &rarr;
        </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <?php if($categories_res && $categories_res->num_rows > 0): ?>
            <?php while($cat = $categories_res->fetch_assoc()): ?>
                <a href="search.php?category=<?php echo urlencode($cat['slug']); ?>" class="p-4 bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-2xl hover:border-stone-400 dark:hover:border-stone-600 transition-colors text-center flex flex-col items-center justify-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 flex items-center justify-center">
                        <i class="fas <?php echo !empty($cat['icon']) ? htmlspecialchars($cat['icon']) : 'fa-box'; ?> text-sm"></i>
                    </div>
                    <span class="text-xs font-medium text-stone-700 dark:text-stone-300 truncate w-full px-1">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </span>
                </a>
            <?php endwhile; unset($cat); ?>
        <?php else: ?>
            <?php 
            $fallback_cats = [
                ['name' => 'Medical', 'icon' => 'fa-house-medical', 'slug' => 'medical'],
                ['name' => 'Automotive', 'icon' => 'fa-car', 'slug' => 'automotive'],
                ['name' => 'Restaurants', 'icon' => 'fa-utensils', 'slug' => 'restaurants'],
                ['name' => 'Retail Stores', 'icon' => 'fa-store', 'slug' => 'retail'],
                ['name' => 'Financial', 'icon' => 'fa-wallet', 'slug' => 'finance'],
                ['name' => 'Education', 'icon' => 'fa-graduation-cap', 'slug' => 'education']
            ];
            foreach($fallback_cats as $fcat): ?>
                <a href="search.php?category=<?php echo $fcat['slug']; ?>" class="p-4 bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-2xl hover:border-stone-400 dark:hover:border-stone-600 transition-colors text-center flex flex-col items-center justify-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 flex items-center justify-center">
                        <i class="fas <?php echo $fcat['icon']; ?> text-sm"></i>
                    </div>
                    <span class="text-xs font-medium text-stone-700 dark:text-stone-300 truncate w-full"><?php echo $fcat['name']; ?></span>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Premium Featured Businesses -->
<section class="py-10 px-4 sm:px-6 max-w-7xl mx-auto w-full space-y-6">
    <div class="border-b border-stone-200 dark:border-stone-800 pb-4">
        <h2 class="text-xl font-bold text-stone-900 dark:text-white tracking-tight">Featured Businesses</h2>
        <p class="text-xs text-stone-500 dark:text-stone-400">Top-rated and verified businesses offering quality services.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php if($premium_res && $premium_res->num_rows > 0): ?>
            <?php while($shop = $premium_res->fetch_assoc()): ?>
                <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-2xl overflow-hidden relative flex flex-col justify-between">
                    
                    <?php if(!empty($shop['is_premium'])): ?>
                        <span class="absolute top-3 right-3 bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 text-[10px] px-2 py-0.5 rounded font-medium z-10">Premium</span>
                    <?php endif; ?>
                    
                    <!-- Image -->
                    <div class="w-full h-40 bg-stone-100 dark:bg-stone-950 overflow-hidden relative border-b border-stone-200 dark:border-stone-800">
                        <?php if(!empty($shop['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($shop['image_url']); ?>" alt="<?php echo htmlspecialchars($shop['title']); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-stone-400">
                                <i class="fas fa-store text-2xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Details -->
                    <div class="p-4 flex-1 flex flex-col justify-between space-y-4">
                        <div class="space-y-1">
                            <span class="text-xs text-stone-500 font-medium">
                                <?php echo htmlspecialchars($shop['category_name'] ?? 'General'); ?>
                            </span>
                            <h3 class="font-semibold text-base text-stone-900 dark:text-white truncate">
                                <?php echo htmlspecialchars($shop['title']); ?>
                            </h3>
                            <p class="text-xs text-stone-500 dark:text-stone-400 truncate">
                                <i class="fas fa-map-marker-alt text-[10px] mr-1"></i>
                                <?php echo htmlspecialchars($shop['address']); ?>
                            </p>
                        </div>
                        
                        <!-- Actions -->
                        <div class="grid grid-cols-2 gap-2 pt-3 border-t border-stone-100 dark:border-stone-800">
                            <?php if(!empty($shop['phone'])): ?>
                                <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $shop['phone']); ?>" class="py-2 bg-stone-100 dark:bg-stone-800 text-stone-800 dark:text-stone-200 text-xs font-medium text-center rounded-xl hover:bg-stone-200 transition-colors">
                                    <i class="fas fa-phone-alt mr-1"></i> Call
                                </a>
                            <?php else: ?>
                                <span class="py-2 bg-stone-50 dark:bg-stone-900 text-stone-400 text-xs text-center rounded-xl select-none">
                                    No Phone
                                </span>
                            <?php endif; ?>
                            
                            <a href="shop-details.php?id=<?php echo $shop['id']; ?>" class="py-2 bg-stone-900 dark:bg-white text-white dark:text-stone-900 text-xs font-medium text-center rounded-xl hover:opacity-90 transition-opacity">
                                View Profile
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-span-full p-8 bg-stone-50 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-2xl text-center text-stone-500 text-xs">
                No featured businesses available right now.
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-12 bg-stone-50 dark:bg-stone-900/50 border-t border-b border-stone-200 dark:border-stone-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 space-y-8">
        <div class="text-center max-w-xl mx-auto space-y-1">
            <h2 class="text-xl font-bold text-stone-900 dark:text-white">Why Choose Our Platform?</h2>
            <p class="text-xs text-stone-500 dark:text-stone-400">We help you connect directly with genuine local service providers quickly.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="p-6 bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 space-y-2">
                <div class="w-8 h-8 rounded-lg bg-stone-100 dark:bg-stone-800 text-stone-800 dark:text-stone-200 flex items-center justify-center text-sm"><i class="fas fa-check"></i></div>
                <h3 class="text-sm font-semibold text-stone-900 dark:text-white">Verified Listings</h3>
                <p class="text-xs text-stone-500 dark:text-stone-400 leading-relaxed">Every business profile is reviewed to ensure accurate information and reliable contacts.</p>
            </div>
            <div class="p-6 bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 space-y-2">
                <div class="w-8 h-8 rounded-lg bg-stone-100 dark:bg-stone-800 text-stone-800 dark:text-stone-200 flex items-center justify-center text-sm"><i class="fas fa-bolt"></i></div>
                <h3 class="text-sm font-semibold text-stone-900 dark:text-white">Direct Contact</h3>
                <p class="text-xs text-stone-500 dark:text-stone-400 leading-relaxed">Connect directly via phone calls or visit locations without going through intermediaries.</p>
            </div>
            <div class="p-6 bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 space-y-2">
                <div class="w-8 h-8 rounded-lg bg-stone-100 dark:bg-stone-800 text-stone-800 dark:text-stone-200 flex items-center justify-center text-sm"><i class="fas fa-search-location"></i></div>
                <h3 class="text-sm font-semibold text-stone-900 dark:text-white">Local Search</h3>
                <p class="text-xs text-stone-500 dark:text-stone-400 leading-relaxed">Quickly search businesses by category or location name within your immediate zone.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call To Action Banner -->
<section class="py-12 px-4 sm:px-6 max-w-7xl mx-auto w-full mb-8">
    <div class="bg-stone-900 dark:bg-stone-900 border border-stone-800 rounded-3xl p-8 flex flex-col md:flex-row items-center justify-between gap-6 text-white">
        <div class="space-y-2 text-center md:text-left">
            <span class="text-xs text-stone-400 font-medium uppercase tracking-wider">Grow Your Business</span>
            <h2 class="text-2xl font-bold">List Your Business With Us Today</h2>
            <p class="text-xs text-stone-400 max-w-md">Reach local customers searching for your services every day. Listing is quick and straightforward.</p>
        </div>
        <div>
            <a href="register.php" class="px-6 py-3 bg-white text-stone-900 font-medium text-xs rounded-xl hover:bg-stone-100 transition-colors">
                Register Business
            </a>
        </div>
    </div>
</section>

<?php amraj_render_public_footer(); ?>