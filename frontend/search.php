<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Load core configurations and layout
require_once '../backend/config.php';
require_once '../backend/search_handler.php';
require_once __DIR__ . '/public_layout.php';

// Sanitize inputs
$query    = isset($_GET['query']) ? trim((string)$_GET['query']) : '';
$location = isset($_GET['location']) ? trim((string)$_GET['location']) : '';
$category = isset($_GET['category']) ? trim((string)$_GET['category']) : '';
$sort_by  = isset($_GET['sort_by']) ? trim((string)$_GET['sort_by']) : 'relevance';
$verified = isset($_GET['verified']) && $_GET['verified'] === '1' ? 1 : 0;
$radius   = isset($_GET['radius']) ? (int)$_GET['radius'] : 25;

$results  = getFilteredShops($query, $location, $category, $sort_by, 25, $verified);
$total    = $results ? (int)$results->num_rows : 0;
$cat_list = amraj_query($conn, "SELECT name, slug FROM categories ORDER BY name ASC");

/**
 * XSS mitigation helper
 */
function safe_out($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * Resource URL cleaner
 */
function safe_url($url) {
    if (empty($url)) {
        return '#';
    }
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        return $url;
    }
    if (preg_match('/^\/?([a-zA-Z0-9_\-]+\/)*[a-zA-Z0-9_\-]+\.[a-zA-Z]{2,4}/', $url)) {
        return $url;
    }
    return '#';
}

// Page header
amraj_render_public_header('Search Businesses', 'search', true);
?>

<!-- Search Bar Section -->
<section class="border-b border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-950">
    <form action="search.php" method="GET" class="max-w-5xl mx-auto px-4 sm:px-6 py-6 flex flex-col sm:flex-row gap-3">
        <div class="flex items-center flex-1 gap-2 px-4 py-3 bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-xl">
            <i class="fas fa-search text-stone-400 text-sm"></i>
            <input type="text" name="query" value="<?php echo safe_out($query); ?>" placeholder="What are you looking for?" class="bg-transparent border-none w-full text-sm text-stone-800 dark:text-white focus:outline-none placeholder-stone-400">
        </div>
        <div class="flex items-center flex-1 gap-2 px-4 py-3 bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-xl">
            <i class="fas fa-map-marker-alt text-stone-400 text-sm"></i>
            <input type="text" name="location" value="<?php echo safe_out($location); ?>" placeholder="City, area or pincode" class="bg-transparent border-none w-full text-sm text-stone-800 dark:text-white focus:outline-none placeholder-stone-400">
        </div>
        <button type="submit" class="px-8 py-3 bg-stone-900 dark:bg-white text-white dark:text-stone-900 text-sm font-medium rounded-xl hover:opacity-90">Search</button>
    </form>
</section>

