<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/public_layout.php';
amraj_render_public_header('Categories - AMRAJ', 'categories', false);

// Fetch available categories with active business counts
$cat_query = "
    SELECT c.name AS category, c.slug AS category_slug, COUNT(*) as active_count 
    FROM businesses b
    JOIN categories c ON b.category_id = c.id
    WHERE c.name IS NOT NULL AND c.name != ''
    GROUP BY c.name, c.slug
    ORDER BY active_count DESC, c.name ASC";
$cat_res = amraj_query($conn, $cat_query);

// Predefined icon map dictionary for categories
$icon_map = [
    'automotive' => 'fa-car',
    'restaurant' => 'fa-utensils',
    'food' => 'fa-burger',
    'medical' => 'fa-house-medical',
    'health' => 'fa-heart-pulse',
    'shopping' => 'fa-bag-shopping',
    'retail' => 'fa-store',
    'education' => 'fa-graduation-cap',
    'beauty' => 'fa-sparkles',
    'salon' => 'fa-scissors',
    'finance' => 'fa-wallet',
    'real estate' => 'fa-building-shield',
    'tech' => 'fa-laptop-code',
    'legal' => 'fa-scale-balanced'
];

$categories_array = [];
$total_listings_sum = 0;
if ($cat_res) {
    while ($row = $cat_res->fetch_assoc()) {
        $categories_array[] = $row;
        $total_listings_sum += (int)$row['active_count'];
    }
}
$total_categories_count = count($categories_array);
?>

<!-- Main Content -->
<main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 space-y-8">
    
    <!-- Hero Section -->
    <section class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-2xl p-6 sm:p-8 lg:p-10 shadow-sm">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl text-left space-y-3">
                <span class="inline-flex items-center gap-1.5 text-[10px] uppercase tracking-wider font-bold text-stone-700 dark:text-stone-300 bg-stone-100 dark:bg-stone-800 px-3 py-1 rounded-full border border-stone-200 dark:border-stone-700">
                    <i class="fas fa-layer-group text-[9px]"></i> Directory
                </span>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-stone-900 dark:text-white tracking-tight leading-tight">
                    Explore Businesses <span class="text-stone-500 dark:text-stone-400">By Categories</span>
                </h1>
                <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400 leading-relaxed font-normal">
                    Browse through all available categories to quickly locate verified local stores, service providers, and professionals in your area.
                </p>
            </div>
            
            <!-- Quick Stats Blocks -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full lg:w-auto min-w-[280px]">
                <div class="rounded-xl bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 p-4 flex items-center gap-3">
                    <span class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 text-stone-800 dark:text-white text-sm">
                        <i class="fas fa-boxes-stacked"></i>
                    </span>
                    <div>
                        <p class="text-[10px] uppercase font-bold tracking-wider text-stone-400">Total Categories</p>
                        <p class="text-sm text-stone-800 dark:text-stone-200 font-extrabold mt-0.5"><?php echo $total_categories_count; ?></p>
                    </div>
                </div>
                <div class="rounded-xl bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 p-4 flex items-center gap-3">
                    <span class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 text-stone-800 dark:text-white text-sm">
                        <i class="fas fa-chart-pie"></i>
                    </span>
                    <div>
                        <p class="text-[10px] uppercase font-bold tracking-wider text-stone-400">Active Listings</p>
                        <p class="text-sm text-stone-800 dark:text-stone-200 font-extrabold mt-0.5"><?php echo $total_listings_sum; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Search Input -->
        <div class="mt-6 pt-6 border-t border-stone-100 dark:border-stone-800 grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
            <div class="md:col-span-3">
                <label class="text-xs font-bold uppercase tracking-wider text-stone-500 dark:text-stone-400">Filter Categories</label>
            </div>
            <div class="md:col-span-9 relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-stone-400"><i class="fas fa-search text-xs"></i></span>
                <input type="text" id="categorySearchInput" placeholder="Type category name to filter instantly..." class="w-full pl-11 pr-4 py-3 bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 rounded-xl text-stone-800 dark:text-white placeholder-stone-400 text-xs font-medium focus:outline-none focus:border-stone-400 transition-all">
            </div>
        </div>
    </section>

    <!-- Alphabetical Navigation -->
    <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-2xl p-2.5 flex flex-wrap gap-1 justify-center items-center">
        <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400 mr-2">Filter by Letter:</span>
        <button onclick="filterAlphabet('ALL')" class="alphabet-btn active px-2.5 py-1 text-[10px] font-bold rounded-lg bg-stone-900 dark:bg-white text-white dark:text-stone-900 transition-all">ALL</button>
        <?php foreach(range('A', 'Z') as $char): ?>
            <button onclick="filterAlphabet('<?php echo $char; ?>')" class="alphabet-btn px-2.5 py-1 text-[10px] font-semibold rounded-lg bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 text-stone-500 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-900 transition-all"><?php echo $char; ?></button>
        <?php endforeach; ?>
    </div>

    <!-- Categories Grid -->
    <section class="relative">
        <div id="categoriesContainerGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <?php if (!empty($categories_array)): ?>
                <?php foreach ($categories_array as $cat): 
                    $cat_name = trim($cat['category']);
                    $lookup_key = strtolower($cat_name);
                    $selected_icon = 'fa-icons';
                    foreach ($icon_map as $keyword => $icon) {
                        if (str_contains($lookup_key, $keyword)) {
                            $selected_icon = $icon;
                            break;
                        }
                    }
                    $first_letter = strtoupper(substr($cat_name, 0, 1));
                ?>
                    <div class="category-card-item group bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-2xl p-5 hover:border-stone-400 dark:hover:border-stone-600 transition-all duration-200 flex flex-col justify-between shadow-sm" data-name="<?php echo htmlspecialchars($lookup_key); ?>" data-alpha="<?php echo $first_letter; ?>">
                        <div>
                            <div class="flex items-center justify-between gap-3 mb-3">
                                <span class="flex items-center justify-center w-11 h-11 rounded-xl bg-stone-100 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 text-stone-700 dark:text-stone-300 group-hover:bg-stone-900 group-hover:text-white dark:group-hover:bg-white dark:group-hover:text-stone-900 transition-all text-sm">
                                    <i class="fas <?php echo $selected_icon; ?>"></i>
                                </span>
                                <span class="rounded-full bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 px-2.5 py-1 text-[10px] font-bold">
                                    <?php echo $cat['active_count']; ?> Listings
                                </span>
                            </div>
                            <h3 class="font-bold text-base text-stone-900 dark:text-white transition-colors line-clamp-1"><?php echo htmlspecialchars($cat_name); ?></h3>
                            <p class="text-xs text-stone-500 dark:text-stone-400 mt-1 font-normal line-clamp-2">Explore trusted businesses listed under this category.</p>
                        </div>

                        <a href="search.php?category=<?php echo urlencode($cat['category_slug']); ?>" class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold text-stone-900 dark:text-white hover:underline">
                            <span>View Businesses</span> <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- No Results State -->
        <div id="searchEmptyState" class="hidden rounded-2xl border border-dashed border-stone-200 dark:border-stone-800 p-10 text-center max-w-sm mx-auto bg-white dark:bg-stone-900">
            <i class="fas fa-search text-2xl text-stone-300 dark:text-stone-600 block mb-2"></i>
            <p class="font-bold text-sm text-stone-800 dark:text-stone-200">No categories found</p>
            <p class="text-xs text-stone-400 mt-1">Try searching with a different name or letter.</p>
        </div>
    </section>

    <!-- Empty Database Fallback -->
    <?php if (empty($categories_array)): ?>
        <div class="rounded-2xl border border-dashed border-stone-200 dark:border-stone-800 p-12 text-center max-w-sm mx-auto bg-white dark:bg-stone-900">
            <i class="fas fa-layer-group text-3xl text-stone-300 dark:text-stone-600 block mb-2"></i>
            <p class="font-bold text-sm text-stone-800 dark:text-stone-200">No active categories available</p>
            <p class="text-xs text-stone-400 mt-1">Categories will appear here once businesses are added.</p>
        </div>
    <?php endif; ?>

