<?php
// edit-user.php - Admin User Editor
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once '../../backend/config.php';
require_once 'layout.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$allowed_roles = ['admin', 'owner', 'user'];
$user_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$error_message = '';
$success_message = '';
$has_error = false;

if ($user_id <= 0) {
    header("Location: manage-users.php");
    exit();
}

if (isset($_GET['msg']) && $_GET['msg'] === 'updated') {
    $success_message = 'User profile updated successfully.';
}

if (isset($_POST['update_user'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $password = $_POST['password'] ?? '';

    if (!in_array($role, $allowed_roles, true)) {
        $role = 'user';
    }

    if ($name === '' || $email === '' || $phone === '') {
        $error_message = 'Name, email aur phone required hain.';
        $has_error = true;
    } else {
        $user_stmt = $conn->prepare("SELECT id, password, role FROM users WHERE id = ? LIMIT 1");
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $target_user = $user_stmt->get_result()->fetch_assoc();

        if (!$target_user) {
            $error_message = 'User account nahi mila.';
            $has_error = true;
        } else {
            $duplicate = $conn->prepare("SELECT id FROM users WHERE (email = ? OR phone = ?) AND id != ? LIMIT 1");
            $duplicate->bind_param("ssi", $email, $phone, $user_id);
            $duplicate->execute();
            $duplicate_result = $duplicate->get_result();

            if ($duplicate_result->num_rows > 0) {
                $error_message = 'Email ya phone already another account me use ho raha hai.';
                $has_error = true;
            } else {
                $hashed_password = $target_user['password'];

                if ($password !== '') {
                    if (strlen($password) < 6) {
                        $error_message = 'Password kam se kam 6 characters ka hona chahiye.';
                        $has_error = true;
                    } else {
                        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    }
                }

                if (!$has_error) {
                    if ($user_id === (int) $_SESSION['user_id']) {
                        $role = 'admin';
                    }

                    if ($password !== '') {
                        $update = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, role = ?, password = ? WHERE id = ?");
                        $update->bind_param("sssssi", $name, $email, $phone, $role, $hashed_password, $user_id);
                    } else {
                        $update = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, role = ? WHERE id = ?");
                        $update->bind_param("ssssi", $name, $email, $phone, $role, $user_id);
                    }

                    if ($update->execute()) {
                        if ($user_id === (int) $_SESSION['user_id']) {
                            $_SESSION['user_name'] = $name;
                            $_SESSION['user_role'] = 'admin';
                        }
                        if (function_exists('amraj_log_admin_action')) {
                            amraj_log_admin_action($conn, (int) $_SESSION['user_id'], 'update_user', 'user', $user_id, 'Updated user account', ['name' => $name, 'email' => $email, 'role' => $role]);
                        }
                        header("Location: edit-user.php?id={$user_id}&msg=updated");
                        exit();
                    }

                    $error_message = 'User update fail ho gaya.';
                    $has_error = true;
                }
            }
        }
    }
}

$stmt = $conn->prepare("SELECT id, name, email, phone, role, created_at FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: manage-users.php");
    exit();
}

ob_start();
?>

<div class="space-y-6 animate-fade-in">
    <!-- Section Interactive Header Structure -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-200 dark:border-stone-800 pb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-stone-900 dark:text-white">
                Edit <span class="text-terracotta-700 dark:text-terracotta-400">User Profile</span>
            </h1>
            <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">Admin yahan se user account details aur role view aur update kar sakta hai.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="manage-users.php" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-xs font-bold text-stone-700 dark:text-stone-300 shadow-sm hover:bg-stone-50 dark:hover:bg-stone-700 transition-colors">
                <i class="fas fa-arrow-left text-[10px]"></i> Back to Users
            </a>
            <!-- Initial Edit Trigger Button -->
            <button type="button" id="enableEditBtn" onclick="toggleEditMode(true)" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-stone-900 dark:bg-stone-100 text-white dark:text-stone-950 text-xs font-bold hover:opacity-95 transition-all shadow-sm">
                <i class="fas fa-pen-to-square text-[10px]"></i> Edit User
            </button>
        </div>
    </div>

    <!-- Status Toast Notifications -->
    <?php if ($error_message !== ''): ?>
        <div class="rounded-xl border border-red-200 dark:border-red-500/20 bg-red-50 dark:bg-red-500/10 px-4 py-3 text-xs font-bold text-red-700 dark:text-red-400 flex items-center gap-2">
            <i class="fas fa-circle-exclamation text-sm shrink-0"></i> <span><?php echo htmlspecialchars($error_message); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($success_message !== ''): ?>
        <div class="rounded-xl border border-emerald-200 dark:border-emerald-500/20 bg-emerald-50 dark:bg-emerald-500/10 px-4 py-3 text-xs font-bold text-emerald-700 dark:text-emerald-400 flex items-center gap-2">
            <i class="fas fa-circle-check text-sm shrink-0"></i> <span><?php echo htmlspecialchars($success_message); ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-1 space-y-4">
            <div class="p-5 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-xl bg-gradient-to-tr from-red-500 to-olive-500 flex items-center justify-center text-white font-bold shadow-md shadow-red-500/20 shrink-0">
                        <i class="fas fa-user text-lg"></i>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-[10px] uppercase tracking-wider text-stone-400 dark:text-stone-500 font-bold">Editing Target</p>
                        <h2 class="text-sm font-black text-stone-900 dark:text-white truncate mt-0.5"><?php echo htmlspecialchars($user['name']); ?></h2>
                        <p class="text-xs text-stone-500 dark:text-stone-400 truncate"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                </div>
            </div>

            <div class="p-4 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm">
                <p class="text-[10px] uppercase tracking-wider text-stone-400 dark:text-stone-500 font-bold">Account Reference</p>
                <p class="mt-1 text-xs font-mono font-bold text-stone-900 dark:text-white">#USR-<?php echo str_pad((string)$user['id'], 4, '0', STR_PAD_LEFT); ?></p>
            </div>
            <div class="p-4 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm">
                <p class="text-[10px] uppercase tracking-wider text-stone-400 dark:text-stone-500 font-bold">Created On</p>
                <p class="mt-1 text-xs font-bold text-stone-900 dark:text-white"><?php echo date('d M Y', strtotime($user['created_at'])); ?></p>
            </div>
        </div>

        <div class="lg:col-span-2">
            <form method="POST" id="userForm" class="p-5 sm:p-6 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm space-y-6">
                <div class="border-b border-stone-100 dark:border-stone-800 pb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-stone-900 dark:text-white" id="formTitleText">Account Details (View Mode)</h3>
                        <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5" id="formDescText">User data yahan displayed hai. Modify karne ke liye upar button dabayein.</p>
                    </div>
                    <span id="modeBadge" class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold rounded-lg bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400 border border-stone-200 dark:border-stone-700">
                        View Only
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="space-y-1.5 block">
                        <span class="block text-[10px] uppercase tracking-wider font-bold text-stone-400 dark:text-stone-500">Full Name</span>
                        <input type="text" name="name" id="inputName" value="<?php echo htmlspecialchars($user['name']); ?>" disabled class="user-input w-full h-10 px-3 rounded-xl bg-stone-100 dark:bg-stone-800/40 border border-stone-200 dark:border-stone-700 text-xs text-stone-700 dark:text-stone-300 disabled:opacity-80 disabled:cursor-not-allowed focus:outline-none focus:border-red-500 transition-colors">
                    </label>

                    <label class="space-y-1.5 block">
                        <span class="block text-[10px] uppercase tracking-wider font-bold text-stone-400 dark:text-stone-500">Email Address</span>
                        <input type="email" name="email" id="inputEmail" value="<?php echo htmlspecialchars($user['email']); ?>" disabled class="user-input w-full h-10 px-3 rounded-xl bg-stone-100 dark:bg-stone-800/40 border border-stone-200 dark:border-stone-700 text-xs text-stone-700 dark:text-stone-300 disabled:opacity-80 disabled:cursor-not-allowed focus:outline-none focus:border-red-500 transition-colors">
                    </label>

                    <label class="space-y-1.5 block">
                        <span class="block text-[10px] uppercase tracking-wider font-bold text-stone-400 dark:text-stone-500">Phone Number</span>
                        <input type="text" name="phone" id="inputPhone" value="<?php echo htmlspecialchars($user['phone']); ?>" disabled class="user-input w-full h-10 px-3 rounded-xl bg-stone-100 dark:bg-stone-800/40 border border-stone-200 dark:border-stone-700 text-xs text-stone-700 dark:text-stone-300 disabled:opacity-80 disabled:cursor-not-allowed focus:outline-none focus:border-red-500 transition-colors">
                    </label>

                    <label class="space-y-1.5 block">
                        <span class="block text-[10px] uppercase tracking-wider font-bold text-stone-400 dark:text-stone-500">System Role</span>
                        <select name="role" id="inputRole" disabled <?php echo $user['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?> class="user-input w-full h-10 px-3 rounded-xl bg-stone-100 dark:bg-stone-800/40 border border-stone-200 dark:border-stone-700 text-xs text-stone-700 dark:text-stone-300 disabled:opacity-80 disabled:cursor-not-allowed focus:outline-none focus:border-red-500 transition-colors">
                            <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Standard User</option>
                            <option value="owner" <?php echo $user['role'] === 'owner' ? 'selected' : ''; ?>>Owner</option>
                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                        <?php if ($user['id'] == $_SESSION['user_id']): ?>
                            <p class="text-[10px] text-amber-600 dark:text-amber-400 font-bold mt-1">Current admin ka role lock hai.</p>
                        <?php endif; ?>
                    </label>
                </div>

                <!-- Password Reset Section (Hidden in view mode) -->
                <div id="passwordSection" class="pt-2 border-t border-stone-100 dark:border-stone-800 hidden">
                    <div class="mb-4">
                        <h4 class="text-xs font-bold text-stone-900 dark:text-white">Reset Password</h4>
                        <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">Blank chhodoge to password same rahega.</p>
                    </div>

                    <label class="space-y-1.5 block max-w-md">
                        <span class="block text-[10px] uppercase tracking-wider font-bold text-stone-400 dark:text-stone-500">New Password</span>
                        <input type="password" name="password" placeholder="••••••••" class="user-input w-full h-10 px-3 rounded-xl bg-stone-50 dark:bg-stone-800/80 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-red-500 transition-colors">
                    </label>
                </div>

                <!-- Form Footer Actions (Hidden in view mode) -->
                <div id="formActionFooter" class="flex items-center justify-between gap-3 pt-3 border-t border-stone-100 dark:border-stone-800 hidden">
                    <button type="button" onclick="toggleEditMode(false)" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-xs font-bold text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-700 transition-colors">
                        <i class="fas fa-xmark text-[10px]"></i> Cancel Edit
                    </button>
                    <button type="submit" name="update_user" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-olive-500 text-white text-xs font-bold hover:opacity-90 transition-all shadow-md shadow-red-500/20 active:scale-95">
                        <i class="fas fa-floppy-disk text-[10px]"></i> Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleEditMode(isEditing) {
    const inputs = document.querySelectorAll('.user-input');
    const passwordSection = document.getElementById('passwordSection');
    const formActionFooter = document.getElementById('formActionFooter');
    const enableEditBtn = document.getElementById('enableEditBtn');
    const formTitleText = document.getElementById('formTitleText');
    const formDescText = document.getElementById('formDescText');
    const modeBadge = document.getElementById('modeBadge');

    if (isEditing) {
        inputs.forEach(input => {
            // Keep role disabled if it's the logged-in admin themselves
            if(input.name === 'role' && <?php echo ($user['id'] == $_SESSION['user_id']) ? 'true' : 'false'; ?>) return;
            input.disabled = false;
            input.classList.remove('bg-stone-100', 'dark:bg-stone-800/40', 'border-stone-200', 'dark:border-stone-700', 'text-stone-700', 'dark:text-stone-300');
            input.classList.add('bg-stone-50', 'dark:bg-stone-800/80', 'border-stone-200', 'dark:border-stone-700/80', 'text-stone-900', 'dark:text-stone-100');
        });
        passwordSection.classList.remove('hidden');
        formActionFooter.classList.remove('hidden');
        enableEditBtn.classList.add('hidden');

        formTitleText.textContent = "Edit Account Details (Edit Mode)";
        formDescText.textContent = "Changes karne ke baad 'Update User' button par click karein.";
        modeBadge.textContent = "Editing Mode";
        modeBadge.className = "inline-flex items-center px-2.5 py-1 text-[10px] font-bold rounded-lg bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/20";
    } else {
        inputs.forEach(input => {
            input.disabled = true;
            input.classList.add('bg-stone-100', 'dark:bg-stone-800/40', 'border-stone-200', 'dark:border-stone-700', 'text-stone-700', 'dark:text-stone-300');
            input.classList.remove('bg-stone-50', 'dark:bg-stone-800/80', 'border-stone-200', 'dark:border-stone-700/80', 'text-stone-900', 'dark:text-stone-100');
            if(input.type === 'password') input.value = '';
        });
        passwordSection.classList.add('hidden');
        formActionFooter.classList.add('hidden');
        enableEditBtn.classList.remove('hidden');

        formTitleText.textContent = "Account Details (View Mode)";
        formDescText.textContent = "User data yahan displayed hai. Modify karne ke liye upar button dabayein.";
        modeBadge.textContent = "View Only";
        modeBadge.className = "inline-flex items-center px-2.5 py-1 text-[10px] font-bold rounded-lg bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400 border border-stone-200 dark:border-stone-700";
    }
}

// Agar validation error aaya hai toh page load hone par automatically edit mode khula rahega
<?php if ($has_error): ?>
window.addEventListener('DOMContentLoaded', () => {
    toggleEditMode(true);
});
<?php endif; ?>
</script>

<?php
$edit_user_html = ob_get_clean();
render_admin_layout("Edit User", $edit_user_html, 'manage-users');
?>