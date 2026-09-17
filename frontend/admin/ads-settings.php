<?php
// ads-settings.php - Multi-banner marketing manager
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once '../../backend/config.php';
require_once 'layout.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$config_path = '../../backend/ad_config.json';
$error_message = '';

function amraj_normalize_banner_config($raw_config) {
    $default_banner = [
        'id' => uniqid('banner_', true),
        'title' => 'Main Banner',
        'text' => 'AMRAJ HUB par apni shop register karein aur customers tak turant pahunchein!',
        'status' => 'active',
        'start_at' => '',
        'end_at' => '',
        'priority' => 1,
    ];

    if (!is_array($raw_config)) {
        return ['version' => 2, 'banners' => [$default_banner]];
    }

    if (isset($raw_config['banners']) && is_array($raw_config['banners'])) {
        $banners = [];
        foreach ($raw_config['banners'] as $banner) {
            $banners[] = [
                'id' => $banner['id'] ?? uniqid('banner_', true),
                'title' => $banner['title'] ?? 'Banner',
                'text' => $banner['text'] ?? '',
                'status' => in_array(($banner['status'] ?? 'active'), ['active', 'paused', 'scheduled'], true) ? $banner['status'] : 'active',
                'start_at' => $banner['start_at'] ?? '',
                'end_at' => $banner['end_at'] ?? '',
                'priority' => max(1, (int) ($banner['priority'] ?? 1)),
            ];
        }

        if (empty($banners)) {
            $banners[] = $default_banner;
        }

        return ['version' => 2, 'banners' => $banners];
    }

    if (isset($raw_config['banner_text']) || isset($raw_config['status'])) {
        return [
            'version' => 2,
            'banners' => [[
                'id' => uniqid('banner_', true),
                'title' => 'Main Banner',
                'text' => $raw_config['banner_text'] ?? $default_banner['text'],
                'status' => $raw_config['status'] ?? 'active',
                'start_at' => '',
                'end_at' => '',
                'priority' => 1,
            ]]
        ];
    }

    return ['version' => 2, 'banners' => [$default_banner]];
}

$current_config = amraj_normalize_banner_config(null);
if (file_exists($config_path)) {
    $decoded_config = json_decode(file_get_contents($config_path), true);
    $current_config = amraj_normalize_banner_config($decoded_config);
}

