<?php
require_once __DIR__ . '/../backend/config.php';

if (!function_exists('amraj_sanitize_meta')) {
    function amraj_sanitize_meta(string $value): string {
        return htmlspecialchars(trim(preg_replace('/\s+/', ' ', strip_tags($value))), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('amraj_get_base_url')) {
    function amraj_get_base_url(): string {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script_dir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '\\/');
        return $scheme . '://' . $host . ($script_dir === '.' ? '' : $script_dir) . '/';
    }
}

if (!function_exists('amraj_get_current_url')) {
    function amraj_get_current_url(): string {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return $scheme . '://' . $host . $uri;
    }
}

if (!function_exists('amraj_render_public_header')) {
    function amraj_render_public_header(string $page_title, string $active_page = '', bool $show_search = false): void {
        $role = $_SESSION['user_role'] ?? '';
        $dashboard_href = '';
        $title_text = stripos($page_title, 'amraj') === false ? $page_title . ' - AMRAJ' : $page_title;
        $description_map = [
            'home' => 'AMRAJ is a modern local business search directory that helps users discover verified shops, services and restaurants from one unified search platform.',
            'categories' => 'Browse AMRAJ categories to find trusted local businesses in automotive, food, health, shopping, beauty and more.',
            'about' => 'Learn how AMRAJ connects users with local services and verified businesses through an easy public directory and search experience.',
            'contact' => 'Contact the AMRAJ team for support, feedback or partnership inquiries related to our local business directory and search engine.',
            'terms' => 'Review AMRAJ terms of service and platform usage policies for business listings, user accounts and directory access.',
            'privacy' => 'Read AMRAJ privacy policies to understand how we protect user data, search behavior and account information.',
            'register' => 'Create an AMRAJ account to list businesses, save shops and access local search features.',
            'search' => 'Use the AMRAJ search engine to find local businesses, shops, restaurants and services across categories and locations.',
        ];
        $page_description = $description_map[$active_page] ?? "Discover " . trim($page_title) . " on AMRAJ, your trusted local search and directory engine.";
        $page_description = amraj_sanitize_meta($page_description);
        $canonical_url = amraj_get_current_url();
        $base_url = amraj_get_base_url();
        $og_image = $base_url . 'assets/og-default.svg';
        if ($role === 'admin' || $role === 'owner' || $role === 'user') {
            $dashboard_href = $role . '/dashboard.php';
        }
        ?>
        <!DOCTYPE html>
        <html lang="en" class="h-full scroll-smooth">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="robots" content="index, follow">
            <meta name="description" content="<?php echo $page_description; ?>">
            <meta name="keywords" content="AMRAJ, local search, business directory, shop finder, nearby services, verified businesses, local marketplace">
            <link rel="canonical" href="<?php echo htmlspecialchars($canonical_url, ENT_QUOTES, 'UTF-8'); ?>">
            <link rel="icon" href="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>assets/icons/favicon-32.png" type="image/png">
            <link rel="icon" href="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>assets/logo-icon.svg" type="image/svg+xml">
            <meta property="og:title" content="<?php echo amraj_sanitize_meta($title_text); ?>">
            <meta property="og:description" content="<?php echo $page_description; ?>">
            <meta property="og:type" content="website">
            <meta property="og:url" content="<?php echo htmlspecialchars($canonical_url, ENT_QUOTES, 'UTF-8'); ?>">
            <meta property="og:site_name" content="AMRAJ">
            <meta property="og:image" content="<?php echo htmlspecialchars($og_image, ENT_QUOTES, 'UTF-8'); ?>">
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="<?php echo amraj_sanitize_meta($title_text); ?>">
            <meta name="twitter:description" content="<?php echo $page_description; ?>">
            <meta name="twitter:image" content="<?php echo htmlspecialchars($og_image, ENT_QUOTES, 'UTF-8'); ?>">
            <meta name="theme-color" content="#241f18">
            <title><?php echo amraj_sanitize_meta($title_text); ?></title>
            <!-- PWA -->
            <link rel="manifest" href="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>manifest.json">
            <link rel="apple-touch-icon" href="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>assets/icons/icon-192.png">
            <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <link rel="stylesheet" href="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>assets/css/style.css">
            <script>
                tailwind.config = { darkMode: 'class', theme: { extend: { colors: {
                        terracotta: {
                            50: '#fdf3ef', 100: '#fae2d7', 200: '#f2c3a7', 300: '#e79c6f',
                            400: '#dc7a4a', 500: '#c85f34', 600: '#b5502f', 700: '#943e25',
                            800: '#78321f', 900: '#5c2717'
                        },
                        olive: {
                            50: '#f6f7ee', 100: '#e9edd6', 200: '#d3dbae', 300: '#b6c47e',
                            400: '#98ac59', 500: '#78913f', 600: '#5c6b3f', 700: '#4a5732',
                            800: '#3a4527', 900: '#2b331d'
                        },
                        ink: '#241f18',
                        paper: '#f6f1e7'
                    }, fontFamily: { serif: ['Fraunces','serif'], sans: ['Inter','ui-sans-serif','system-ui','sans-serif'] } } } };
                (function () {
                    var saved = localStorage.getItem('amraj-theme');
                    var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                    var useDark = saved ? saved === 'dark' : prefersDark;
                    document.documentElement.classList.toggle('dark', useDark);
                    document.documentElement.classList.toggle('light', !useDark);
                })();
            </script>
            <script>
                function amrajToggleTheme() {
                    var root = document.documentElement;
                    var isDark = root.classList.contains('dark');
                    root.classList.toggle('dark', !isDark);
                    root.classList.toggle('light', isDark);
                    localStorage.setItem('amraj-theme', !isDark ? 'dark' : 'light');
                }
                if ('serviceWorker' in navigator) {
                    window.addEventListener('load', function () {
                        navigator.serviceWorker.register('<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>service-worker.js').catch(function () {});
                    });
                }
            </script>
        </head>
        <body class="min-h-screen flex flex-col font-sans bg-stone-50 text-stone-900 dark:bg-stone-950 dark:text-stone-100 transition-colors duration-200">
            <!-- Main Navigation -->
            <nav class="sticky top-0 z-50 bg-white/80 dark:bg-stone-950/80 backdrop-blur-md border-b border-stone-200 dark:border-stone-900 px-4 py-3.5 sm:px-8 flex flex-col sm:flex-row sm:justify-between sm:items-center shadow-sm dark:shadow-none">
                <div class="flex items-center justify-between w-full sm:w-auto">
                    <a href="index.php" class="flex items-center gap-2.5 leading-none group">
                        <img src="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>assets/icons/icon-192.png" alt="AMRAJ logo" class="h-9 w-9 rounded-lg shadow-sm shrink-0">
                        <span class="flex flex-col leading-none">
                            <span class="text-2xl font-black tracking-tight text-terracotta-700 dark:text-terracotta-400">AMRAJ</span>
                            <span class="text-[9px] text-stone-500 dark:text-stone-400 uppercase tracking-widest font-semibold mt-0.5">Every Need, Just One Search.</span>
                        </span>
                    </a>

                    <!-- Mobile Right Utilities -->
                    <div class="flex items-center gap-2.5 sm:hidden">
                        <button type="button" onclick="amrajToggleTheme()" class="w-9 h-9 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 flex items-center justify-center text-stone-600 dark:text-stone-400" aria-label="Toggle theme">
                            <i class="fas fa-moon dark:hidden text-terracotta-600 text-xs"></i>
                            <i class="fas fa-sun hidden dark:inline text-amber-400 text-xs"></i>
                        </button>
                        <button type="button" class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 text-stone-600 dark:text-stone-300 transition-all active:scale-95" data-public-nav-toggle aria-label="Toggle navigation">
                            <i class="fas fa-bars text-sm transition-transform duration-200" id="menu-icon"></i>
                        </button>
                    </div>
                </div>

                <!-- Desktop Navigation Menu -->
                <div class="hidden sm:flex items-center gap-6 text-xs font-bold tracking-tight text-stone-600 dark:text-stone-400">
                    <a href="index.php" class="hover:text-terracotta-600 dark:hover:text-olive-400 transition-colors <?php echo $active_page === 'home' ? 'text-terracotta-600 dark:text-olive-400 font-extrabold' : ''; ?>">Home</a>
                    <a href="categories.php" class="hover:text-terracotta-600 dark:hover:text-olive-400 transition-colors <?php echo $active_page === 'categories' ? 'text-terracotta-600 dark:text-olive-400 font-extrabold' : ''; ?>">Categories</a>
                    <a href="contact.php" class="hover:text-terracotta-600 dark:hover:text-olive-400 transition-colors <?php echo $active_page === 'contact' ? 'text-terracotta-600 dark:text-olive-400 font-extrabold' : ''; ?>">Contact</a>
                    <a href="about.php" class="hover:text-terracotta-600 dark:hover:text-olive-400 transition-colors <?php echo $active_page === 'about' ? 'text-terracotta-600 dark:text-olive-400 font-extrabold' : ''; ?>">About</a>

                    <div class="h-4 w-px bg-stone-200 dark:bg-stone-800 mx-1"></div>

                    <?php if ($dashboard_href !== ''): ?>
                        <div class="flex items-center gap-2">
                            <a href="<?php echo htmlspecialchars($dashboard_href); ?>" class="px-3.5 py-2 bg-stone-900 dark:bg-stone-100 hover:opacity-90 text-[11px] font-bold uppercase tracking-wider rounded-xl flex items-center gap-2 text-white dark:text-stone-950 shadow-sm transition-all">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span><?php echo htmlspecialchars(ucfirst($role)); ?> Dashboard</span>
                            </a>
                            <a href="../backend/logout.php" class="w-9 h-9 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 flex items-center justify-center text-stone-400 hover:text-rose-600 transition-colors" title="Log Out"><i class="fas fa-sign-out-alt text-xs"></i></a>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center gap-3">
                            <a href="login.php" class="hover:text-terracotta-600 dark:hover:text-olive-400 transition-colors">Log In</a>
                            <a href="register.php" class="px-4 py-2 bg-terracotta-600 hover:bg-terracotta-700 text-white font-bold tracking-wide text-xs rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                                <span>Get Started</span> <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Desktop Skin Switcher -->
                    <button type="button" onclick="amrajToggleTheme()" class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 text-stone-600 dark:text-stone-400 hover:scale-105 active:scale-95 shadow-sm transition-all duration-200" aria-label="Toggle theme">
                        <i class="fas fa-moon dark:hidden text-terracotta-600 text-xs"></i>
                        <i class="fas fa-sun hidden dark:inline text-amber-400 text-xs"></i>
                    </button>
                </div>

                <!-- Mobile Drawer -->
                <div class="sm:hidden hidden flex-col gap-2 text-sm font-semibold text-stone-600 dark:text-stone-400 border-t border-stone-100 dark:border-stone-900 mt-3 pt-4 pb-2 transition-all duration-300" data-public-nav-panel>
                    <a href="index.php" class="flex items-center justify-between p-2.5 rounded-xl transition-all <?php echo $active_page === 'home' ? 'bg-terracotta-50 dark:bg-stone-900 text-terracotta-600 dark:text-olive-400 font-bold' : 'hover:bg-stone-100 dark:hover:bg-stone-900'; ?>">
                        <span>Home</span> <i class="fas fa-chevron-right text-[10px] opacity-40"></i>
                    </a>
                    <a href="categories.php" class="flex items-center justify-between p-2.5 rounded-xl transition-all <?php echo $active_page === 'categories' ? 'bg-terracotta-50 dark:bg-stone-900 text-terracotta-600 dark:text-olive-400 font-bold' : 'hover:bg-stone-100 dark:hover:bg-stone-900'; ?>">
                        <span>Categories</span> <i class="fas fa-chevron-right text-[10px] opacity-40"></i>
                    </a>
                    <a href="contact.php" class="flex items-center justify-between p-2.5 rounded-xl transition-all <?php echo $active_page === 'contact' ? 'bg-terracotta-50 dark:bg-stone-900 text-terracotta-600 dark:text-olive-400 font-bold' : 'hover:bg-stone-100 dark:hover:bg-stone-900'; ?>">
                        <span>Contact</span> <i class="fas fa-chevron-right text-[10px] opacity-40"></i>
                    </a>
                    <a href="about.php" class="flex items-center justify-between p-2.5 rounded-xl transition-all <?php echo $active_page === 'about' ? 'bg-terracotta-50 dark:bg-stone-900 text-terracotta-600 dark:text-olive-400 font-bold' : 'hover:bg-stone-100 dark:hover:bg-stone-900'; ?>">
                        <span>About</span> <i class="fas fa-chevron-right text-[10px] opacity-40"></i>
                    </a>
                    <a href="terms.php" class="flex items-center justify-between p-2.5 rounded-xl transition-all <?php echo $active_page === 'terms' ? 'bg-terracotta-50 dark:bg-stone-900 text-terracotta-600 dark:text-olive-400 font-bold' : 'hover:bg-stone-100 dark:hover:bg-stone-900'; ?>">
                        <span>Terms of Service</span> <i class="fas fa-chevron-right text-[10px] opacity-40"></i>
                    </a>
                    <a href="privacy-policy.php" class="flex items-center justify-between p-2.5 rounded-xl transition-all <?php echo $active_page === 'privacy' ? 'bg-terracotta-50 dark:bg-stone-900 text-terracotta-600 dark:text-olive-400 font-bold' : 'hover:bg-stone-100 dark:hover:bg-stone-900'; ?>">
                        <span>Privacy Policy</span> <i class="fas fa-chevron-right text-[10px] opacity-40"></i>
                    </a>

                    <div class="h-px bg-stone-100 dark:bg-stone-900 my-2"></div>

                    <div class="pt-2">
                        <?php if ($dashboard_href !== ''): ?>
                            <div class="flex flex-col gap-2">
                                <a href="<?php echo htmlspecialchars($dashboard_href); ?>" class="w-full py-3 bg-stone-900 dark:bg-stone-100 text-white dark:text-stone-950 font-bold text-center rounded-xl shadow-sm text-xs tracking-wider uppercase"><i class="fas fa-th-large mr-1.5 text-terracotta-500"></i> Open Dashboard</a>
                                <a href="../backend/logout.php" class="w-full py-3 bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400 font-bold text-center rounded-xl text-xs tracking-wider uppercase"><i class="fas fa-sign-out-alt mr-1.5"></i> Logout Account</a>
                            </div>
                        <?php else: ?>
                            <a href="register.php" class="w-full py-3.5 bg-gradient-to-r from-terracotta-600 via-terracotta-600 to-olive-500 text-white font-black text-center rounded-xl shadow-md tracking-widest text-[11px] uppercase flex items-center justify-center gap-2">
                                <span>Register Profile</span> <i class="fas fa-shield-alt text-[10px]"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var btn = document.querySelector('[data-public-nav-toggle]');
                    var panel = document.querySelector('[data-public-nav-panel]');
                    var icon = document.getElementById('menu-icon');
                    if (btn && panel) {
                        btn.addEventListener('click', function () {
                            var isHidden = panel.classList.contains('hidden');
                            panel.classList.toggle('hidden', !isHidden);
                            panel.classList.toggle('flex', isHidden);

                            if (isHidden) {
                                icon.classList.remove('fa-bars');
                                icon.classList.add('fa-times');
                            } else {
                                icon.classList.remove('fa-times');
                                icon.classList.add('fa-bars');
                            }
                        });
                    }
                });
            </script>
        <?php
    }
}

