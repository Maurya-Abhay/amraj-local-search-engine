<?php
// create-user.php - Admin User Creator
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once '../../backend/config.php';
require_once 'layout.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$allowed_roles = ['admin', 'owner', 'user'];
$error_message = '';

if (isset($_POST['create_user'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $password = $_POST['password'] ?? '';

    if (!in_array($role, $allowed_roles, true)) {
        $role = 'user';
    }

    if ($name === '' || $email === '' || $phone === '' || $password === '') {
        $error_message = 'Sabhi fields required hain.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password kam se kam 6 characters ka hona chahiye.';
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1");
        $check->bind_param("ss", $email, $phone);
        $check->execute();
        $duplicate = $check->get_result();

        if ($duplicate->num_rows > 0) {
            $error_message = 'Email ya phone already register hai.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $insert = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
            $insert->bind_param("sssss", $name, $email, $phone, $hashed_password, $role);

            if ($insert->execute()) {
                if (function_exists('amraj_log_admin_action')) {
                    amraj_log_admin_action($conn, (int) $_SESSION['user_id'], 'create_user', 'user', (int) $conn->insert_id, 'Created new user account', ['name' => $name, 'email' => $email, 'role' => $role]);
                }
                header("Location: manage-users.php?msg=created");
                exit();
            }

            $error_message = 'User create nahi ho paya.';
        }
    }
}

ob_start();
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-200 dark:border-stone-800 pb-5 mb-5">
    <div>
        <h1 class="text-xl font-bold tracking-tight text-stone-900 dark:text-white">
            Create <span class="text-terracotta-600 dark:text-terracotta-400">New User</span>
        </h1>
        <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">Admin yahan se fresh account create kar sakta hai.</p>
    </div>
    <a href="manage-users.php" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-xs font-semibold text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-700 transition-colors">
        <i class="fas fa-arrow-left text-[10px]"></i> Back to Users
    </a>
</div>

<?php if ($error_message !== ''): ?>
    <div class="mb-5 rounded border border-red-200 dark:border-red-500/20 bg-red-50 dark:bg-red-500/10 px-4 py-3 text-xs font-medium text-red-700 dark:text-red-400">
        <i class="fas fa-circle-exclamation mr-1.5"></i> <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>

<div class="max-w-2xl p-5 sm:p-6 rounded border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-800/80 shadow-sm">
    <form method="POST" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label class="space-y-1.5 block">
                <span class="block text-[10px] uppercase tracking-wider font-semibold text-stone-400 dark:text-stone-500">Full Name</span>
                <input type="text" name="name" class="w-full h-10 px-3 rounded bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-800 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-terracotta-500 transition-colors">
            </label>
            <label class="space-y-1.5 block">
                <span class="block text-[10px] uppercase tracking-wider font-semibold text-stone-400 dark:text-stone-500">Email Address</span>
                <input type="email" name="email" class="w-full h-10 px-3 rounded bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-800 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-terracotta-500 transition-colors">
            </label>
            <label class="space-y-1.5 block">
                <span class="block text-[10px] uppercase tracking-wider font-semibold text-stone-400 dark:text-stone-500">Phone Number</span>
                <input type="text" name="phone" class="w-full h-10 px-3 rounded bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-800 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-terracotta-500 transition-colors">
            </label>
            <label class="space-y-1.5 block">
                <span class="block text-[10px] uppercase tracking-wider font-semibold text-stone-400 dark:text-stone-500">Access Role</span>
                <select name="role" class="w-full h-10 px-3 rounded bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-800 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-terracotta-500 transition-colors cursor-pointer">
                    <option value="user">Standard User</option>
                    <option value="owner">Owner</option>
                    <option value="admin">Admin</option>
                </select>
            </label>
        </div>

        <label class="space-y-1.5 block max-w-md">
            <span class="block text-[10px] uppercase tracking-wider font-semibold text-stone-400 dark:text-stone-500">Initial Password</span>
            <input type="password" name="password" class="w-full h-10 px-3 rounded bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-800 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-terracotta-500 transition-colors">
        </label>

        <div class="flex items-center justify-between gap-3 pt-2 border-t border-stone-100 dark:border-stone-800">
            <p class="text-[10px] text-stone-400 dark:text-stone-500">Make sure role aur password sahi ho.</p>
            <button type="submit" name="create_user" class="inline-flex items-center gap-1.5 px-4 py-2 rounded bg-terracotta-600 text-white text-xs font-semibold hover:bg-terracotta-700 transition-colors shadow-sm">
                <i class="fas fa-user-plus text-[10px]"></i> Create User
            </button>
        </div>
    </form>
</div>

<?php
$create_user_html = ob_get_clean();
render_admin_layout("Create User", $create_user_html, 'manage-users');
?>