if (isset($_POST['update_settings'])) {
    $submitted_banners = $_POST['banners'] ?? [];
    $sanitized_banners = [];

    foreach ($submitted_banners as $banner) {
        $text = trim($banner['text'] ?? '');
        if ($text === '') {
            continue;
        }

        $sanitized_banners[] = [
            'id' => preg_replace('/[^a-zA-Z0-9_\-]/', '', $banner['id'] ?? uniqid('banner_', true)),
            'title' => trim($banner['title'] ?? 'Banner'),
            'text' => $text,
            'status' => in_array(($banner['status'] ?? 'active'), ['active', 'paused', 'scheduled'], true) ? $banner['status'] : 'active',
            'start_at' => trim($banner['start_at'] ?? ''),
            'end_at' => trim($banner['end_at'] ?? ''),
            'priority' => max(1, (int) ($banner['priority'] ?? 1)),
        ];
    }

    if (empty($sanitized_banners)) {
        $error_message = 'Kam se kam ek banner text required hai.';
    } else {
        usort($sanitized_banners, function ($left, $right) {
            return $left['priority'] <=> $right['priority'];
        });

        $save_config = [
            'version' => 2,
            'updated_at' => date('c'),
            'banners' => $sanitized_banners,
        ];

        file_put_contents($config_path, json_encode($save_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if (function_exists('amraj_log_admin_action')) {
            amraj_log_admin_action($conn, (int) $_SESSION['user_id'], 'update_ads', 'marketing_config', 0, 'Updated multi-banner marketing settings', ['banner_count' => count($sanitized_banners)]);
        }

        header("Location: ads-settings.php?msg=updated");
        exit();
    }
}

$active_count = 0;
foreach ($current_config['banners'] as $banner) {
    if (($banner['status'] ?? 'active') === 'active') {
        $active_count++;
    }
}

ob_start();
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-200/60 dark:border-stone-800/60 pb-5">
    <div>
        <h1 class="text-xl sm:text-2xl font-black tracking-tight text-stone-900 dark:text-white">
            Global <span class="text-terracotta-700 dark:text-terracotta-400">Marketing Tools</span>
        </h1>
        <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">Multi-banner editor, schedule windows aur live preview yahan se control karein.</p>
    </div>
    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-xs font-semibold text-stone-600 dark:text-stone-300 shadow-sm">
        <i class="fas fa-bullhorn text-red-500"></i>
        <?php echo count($current_config['banners']); ?> Banners | <?php echo $active_count; ?> Active
    </div>
</div>

<?php if ($error_message !== ''): ?>
    <div class="mt-4 mb-5 rounded border border-red-200 dark:border-red-500/20 bg-red-50 dark:bg-red-500/10 px-4 py-3 text-xs font-medium text-red-700 dark:text-red-400">
        <i class="fas fa-circle-exclamation mr-1.5"></i> <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 xl:grid-cols-5 gap-5">
    <div class="xl:col-span-3 space-y-4">
        <form action="ads-settings.php" method="POST" id="bannerForm" class="space-y-4">
            <div id="bannerList" class="space-y-4">
                <?php foreach ($current_config['banners'] as $index => $banner): ?>
                    <div class="banner-card p-4 sm:p-5 rounded-2xl border border-stone-200 dark:border-stone-700/80 bg-white dark:bg-stone-800/80 shadow-sm" data-banner-card>
                        <input type="hidden" name="banners[<?php echo $index; ?>][id]" value="<?php echo htmlspecialchars($banner['id']); ?>">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div>
                                <h3 class="text-sm font-bold text-stone-900 dark:text-white">Banner #<?php echo $index + 1; ?></h3>
                                <p class="text-[10px] text-stone-400 dark:text-stone-500 mt-0.5">Edit copy, schedule window aur priority.</p>
                            </div>
                            <button type="button" class="removeBannerBtn inline-flex items-center justify-center h-8 w-8 rounded-lg border border-stone-200 dark:border-stone-700 text-stone-500 dark:text-stone-400 hover:bg-red-50 dark:hover:bg-red-500/10 hover:text-red-500 transition-colors" title="Remove Banner">
                                <i class="fas fa-trash-can text-[10px]"></i>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="space-y-1.5 block sm:col-span-2">
                                <span class="block text-[10px] uppercase tracking-wider font-black text-stone-400 dark:text-stone-500">Banner Title</span>
                                <input type="text" name="banners[<?php echo $index; ?>][title]" value="<?php echo htmlspecialchars($banner['title']); ?>" class="w-full h-10 px-3 rounded bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-red-500 transition-colors">
                            </label>

                            <label class="space-y-1.5 block sm:col-span-2">
                                <span class="block text-[10px] uppercase tracking-wider font-black text-stone-400 dark:text-stone-500">Banner Text</span>
                                <textarea name="banners[<?php echo $index; ?>][text]" rows="4" required class="w-full px-3 py-2.5 rounded bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-red-500 transition-colors resize-none" placeholder="Type banner copy here..."><?php echo htmlspecialchars($banner['text']); ?></textarea>
                            </label>

                            <label class="space-y-1.5 block">
                                <span class="block text-[10px] uppercase tracking-wider font-black text-stone-400 dark:text-stone-500">Status</span>
                                <select name="banners[<?php echo $index; ?>][status]" class="w-full h-10 px-3 rounded bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-red-500 transition-colors cursor-pointer">
                                    <option value="active" <?php echo ($banner['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="paused" <?php echo ($banner['status'] ?? 'active') === 'paused' ? 'selected' : ''; ?>>Paused</option>
                                    <option value="scheduled" <?php echo ($banner['status'] ?? 'active') === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                </select>
                            </label>

                            <label class="space-y-1.5 block">
                                <span class="block text-[10px] uppercase tracking-wider font-black text-stone-400 dark:text-stone-500">Priority</span>
                                <input type="number" min="1" name="banners[<?php echo $index; ?>][priority]" value="<?php echo (int) ($banner['priority'] ?? 1); ?>" class="w-full h-10 px-3 rounded bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-red-500 transition-colors">
                            </label>

                            <label class="space-y-1.5 block">
                                <span class="block text-[10px] uppercase tracking-wider font-black text-stone-400 dark:text-stone-500">Start At</span>
                                <input type="datetime-local" name="banners[<?php echo $index; ?>][start_at]" value="<?php echo htmlspecialchars(str_replace(' ', 'T', substr($banner['start_at'] ?? '', 0, 16))); ?>" class="w-full h-10 px-3 rounded bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-red-500 transition-colors">
                            </label>

                            <label class="space-y-1.5 block">
                                <span class="block text-[10px] uppercase tracking-wider font-black text-stone-400 dark:text-stone-500">End At</span>
                                <input type="datetime-local" name="banners[<?php echo $index; ?>][end_at]" value="<?php echo htmlspecialchars(str_replace(' ', 'T', substr($banner['end_at'] ?? '', 0, 16))); ?>" class="w-full h-10 px-3 rounded bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-red-500 transition-colors">
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                <button type="button" id="addBannerBtn" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-dashed border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800/80 text-xs font-bold text-stone-600 dark:text-stone-300 hover:border-red-300 hover:text-red-500 transition-all">
                    <i class="fas fa-plus text-[10px]"></i> Add Banner
                </button>
                <button type="submit" name="update_settings" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-olive-500 hover:from-red-600 hover:to-olive-600 text-white font-bold text-xs shadow-sm hover:shadow transition-all duration-200">
                    <i class="fas fa-paper-plane text-[10px]"></i> Save Campaign Set
                </button>
            </div>
        </form>

        <template id="bannerTemplate">
            <div class="banner-card p-4 sm:p-5 rounded-2xl border border-stone-200 dark:border-stone-700/80 bg-white dark:bg-stone-800/80 shadow-sm" data-banner-card>
                <input type="hidden" name="banners[__INDEX__][id]" value="banner___INDEX__">
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-stone-900 dark:text-white">Banner __DISPLAY_INDEX__</h3>
                        <p class="text-[10px] text-stone-400 dark:text-stone-500 mt-0.5">Edit copy, schedule window aur priority.</p>
                    </div>
                    <button type="button" class="removeBannerBtn inline-flex items-center justify-center h-8 w-8 rounded-lg border border-stone-200 dark:border-stone-700 text-stone-500 dark:text-stone-400 hover:bg-red-50 dark:hover:bg-red-500/10 hover:text-red-500 transition-colors" title="Remove Banner">
                        <i class="fas fa-trash-can text-[10px]"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="space-y-1.5 block sm:col-span-2">
                        <span class="block text-[10px] uppercase tracking-wider font-black text-stone-400 dark:text-stone-500">Banner Title</span>
                        <input type="text" name="banners[__INDEX__][title]" value="Banner" class="w-full h-10 px-3 rounded bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-red-500 transition-colors">
                    </label>

                    <label class="space-y-1.5 block sm:col-span-2">
                        <span class="block text-[10px] uppercase tracking-wider font-black text-stone-400 dark:text-stone-500">Banner Text</span>
                        <textarea name="banners[__INDEX__][text]" rows="4" required class="w-full px-3 py-2.5 rounded bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-red-500 transition-colors resize-none" placeholder="Type banner copy here..."></textarea>
                    </label>

                    <label class="space-y-1.5 block">
                        <span class="block text-[10px] uppercase tracking-wider font-black text-stone-400 dark:text-stone-500">Status</span>
                        <select name="banners[__INDEX__][status]" class="w-full h-10 px-3 rounded bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-red-500 transition-colors cursor-pointer">
                            <option value="active">Active</option>
                            <option value="paused">Paused</option>
                            <option value="scheduled">Scheduled</option>
                        </select>
                    </label>

                    <label class="space-y-1.5 block">
                        <span class="block text-[10px] uppercase tracking-wider font-black text-stone-400 dark:text-stone-500">Priority</span>
                        <input type="number" min="1" name="banners[__INDEX__][priority]" value="1" class="w-full h-10 px-3 rounded bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-red-500 transition-colors">
                    </label>

                    <label class="space-y-1.5 block">
                        <span class="block text-[10px] uppercase tracking-wider font-black text-stone-400 dark:text-stone-500">Start At</span>
                        <input type="datetime-local" name="banners[__INDEX__][start_at]" class="w-full h-10 px-3 rounded bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-red-500 transition-colors">
                    </label>

                    <label class="space-y-1.5 block">
                        <span class="block text-[10px] uppercase tracking-wider font-black text-stone-400 dark:text-stone-500">End At</span>
                        <input type="datetime-local" name="banners[__INDEX__][end_at]" class="w-full h-10 px-3 rounded bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-red-500 transition-colors">
                    </label>
                </div>
            </div>
        </template>
    </div>

    <div class="xl:col-span-2 space-y-4">
        <div class="p-5 rounded-2xl border border-stone-200 dark:border-stone-700/80 bg-white dark:bg-stone-800/80 shadow-sm">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-stone-900 dark:text-white">Live Preview</h3>
                    <p class="text-[10px] text-stone-400 dark:text-stone-500 mt-0.5">How banners will appear in active mode.</p>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-medium rounded-md bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/20">Preview</span>
            </div>

            <div class="space-y-3">
                <?php foreach ($current_config['banners'] as $banner): ?>
                    <div class="rounded-xl border border-stone-200 dark:border-stone-700/80 bg-stone-50 dark:bg-stone-900/60 p-4">
                        <div class="flex items-center justify-between gap-3 mb-2">
                            <div class="text-xs font-bold text-stone-900 dark:text-white"><?php echo htmlspecialchars($banner['title']); ?></div>
                            <span class="text-[10px] px-2 py-0.5 rounded-md font-semibold <?php echo ($banner['status'] ?? 'active') === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : (($banner['status'] ?? 'active') === 'scheduled' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-stone-100 text-stone-500 border border-stone-200'); ?>">
                                <?php echo strtoupper($banner['status'] ?? 'active'); ?>
                            </span>
                        </div>
                        <p class="text-xs text-stone-600 dark:text-stone-300 leading-relaxed"><?php echo htmlspecialchars($banner['text']); ?></p>
                        <div class="mt-3 flex flex-wrap gap-2 text-[10px] text-stone-500 dark:text-stone-400">
                            <span class="px-2 py-0.5 rounded-md border border-stone-200 dark:border-stone-700/80 bg-white dark:bg-stone-800/70">Priority <?php echo (int) ($banner['priority'] ?? 1); ?></span>
                            <?php if (!empty($banner['start_at'])): ?>
                                <span class="px-2 py-0.5 rounded-md border border-stone-200 dark:border-stone-700/80 bg-white dark:bg-stone-800/70">From <?php echo htmlspecialchars($banner['start_at']); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($banner['end_at'])): ?>
                                <span class="px-2 py-0.5 rounded-md border border-stone-200 dark:border-stone-700/80 bg-white dark:bg-stone-800/70">Until <?php echo htmlspecialchars($banner['end_at']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="p-5 rounded-2xl border border-stone-200 dark:border-stone-700/80 bg-white dark:bg-stone-800/80 shadow-sm">
            <h3 class="text-sm font-bold text-stone-900 dark:text-white mb-3">How It Works</h3>
            <ul class="space-y-2 text-xs text-stone-500 dark:text-stone-400 leading-relaxed">
                <li>• Active banners show immediately on the frontend.</li>
                <li>• Scheduled banners use start and end timestamps for planned launches.</li>
                <li>• Paused banners stay saved but remain hidden.</li>
            </ul>
        </div>
    </div>
</div>

<script>
(function () {
    const bannerList = document.getElementById('bannerList');
    const template = document.getElementById('bannerTemplate');
    const addBannerBtn = document.getElementById('addBannerBtn');

    function bindRemoveButtons() {
        bannerList.querySelectorAll('.removeBannerBtn').forEach((button) => {
            button.onclick = function () {
                const cards = bannerList.querySelectorAll('[data-banner-card]');
                if (cards.length <= 1) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'At least one banner required',
                        text: 'Single banner ko remove nahi kiya ja sakta.'
                    });
                    return;
                }

                const card = button.closest('[data-banner-card]');
                if (card) {
                    card.remove();
                }
            };
        });
    }

    function refreshIndexes() {
        const cards = bannerList.querySelectorAll('[data-banner-card]');
        cards.forEach((card, index) => {
            card.querySelectorAll('[name]').forEach((field) => {
                field.name = field.name.replace(/banners\[\d+\]/, 'banners[' + index + ']');
            });
            const title = card.querySelector('h3');
            if (title) {
                title.textContent = 'Banner #' + (index + 1);
            }
        });
        bindRemoveButtons();
    }

    addBannerBtn.addEventListener('click', () => {
        const index = bannerList.querySelectorAll('[data-banner-card]').length;
        const html = template.innerHTML
            .replaceAll('__INDEX__', String(index))
            .replace('__DISPLAY_INDEX__', String(index + 1));
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        bannerList.appendChild(wrapper.firstElementChild);
        bindRemoveButtons();
        refreshIndexes();
    });

    bindRemoveButtons();
})();
</script>

<?php
$ads_html = ob_get_clean();
render_admin_layout("Global Marketing Tools", $ads_html, 'ads-settings');
?>