if (!function_exists('amraj_render_public_footer')) {
    function amraj_render_public_footer(): void {
        ?>
            <!-- Site Footer -->
            <footer class="mt-auto border-t border-stone-200 dark:border-stone-900 bg-white dark:bg-stone-950 py-12 px-6 sm:px-12 transition-colors duration-300">
                <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8 mb-10 text-left">
                    <div class="space-y-3 md:col-span-1">
                        <div class="flex items-center gap-2">
                            <img src="<?php echo htmlspecialchars(amraj_get_base_url(), ENT_QUOTES, 'UTF-8'); ?>assets/icons/icon-192.png" alt="AMRAJ logo" class="h-8 w-8 rounded-lg shadow-sm">
                            <span class="text-xl font-black text-terracotta-700 dark:text-terracotta-400">AMRAJ</span>
                        </div>
                        <p class="text-xs text-stone-400 dark:text-stone-500 font-medium leading-relaxed">
                            AMRAJ helps you discover verified local shops, services and restaurants in your city — all in one place.
                        </p>
                    </div>
                    <div>
                        <h4 class="text-[11px] font-black uppercase tracking-widest text-stone-400 dark:text-stone-600 mb-3.5">Explore</h4>
                        <ul class="space-y-2 text-xs font-semibold text-stone-500 dark:text-stone-400">
                            <li><a href="categories.php" class="hover:text-terracotta-500 dark:hover:text-olive-400 transition-colors">Categories</a></li>
                            <li><a href="index.php" class="hover:text-terracotta-500 dark:hover:text-olive-400 transition-colors">Search</a></li>
                            <li><a href="register.php" class="hover:text-terracotta-500 dark:hover:text-olive-400 transition-colors">List Your Business</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-[11px] font-black uppercase tracking-widest text-stone-400 dark:text-stone-600 mb-3.5">Company</h4>
                        <ul class="space-y-2 text-xs font-semibold text-stone-500 dark:text-stone-400">
                            <li><a href="about.php" class="hover:text-terracotta-500 dark:hover:text-olive-400 transition-colors">About Us</a></li>
                            <li><a href="contact.php" class="hover:text-terracotta-500 dark:hover:text-olive-400 transition-colors">Contact</a></li>
                            <li><a href="#" class="hover:text-terracotta-500 dark:hover:text-olive-400 transition-colors">Status</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-[11px] font-black uppercase tracking-widest text-stone-400 dark:text-stone-600 mb-3.5">Legal</h4>
                        <ul class="space-y-2 text-xs font-semibold text-stone-500 dark:text-stone-400">
                            <li><a href="privacy-policy.php" class="hover:text-terracotta-500 dark:hover:text-olive-400 transition-colors">Privacy Policy</a></li>
                            <li><a href="terms.php" class="hover:text-terracotta-500 dark:hover:text-olive-400 transition-colors">Terms of Service</a></li>
                        </ul>
                    </div>
                </div>

                <div class="max-w-7xl mx-auto h-px bg-stone-200 dark:bg-stone-900 my-6"></div>

                <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4 text-[11px] font-medium text-stone-400 dark:text-stone-500">
                    <p class="text-center md:text-left">&copy; <?php echo date('Y'); ?> <span class="font-bold text-stone-700 dark:text-stone-300">AMRAJ</span>. Built to help local businesses get found.</p>
                    <div class="flex items-center gap-4 text-xs">
                        <a href="#" class="text-stone-400 hover:text-terracotta-500 dark:hover:text-olive-500"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-stone-400 hover:text-terracotta-500 dark:hover:text-olive-500"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-stone-400 hover:text-terracotta-500 dark:hover:text-olive-500"><i class="fab fa-github"></i></a>
                    </div>
                </div>
            </footer>
        </body>
        </html>
        <?php
    }
}
?>