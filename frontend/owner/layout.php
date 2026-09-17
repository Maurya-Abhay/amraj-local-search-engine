<?php
// layout.php - AMRAJ Owner Master Layout (Premium Responsive Fix)
if (session_status() == PHP_SESSION_NONE) { session_start(); }

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: ../login.php");
    exit();
}

function render_owner_layout($page_title, $content_html, $current_page = 'dashboard') {
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $page_title; ?> - AMRAJ Owner Center</title>
    <link rel="icon" href="../assets/icons/favicon-32.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
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
                    }
                }
            }
        }
    </script>
    <script>
        if (localStorage.getItem('owner_theme') === 'dark' || (!('owner_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        /* Mobile Navigation Slide-in Animation & Overlay Settings */
        @media (max-width: 767px) {
            #ownerSidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                display: flex !important; /* Forces layout stability */
            }
            #ownerSidebar.open-sidebar {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="h-full bg-stone-50 text-stone-900 dark:bg-stone-950 dark:text-stone-100 antialiased selection:bg-olive-500/30 flex flex-col overflow-hidden">

    <div class="flex flex-1 h-[calc(100vh)] overflow-hidden relative">
        
        <!-- Mobile Sidebar Glass Backdrop Overlay -->
        <div id="ownerSidebarOverlay" class="fixed inset-0 z-40 hidden bg-stone-950/40 backdrop-blur-sm transition-opacity duration-300 md:hidden"></div>

        <!-- Premium Modern Sidebar Layout -->
        <aside id="ownerSidebar" class="hidden md:flex w-64 shrink-0 flex-col justify-between border-r border-stone-200/80 dark:border-stone-800/80 bg-white dark:bg-stone-900 p-4 z-50 shadow-xl md:shadow-none h-full">
            <div class="space-y-5">
                
                <!-- Top Brand Header Container -->
                <div class="rounded-lg border border-stone-200/60 dark:border-stone-800 bg-stone-50 dark:bg-stone-800/50 p-2.5">
                    <p class="text-[10px] uppercase tracking-widest text-stone-400 dark:text-stone-500 font-bold">Business Partner</p>
                    <h2 class="mt-0.5 text-base font-black text-terracotta-700 dark:text-terracotta-400">AMRAJ BUSINESS</h2>
                    <p class="mt-1 text-xs text-stone-500 dark:text-stone-400 font-medium truncate"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Owner'); ?></p>
                </div>

                <!-- Navigation Action Links with optimal spacing -->
                <nav class="space-y-2">
                    <a href="dashboard.php" class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-xs font-bold transition-all duration-200 <?php echo $current_page === 'dashboard' ? 'bg-gradient-to-r from-olive-500 to-terracotta-500 text-white shadow-md shadow-olive-500/10' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800/60 hover:text-stone-900 dark:hover:text-white'; ?>">
                        <i class="fas fa-chart-line text-sm <?php echo $current_page === 'dashboard' ? 'text-white' : 'text-stone-400'; ?>"></i> Dashboard
                    </a>
                    <a href="add-shop.php" class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-xs font-bold transition-all duration-200 <?php echo $current_page === 'add-shop' ? 'bg-gradient-to-r from-olive-500 to-terracotta-500 text-white shadow-md shadow-olive-500/10' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800/60 hover:text-stone-900 dark:hover:text-white'; ?>">
                        <i class="fas fa-plus-circle text-sm <?php echo $current_page === 'add-shop' ? 'text-white' : 'text-stone-400'; ?>"></i> Register Shop
                    </a>
                    <a href="reviews.php" class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-xs font-bold transition-all duration-200 <?php echo $current_page === 'reviews' ? 'bg-gradient-to-r from-olive-500 to-terracotta-500 text-white shadow-md shadow-olive-500/10' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800/60 hover:text-stone-900 dark:hover:text-white'; ?>">
                        <i class="fas fa-star text-sm <?php echo $current_page === 'reviews' ? 'text-white' : 'text-stone-400'; ?>"></i> Reviews
                    </a>
                    <a href="profile.php" class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-xs font-bold transition-all duration-200 <?php echo $current_page === 'profile' ? 'bg-gradient-to-r from-olive-500 to-terracotta-500 text-white shadow-md shadow-olive-500/10' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800/60 hover:text-stone-900 dark:hover:text-white'; ?>">
                        <i class="fas fa-user-gear text-sm <?php echo $current_page === 'profile' ? 'text-white' : 'text-stone-400'; ?>"></i> Profile
                    </a>
                </nav>
            </div>

            <!-- Footer Meta Options inside Sidebar -->
            <div class="space-y-1.5 pt-3 border-t border-stone-100 dark:border-stone-800/80">
                <a href="../index.php" class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-xs font-bold text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800/60 hover:text-stone-900 dark:hover:text-white transition-all duration-200">
                    <i class="fas fa-globe text-sm text-stone-400"></i> Live Site
                </a>
                <a href="#" id="ownerLogoutBtn" class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-xs font-bold text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-all duration-200">
                    <i class="fas fa-right-from-bracket text-sm"></i> Logout
                </a>
            </div>
        </aside>

        <!-- Main Screen Body Frame -->
        <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
            
            <!-- Universal Top Header Bar -->
            <header class="sticky top-0 z-30 border-b border-stone-200/70 dark:border-stone-800/70 bg-white/80 dark:bg-stone-900/80 backdrop-blur-md px-4 py-3 sm:px-6 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <!-- Responsive Menu Button for Mobile view -->
                    <button id="ownerSidebarToggle" type="button" class="md:hidden inline-flex items-center justify-center h-10 w-10 rounded-xl bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 hover:scale-95 transition-transform active:scale-90">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-stone-400 dark:text-stone-500 font-bold">Owner Center</p>
                        <h1 class="text-sm font-black text-stone-800 dark:text-white tracking-wide"><?php echo $page_title; ?></h1>
                    </div>
                </div>

                <!-- Utilities Bar Control options -->
                <div class="flex items-center gap-2">
                    <button id="ownerThemeToggle" type="button" class="inline-flex items-center justify-center h-10 w-10 rounded-xl bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-amber-400 transition-all duration-200 hover:scale-95">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:block"></i>
                    </button>
                    <a href="profile.php" class="hidden sm:inline-flex items-center gap-2 rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 px-3.5 py-2 text-xs font-bold text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-700 transition-all duration-200">
                        <i class="fas fa-user-gear text-[11px]"></i> Profile
                    </a>
                </div>
            </header>

            <!-- Inner Layout Content Inject (Scrollable Area) -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-4 lg:p-4 bg-stone-50/50 dark:bg-stone-950/40">
                <div class="max-w-7xl mx-auto w-full">
                    <?php echo $content_html; ?>
                </div>
            </main>

            <!-- Professional Owner Footer -->
            <footer class="w-full bg-white dark:bg-stone-900 border-t border-stone-200 dark:border-stone-800 px-4 py-3 text-center shrink-0">
                <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-stone-500 dark:text-stone-400 font-medium">
                    <p>&copy; <?php echo date('Y'); ?> <span class="font-bold text-stone-700 dark:text-stone-300">AMRAJ BUSINESS</span>. All rights reserved.</p>
                    <div class="flex items-center gap-4">
                        <span class="inline-flex items-center gap-1.5"><i class="fas fa-shield-alt text-olive-500 text-[10px]"></i> Secure Owner Portal v2.1</span>
                        <a href="dashboard.php" class="hover:text-olive-500 transition-colors">Partner Support</a>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Layout Functional Actions Script -->
    <script>
        // Light & Dark theme toggle controller logic
        const ownerThemeToggle = document.getElementById('ownerThemeToggle');
        ownerThemeToggle.addEventListener('click', () => {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('owner_theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('owner_theme', 'dark');
            }
        });

        // Robust Drawer Control for Mobile Viewports
        const sidebar = document.getElementById('ownerSidebar');
        const sidebarToggle = document.getElementById('ownerSidebarToggle');
        const sidebarOverlay = document.getElementById('ownerSidebarOverlay');

        function openMobileSidebar() {
            sidebar.classList.add('open-sidebar');
            sidebarOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeMobileSidebar() {
            sidebar.classList.remove('open-sidebar');
            sidebarOverlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        if (sidebarToggle && sidebar && sidebarOverlay) {
            sidebarToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                if (sidebar.classList.contains('open-sidebar')) {
                    closeMobileSidebar();
                } else {
                    openMobileSidebar();
                }
            });

            sidebarOverlay.addEventListener('click', closeMobileSidebar);

            // Close mobile sidebar on navigation item click
            sidebar.querySelectorAll('nav a').forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth < 768) {
                        closeMobileSidebar();
                    }
                });
            });
        }

        // Logout Confirmation via SweetAlert for Owner Panel
        const logoutBtn = document.getElementById('ownerLogoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const isDark = document.documentElement.classList.contains('dark');
                
                Swal.fire({
                    title: 'Terminate Session?',
                    text: "Are you sure you want to log out of the owner panel?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#78913f',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, Logout',
                    cancelButtonText: 'Cancel',
                    background: isDark ? '#241f18' : '#fff',
                    color: isDark ? '#f8fafc' : '#1e293b'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '../../backend/logout.php';
                    }
                });
            });
        }
    </script>
</body>
</html>
<?php
}
?>