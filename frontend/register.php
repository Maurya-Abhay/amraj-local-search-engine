<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/public_layout.php';
amraj_render_public_header('Create Account - AMRAJ', 'register', false);
?>

<main class="flex items-center justify-center p-4 relative overflow-hidden bg-stone-50 text-stone-900 dark:bg-stone-950 dark:text-stone-50 transition-colors duration-300 min-h-[calc(100vh-80px)]">
    <!-- Subtle Background Glow -->


    <!-- Clean Form Wrapper Box -->
    <div class="w-full max-w-md bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 p-6 sm:p-8 rounded-2xl shadow-sm transition-all duration-300 my-4 z-10">
        
        <!-- Header Text Block -->
        <div class="text-center mb-6">
            <h1 class="text-3xl font-extrabold tracking-tight text-terracotta-700 dark:text-terracotta-400">AMRAJ</h1>
            <p class="text-[11px] text-stone-500 font-medium mt-1">Every Necessity, Single Query Solution</p>
            <h2 class="text-base font-bold text-stone-800 dark:text-stone-200 mt-4 tracking-tight">Create your account</h2>
            <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">Join our platform in seconds</p>
        </div>

        <!-- Error Notification Block -->
        <?php if(isset($_SESSION['error'])): ?>
            <div class="mb-4 p-3.5 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/50 text-rose-600 dark:text-rose-400 text-xs font-medium rounded-xl flex items-center gap-2.5">
                <i class="fas fa-exclamation-circle text-sm shrink-0"></i> 
                <span><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
            </div>
        <?php endif; ?>

        <!-- Active Registration Form -->
        <form action="../backend/auth_handler.php" method="POST" class="space-y-4">
            <?php echo amraj_csrf_input(); ?>
            
            <!-- Account Type Switcher -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-stone-700 dark:text-stone-300">I want to join as a</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer group">
                        <input type="radio" name="role" value="user" checked class="sr-only peer">
                        <div class="p-3 text-center rounded-xl bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 text-stone-600 dark:text-stone-400 peer-checked:bg-terracotta-600 peer-checked:border-terracotta-600 peer-checked:text-white transition-all font-medium text-xs shadow-sm flex items-center justify-center gap-2">
                            <i class="fas fa-user text-xs"></i> Consumer
                        </div>
                    </label>
                    <label class="cursor-pointer group">
                        <input type="radio" name="role" value="owner" class="sr-only peer">
                        <div class="p-3 text-center rounded-xl bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 text-stone-600 dark:text-stone-400 peer-checked:bg-olive-600 peer-checked:border-olive-600 peer-checked:text-white transition-all font-medium text-xs shadow-sm flex items-center justify-center gap-2">
                            <i class="fas fa-store text-xs"></i> Merchant
                        </div>
                    </label>
                </div>
            </div>

            <!-- Full Name -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-stone-700 dark:text-stone-300">Full Name</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-stone-400">
                        <i class="fas fa-user text-xs"></i>
                    </span>
                    <input type="text" name="name" placeholder="John Doe" required class="w-full pl-10 pr-4 py-2.5 bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 text-stone-900 dark:text-white placeholder-stone-400 rounded-xl focus:outline-none focus:ring-2 focus:ring-terracotta-500/20 focus:border-terracotta-500 transition-all text-xs font-medium">
                </div>
            </div>

            <!-- Email Address -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-stone-700 dark:text-stone-300">Email Address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-stone-400">
                        <i class="fas fa-envelope text-xs"></i>
                    </span>
                    <input type="email" name="email" placeholder="name@example.com" required class="w-full pl-10 pr-4 py-2.5 bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 text-stone-900 dark:text-white placeholder-stone-400 rounded-xl focus:outline-none focus:ring-2 focus:ring-terracotta-500/20 focus:border-terracotta-500 transition-all text-xs font-medium">
                </div>
            </div>

            <!-- Phone Number -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-stone-700 dark:text-stone-300">Phone Number</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-stone-400">
                        <i class="fas fa-phone text-xs"></i>
                    </span>
                    <input type="tel" name="phone" placeholder="+91 98765 43210" required class="w-full pl-10 pr-4 py-2.5 bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 text-stone-900 dark:text-white placeholder-stone-400 rounded-xl focus:outline-none focus:ring-2 focus:ring-terracotta-500/20 focus:border-terracotta-500 transition-all text-xs font-medium">
                </div>
            </div>

            <!-- Password -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-stone-700 dark:text-stone-300">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-stone-400">
                        <i class="fas fa-lock text-xs"></i>
                    </span>
                    <input type="password" name="password" placeholder="••••••••••••" required class="w-full pl-10 pr-4 py-2.5 bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 text-stone-900 dark:text-white placeholder-stone-400 rounded-xl focus:outline-none focus:ring-2 focus:ring-terracotta-500/20 focus:border-terracotta-500 transition-all text-xs font-medium">
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" name="register_user" class="w-full py-3 bg-terracotta-600 hover:bg-terracotta-700 active:scale-[0.99] text-white font-bold text-xs rounded-xl shadow-sm transition-all duration-200 flex items-center justify-center gap-2 mt-2">
                <span>Create Account</span> 
                <i class="fas fa-arrow-right text-[10px]"></i>
            </button>
        </form>

        <!-- Redirect Link -->
        <p class="text-center text-xs text-stone-500 dark:text-stone-400 mt-5 font-medium">
            Already have an account? 
            <a href="login.php" class="text-terracotta-600 dark:text-terracotta-400 hover:underline font-bold ml-1">Log In</a>
        </p>
    </div>
</main>

<?php 
amraj_render_public_footer(); 
?>