</main>

<!-- Filtering Script -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('categorySearchInput');
    const cards = document.querySelectorAll('.category-card-item');
    const emptyState = document.getElementById('searchEmptyState');
    let currentAlphabet = 'ALL';

    function evaluateFilters() {
        let visibleCount = 0;
        const queryValue = searchInput.value.toLowerCase().trim();

        cards.forEach(card => {
            const cardName = card.getAttribute('data-name') || '';
            const cardAlpha = card.getAttribute('data-alpha') || '';
            
            const matchQuery = cardName.includes(queryValue);
            const matchAlpha = (currentAlphabet === 'ALL' || cardAlpha === currentAlphabet);

            if (matchQuery && matchAlpha) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (visibleCount === 0) {
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', evaluateFilters);
    }

    window.filterAlphabet = function(letter) {
        currentAlphabet = letter;
        
        document.querySelectorAll('.alphabet-btn').forEach(btn => {
            btn.classList.remove('bg-stone-900', 'dark:bg-white', 'text-white', 'dark:text-stone-900', 'active');
            btn.classList.add('bg-stone-50', 'dark:bg-stone-950', 'text-stone-500', 'dark:text-stone-400', 'border', 'border-stone-200', 'dark:border-stone-800');
        });

        const targetBtn = event.currentTarget;
        if(targetBtn) {
            targetBtn.classList.remove('bg-stone-50', 'dark:bg-stone-950', 'text-stone-500', 'dark:text-stone-400', 'border');
            targetBtn.classList.add('bg-stone-900', 'dark:bg-white', 'text-white', 'dark:text-stone-900', 'active');
        }

        evaluateFilters();
    };
});
</script>

<?php amraj_render_public_footer(); ?>