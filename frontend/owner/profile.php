<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once '../../backend/config.php';
require_once 'layout.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: ../login.php");
    exit();
}

$owner_id = (int) $_SESSION['user_id'];
$error_message = '';
$success_message = '';

if (isset($_GET['msg']) && $_GET['msg'] === 'updated') {
    $success_message = 'Your profile configuration has been updated successfully.';
}

if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $phone === '') {
        $error_message = 'Name, email configuration, and phone contact are mandatory requirements.';
    } else {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE id = ? AND role = 'owner' LIMIT 1");
        $stmt->bind_param("i", $owner_id);
        $stmt->execute();
        $owner = $stmt->get_result()->fetch_assoc();

        if (!$owner) {
            $error_message = 'Owner authentication identity profile context missing.';
        } else {
            $dup = $conn->prepare("SELECT id FROM users WHERE (email = ? OR phone = ?) AND id != ? LIMIT 1");
            $dup->bind_param("ssi", $email, $phone, $owner_id);
            $dup->execute();

            if ($dup->get_result()->num_rows > 0) {
                $error_message = 'The target email address or contact number is already mapped to an existing user account.';
            } else {
                $hashed_password = $owner['password'];
                $change_password = false;

                if ($current_password !== '' || $new_password !== '' || $confirm_password !== '') {
                    if ($current_password === '' || $new_password === '' || $confirm_password === '') {
                        $error_message = 'To complete password modifications, all relevant password inputs must be defined.';
                    } elseif (!password_verify($current_password, $owner['password'])) {
                        $error_message = 'The current verification password provided does not match our records.';
                    } elseif ($new_password !== $confirm_password) {
                        $error_message = 'The new security password and validation confirmation entry must match.';
                    } elseif (strlen($new_password) < 6) {
                        $error_message = 'Secured new authentication keys must consist of at least 6 characters.';
                    } else {
                        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                        $change_password = true;
                    }
                }

                if ($error_message === '') {
                    if ($change_password) {
                        $update = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, password = ? WHERE id = ? AND role = 'owner'");
                        $update->bind_param("ssssi", $name, $email, $phone, $hashed_password, $owner_id);
                    } else {
                        $update = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ? AND role = 'owner'");
                        $update->bind_param("sssi", $name, $email, $phone, $owner_id);
                    }

                    if ($update->execute()) {
                        $_SESSION['user_name'] = $name;
                        header("Location: profile.php?msg=updated");
                        exit();
                    }
                    $error_message = 'System architecture failed to commit your profile metadata update.';
                }
            }
        }
    }
}

$owner_stmt = $conn->prepare("SELECT id, name, email, phone, role, created_at FROM users WHERE id = ? AND role = 'owner' LIMIT 1");
$owner_stmt->bind_param("i", $owner_id);
$owner_stmt->execute();
$owner = $owner_stmt->get_result()->fetch_assoc();

ob_start();
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-200 dark:border-stone-800 pb-5 mb-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-black tracking-tight text-stone-900 dark:text-white">Owner <span class="text-olive-500">Workspace Profile</span></h1>
        <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">Manage and audit your core credential profiles, linked communication pipelines, and authentication parameter matrices.</p>
    </div>
    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-xs font-semibold text-stone-600 dark:text-stone-300 shadow-sm self-start sm:self-auto">
        <i class="fas fa-shield-halved text-olive-500"></i> Secure Node Access
    </div>
</div>

<?php if ($error_message !== ''): ?>
    <div class="mb-5 rounded-lg border border-red-200 dark:border-red-500/20 bg-red-50 dark:bg-red-500/10 px-4 py-3 text-xs font-medium text-red-700 dark:text-red-300 flex items-center gap-2">
        <i class="fas fa-circle-exclamation text-red-500"></i>
        <span><?php echo htmlspecialchars($error_message); ?></span>
    </div>
<?php endif; ?>

