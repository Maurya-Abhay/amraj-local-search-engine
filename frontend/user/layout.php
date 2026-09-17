<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../backend/config.php';

// Strict session check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}

if (!function_exists('amraj_render_user_layout')) {
    function amraj_render_user_layout(string $page_title, string $content_html, string $current_page = 'dashboard'): void {
        $base_url = '../'; // Adjust based on user folder depth (e.g. user/ subfolders)
        $user_name = $_SESSION['user_name'] ?? 'Authorized User';
        $user_id = $_SESSION['user_id'] ?? 0;
        ?>
        <!DOCTYPE html>
        <html lang="en" class="h-full">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo htmlspecialchars($page_title); ?> - AMRAJ Consumer Portal</title>
            <link rel="icon" href="<?php echo $base_url; ?>assets/icons/favicon-32.png" type="image/png">
            <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/style.css">
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
                // Theme initializer to prevent FOUC
                (function () {
                    var saved = localStorage.getItem('amraj-theme');
                    var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    document.documentElement.classList.toggle('dark', saved ? saved === 'dark' : prefersDark);
                })();
            </script>
        </head>
        <body class="min-h-screen flex flex-col md:flex-row bg-stone-50 text-stone-900 dark:bg-stone-950 dark:text-stone-100 font-sans antialiased transition-colors duration-200">
            
            <!-- Mobile Header Bar (Fixed / Sticky) -->
            <header class="md:hidden bg-white/90 dark:bg-stone-900/90 backdrop-blur-md border-b border-stone-200 dark:border-stone-800 px-4 py-3 flex items-center justify-between sticky top-0 z-30 shadow-sm">
                <div class="flex items-center gap-3">
                    <button type="button" onclick="toggleMobileSidebar()" class="w-9 h-9 rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-800 flex items-center justify-center text-stone-600 dark:text-stone-400">
                        <i class="fas fa-bars text-xs"></i>
                    </button>
                    <div>
                        <span class="text-sm font-black text-terracotta-700 dark:text-terracotta-400">AMRAJ</span>
                        <span class="text-[9px] text-stone-400 uppercase tracking-widest font-bold block">Portal</span>
                    </div>
                </div>
                <button type="button" onclick="amrajToggleTheme()" class="w-9 h-9 rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-800 flex items-center justify-center text-stone-600 dark:text-stone-400">
                    <i class="fas fa-moon dark:hidden text-terracotta-600 text-xs"></i>
                    <i class="fas fa-sun hidden dark:inline text-amber-400 text-xs"></i>
                </button>
            </header>

            <!-- Sidebar Overlay for Mobile Drawer -->
            <div id="sidebarOverlay" onclick="toggleMobileSidebar()" class="fixed inset-0 bg-stone-900/50 backdrop-blur-sm z-40 hidden md:hidden transition-opacity"></div>

            <!-- Responsive Sidebar Drawer -->
            <aside id="mobileSidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-white dark:bg-stone-900 border-r border-stone-200 dark:border-stone-800 p-6 flex flex-col justify-between transform -translate-x-full md:translate-x-0 md:static md:w-64 transition-transform duration-300 shadow-2xl md:shadow-none">
                <div>
                    <!-- Brand Header -->
                    <div class="flex items-center justify-between mb-8 pb-4 border-b border-stone-100 dark:border-stone-800">
                        <div>
                            <span class="text-lg font-black text-terracotta-700 dark:text-terracotta-400">AMRAJ</span>
                            <span class="block text-[10px] text-stone-400 uppercase tracking-widest font-bold">Consumer Portal</span>
                        </div>
                        <button type="button" onclick="amrajToggleTheme()" class="hidden md:flex w-9 h-9 rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-800 items-center justify-center text-stone-600 dark:text-stone-400">
                            <i class="fas fa-moon dark:hidden text-terracotta-600 text-xs"></i>
                            <i class="fas fa-sun hidden dark:inline text-amber-400 text-xs"></i>
                        </button>
                    </div>

                    <!-- Navigation Links -->
                    <nav class="space-y-1 text-xs font-bold">
                        <a href="dashboard.php" class="flex items-center gap-3 px-3.5 py-3 rounded-xl transition-all <?php echo $current_page === 'dashboard' ? 'bg-terracotta-600 text-white shadow-sm' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'; ?>">
                            <i class="fas fa-chart-pie w-4"></i> Dashboard
                        </a>
                        <a href="saved-shops.php" class="flex items-center gap-3 px-3.5 py-3 rounded-xl transition-all <?php echo $current_page === 'saved-shops' ? 'bg-terracotta-600 text-white shadow-sm' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'; ?>">
                            <i class="fas fa-bookmark w-4"></i> Saved Stores
                        </a>
                        <a href="reviews-manager.php" class="flex items-center gap-3 px-3.5 py-3 rounded-xl transition-all <?php echo $current_page === 'reviews' ? 'bg-terracotta-600 text-white shadow-sm' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800'; ?>">
                            <i class="fas fa-star w-4"></i> My Reviews
                        </a>
                    </nav>

                    <div class="my-6 h-px bg-stone-100 dark:bg-stone-800"></div>

                    <!-- Quick Links back to main site -->
                    <nav class="space-y-1 text-xs font-bold text-stone-500 dark:text-stone-400">
                        <a href="../index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors">
                            <i class="fas fa-globe w-4"></i> Home Search
                        </a>
                        <a href="../categories.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors">
                            <i class="fas fa-tags w-4"></i> Browse Categories
                        </a>
                    </nav>
                </div>

                <!-- User Profile & Custom Logout Trigger -->
                <div class="pt-4 border-t border-stone-100 dark:border-stone-800 mt-6">
                    <div class="flex items-center gap-3 mb-3 px-1">
                        <div class="w-8 h-8 rounded-full bg-terracotta-600 text-white flex items-center justify-center font-bold text-xs uppercase shrink-0">
                            <?php echo substr($user_name, 0, 2); ?>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-xs font-bold text-stone-800 dark:text-stone-200 truncate"><?php echo htmlspecialchars($user_name); ?></p>
                            <p class="text-[10px] text-stone-400">User ID: #<?php echo $user_id; ?></p>
                        </div>
                    </div>
                    <button type="button" onclick="openLogoutModal()" class="flex items-center justify-center gap-2 w-full py-2.5 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 font-bold text-xs rounded-xl hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-colors cursor-pointer">
                        <i class="fas fa-sign-out-alt text-[11px]"></i> Log Out
                    </button>
                </div>
            </aside>

            <!-- Custom Tailwind Logout Confirmation Modal -->
            <div id="logoutModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/60 backdrop-blur-sm hidden transition-opacity">
                <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-3xl p-6 w-full max-w-sm shadow-2xl transform transition-all scale-95 opacity-0 duration-200" id="logoutModalBox">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg mb-4 mx-auto">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <h3 class="text-base font-black text-stone-800 dark:text-stone-100 text-center mb-1">Ready to Leave?</h3>
                    <p class="text-xs text-stone-500 dark:text-stone-400 text-center mb-6">Are you sure you want to log out of your AMRAJ account?</p>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="closeLogoutModal()" class="flex-1 py-3 bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 font-bold text-xs rounded-xl hover:bg-stone-200 dark:hover:bg-stone-700 transition-colors">
                            Cancel
                        </button>
                        <a href="../../backend/logout.php" class="flex-1 py-3 bg-rose-600 text-white font-bold text-xs rounded-xl hover:bg-rose-700 transition-colors text-center shadow-lg shadow-rose-600/20">
                            Yes, Log Out
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content Area with Integrated Footer -->
            <main class="flex-1 flex flex-col justify-between p-4 sm:p-4 overflow-y-auto">
                <div class="w-full max-w-7xl mx-auto">
                    <?php if (isset($_SESSION['flash_success'])): ?>
                        <div class="mb-5 p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 rounded-2xl text-xs font-semibold flex items-center gap-2 shadow-sm">
                            <i class="fas fa-check-circle"></i>
                            <span><?php echo $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php echo $content_html; ?>
                </div>

                <!-- Compact & Elegant Footer -->
                <footer class="mt-12 pt-4 border-t border-stone-200/80 dark:border-stone-800/80 text-stone-400 text-[11px] flex flex-col sm:flex-row items-center justify-between gap-3 max-w-7xl mx-auto w-full">
                    <p class="font-medium">&copy; <?php echo date('Y'); ?> AMRAJ Consumer Portal. All rights reserved.</p>
                    <div class="flex items-center gap-4 font-semibold">
                        <a href="../index.php" class="hover:text-terracotta-600 dark:hover:text-terracotta-400 transition-colors">Marketplace</a>
                        <a href="../categories.php" class="hover:text-terracotta-600 dark:hover:text-terracotta-400 transition-colors">Categories</a>
                        <a href="dashboard.php" class="hover:text-terracotta-600 dark:hover:text-terracotta-400 transition-colors">Dashboard</a>
                    </div>
                </footer>
            </main>

            <!-- Theme Toggle, Mobile Sidebar & Custom Modal Scripts -->
            <script>
                function amrajToggleTheme() {
                    var root = document.documentElement;
                    var isDark = root.classList.contains('dark');
                    root.classList.toggle('dark', !isDark);
                    localStorage.setItem('amraj-theme', !isDark ? 'dark' : 'light');
                }

                function toggleMobileSidebar() {
                    var sidebar = document.getElementById('mobileSidebar');
                    var overlay = document.getElementById('sidebarOverlay');
                    var isOpen = sidebar.classList.contains('translate-x-0');
                    
                    if (isOpen) {
                        sidebar.classList.remove('translate-x-0');
                        sidebar.classList.add('-translate-x-full');
                        overlay.classList.add('hidden');
                    } else {
                        sidebar.classList.remove('-translate-x-full');
                        sidebar.classList.add('translate-x-0');
                        overlay.classList.remove('hidden');
                    }
                }

                function openLogoutModal() {
                    var modal = document.getElementById('logoutModal');
                    var box = document.getElementById('logoutModalBox');
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        box.classList.remove('scale-95', 'opacity-0');
                        box.classList.add('scale-100', 'opacity-100');
                    }, 10);
                }

                function closeLogoutModal() {
                    var modal = document.getElementById('logoutModal');
                    var box = document.getElementById('logoutModalBox');
                    box.classList.remove('scale-100', 'opacity-100');
                    box.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                    }, 200);
                }
            </script>
        </body>
        </html>
        <?php
    }
}
?>