<?php
// logs.php - Admin Audit Log History
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once '../../backend/config.php';
require_once 'layout.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$action_filter = isset($_GET['action']) ? trim($_GET['action']) : '';
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$limit = 30;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) { $page = 1; }
$offset = ($page - 1) * $limit;

$count_sql = "SELECT COUNT(*) as total FROM admin_audit_logs WHERE 1=1";
$count_params = [];
$count_types = '';

if ($action_filter !== '') {
    $count_sql .= " AND action = ?";
    $count_params[] = $action_filter;
    $count_types .= 's';
}

if ($search_query !== '') {
    $count_sql .= " AND (summary LIKE ? OR entity_type LIKE ?)";
    $like = '%' . $search_query . '%';
    $count_params[] = $like;
    $count_params[] = $like;
    $count_types .= 'ss';
}

$count_stmt = $conn->prepare($count_sql);
if (!empty($count_params)) {
    $count_stmt->bind_param($count_types, ...$count_params);
}
$count_stmt->execute();
$total_rows = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = max(1, (int) ceil($total_rows / $limit));

$sql = "SELECT l.*, u.name AS admin_name FROM admin_audit_logs l LEFT JOIN users u ON l.admin_id = u.id WHERE 1=1";
$params = [];
$types = '';

if ($action_filter !== '') {
    $sql .= " AND l.action = ?";
    $params[] = $action_filter;
    $types .= 's';
}

if ($search_query !== '') {
    $sql .= " AND (l.summary LIKE ? OR l.entity_type LIKE ?)";
    $like = '%' . $search_query . '%';
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}

$sql .= " ORDER BY l.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$logs = $stmt->get_result();

ob_start();
?>

