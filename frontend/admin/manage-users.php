<?php
// manage-users.php - User Accounts Dashboard
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once '../../backend/config.php';
require_once 'layout.php'; // Include Master Layout Base

// Strict Admin Access Control
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Search and filtering controls
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

// --- Dynamic Role Filter Logic ---
$allowed_roles = ['admin', 'owner', 'user'];
$selected_role = isset($_GET['role']) && in_array($_GET['role'], $allowed_roles) ? $_GET['role'] : 'all';

// --- Pagination Configuration (10 Records Per Page) ---
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }
$offset = ($page - 1) * $limit;

// Total counts with dynamic filter matching
$count_sql = "SELECT COUNT(*) as total FROM users WHERE 1=1";
$count_params = [];
$count_types = '';

if ($selected_role !== 'all') {
    $count_sql .= " AND role = ?";
    $count_params[] = $selected_role;
    $count_types .= 's';
}

if ($search_query !== '') {
    $count_sql .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $like_query = '%' . $search_query . '%';
    $count_params[] = $like_query;
    $count_params[] = $like_query;
    $count_params[] = $like_query;
    $count_types .= 'sss';
}

$count_stmt = $conn->prepare($count_sql);
if (!empty($count_params)) {
    $count_stmt->bind_param($count_types, ...$count_params);
}
$count_stmt->execute();
$total_rows = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch users conditionally based on filters with Prepared Statement
$query = "SELECT id, name, email, role, is_blocked, created_at FROM users WHERE 1=1";
$params = [];
$types = '';

if ($selected_role !== 'all') {
    $query .= " AND role = ?";
    $params[] = $selected_role;
    $types .= 's';
}

if ($search_query !== '') {
    $query .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $like_query = '%' . $search_query . '%';
    $params[] = $like_query;
    $params[] = $like_query;
    $params[] = $like_query;
    $types .= 'sss';
}

$query .= " ORDER BY FIELD(role, 'admin', 'owner', 'user'), created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users_res = $stmt->get_result();

// Capture page view blocks inside buffering sequence
ob_start();
?>

