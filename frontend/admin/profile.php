<?php
// profile.php - Admin Profile Center
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once '../../backend/config.php';
require_once 'layout.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_id = (int) $_SESSION['user_id'];
$error_message = '';
$success_message = '';
$has_error = false;

if (isset($_GET['msg']) && $_GET['msg'] === 'updated') {
    $success_message = 'Profile successfully update ho gayi hai.';
}

if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $phone === '') {
        $error_message = 'Name, email aur phone required hain.';
        $has_error = true;
    } else {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE id = ? AND role = 'admin'");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $current_admin = $stmt->get_result()->fetch_assoc();

        if (!$current_admin) {
            $error_message = 'Admin account load nahi ho saka.';
            $has_error = true;
        } else {
            $duplicate = $conn->prepare("SELECT id FROM users WHERE (email = ? OR phone = ?) AND id != ? LIMIT 1");
            $duplicate->bind_param("ssi", $email, $phone, $admin_id);
            $duplicate->execute();
            $duplicate_result = $duplicate->get_result();

            if ($duplicate_result->num_rows > 0) {
                $error_message = 'Ye email ya phone kisi aur account me already use ho raha hai.';
                $has_error = true;
            } else {
                $update_password = false;
                $hashed_password = $current_admin['password'];

                if ($current_password !== '' || $new_password !== '' || $confirm_password !== '') {
                    if ($current_password === '' || $new_password === '' || $confirm_password === '') {
                        $error_message = 'Password change ke liye sabhi fields required hain.';
                        $has_error = true;
                    } elseif (!password_verify($current_password, $current_admin['password'])) {
                        $error_message = 'Current password galat hai.';
                        $has_error = true;
                    } elseif ($new_password !== $confirm_password) {
                        $error_message = 'New password aur confirm password match nahi kar rahe.';
                        $has_error = true;
                    } elseif (strlen($new_password) < 6) {
                        $error_message = 'New password kam se kam 6 characters ka hona chahiye.';
                        $has_error = true;
                    } else {
                        $update_password = true;
                        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                    }
                }

                if (!$has_error) {
                    if ($update_password) {
                        $update = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, password = ? WHERE id = ? AND role = 'admin'");
                        $update->bind_param("ssssi", $name, $email, $phone, $hashed_password, $admin_id);
                    } else {
                        $update = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ? AND role = 'admin'");
                        $update->bind_param("sssi", $name, $email, $phone, $admin_id);
                    }

                    if ($update->execute()) {
                        $_SESSION['user_name'] = $name;
                        if (function_exists('amraj_log_admin_action')) {
                            amraj_log_admin_action($conn, $admin_id, 'update_profile', 'admin_profile', $admin_id, 'Updated admin profile', ['name' => $name, 'email' => $email, 'phone' => $phone]);
                        }
                        header("Location: profile.php?msg=updated");
                        exit();
                    }
                    $error_message = 'Profile save nahi ho saka. Dobara try karein.';
                    $has_error = true;
                }
            }
        }
    }
}

$profile_stmt = $conn->prepare("SELECT id, name, email, phone, role, created_at FROM users WHERE id = ? AND role = 'admin' LIMIT 1");
$profile_stmt->bind_param("i", $admin_id);
$profile_stmt->execute();
$profile = $profile_stmt->get_result()->fetch_assoc();

ob_start();
?>