<main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 grid grid-cols-1 lg:grid-cols-4 gap-8">
    <!-- Sidebar Filters -->
    <aside class="w-full space-y-6">
        <form action="search.php" method="GET" class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-2xl p-5 space-y-5 h-fit lg:sticky lg:top-24">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-sm text-stone-800 dark:text-stone-200">Filters</h3>
                <a href="search.php" class="text-xs text-stone-500 hover:underline">Clear all</a>
            </div>

            <input type="hidden" name="query" value="<?php echo safe_out($query); ?>">
            <input type="hidden" name="location" value="<?php echo safe_out($location); ?>">

            <div>
                <label class="block text-xs font-medium text-stone-500 mb-2">Category</label>
                <select name="category" class="w-full bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 text-sm rounded-xl px-3 py-2.5 text-stone-700 dark:text-stone-300 focus:outline-none">
                    <option value="">All categories</option>
                    <?php if ($cat_list): while ($c = $cat_list->fetch_assoc()): ?>
                        <option value="<?php echo safe_out($c['slug']); ?>" <?php echo $category === $c['slug'] ? 'selected' : ''; ?>><?php echo safe_out($c['name']); ?></option>
                    <?php endwhile; endif; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-stone-500 mb-2">Sort by</label>
                <select name="sort_by" class="w-full bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 text-sm rounded-xl px-3 py-2.5 text-stone-700 dark:text-stone-300 focus:outline-none">
                    <option value="relevance" <?php echo $sort_by === 'relevance' ? 'selected' : ''; ?>>Best match</option>
                    <option value="premium_first" <?php echo $sort_by === 'premium_first' ? 'selected' : ''; ?>>Premium first</option>
                    <option value="alphabetical" <?php echo $sort_by === 'alphabetical' ? 'selected' : ''; ?>>Name (A-Z)</option>
                    <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Newest first</option>
                </select>
            </div>

            <label class="flex items-center gap-2.5 cursor-pointer text-sm text-stone-600 dark:text-stone-300">
                <input type="checkbox" name="verified" value="1" <?php echo $verified ? 'checked' : ''; ?> class="w-4 h-4 rounded border-stone-300 text-stone-900 focus:ring-stone-500"> Verified only
            </label>

            <button type="submit" class="w-full py-2.5 bg-stone-900 dark:bg-white text-white dark:text-stone-900 text-sm font-medium rounded-xl hover:opacity-90">Apply filters</button>
        </form>

        <!-- Popular Categories -->
        <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-2xl p-5">
            <h4 class="font-semibold text-xs text-stone-500 mb-3">Popular Categories</h4>
            <div class="flex flex-wrap gap-2">
                <?php
                $hot_categories = ['Automotive', 'Technology', 'Healthcare', 'Real Estate', 'Logistics', 'Retail'];
                foreach ($hot_categories as $hot_cat):
                    $is_active = (strtolower($category) === strtolower($hot_cat));
                    $target_cat = $is_active ? '' : $hot_cat;
                ?>
                    <a href="search.php?query=<?php echo urlencode($query); ?>&location=<?php echo urlencode($location); ?>&category=<?php echo urlencode($target_cat); ?>&sort_by=<?php echo urlencode($sort_by); ?>" 
                       class="text-xs px-3 py-1.5 rounded-lg border transition-colors <?php echo $is_active ? 'bg-stone-900 text-white dark:bg-white dark:text-stone-900 border-transparent' : 'bg-stone-50 dark:bg-stone-950 border-stone-200 dark:border-stone-800 text-stone-600 dark:text-stone-400 hover:border-stone-300'; ?>">
                        <?php echo safe_out($hot_cat); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>

    <!-- Main Results Column -->
    <div class="lg:col-span-3">
        <p class="text-sm text-stone-500 dark:text-stone-400 pb-4 border-b border-stone-200 dark:border-stone-800 mb-6">
            <?php if ($query !== '' || $location !== '' || $category !== ''): ?>
                <strong class="text-stone-800 dark:text-stone-100"><?php echo $total; ?> result<?php echo $total === 1 ? '' : 's'; ?></strong>
                <?php if ($query !== ''): ?> for "<?php echo safe_out($query); ?>"<?php endif; ?>
                <?php if ($location !== ''): ?> in <?php echo safe_out($location); ?><?php endif; ?>
            <?php else: ?>
                <strong class="text-stone-800 dark:text-stone-100"><?php echo $total; ?> businesses</strong> listed
            <?php endif; ?>
        </p>

        <?php if ($results && $total > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <?php while ($shop = $results->fetch_assoc()): ?>
                    <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-2xl p-5 flex flex-col justify-between relative">
                        
                        <?php if (!empty($shop['is_premium'])): ?>
                            <span class="absolute top-4 right-4 bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 text-[10px] px-2 py-0.5 rounded font-medium">Premium</span>
                        <?php endif; ?>

                        <div class="flex gap-4">
                            <div class="w-20 h-20 rounded-xl bg-stone-100 dark:bg-stone-950 shrink-0 overflow-hidden border border-stone-200 dark:border-stone-800">
                                <?php if (!empty($shop['image_url'])): ?>
                                    <img src="<?php echo safe_url($shop['image_url']); ?>" alt="<?php echo safe_out($shop['title']); ?>" class="w-full h-full object-cover" loading="lazy">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-stone-400">
                                        <i class="fas fa-store"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="space-y-1 pr-12">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-stone-500 font-medium">
                                        <?php echo !empty($shop['category_name']) ? safe_out($shop['category_name']) : 'General'; ?>
                                    </span>
                                    <?php if (!empty($shop['is_verified'])): ?>
                                        <span class="text-xs text-emerald-600 dark:text-emerald-400 flex items-center gap-1"><i class="fas fa-check-circle"></i> Verified</span>
                                    <?php endif; ?>
                                </div>
                                
                                <h3 class="font-semibold text-base text-stone-900 dark:text-white truncate">
                                    <?php echo safe_out($shop['title']); ?>
                                </h3>
                                
                                <p class="text-xs text-stone-500 dark:text-stone-400 truncate">
                                    <i class="fas fa-map-marker-alt mr-1"></i><?php echo safe_out($shop['address']); ?>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-5 pt-3 border-t border-stone-100 dark:border-stone-800">
                            <a href="shop-details.php?id=<?php echo urlencode($shop['id'] ?? ''); ?>" class="text-xs font-medium text-stone-700 dark:text-stone-300 hover:underline">
                                View profile
                            </a>
                            
                            <?php if (!empty($shop['phone'])): ?>
                                <a href="tel:<?php echo safe_out(filter_var($shop['phone'], FILTER_SANITIZE_NUMBER_INT)); ?>" class="text-xs px-3 py-1.5 bg-stone-100 dark:bg-stone-800 text-stone-800 dark:text-stone-200 rounded-lg font-medium hover:bg-stone-200">
                                    <i class="fas fa-phone-alt mr-1"></i> Call
                                </a>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="p-12 text-center bg-stone-50 dark:bg-stone-900/50 border border-stone-200 dark:border-stone-800 rounded-2xl">
                <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-stone-100 dark:bg-stone-800 flex items-center justify-center text-stone-400">
                    <i class="fas fa-search"></i>
                </div>
                <h4 class="font-semibold text-stone-800 dark:text-stone-200 text-sm">No results found</h4>
                <p class="text-xs text-stone-500 mt-1">Try adjusting your search terms or filters.</p>
                <a href="search.php" class="inline-block mt-4 px-4 py-2 bg-stone-900 dark:bg-white text-white dark:text-stone-900 text-xs font-medium rounded-xl">
                    Reset filters
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php 
amraj_render_public_footer(); 
?>