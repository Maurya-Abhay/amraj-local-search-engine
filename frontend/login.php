<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/public_layout.php';
amraj_render_public_header('Login - AMRAJ', 'login', false);
?>

<!-- Include SweetAlert2 for modern gorgeous popups -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="flex items-center justify-center p-4 relative overflow-hidden bg-stone-50 text-stone-900 dark:bg-stone-950 dark:text-stone-50 transition-colors duration-300 min-h-[calc(100vh-80px)]">
    <!-- Subtle Background Glow -->


    <!-- Clean Form Wrapper Box -->
    <div class="w-full max-w-md bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 p-6 sm:p-8 rounded-2xl shadow-sm transition-all duration-300 my-4 z-10">
        
        <!-- Header Text Block -->
        <div class="text-center mb-6">
            <h1 class="text-3xl font-extrabold tracking-tight text-terracotta-700 dark:text-terracotta-400">AMRAJ</h1>
            <p class="text-[11px] text-stone-500 font-medium mt-1">Every Necessity, Single Query Solution</p>
            <h2 class="text-base font-bold text-stone-800 dark:text-stone-200 mt-4 tracking-tight">Welcome back</h2>
            <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">Log in to manage your account and listings</p>
        </div>

        <!-- Success Notification Block -->
        <?php if(isset($_SESSION['success'])): ?>
            <div class="mb-4 p-3.5 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/50 text-emerald-600 dark:text-emerald-400 text-xs font-medium rounded-xl flex items-center gap-2.5">
                <i class="fas fa-check-circle text-sm shrink-0"></i> 
                <span><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Error Notification Block -->
        <?php if(isset($_SESSION['error'])): ?>
            <div class="mb-4 p-3.5 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/50 text-rose-600 dark:text-rose-400 text-xs font-medium rounded-xl flex items-center gap-2.5">
                <i class="fas fa-exclamation-circle text-sm shrink-0"></i> 
                <span><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
            </div>
        <?php endif; ?>

        <!-- Active Login Form -->
        <form action="../backend/auth_handler.php" method="POST" id="loginForm" class="space-y-4">
            <?php echo amraj_csrf_input(); ?>
            
            <!-- Email or Phone -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-stone-700 dark:text-stone-300">Email or Phone Number</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-stone-400">
                        <i class="fas fa-user text-xs"></i>
                    </span>
                    <input type="text" name="identity" placeholder="name@example.com or phone" required class="w-full pl-10 pr-4 py-2.5 bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 text-stone-900 dark:text-white placeholder-stone-400 rounded-xl focus:outline-none focus:ring-2 focus:ring-terracotta-500/20 focus:border-terracotta-500 transition-all text-xs font-medium">
                </div>
            </div>

            <!-- Password -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-stone-700 dark:text-stone-300">Password</label>
                </div>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-stone-400">
                        <i class="fas fa-lock text-xs"></i>
                    </span>
                    <input type="password" id="passwordField" name="password" placeholder="••••••••••••" required class="w-full pl-10 pr-10 py-2.5 bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 text-stone-900 dark:text-white placeholder-stone-400 rounded-xl focus:outline-none focus:ring-2 focus:ring-terracotta-500/20 focus:border-terracotta-500 transition-all text-xs font-medium">
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-stone-400 hover:text-stone-600 dark:hover:text-stone-200">
                        <i id="eyeIcon" class="fas fa-eye text-xs"></i>
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" name="login_user" class="w-full py-3 bg-terracotta-600 hover:bg-terracotta-700 active:scale-[0.99] text-white font-bold text-xs rounded-xl shadow-sm transition-all duration-200 flex items-center justify-center gap-2 mt-2 cursor-pointer">
                <span>Log In</span> 
                <i class="fas fa-arrow-right text-[10px]"></i>
            </button>
        </form>

        <!-- Redirect Link -->
        <p class="text-center text-xs text-stone-500 dark:text-stone-400 mt-5 font-medium">
            Don't have an account? 
            <a href="register.php" class="text-terracotta-600 dark:text-terracotta-400 hover:underline font-bold ml-1">Create an account</a>
        </p>
    </div>
</main>

<script>
    function togglePassword() {
        var pwd = document.getElementById('passwordField');
        var icon = document.getElementById('eyeIcon');
        if (pwd.type === 'passsword' || pwd.type === 'password') {
            pwd.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            pwd.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>

<?php 
// Check if login session flag is set by backend handler to trigger SweetAlert popup
if (isset($_SESSION['login_success_alert'])) {
    unset($_SESSION['login_success_alert']);
    $dashboard_url = 'user/dashboard.php'; // adjust path if needed based on role (e.g. admin or merchant)
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Login Successful! 🎉',
                text: 'Welcome back to AMRAJ Portal. Redirecting to your dashboard...',
                icon: 'success',
                timer: 1800,
                timerProgressBar: true,
                showConfirmButton: false,
                background: document.documentElement.classList.contains('dark') ? '#241f18' : '#ffffff',
                color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#241f18',
                didClose: () => {
                    window.location.href = '<?php echo $dashboard_url; ?>';
                }
            });
        });
    </script>
    <?php
}

amraj_render_public_footer(); 
?>