<div class="space-y-6 animate-fade-in">
    <!-- Section Interactive Header Structure -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-200 dark:border-stone-800 pb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-stone-900 dark:text-white">
                Admin <span class="text-terracotta-700 dark:text-terracotta-400">Profile Center</span>
            </h1>
            <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">Yahin se aap apna admin identity aur login security credentials view aur update kar sakte hain.</p>
        </div>
        <div class="flex items-center gap-2">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-xs font-bold text-stone-700 dark:text-stone-300 shadow-sm">
                <i class="fas fa-shield-halved text-red-500"></i> Secure Admin Access
            </div>
            <!-- Initial Edit Trigger Button -->
            <button type="button" id="enableEditBtn" onclick="toggleEditMode(true)" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-stone-900 dark:bg-stone-100 text-white dark:text-stone-950 text-xs font-bold hover:opacity-95 transition-all shadow-sm">
                <i class="fas fa-pen-to-square text-[10px]"></i> Edit Profile
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
        <!-- Meta Summary Side Deck -->
        <div class="lg:col-span-1 space-y-4">
            <div class="p-5 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="h-14 w-14 rounded-xl bg-gradient-to-tr from-red-500 to-olive-500 flex items-center justify-center text-white font-bold shadow-md shadow-red-500/20 shrink-0">
                        <i class="fas fa-user-shield text-xl"></i>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-[10px] uppercase tracking-wider text-stone-400 dark:text-stone-500 font-bold">Active Account</p>
                        <h2 class="text-sm font-black text-stone-900 dark:text-white truncate mt-0.5"><?php echo htmlspecialchars($profile['name'] ?? $_SESSION['user_name']); ?></h2>
                        <p class="text-xs text-stone-500 dark:text-stone-400 truncate"><?php echo htmlspecialchars($profile['email'] ?? ''); ?></p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3">
                <div class="p-4 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm">
                    <p class="text-[10px] uppercase tracking-wider text-stone-400 dark:text-stone-500 font-bold">Account Reference</p>
                    <p class="mt-1 text-xs font-mono font-bold text-stone-900 dark:text-white">#ADM-<?php echo str_pad((string)$profile['id'], 4, '0', STR_PAD_LEFT); ?></p>
                </div>
                <div class="p-4 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm">
                    <p class="text-[10px] uppercase tracking-wider text-stone-400 dark:text-stone-500 font-bold">System Role</p>
                    <p class="mt-1 text-xs font-bold text-red-600 dark:text-red-400">Root Administrator</p>
                </div>
                <div class="p-4 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm">
                    <p class="text-[10px] uppercase tracking-wider text-stone-400 dark:text-stone-500 font-bold">Registration Date</p>
                    <p class="mt-1 text-xs font-bold text-stone-900 dark:text-white"><?php echo date('d M Y', strtotime($profile['created_at'] ?? 'now')); ?></p>
                </div>
            </div>
        </div>

        <!-- Main Data Management Form Card -->
        <div class="lg:col-span-2">
            <form method="POST" id="profileForm" class="p-5 sm:p-6 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm space-y-6">
                <div class="border-b border-stone-100 dark:border-stone-800 pb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-stone-900 dark:text-white" id="formTitleText">Profile Information (View Mode)</h3>
                        <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5" id="formDescText">Aapke account ki details yahan displayed hain. Edit karne ke liye upar button dabayein.</p>
                    </div>
                    <span id="modeBadge" class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold rounded-lg bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400 border border-stone-200 dark:border-stone-700">
                        View Only
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="space-y-1.5 block">
                        <span class="block text-[10px] uppercase tracking-wider font-bold text-stone-400 dark:text-stone-500">Full Name</span>
                        <input type="text" name="name" id="inputName" value="<?php echo htmlspecialchars($profile['name'] ?? ''); ?>" disabled class="profile-input w-full h-10 px-3 rounded-xl bg-stone-100 dark:bg-stone-800/40 border border-stone-200 dark:border-stone-700 text-xs text-stone-700 dark:text-stone-300 disabled:opacity-80 disabled:cursor-not-allowed focus:outline-none focus:border-red-500 transition-colors">
                    </label>

                    <label class="space-y-1.5 block">
                        <span class="block text-[10px] uppercase tracking-wider font-bold text-stone-400 dark:text-stone-500">Email Address</span>
                        <input type="email" name="email" id="inputEmail" value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>" disabled class="profile-input w-full h-10 px-3 rounded-xl bg-stone-100 dark:bg-stone-800/40 border border-stone-200 dark:border-stone-700 text-xs text-stone-700 dark:text-stone-300 disabled:opacity-80 disabled:cursor-not-allowed focus:outline-none focus:border-red-500 transition-colors">
                    </label>

                    <label class="space-y-1.5 block">
                        <span class="block text-[10px] uppercase tracking-wider font-bold text-stone-400 dark:text-stone-500">Phone Number</span>
                        <input type="text" name="phone" id="inputPhone" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>" disabled class="profile-input w-full h-10 px-3 rounded-xl bg-stone-100 dark:bg-stone-800/40 border border-stone-200 dark:border-stone-700 text-xs text-stone-700 dark:text-stone-300 disabled:opacity-80 disabled:cursor-not-allowed focus:outline-none focus:border-red-500 transition-colors">
                    </label>

                    <label class="space-y-1.5 block">
                        <span class="block text-[10px] uppercase tracking-wider font-bold text-stone-400 dark:text-stone-500">Security Rank</span>
                        <input type="text" value="Administrator (Full Access)" disabled class="w-full h-10 px-3 rounded-xl bg-stone-100 dark:bg-stone-800/40 border border-stone-200 dark:border-stone-700 text-xs text-stone-400 dark:text-stone-500 cursor-not-allowed">
                    </label>
                </div>

                <!-- Password Change Section (Hidden by default in view mode) -->
                <div id="passwordSection" class="pt-2 border-t border-stone-100 dark:border-stone-800 hidden">
                    <div class="mb-4">
                        <h4 class="text-xs font-bold text-stone-900 dark:text-white">Change Access Password</h4>
                        <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">Agar current login password change nahi karna, toh is matrix block ko blank chhod dein.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <label class="space-y-1.5 block">
                            <span class="block text-[10px] uppercase tracking-wider font-bold text-stone-400 dark:text-stone-500">Current Password</span>
                            <input type="password" name="current_password" placeholder="••••••••" class="profile-input w-full h-10 px-3 rounded-xl bg-stone-50 dark:bg-stone-800/80 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-red-500 transition-colors">
                        </label>

                        <label class="space-y-1.5 block">
                            <span class="block text-[10px] uppercase tracking-wider font-bold text-stone-400 dark:text-stone-500">New Password</span>
                            <input type="password" name="new_password" placeholder="••••••••" class="profile-input w-full h-10 px-3 rounded-xl bg-stone-50 dark:bg-stone-800/80 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-red-500 transition-colors">
                        </label>

                        <label class="space-y-1.5 block">
                            <span class="block text-[10px] uppercase tracking-wider font-bold text-stone-400 dark:text-stone-500">Confirm Password</span>
                            <input type="password" name="confirm_password" placeholder="••••••••" class="profile-input w-full h-10 px-3 rounded-xl bg-stone-50 dark:bg-stone-800/80 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-red-500 transition-colors">
                        </label>
                    </div>
                </div>

                <!-- Form Action Buttons Footer (Hidden in View Mode, Visible in Edit Mode) -->
                <div id="formActionFooter" class="flex items-center justify-between gap-3 pt-3 border-t border-stone-100 dark:border-stone-800 hidden">
                    <button type="button" onclick="toggleEditMode(false)" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-xs font-bold text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-700 transition-colors">
                        <i class="fas fa-xmark text-[10px]"></i> Cancel Edit
                    </button>
                    <button type="submit" name="update_profile" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-olive-500 text-white text-xs font-bold hover:opacity-90 transition-all shadow-md shadow-red-500/20 active:scale-95">
                        <i class="fas fa-floppy-disk text-[10px]"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleEditMode(isEditing) {
    const inputs = document.querySelectorAll('.profile-input');
    const passwordSection = document.getElementById('passwordSection');
    const formActionFooter = document.getElementById('formActionFooter');
    const enableEditBtn = document.getElementById('enableEditBtn');
    const formTitleText = document.getElementById('formTitleText');
    const formDescText = document.getElementById('formDescText');
    const modeBadge = document.getElementById('modeBadge');

    if (isEditing) {
        // Switch to Edit Mode
        inputs.forEach(input => {
            input.disabled = false;
            input.classList.remove('bg-stone-100', 'dark:bg-stone-800/40', 'border-stone-200', 'dark:border-stone-700', 'text-stone-700', 'dark:text-stone-300');
            input.classList.add('bg-stone-50', 'dark:bg-stone-800/80', 'border-stone-200', 'dark:border-stone-700/80', 'text-stone-900', 'dark:text-stone-100');
        });
        passwordSection.classList.remove('hidden');
        formActionFooter.classList.remove('hidden');
        enableEditBtn.classList.add('hidden');
        
        formTitleText.textContent = "Edit Profile Information (Edit Mode)";
        formDescText.textContent = "Apni details update karne ke baad 'Save Changes' button par click karein.";
        modeBadge.textContent = "Editing Mode";
        modeBadge.className = "inline-flex items-center px-2.5 py-1 text-[10px] font-bold rounded-lg bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/20";
    } else {
        // Switch back to View Mode
        inputs.forEach(input => {
            input.disabled = true;
            input.classList.add('bg-stone-100', 'dark:bg-stone-800/40', 'border-stone-200', 'dark:border-stone-700', 'text-stone-700', 'dark:text-stone-300');
            input.classList.remove('bg-stone-50', 'dark:bg-stone-800/80', 'border-stone-200', 'dark:border-stone-700/80', 'text-stone-900', 'dark:text-stone-100');
            if(input.type === 'password') input.value = ''; // clear password fields on cancel
        });
        passwordSection.classList.add('hidden');
        formActionFooter.classList.add('hidden');
        enableEditBtn.classList.remove('hidden');

        formTitleText.textContent = "Profile Information (View Mode)";
        formDescText.textContent = "Aapke account ki details yahan displayed hain. Edit karne ke liye upar button dabayein.";
        modeBadge.textContent = "View Only";
        modeBadge.className = "inline-flex items-center px-2.5 py-1 text-[10px] font-bold rounded-lg bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400 border border-stone-200 dark:border-stone-700";
    }
}

// Agar validation error aaya hai toh page reload hone par automatically edit mode khula rahega
<?php if ($has_error): ?>
window.addEventListener('DOMContentLoaded', () => {
    toggleEditMode(true);
});
<?php endif; ?>
</script>

<?php
$profile_html = ob_get_clean();
render_admin_layout("Profile Center", $profile_html, 'profile');
?>