<?php if ($success_message !== ''): ?>
    <div class="mb-5 rounded-lg border border-emerald-200 dark:border-emerald-500/20 bg-emerald-50 dark:bg-emerald-500/10 px-4 py-3 text-xs font-medium text-emerald-700 dark:text-emerald-300 flex items-center gap-2">
        <i class="fas fa-circle-check text-emerald-500"></i>
        <span><?php echo htmlspecialchars($success_message); ?></span>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Identity Matrix Cards -->
    <div class="lg:col-span-1 space-y-4">
        <div class="p-5 rounded-xl border border-stone-200 dark:border-stone-700/80 bg-white dark:bg-stone-800/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-gradient-to-tr from-olive-500 to-amber-400 flex items-center justify-center text-white text-lg font-black shadow-sm">
                <?php echo strtoupper(substr($owner['name'] ?? $_SESSION['user_name'], 0, 1)); ?>
            </div>
            <div class="overflow-hidden">
                <p class="text-[10px] uppercase tracking-wider text-stone-400 dark:text-stone-500 font-bold">Authorized Merchant</p>
                <h2 class="text-base font-black text-stone-900 dark:text-white truncate"><?php echo htmlspecialchars($owner['name'] ?? $_SESSION['user_name']); ?></h2>
                <p class="text-xs text-stone-500 dark:text-stone-400 truncate"><?php echo htmlspecialchars($owner['email'] ?? ''); ?></p>
            </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-1 gap-4">
            <div class="p-4 rounded-xl border border-stone-200 dark:border-stone-700/80 bg-white dark:bg-stone-800/80 shadow-sm">
                <p class="text-[10px] uppercase tracking-wider text-stone-400 dark:text-stone-500 font-bold">Account Registration Token</p>
                <p class="mt-1 text-xs font-mono font-semibold text-stone-900 dark:text-white">#OWN-<?php echo str_pad((string)$owner['id'], 4, '0', STR_PAD_LEFT); ?></p>
            </div>
            <div class="p-4 rounded-xl border border-stone-200 dark:border-stone-700/80 bg-white dark:bg-stone-800/80 shadow-sm">
                <p class="text-[10px] uppercase tracking-wider text-stone-400 dark:text-stone-500 font-bold">Verification Timeline</p>
                <p class="mt-1 text-xs font-semibold text-stone-900 dark:text-white"><?php echo date('d M Y', strtotime($owner['created_at'] ?? 'now')); ?></p>
            </div>
        </div>

        <button type="button" id="toggleEditBtn" class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-lg border border-olive-200 dark:border-olive-500/30 bg-olive-500/5 hover:bg-olive-500 text-olive-600 dark:text-olive-400 hover:text-white font-bold text-xs tracking-wider uppercase transition-all shadow-sm">
            <i class="fas fa-user-pen text-[11px]" id="toggleIcon"></i> <span id="toggleText">Unlock Profile Editing</span>
        </button>
    </div>

    <!-- Configuration Form Body -->
    <div class="lg:col-span-2">
        <form method="POST" id="profileConfigForm" class="p-5 sm:p-6 rounded-xl border border-stone-200 dark:border-stone-700/80 bg-white dark:bg-stone-800/80 shadow-sm space-y-5">
            <div class="border-b border-stone-100 dark:border-stone-700/60 pb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-stone-900 dark:text-white">Identity Credentials</h3>
                    <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">Modify individual profile nodes and registration values.</p>
                </div>
                <span id="formStatusBadge" class="px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider rounded bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-700 text-stone-400 dark:text-stone-500">Read-Only</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="space-y-1.5 block">
                    <span class="block text-[10px] uppercase tracking-wider font-black text-stone-400 dark:text-stone-500">Full Structural Name</span>
                    <input type="text" name="name" id="inputName" value="<?php echo htmlspecialchars($owner['name'] ?? ''); ?>" disabled class="w-full h-11 px-4 rounded-lg bg-stone-100 dark:bg-stone-900/40 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-olive-500 disabled:opacity-60 disabled:cursor-not-allowed transition-all">
                </label>
                <label class="space-y-1.5 block">
                    <span class="block text-[10px] uppercase tracking-wider font-black text-stone-400 dark:text-stone-500">Core Electronic Address</span>
                    <input type="email" name="email" id="inputEmail" value="<?php echo htmlspecialchars($owner['email'] ?? ''); ?>" disabled class="w-full h-11 px-4 rounded-lg bg-stone-100 dark:bg-stone-900/40 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-olive-500 disabled:opacity-60 disabled:cursor-not-allowed transition-all">
                </label>
                <label class="space-y-1.5 block">
                    <span class="block text-[10px] uppercase tracking-wider font-black text-stone-400 dark:text-stone-500">Communication Contact Line</span>
                    <input type="text" name="phone" id="inputPhone" value="<?php echo htmlspecialchars($owner['phone'] ?? ''); ?>" disabled class="w-full h-11 px-4 rounded-lg bg-stone-100 dark:bg-stone-900/40 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-olive-500 disabled:opacity-60 disabled:cursor-not-allowed transition-all">
                </label>
                <label class="space-y-1.5 block">
                    <span class="block text-[10px] uppercase tracking-wider font-black text-stone-400 dark:text-stone-500">System Role Map</span>
                    <input type="text" value="Business Owner Listing Operator" disabled class="w-full h-11 px-4 rounded-lg bg-stone-100 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-400 dark:text-stone-500 cursor-not-allowed opacity-60">
                </label>
            </div>

            <div class="pt-4 border-t border-stone-100 dark:border-stone-700/60 hidden transition-all duration-300" id="passwordSection">
                <div class="mb-4">
                    <h4 class="text-sm font-bold text-stone-900 dark:text-white">Security Validation Parameters</h4>
                    <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">Leave inputs blank if no cryptographic structural conversion is required.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <label class="space-y-1.5 block">
                        <span class="block text-[10px] uppercase tracking-wider font-black text-stone-400 dark:text-stone-500">Active Token</span>
                        <input type="password" name="current_password" id="inputCurrPass" disabled class="w-full h-11 px-4 rounded-lg bg-stone-100 dark:bg-stone-900/40 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-olive-500 disabled:opacity-60 disabled:cursor-not-allowed transition-all">
                    </label>
                    <label class="space-y-1.5 block">
                        <span class="block text-[10px] uppercase tracking-wider font-black text-stone-400 dark:text-stone-500">Target New Token</span>
                        <input type="password" name="new_password" id="inputNewPass" disabled class="w-full h-11 px-4 rounded-lg bg-stone-100 dark:bg-stone-900/40 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-olive-500 disabled:opacity-60 disabled:cursor-not-allowed transition-all">
                    </label>
                    <label class="space-y-1.5 block">
                        <span class="block text-[10px] uppercase tracking-wider font-black text-stone-400 dark:text-stone-500">Confirm Target Map</span>
                        <input type="password" name="confirm_password" id="inputConfPass" disabled class="w-full h-11 px-4 rounded-lg bg-stone-100 dark:bg-stone-900/40 border border-stone-200 dark:border-stone-700/80 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-olive-500 disabled:opacity-60 disabled:cursor-not-allowed transition-all">
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 pt-4 border-t border-stone-100 dark:border-stone-700/60">
                <a href="dashboard.php" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900/50 text-xs font-bold text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-700 transition-all">
                    <i class="fas fa-arrow-left text-[10px]"></i> Dashboard
                </a>
                <button type="submit" name="update_profile" id="saveProfileBtn" disabled class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-gradient-to-r from-olive-500 to-terracotta-500 text-white text-xs font-bold shadow-md shadow-olive-500/15 opacity-50 cursor-not-allowed transition-all">
                    <i class="fas fa-floppy-disk text-[10px]"></i> Save Modifications
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('toggleEditBtn').addEventListener('click', function() {
    const inputs = ['inputName', 'inputEmail', 'inputPhone', 'inputCurrPass', 'inputNewPass', 'inputConfPass'];
    const saveBtn = document.getElementById('saveProfileBtn');
    const badge = document.getElementById('formStatusBadge');
    const passSection = document.getElementById('passwordSection');
    const textSpan = document.getElementById('toggleText');
    const icon = document.getElementById('toggleIcon');
    
    // Check if currently disabled
    const isLocked = document.getElementById('inputName').disabled;
    
    inputs.forEach(id => {
        const element = document.getElementById(id);
        element.disabled = !isLocked;
        if(isLocked) {
            element.classList.remove('bg-stone-100', 'dark:bg-stone-900/40');
            element.classList.add('bg-stone-50', 'dark:bg-stone-900/10');
        } else {
            element.classList.add('bg-stone-100', 'dark:bg-stone-900/40');
            element.classList.remove('bg-stone-50', 'dark:bg-stone-900/10');
        }
    });
    
    if(isLocked) {
        saveBtn.disabled = false;
        saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        passSection.classList.remove('hidden');
        badge.innerText = "Write Mode";
        badge.classList.replace('text-stone-400', 'text-olive-500');
        badge.classList.replace('dark:text-stone-500', 'dark:text-olive-400');
        badge.classList.add('border-olive-500/30', 'bg-olive-500/5');
        textSpan.innerText = "Lock Matrix Inputs";
        icon.classList.replace('fa-user-pen', 'fa-lock');
    } else {
        saveBtn.disabled = true;
        saveBtn.classList.add('opacity-50', 'cursor-not-allowed');
        passSection.classList.add('hidden');
        badge.innerText = "Read-Only";
        badge.classList.remove('border-olive-500/30', 'bg-olive-500/5');
        badge.classList.add('text-stone-400', 'dark:text-stone-500');
        textSpan.innerText = "Unlock Profile Editing";
        icon.classList.replace('fa-lock', 'fa-user-pen');
    }
});
</script>

<?php
$owner_html = ob_get_clean();
render_owner_layout("Owner Profile", $owner_html, 'profile');
?>