<?php
// layout.php - AMRAJ ADMIN Master Layout Engine
if (session_status() == PHP_SESSION_NONE) { session_start(); }

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

function render_admin_layout($page_title, $content_html, $current_page = 'dashboard') {
    global $pending_shops;
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - AMRAJ Master Control</title>
    
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
        if (localStorage.getItem('admin_theme') === 'dark' || (!('admin_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    
    <style>
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        .dark ::-webkit-scrollbar-thumb { background: #334155; border-radius: 99px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
    </style>
</head>
<body class="bg-stone-50 text-stone-900 dark:bg-stone-950 dark:text-stone-100 h-full antialiased flex flex-col overflow-hidden transition-colors duration-200 selection:bg-red-500 selection:text-white">

    <!-- Fixed Top Navbar (Compact) -->
    <header class="w-full bg-white/90 dark:bg-stone-900/90 backdrop-blur-md border-b border-stone-200 dark:border-stone-800 px-3.5 py-2 flex items-center justify-between shrink-0 z-50">
        <div class="flex items-center gap-2.5">
            <button id="sidebarToggle" class="md:hidden p-1.5 text-stone-500 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800 rounded-lg focus:outline-none transition-colors">
                <i class="fas fa-bars text-sm"></i>
            </button>
            <div class="flex items-center gap-2">
                <div class="h-7 w-7 rounded-lg bg-gradient-to-tr from-brand-start to-brand-end flex items-center justify-center shadow-sm shadow-red-500/20 shrink-0">
                    <i class="fas fa-crown text-white text-[10px]"></i>
                </div>
                <span class="font-black tracking-tight text-xs text-terracotta-700 dark:text-terracotta-400">AMRAJ HUB</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button id="themeSwitcher" class="p-1.5 h-8 w-8 rounded-lg bg-stone-100 dark:bg-stone-800 hover:bg-stone-200 dark:hover:bg-stone-700 text-stone-600 dark:text-amber-400 flex items-center justify-center transition-all shadow-sm cursor-pointer" aria-label="Toggle theme">
                <i class="fas fa-moon dark:hidden text-[11px]"></i>
                <i class="fas fa-sun hidden dark:block text-[11px]"></i>
            </button>
            <a href="../index.php" target="_blank" class="hidden sm:flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold text-stone-600 dark:text-stone-400 hover:text-brand-start dark:hover:text-white transition-all">
                <i class="fas fa-external-link-alt text-[9px]"></i> Live Site
            </a>

            <a href="profile.php" class="group h-7 w-7 rounded-full bg-gradient-to-br from-stone-200 to-stone-300 dark:from-stone-700 dark:to-stone-800 border border-stone-300 dark:border-stone-600 flex items-center justify-center shadow-sm transition-transform hover:scale-105 cursor-pointer" title="Open Profile" aria-label="Open admin profile">
                <i class="fas fa-user-shield text-[10px] text-stone-600 dark:text-stone-300"></i>
            </a>
        </div>
    </header>

    <!-- Main Layout Container Frame -->
    <div class="flex flex-1 h-[calc(100vh-45px)] overflow-hidden relative">
        
        <!-- Sidebar Navigation Menu (Compact & Clean with optimal gap) -->
        <aside id="sidebarMenu" class="fixed inset-y-0 left-0 z-40 w-56 transform -translate-x-full md:translate-x-0 md:static bg-white dark:bg-stone-900 border-r border-stone-200 dark:border-stone-800 p-3 flex flex-col justify-between transition-transform duration-300 ease-in-out shrink-0 h-full">
            <div class="space-y-4">
                <div class="p-2.5 bg-stone-50 dark:bg-stone-800/50 rounded-lg border border-stone-100 dark:border-stone-700/50 flex items-center gap-2.5">
                    <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></div>
                    <div class="overflow-hidden">
                        <p class="text-[9px] font-extrabold text-stone-400 dark:text-stone-500 uppercase tracking-widest leading-none">Root Session</p>
                        <p class="text-[11px] font-bold text-stone-700 dark:text-stone-300 truncate mt-0.5">Admin Active</p>
                    </div>
                </div>

                <nav class="space-y-2">
                    <a href="dashboard.php" class="group flex items-center gap-2.5 px-3 py-2 rounded-lg text-[11px] font-bold transition-all <?php echo $current_page === 'dashboard' ? 'bg-gradient-to-r from-brand-start to-brand-end text-white shadow-sm shadow-red-500/10' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800/60 hover:text-stone-900 dark:hover:text-white'; ?>">
                        <i class="fas fa-chart-pie text-xs w-3.5 text-center <?php echo $current_page === 'dashboard' ? 'text-white' : 'text-stone-400 group-hover:text-brand-start'; ?>"></i> 
                        Command Center
                    </a>

                    <a href="manage-shops.php" class="group flex items-center gap-2.5 px-3 py-2 rounded-lg text-[11px] font-bold transition-all <?php echo $current_page === 'manage-shops' ? 'bg-gradient-to-r from-brand-start to-brand-end text-white shadow-sm shadow-red-500/10' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800/60 hover:text-stone-900 dark:hover:text-white'; ?>">
                        <i class="fas fa-clipboard-check text-xs w-3.5 text-center <?php echo $current_page === 'manage-shops' ? 'text-white' : 'text-stone-400 group-hover:text-brand-start'; ?>"></i> 
                        Verification Queue
                        <?php if(isset($pending_shops) && $pending_shops > 0): ?>
                            <span class="ml-auto px-1.5 py-0.5 text-[9px] font-black rounded-md <?php echo $current_page === 'manage-shops' ? 'bg-white text-brand-start' : 'bg-amber-500 text-stone-950 animate-pulse'; ?>"><?php echo $pending_shops; ?></span>
                        <?php endif; ?>
                    </a>

                    <a href="manage-users.php" class="group flex items-center gap-2.5 px-3 py-2 rounded-lg text-[11px] font-bold transition-all <?php echo $current_page === 'manage-users' ? 'bg-gradient-to-r from-brand-start to-brand-end text-white shadow-sm shadow-red-500/10' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800/60 hover:text-stone-900 dark:hover:text-white'; ?>">
                        <i class="fas fa-users-cog text-xs w-3.5 text-center <?php echo $current_page === 'manage-users' ? 'text-white' : 'text-stone-400 group-hover:text-brand-start'; ?>"></i> 
                        User Accounts
                    </a>

                    <a href="ads-settings.php" class="group flex items-center gap-2.5 px-3 py-2 rounded-lg text-[11px] font-bold transition-all <?php echo $current_page === 'ads-settings' ? 'bg-gradient-to-r from-brand-start to-brand-end text-white shadow-sm shadow-red-500/10' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800/60 hover:text-stone-900 dark:hover:text-white'; ?>">
                        <i class="fas fa-rectangle-ad text-xs w-3.5 text-center <?php echo $current_page === 'ads-settings' ? 'text-white' : 'text-stone-400 group-hover:text-brand-start'; ?>"></i> 
                        Ad Campaigns
                    </a>

                    <a href="logs.php" class="group flex items-center gap-2.5 px-3 py-2 rounded-lg text-[11px] font-bold transition-all <?php echo $current_page === 'logs' ? 'bg-gradient-to-r from-brand-start to-brand-end text-white shadow-sm shadow-red-500/10' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800/60 hover:text-stone-900 dark:hover:text-white'; ?>">
                        <i class="fas fa-clipboard-list text-xs w-3.5 text-center <?php echo $current_page === 'logs' ? 'text-white' : 'text-stone-400 group-hover:text-brand-start'; ?>"></i> 
                        Audit Logs
                    </a>
                </nav>
            </div>

            <div class="pt-3 border-t border-stone-100 dark:border-stone-800">
                <a href="#" id="logoutBtn" class="flex items-center gap-2.5 px-3 py-2 text-[11px] font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-all">
                    <i class="fas fa-power-off text-xs w-3.5 text-center"></i> Terminate Session
                </a>
            </div>
        </aside>

        <!-- Sidebar Overlay For Mobile -->
        <div id="sidebarOverlay" class="fixed inset-0 z-30 bg-stone-900/50 backdrop-blur-xs hidden md:hidden transition-opacity"></div>

        <!-- Scrollable Main Content & Footer Wrapper -->
        <div class="flex-1 flex flex-col h-full overflow-y-auto">
            
            <!-- Main Content Workspace -->
            <main class="flex-1 p-3 sm:p-4 lg:p-5 max-w-[1500px] mx-auto w-full space-y-4">
                <?php echo $content_html; ?>
            </main>

            <!-- Compact Admin Footer -->
            <footer class="w-full bg-white dark:bg-stone-900 border-t border-stone-200 dark:border-stone-800 px-4 py-3 text-center shrink-0 mt-auto">
                <div class="max-w-[1500px] mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-stone-500 dark:text-stone-400 font-medium">
                    <p>&copy; <?php echo date('Y'); ?> <span class="font-bold text-stone-700 dark:text-stone-300">AMRAJ HUB</span>. All rights reserved.</p>
                    <div class="flex items-center gap-4">
                        <span class="inline-flex items-center gap-1.5"><i class="fas fa-shield-alt text-brand-start text-[10px]"></i> Secure Control Panel v2.1</span>
                        <a href="dashboard.php" class="hover:text-brand-start transition-colors">Privacy & Logs</a>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Layout Engine Scripts -->
    <script>
        const toggleBtn = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebarMenu');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        toggleBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        // Close sidebar on mobile item click
        sidebar.querySelectorAll('nav a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 768) {
                    toggleSidebar();
                }
            });
        });

        const themeBtn = document.getElementById('themeSwitcher');
        themeBtn.addEventListener('click', () => {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('admin_theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('admin_theme', 'dark');
            }
        });

        // Logout Confirmation via SweetAlert
        document.getElementById('logoutBtn').addEventListener('click', (e) => {
            e.preventDefault();
            const isDark = document.documentElement.classList.contains('dark');
            
            Swal.fire({
                title: 'Terminate Session?',
                text: "Are you sure you want to log out of the admin panel?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
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

        // SweetAlert Handler for both Session Messages & URL Params
        window.addEventListener('DOMContentLoaded', () => {
            let alertConfig = null;

            <?php if(isset($_SESSION['success'])): ?>
                alertConfig = {
                    icon: 'success',
                    title: 'Success',
                    text: '<?php echo addslashes($_SESSION['success']); ?>',
                    confirmButtonColor: '#ef4444'
                };
                <?php unset($_SESSION['success']); ?>
            <?php elseif(isset($_SESSION['error'])): ?>
                alertConfig = {
                    icon: 'error',
                    title: 'Error Occurred',
                    text: '<?php echo addslashes($_SESSION['error']); ?>',
                    confirmButtonColor: '#ef4444'
                };
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('msg') && !alertConfig) {
                const msgType = urlParams.get('msg');
                alertConfig = {
                    icon: 'success',
                    title: 'Action Processed',
                    text: 'Operation completed successfully!',
                    confirmButtonColor: '#ef4444'
                };

                if (msgType === 'deleted') {
                    alertConfig.icon = 'warning';
                    alertConfig.title = 'Account Removed';
                    alertConfig.text = 'The requested user has been deleted permanently.';
                } else if (msgType === 'blocked') {
                    alertConfig.icon = 'warning';
                    alertConfig.title = 'Account Blocked';
                    alertConfig.text = 'The requested user account has been blocked.';
                } else if (msgType === 'unblocked') {
                    alertConfig.icon = 'success';
                    alertConfig.title = 'Account Restored';
                    alertConfig.text = 'The requested user account has been unblocked.';
                } else if (msgType === 'created') {
                    alertConfig.icon = 'success';
                    alertConfig.title = 'User Created';
                    alertConfig.text = 'New user account successfully created.';
                } else if (msgType === 'updated') {
                    alertConfig.icon = 'success';
                    alertConfig.title = 'Config Saved';
                    alertConfig.text = 'Production settings updated successfully.';
                }
            }

            if (alertConfig) {
                if (document.documentElement.classList.contains('dark')) {
                    alertConfig.background = '#241f18';
                    alertConfig.color = '#f8fafc';
                }

                Swal.fire(alertConfig).then(() => {
                    if (urlParams.has('msg')) {
                        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                        window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
                    }
                });
            }
        });
    </script>
</body>
</html>
<?php
}
?>