<div class="space-y-6 animate-fade-in">
    <!-- Section Functional Header Grid -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-200 dark:border-stone-800 pb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-stone-900 dark:text-white">
                Registered <span class="text-terracotta-700 dark:text-terracotta-400">Accounts</span>
            </h1>
            <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">Platform ke sabhi system users, customers aur shop owners ko securely manage aur monitor karein.</p>
        </div>
        <a href="create-user.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-brand-start to-brand-end text-white text-xs font-bold shadow-sm hover:opacity-95 transition-all active:scale-95 shrink-0">
            <i class="fas fa-user-plus text-[10px]"></i> Add New User
        </a>
    </div>

    <!-- Search Control Form -->
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <input type="hidden" name="role" value="<?php echo htmlspecialchars($selected_role); ?>">
        <div class="md:col-span-2">
            <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search by name, email or phone" class="w-full h-10 px-3 rounded-xl bg-white dark:bg-stone-800/80 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-red-500 transition-colors">
        </div>
        <button type="submit" class="h-10 px-4 rounded-xl bg-stone-900 dark:bg-stone-100 text-white dark:text-stone-950 text-xs font-bold hover:opacity-90 transition-all active:scale-95">
            Search Users
        </button>
    </form>

    <!-- Dynamic Matrix Filter Engine Tabs -->
    <div class="flex flex-wrap items-center gap-1.5">
        <a href="manage-users.php?role=all<?php echo $search_query !== '' ? '&q=' . urlencode($search_query) : ''; ?>" class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition-colors <?php echo $selected_role === 'all' ? 'bg-stone-900 text-white dark:bg-stone-100 dark:text-stone-950 shadow-sm' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 dark:bg-stone-800 dark:text-stone-400 dark:hover:bg-stone-700' ?>">
            All Accounts
        </a>
        <a href="manage-users.php?role=admin<?php echo $search_query !== '' ? '&q=' . urlencode($search_query) : ''; ?>" class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition-colors <?php echo $selected_role === 'admin' ? 'bg-red-600 text-white shadow-sm' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 dark:bg-stone-800 dark:text-stone-400 dark:hover:bg-stone-700' ?>">
            Admins Only
        </a>
        <a href="manage-users.php?role=owner<?php echo $search_query !== '' ? '&q=' . urlencode($search_query) : ''; ?>" class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition-colors <?php echo $selected_role === 'owner' ? 'bg-olive-500 text-white shadow-sm' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 dark:bg-stone-800 dark:text-stone-400 dark:hover:bg-stone-700' ?>">
            Merchants / Owners
        </a>
        <a href="manage-users.php?role=user<?php echo $search_query !== '' ? '&q=' . urlencode($search_query) : ''; ?>" class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition-colors <?php echo $selected_role === 'user' ? 'bg-terracotta-600 text-white shadow-sm' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 dark:bg-stone-800 dark:text-stone-400 dark:hover:bg-stone-700' ?>">
            Standard Users
        </a>
    </div>

    <!-- Premium Database Matrix Table Frame -->
    <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-stone-50/80 dark:bg-stone-800/50 border-b border-stone-200 dark:border-stone-800 text-[11px] text-stone-500 dark:text-stone-400 font-bold tracking-wider uppercase">
                        <th class="py-3.5 px-4">Account Reference</th>
                        <th class="py-3.5 px-4">Full Identity / Name</th>
                        <th class="py-3.5 px-4">Email Address</th>
                        <th class="py-3.5 px-4">System Access Role</th>
                        <th class="py-3.5 px-4">Enrolled On</th>
                        <th class="py-3.5 px-4 text-center">Operational Security</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 dark:divide-stone-800/80 text-xs text-stone-700 dark:text-stone-300">
                    <?php if($users_res && $users_res->num_rows > 0): ?>
                        <?php while($user = $users_res->fetch_assoc()): ?>
                            <tr class="hover:bg-stone-50/60 dark:hover:bg-stone-800/40 transition-colors">
                                <!-- Reference Component -->
                                <td class="py-3.5 px-4 font-mono text-stone-400 dark:text-stone-500 text-[11px]">
                                    #USR-<?php echo str_pad($user['id'], 4, '0', STR_PAD_LEFT); ?>
                                </td>

                                <!-- User Name Profile Badge Frame -->
                                <td class="py-3.5 px-4 font-bold text-stone-900 dark:text-white">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-xl bg-stone-100 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 flex items-center justify-center text-stone-400 dark:text-stone-500 shrink-0">
                                            <i class="fas fa-user text-[11px]"></i>
                                        </div>
                                        <span class="truncate max-w-[180px]"><?php echo htmlspecialchars($user['name']); ?></span>
                                    </div>
                                </td>

                                <!-- Email Endpoint Column -->
                                <td class="py-3.5 px-4 font-mono text-[11px] text-stone-500 dark:text-stone-400">
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </td>

                                <!-- Dynamic Role Badges Matrix -->
                                <td class="py-3.5 px-4">
                                    <?php if($user['role'] == 'admin'): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-lg">
                                            Root System Admin
                                        </span>
                                    <?php elseif($user['role'] == 'owner'): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold text-olive-700 dark:text-olive-400 bg-olive-50 dark:bg-olive-500/10 border border-olive-200 dark:border-olive-500/20 rounded-lg">
                                            Premium Merchant
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold text-terracotta-700 dark:text-terracotta-400 bg-terracotta-50 dark:bg-terracotta-500/10 border border-terracotta-200 dark:border-terracotta-500/20 rounded-lg">
                                            Standard User
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($user['is_blocked'])): ?>
                                        <div class="mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-md">
                                                Blocked Access
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Calendar Mapping Date Component -->
                                <td class="py-3.5 px-4 text-stone-500 dark:text-stone-400 font-semibold">
                                    <?php echo date("d M Y", strtotime($user['created_at'])); ?>
                                </td>

                                <!-- Action Buttons Component -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="edit-user.php?id=<?php echo $user['id']; ?>" 
                                           class="inline-flex items-center justify-center h-8 w-8 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 border border-transparent hover:border-red-200 dark:hover:border-red-500/20 rounded-lg transition-colors" 
                                           title="Edit Account">
                                            <i class="fas fa-pen-to-square text-xs"></i>
                                        </a>

                                        <?php if($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="../../backend/admin_handler.php?action=<?php echo !empty($user['is_blocked']) ? 'unblock_user' : 'block_user'; ?>&id=<?php echo $user['id']; ?>&role=<?php echo $selected_role; ?>&page=<?php echo $page; ?><?php echo $search_query !== '' ? '&q=' . urlencode($search_query) : ''; ?>"
                                               data-swal-confirm="1"
                                               data-confirm-title="<?php echo !empty($user['is_blocked']) ? 'Unblock User?' : 'Block User?'; ?>"
                                               data-confirm-text="<?php echo !empty($user['is_blocked']) ? 'User access restore karne ke liye confirm karein.' : 'User account temporarily block karne ke liye confirm karein.'; ?>"
                                               data-confirm-icon="<?php echo !empty($user['is_blocked']) ? 'question' : 'warning'; ?>"
                                               data-confirm-color="<?php echo !empty($user['is_blocked']) ? '#10b981' : '#f59e0b'; ?>"
                                               class="inline-flex items-center justify-center h-8 w-8 <?php echo !empty($user['is_blocked']) ? 'text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 hover:border-emerald-200 dark:hover:border-emerald-500/20' : 'text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 hover:border-amber-200 dark:hover:border-amber-500/20'; ?> border border-transparent rounded-lg transition-colors"
                                               title="<?php echo !empty($user['is_blocked']) ? 'Unblock Account' : 'Block Account'; ?>">
                                                <i class="fas <?php echo !empty($user['is_blocked']) ? 'fa-user-check' : 'fa-user-lock'; ?> text-xs"></i>
                                            </a>
                                        <?php endif; ?>

                                        <?php if($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="../../backend/admin_handler.php?action=delete_user&id=<?php echo $user['id']; ?>&role=<?php echo $selected_role; ?>&page=<?php echo $page; ?><?php echo $search_query !== '' ? '&q=' . urlencode($search_query) : ''; ?>" 
                                               data-swal-confirm="1"
                                               data-confirm-title="Delete User?"
                                               data-confirm-text="Security Warning! Kya aap sach me is user account ko hamesha ke liye server se delete karna chahte hain?"
                                               data-confirm-icon="warning"
                                               data-confirm-color="#ef4444"
                                               class="inline-flex items-center justify-center h-8 w-8 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 border border-transparent hover:border-red-200 dark:hover:border-red-500/20 rounded-lg transition-colors" 
                                               title="Remove Account">
                                                <i class="fas fa-trash-can text-xs"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-[10px] font-bold tracking-wider uppercase px-2 py-1 bg-stone-100 dark:bg-stone-800 text-stone-400 dark:text-stone-500 rounded-lg">
                                                Active
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="p-12 text-center text-stone-400 dark:text-stone-600">
                                <div class="space-y-2">
                                    <i class="fas fa-users-slash text-2xl block text-stone-300 dark:text-stone-700"></i>
                                    <span class="text-xs font-medium">Is role context me koi bhi accounts nahi mile.</span>
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
                    Showing <span class="font-bold text-stone-700 dark:text-stone-300"><?php echo $offset + 1; ?></span> to 
                    <span class="font-bold text-stone-700 dark:text-stone-300"><?php echo min($offset + $limit, $total_rows); ?></span> of 
                    <span class="font-bold text-stone-700 dark:text-stone-300"><?php echo $total_rows; ?></span> accounts
                </div>
                <div class="flex items-center gap-1.5">
                    <!-- Previous Button -->
                    <a href="<?php echo $page > 1 ? '?role='.$selected_role.($search_query !== '' ? '&q=' . urlencode($search_query) : '').'&page=' . ($page - 1) : '#'; ?>" 
                       class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-xs text-stone-600 dark:text-stone-400 font-bold hover:bg-stone-50 dark:hover:bg-stone-700/50 transition-colors <?php if($page <= 1) echo 'opacity-50 pointer-events-none'; ?>">
                        Previous
                    </a>
                    
                    <!-- Page Numbers -->
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?role=<?php echo $selected_role; ?><?php echo $search_query !== '' ? '&q=' . urlencode($search_query) : ''; ?>&page=<?php echo $i; ?>" 
                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-bold transition-all <?php echo $page == $i ? 'bg-gradient-to-r from-brand-start to-brand-end text-white shadow-sm shadow-red-500/20' : 'border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-700/50'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Next Button -->
                    <a href="<?php echo $page < $total_pages ? '?role='.$selected_role.($search_query !== '' ? '&q=' . urlencode($search_query) : '').'&page=' . ($page + 1) : '#'; ?>" 
                       class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-xs text-stone-600 dark:text-stone-400 font-bold hover:bg-stone-50 dark:hover:bg-stone-700/50 transition-colors <?php if($page >= $total_pages) echo 'opacity-50 pointer-events-none'; ?>">
                        Next
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('[data-swal-confirm="1"]').forEach((link) => {
    link.addEventListener('click', (event) => {
        event.preventDefault();
        const confirmTitle = link.dataset.confirmTitle || 'Are you sure?';
        const confirmText = link.dataset.confirmText || '';
        const confirmIcon = link.dataset.confirmIcon || 'warning';
        const confirmColor = link.dataset.confirmColor || '#ef4444';

        Swal.fire({
            icon: confirmIcon,
            title: confirmTitle,
            text: confirmText,
            showCancelButton: true,
            confirmButtonText: 'Yes, continue',
            cancelButtonText: 'Cancel',
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#64748b'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = link.href;
            }
        });
    });
});
</script>

<?php
$users_html = ob_get_clean();

// Pass dynamic page parameters directly to master pipeline
render_admin_layout("Accounts Management", $users_html, 'manage-users');
?>