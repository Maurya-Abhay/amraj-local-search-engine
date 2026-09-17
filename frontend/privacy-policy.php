<?php 
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/public_layout.php';
amraj_render_public_header('Privacy Policy', 'privacy', false);
?>

<!-- Main Wrapper Layer -->
<main class="flex-1 max-w-7xl mx-auto px-4 sm:px-8 py-10 md:py-16 space-y-10 md:space-y-16">
    
    <!-- Dynamic Header Segment -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-stone-200/60 dark:border-stone-800/80 pb-6 gap-4">
        <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-terracotta-500/10 text-terracotta-600 dark:text-terracotta-400 text-xs font-semibold border border-terracotta-500/20">
                <i class="fas fa-shield-alt text-[10px]"></i> Data Governance
            </span>
            <h1 class="text-3xl sm:text-4xl font-bold text-stone-900 dark:text-white tracking-tight">
                Privacy <span class="text-terracotta-700 dark:text-terracotta-400">Policy</span>
            </h1>
            <p class="text-stone-600 dark:text-stone-300 text-xs sm:text-sm leading-relaxed max-w-2xl">
                Learn how we collect, protect, and handle your personal information and merchant data on our platform.
            </p>
        </div>
        <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-xl px-4 py-2.5 text-left min-w-[200px] shrink-0 shadow-sm">
            <span class="block text-[10px] uppercase font-bold text-stone-400">Last Updated</span>
            <span class="text-xs font-bold text-stone-800 dark:text-stone-200">July 2026</span>
        </div>
    </div>

    <!-- Master Content Split Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12 items-start">
        
        <!-- Left Side: Policy Clauses Blocks -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Clause 1 -->
            <div class="group p-6 sm:p-8 bg-white dark:bg-stone-900/40 border border-stone-200/80 dark:border-stone-800 rounded-2xl space-y-3 shadow-sm hover:border-terracotta-500/40 transition-colors">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-terracotta-500/10 text-terracotta-600 dark:text-terracotta-400 flex items-center justify-center text-xs font-bold shadow-inner">1</div>
                    <h3 class="text-sm sm:text-base font-bold text-stone-900 dark:text-white">Information We Collect</h3>
                </div>
                <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300 leading-relaxed pl-14">
                    We collect personal details such as your name, email address, phone numbers (including WhatsApp details if provided), and business specifications when you register an account or submit your merchant profile on our directory.
                </p>
            </div>

            <!-- Clause 2 -->
            <div class="group p-6 sm:p-8 bg-white dark:bg-stone-900/40 border border-stone-200/80 dark:border-stone-800 rounded-2xl space-y-3 shadow-sm hover:border-olive-500/40 transition-colors">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-olive-500/10 text-olive-600 dark:text-olive-400 flex items-center justify-center text-xs font-bold shadow-inner">2</div>
                    <h3 class="text-sm sm:text-base font-bold text-stone-900 dark:text-white">How We Use Your Data</h3>
                </div>
                <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300 leading-relaxed pl-14">
                    Business contact details (such as phone and WhatsApp links) are displayed publicly on listing pages to help customers reach out to vendors directly. Your account password and sensitive credentials are encrypted and never shared with third-party advertisers.
                </p>
            </div>

            <!-- Clause 3 -->
            <div class="group p-6 sm:p-8 bg-white dark:bg-stone-900/40 border border-stone-200/80 dark:border-stone-800 rounded-2xl space-y-3 shadow-sm hover:border-emerald-500/40 transition-colors">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs font-bold shadow-inner">3</div>
                    <h3 class="text-sm sm:text-base font-bold text-stone-900 dark:text-white">Cookies & Session Tracking</h3>
                </div>
                <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300 leading-relaxed pl-14">
                    We use standard PHP session cookies to maintain your login state and keep your dashboard secure while you browse. These cookies are temporary and expire automatically when you log out or close your browser.
                </p>
            </div>
        </div>

        <!-- Right Side: Sticky Policy Reference System -->
        <div class="space-y-6 lg:sticky lg:top-8">
            <div class="p-6 bg-stone-50 dark:bg-stone-900/30 border border-stone-200/80 dark:border-stone-800 rounded-2xl space-y-4">
                <h4 class="text-xs font-bold text-stone-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-user-shield text-terracotta-500"></i> Your Control Rights
                </h4>
                <p class="text-xs text-stone-600 dark:text-stone-300 leading-relaxed">
                    You have complete control over your data. You can update or delete your profile information directly from your user dashboard at any time.
                </p>
                <div class="pt-2 border-t border-stone-200 dark:border-stone-800">
                    <a href="mailto:privacy@amraj.engine" class="flex items-center justify-between text-xs font-semibold text-terracotta-600 dark:text-terracotta-400 hover:underline">
                        <span>Contact Data Officer</span>
                        <i class="fas fa-external-link-alt text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Scan Card -->
            <div class="p-6 bg-white dark:bg-stone-900/40 border border-stone-200/80 dark:border-stone-800 rounded-2xl space-y-3 shadow-sm">
                <h4 class="text-xs font-bold text-stone-900 dark:text-white uppercase tracking-wider">Privacy Highlights</h4>
                <div class="space-y-2 text-xs font-medium">
                    <div class="flex items-center justify-between p-2.5 bg-stone-50 dark:bg-stone-950 rounded-xl border border-stone-100 dark:border-stone-800/60">
                        <span class="text-stone-500">Password Security</span>
                        <span class="text-terracotta-500 bg-terracotta-500/10 px-2 py-0.5 rounded text-[10px] font-bold">Secure Hashed</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 bg-stone-50 dark:bg-stone-950 rounded-xl border border-stone-100 dark:border-stone-800/60">
                        <span class="text-stone-500">Data Selling</span>
                        <span class="text-red-500 bg-red-500/10 px-2 py-0.5 rounded text-[10px] font-bold">Never</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 bg-stone-50 dark:bg-stone-950 rounded-xl border border-stone-100 dark:border-stone-800/60">
                        <span class="text-stone-500">Session Cookies</span>
                        <span class="text-emerald-500 bg-emerald-500/10 px-2 py-0.5 rounded text-[10px] font-bold">Temporary</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Retention Table -->
    <div class="space-y-4 pt-4">
        <h3 class="text-xs font-bold text-stone-800 dark:text-stone-200 uppercase tracking-wider flex items-center gap-2">
            <i class="fas fa-database text-terracotta-500"></i> Data Retention & Storage Overview
        </h3>
        <div class="overflow-hidden border border-stone-200/80 dark:border-stone-800 rounded-2xl bg-white dark:bg-stone-900/40 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-stone-50 dark:bg-stone-950 text-[10px] uppercase text-stone-400 font-bold border-b border-stone-200 dark:border-stone-800">
                            <th class="p-4">Data Category</th>
                            <th class="p-4">Protection Method</th>
                            <th class="p-4 text-right">Retention Period</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 dark:divide-stone-800 text-stone-600 dark:text-stone-300 font-medium">
                        <tr>
                            <td class="p-4 font-semibold text-stone-900 dark:text-white">User Account Credentials</td>
                            <td class="p-4 text-stone-500">Encrypted Password Hashing</td>
                            <td class="p-4 text-emerald-600 dark:text-emerald-400 font-semibold text-right">Until Account Deletion</td>
                        </tr>
                        <tr>
                            <td class="p-4 font-semibold text-stone-900 dark:text-white">Public Business Listings</td>
                            <td class="p-4 text-stone-500">Visible for Directory Access</td>
                            <td class="p-4 text-terracotta-600 dark:text-terracotta-400 font-semibold text-right">Editable in Real-Time</td>
                        </tr>
                        <tr>
                            <td class="p-4 font-semibold text-stone-900 dark:text-white">Active Login Sessions</td>
                            <td class="p-4 text-stone-500">Secure PHP Session Tokens</td>
                            <td class="p-4 text-olive-600 dark:text-olive-400 font-semibold text-right">Until Logout / Expiry</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php amraj_render_public_footer(); ?>