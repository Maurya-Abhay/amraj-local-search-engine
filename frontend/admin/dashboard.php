<?php
// dashboard.php - Admin Command Center
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../backend/config.php';
require_once __DIR__ . '/layout.php'; // Include Master Layout

// Strict Admin Access Control
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch Master Analytics from Database
$user_count_result = amraj_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='user'");
$total_users = $user_count_result ? (int)$user_count_result->fetch_assoc()['total'] : 0;
$owner_count_result = amraj_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='owner'");
$total_owners = $owner_count_result ? (int)$owner_count_result->fetch_assoc()['total'] : 0;
$shop_count_result = amraj_query($conn, "SELECT COUNT(*) as total FROM businesses");
$total_shops = $shop_count_result ? (int)$shop_count_result->fetch_assoc()['total'] : 0;
$pending_shops_result = amraj_query($conn, "SELECT COUNT(*) as total FROM businesses WHERE status='pending'");
$pending_shops = $pending_shops_result ? (int)$pending_shops_result->fetch_assoc()['total'] : 0;

// Output buffer content setup for the dashboard body
ob_start();
?>

<div class="space-y-6 animate-fade-in">
    <!-- Dashboard Context Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-200/60 dark:border-stone-800/80 pb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-stone-900 dark:text-white">
                System <span class="text-terracotta-700 dark:text-terracotta-400">Overview</span>
            </h1>
            <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">Platform real-time structural metrics and system analytical updates.</p>
        </div>
        
        <!-- Live Clock Engine Component -->
        <div class="flex items-center gap-2 text-[11px] font-bold px-3 py-2 bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-xl shadow-sm text-stone-600 dark:text-stone-300 max-w-max">
            <i class="fas fa-clock text-red-500 animate-pulse"></i>
            <span id="liveClock">Loading Clock...</span>
        </div>
    </div>

    <!-- 4-Grid Premium Statistics Counters -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Card: Total Users -->
        <div class="p-5 bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-xl flex items-center justify-between shadow-sm hover:shadow-md transition-all duration-300 group">
            <div class="space-y-1">
                <span class="text-[10px] text-stone-400 dark:text-stone-500 font-bold uppercase tracking-wider block">Total Users</span>
                <h3 class="text-2xl font-black text-stone-900 dark:text-white tracking-tight"><?php echo number_format($total_users); ?></h3>
            </div>
            <div class="w-11 h-11 rounded-xl bg-terracotta-50 dark:bg-terracotta-500/10 text-terracotta-500 dark:text-terracotta-400 flex items-center justify-center text-sm transition-transform group-hover:scale-110 duration-300">
                <i class="fas fa-users"></i>
            </div>
        </div>

        <!-- Card: Shop Owners -->
        <div class="p-5 bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-xl flex items-center justify-between shadow-sm hover:shadow-md transition-all duration-300 group">
            <div class="space-y-1">
                <span class="text-[10px] text-stone-400 dark:text-stone-500 font-bold uppercase tracking-wider block">Shop Owners</span>
                <h3 class="text-2xl font-black text-stone-900 dark:text-white tracking-tight"><?php echo number_format($total_owners); ?></h3>
            </div>
            <div class="w-11 h-11 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-500 dark:text-purple-400 flex items-center justify-center text-sm transition-transform group-hover:scale-110 duration-300">
                <i class="fas fa-user-tie"></i>
            </div>
        </div>

        <!-- Card: Total Listings -->
        <div class="p-5 bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-xl flex items-center justify-between shadow-sm hover:shadow-md transition-all duration-300 group">
            <div class="space-y-1">
                <span class="text-[10px] text-stone-400 dark:text-stone-500 font-bold uppercase tracking-wider block">Total Listings</span>
                <h3 class="text-2xl font-black text-stone-900 dark:text-white tracking-tight"><?php echo number_format($total_shops); ?></h3>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-500 dark:text-emerald-400 flex items-center justify-center text-sm transition-transform group-hover:scale-110 duration-300">
                <i class="fas fa-store"></i>
            </div>
        </div>

        <!-- Card: Pending Verification Queue -->
        <div class="p-5 bg-white dark:bg-stone-900 border <?php echo $pending_shops > 0 ? 'border-amber-500/50 bg-amber-500/[0.02] dark:bg-amber-500/[0.04]' : 'border-stone-200 dark:border-stone-800'; ?> rounded-xl flex items-center justify-between shadow-sm hover:shadow-md transition-all duration-300 group">
            <div class="space-y-1">
                <span class="text-[10px] text-stone-400 dark:text-stone-500 font-bold uppercase tracking-wider block">Pending Approval</span>
                <h3 class="text-2xl font-black <?php echo $pending_shops > 0 ? 'text-amber-500 dark:text-amber-400' : 'text-stone-900 dark:text-white'; ?> tracking-tight"><?php echo number_format($pending_shops); ?></h3>
            </div>
            <div class="w-11 h-11 rounded-xl <?php echo $pending_shops > 0 ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 animate-bounce' : 'bg-stone-100 dark:bg-stone-800 text-stone-500 dark:text-stone-400'; ?> flex items-center justify-center text-sm transition-transform group-hover:scale-110 duration-300">
                <i class="fas fa-hourglass-half"></i>
            </div>
        </div>
    </div>

    <!-- Callout Operational Control Row -->
    <div class="p-5 bg-gradient-to-br from-white to-stone-50/50 dark:from-stone-900 dark:to-stone-900/60 border border-stone-200 dark:border-stone-800 rounded-xl flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="h-10 w-10 rounded-xl bg-red-50 dark:bg-red-500/10 text-brand-start flex items-center justify-center shrink-0 mt-0.5">
                <i class="fas fa-shield-halved text-sm"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm text-stone-900 dark:text-white tracking-tight">Business Registration Request Gateway</h4>
                <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5 leading-relaxed">Nayi dukanon ki identity validation, operational permissions aur activation status updates yahan se handle karein.</p>
            </div>
        </div>
        <a href="manage-shops.php" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-stone-900 hover:bg-stone-800 dark:bg-stone-800 dark:hover:bg-stone-700 text-white dark:text-stone-100 font-bold text-xs rounded-xl transition-all shadow-sm shrink-0 whitespace-nowrap active:scale-[0.98]">
            Open Queue Center <i class="fas fa-arrow-right text-[10px]"></i>
        </a>
    </div>

    <!-- Shortcuts Control Grid Panel -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="p-5 bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-xl space-y-3 shadow-sm hover:border-stone-300 dark:hover:border-stone-700 transition-all">
            <div class="flex items-center gap-2.5 text-stone-800 dark:text-stone-200">
                <div class="p-2 rounded-lg bg-stone-100 dark:bg-stone-800 text-stone-500">
                    <i class="fas fa-users-cog text-xs"></i>
                </div>
                <span class="text-xs font-bold tracking-tight">Accounts Management Quick-Link</span>
            </div>
            <p class="text-xs text-stone-500 dark:text-stone-400 leading-relaxed">Platform ke users aur premium business stakeholders ko live control ya filter karne ke liye use karein.</p>
            <a href="manage-users.php" class="text-xs font-bold text-brand-start hover:text-red-600 inline-flex items-center gap-1.5 transition-all pt-1">
                Manage User Accounts <i class="fas fa-angle-right text-[10px]"></i>
            </a>
        </div>

        <div class="p-5 bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-xl space-y-3 shadow-sm hover:border-stone-300 dark:hover:border-stone-700 transition-all">
            <div class="flex items-center gap-2.5 text-stone-800 dark:text-stone-200">
                <div class="p-2 rounded-lg bg-stone-100 dark:bg-stone-800 text-stone-500">
                    <i class="fas fa-rectangle-ad text-xs"></i>
                </div>
                <span class="text-xs font-bold tracking-tight">Marketing Systems Controls</span>
            </div>
            <p class="text-xs text-stone-500 dark:text-stone-400 leading-relaxed">Global application banners, promotional texts, alert configurations aur commercial ads status badlein.</p>
            <a href="ads-settings.php" class="text-xs font-bold text-brand-start hover:text-red-600 inline-flex items-center gap-1.5 transition-all pt-1">
                Configure Announcements <i class="fas fa-angle-right text-[10px]"></i>
            </a>
        </div>
    </div>
</div>

<!-- Realtime Clock Script -->
<script>
    function updateClock() {
        const now = new Date();
        const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
        document.getElementById('liveClock').innerText = now.toLocaleDateString('en-US', options);
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>

<?php
$dashboard_html = ob_get_clean();

// Execute final layout rendering pipeline
render_admin_layout("Command Center", $dashboard_html, 'dashboard');
?>