<div class="space-y-6 animate-fade-in">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-200 dark:border-stone-800 pb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-stone-900 dark:text-white">
                Admin Audit <span class="text-terracotta-700 dark:text-terracotta-400">Logs</span>
            </h1>
            <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">Admin actions ka complete history yahan securely store aur review hota hai.</p>
        </div>
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-xs font-bold text-stone-700 dark:text-stone-300 shadow-sm">
            <i class="fas fa-history text-red-500"></i> <?php echo number_format($total_rows); ?> Total Entries
        </div>
    </div>

    <!-- Filter Control Form -->
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="md:col-span-2">
            <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search summary or entity..." class="w-full h-10 px-3 rounded-xl bg-white dark:bg-stone-800/80 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-red-500 transition-colors">
        </div>
        <select name="action" class="w-full h-10 px-3 rounded-xl bg-white dark:bg-stone-800/80 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-red-500 transition-colors cursor-pointer">
            <option value="">All Actions</option>
            <option value="delete_user" <?php echo $action_filter === 'delete_user' ? 'selected' : ''; ?>>Delete User</option>
            <option value="block_user" <?php echo $action_filter === 'block_user' ? 'selected' : ''; ?>>Block User</option>
            <option value="unblock_user" <?php echo $action_filter === 'unblock_user' ? 'selected' : ''; ?>>Unblock User</option>
            <option value="update_user" <?php echo $action_filter === 'update_user' ? 'selected' : ''; ?>>Update User</option>
            <option value="create_user" <?php echo $action_filter === 'create_user' ? 'selected' : ''; ?>>Create User</option>
            <option value="update_profile" <?php echo $action_filter === 'update_profile' ? 'selected' : ''; ?>>Update Profile</option>
            <option value="update_ads" <?php echo $action_filter === 'update_ads' ? 'selected' : ''; ?>>Ads Update</option>
        </select>
        <button type="submit" class="h-10 px-4 rounded-xl bg-stone-900 dark:bg-stone-100 text-white dark:text-stone-950 text-xs font-bold hover:opacity-90 transition-all active:scale-95 md:col-start-3 md:w-max">
            Filter Logs
        </button>
    </form>

    <!-- Logs Table Frame -->
    <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-stone-50/80 dark:bg-stone-800/50 border-b border-stone-200 dark:border-stone-800 text-[11px] text-stone-500 dark:text-stone-400 font-bold tracking-wider uppercase">
                        <th class="py-3.5 px-4">Timestamp</th>
                        <th class="py-3.5 px-4">Admin Reference</th>
                        <th class="py-3.5 px-4">Action Type</th>
                        <th class="py-3.5 px-4">Target Entity</th>
                        <th class="py-3.5 px-4">Summary Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 dark:divide-stone-800/80 text-xs text-stone-700 dark:text-stone-300">
                    <?php if ($logs && $logs->num_rows > 0): ?>
                        <?php while ($log = $logs->fetch_assoc()): ?>
                            <tr class="hover:bg-stone-50/60 dark:hover:bg-stone-800/40 transition-colors">
                                <td class="py-3.5 px-4 font-semibold text-stone-500 dark:text-stone-400">
                                    <?php echo date('d M Y, h:i A', strtotime($log['created_at'])); ?>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-stone-900 dark:text-white">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-stone-100 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 flex items-center justify-center text-stone-400 shrink-0">
                                            <i class="fas fa-user-shield text-[10px]"></i>
                                        </div>
                                        <span><?php echo htmlspecialchars($log['admin_name'] ?? 'System'); ?></span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-lg">
                                        <?php echo htmlspecialchars($log['action']); ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-[11px] text-stone-500 dark:text-stone-400">
                                    <?php echo htmlspecialchars($log['entity_type']); ?><?php echo $log['entity_id'] ? ' #' . (int) $log['entity_id'] : ''; ?>
                                </td>
                                <td class="py-3.5 px-4 max-w-[460px] whitespace-normal text-stone-600 dark:text-stone-300 leading-relaxed">
                                    <?php echo htmlspecialchars($log['summary']); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="p-12 text-center text-stone-400 dark:text-stone-600">
                                <div class="space-y-2">
                                    <i class="fas fa-clipboard-list text-2xl block text-stone-300 dark:text-stone-700"></i>
                                    <span class="text-xs font-medium">Koi bhi audit log entries nahi mili.</span>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- --- PAGINATION NAVIGATION FOOTER --- -->
        <?php if ($total_pages > 1): ?>
            <div class="px-5 py-3.5 bg-stone-50/80 dark:bg-stone-800/50 border-t border-stone-200 dark:border-stone-800 flex items-center justify-between gap-4">
                <div class="text-xs text-stone-500 dark:text-stone-400">
                    Showing page <span class="font-bold text-stone-700 dark:text-stone-300"><?php echo $page; ?></span> of 
                    <span class="font-bold text-stone-700 dark:text-stone-300"><?php echo $total_pages; ?></span>
                </div>
                <div class="flex items-center gap-1.5">
                    <!-- Previous Button -->
                    <a href="<?php echo $page > 1 ? '?action='.$action_filter.($search_query !== '' ? '&q=' . urlencode($search_query) : '').'&page=' . ($page - 1) : '#'; ?>" 
                       class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-xs text-stone-600 dark:text-stone-400 font-bold hover:bg-stone-50 dark:hover:bg-stone-700/50 transition-colors <?php if($page <= 1) echo 'opacity-50 pointer-events-none'; ?>">
                        Previous
                    </a>
                    
                    <!-- Page Numbers -->
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?action=<?php echo $action_filter; ?><?php echo $search_query !== '' ? '&q=' . urlencode($search_query) : ''; ?>&page=<?php echo $i; ?>" 
                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-bold transition-all <?php echo $page == $i ? 'bg-gradient-to-r from-brand-start to-brand-end text-white shadow-sm shadow-red-500/20' : 'border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-700/50'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Next Button -->
                    <a href="<?php echo $page < $total_pages ? '?action='.$action_filter.($search_query !== '' ? '&q=' . urlencode($search_query) : '').'&page=' . ($page + 1) : '#'; ?>" 
                       class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-xs text-stone-600 dark:text-stone-400 font-bold hover:bg-stone-50 dark:hover:bg-stone-700/50 transition-colors <?php if($page >= $total_pages) echo 'opacity-50 pointer-events-none'; ?>">
                        Next
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$logs_html = ob_get_clean();
render_admin_layout("Audit Logs", $logs_html, 'logs');
?>