<?php 
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/public_layout.php';
amraj_render_public_header('Terms & Conditions', 'terms', false);
?>

<!-- Main Wrapper Layer -->
<main class="flex-1 max-w-7xl mx-auto px-4 sm:px-8 py-10 md:py-16 space-y-10 md:space-y-16">
    
    <!-- Dynamic Header Segment -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-stone-200/60 dark:border-stone-800/80 pb-6 gap-4">
        <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-olive-500/10 text-olive-600 dark:text-olive-400 text-xs font-semibold border border-olive-500/20">
                <i class="fas fa-balance-scale text-[10px]"></i> Legal Agreement
            </span>
            <h1 class="text-3xl sm:text-4xl font-bold text-stone-900 dark:text-white tracking-tight">
                Terms & <span class="text-terracotta-700 dark:text-terracotta-400">Conditions</span>
            </h1>
            <p class="text-stone-600 dark:text-stone-300 text-xs sm:text-sm leading-relaxed max-w-2xl">
                Please review these guidelines and rules governing the use of our platform, merchant directory, and related services.
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
                    <h3 class="text-sm sm:text-base font-bold text-stone-900 dark:text-white">Acceptance of Platform Rules</h3>
                </div>
                <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300 leading-relaxed pl-14">
                    By registering an account, browsing listings, or using any feature on AMRAJ, you agree to comply with these terms. If you do not agree with any part of these conditions, please discontinue using the platform immediately.
                </p>
            </div>

            <!-- Clause 2 -->
            <div class="group p-6 sm:p-8 bg-white dark:bg-stone-900/40 border border-stone-200/80 dark:border-stone-800 rounded-2xl space-y-3 shadow-sm hover:border-olive-500/40 transition-colors">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-olive-500/10 text-olive-600 dark:text-olive-400 flex items-center justify-center text-xs font-bold shadow-inner">2</div>
                    <h3 class="text-sm sm:text-base font-bold text-stone-900 dark:text-white">Listing Authenticity & Vendor Integrity</h3>
                </div>
                <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300 leading-relaxed pl-14">
                    Registered businesses must provide accurate, legitimate, and up-to-date information. Providing false contact numbers, incorrect addresses, or misleading service details will result in profile suspension and removal from our directory.
                </p>
            </div>

            <!-- Clause 3 -->
            <div class="group p-6 sm:p-8 bg-white dark:bg-stone-900/40 border border-stone-200/80 dark:border-stone-800 rounded-2xl space-y-3 shadow-sm hover:border-emerald-500/40 transition-colors">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs font-bold shadow-inner">3</div>
                    <h3 class="text-sm sm:text-base font-bold text-stone-900 dark:text-white">User Reviews & Conduct Guidelines</h3>
                </div>
                <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300 leading-relaxed pl-14">
                    Reviews, ratings, and comments posted on listings must be fair, honest, and respectful. Spam, abusive language, or fake promotional reviews are strictly prohibited. Our moderation team reserves the right to remove inappropriate content.
                </p>
            </div>

            <!-- Clause 4 -->
            <div class="group p-6 sm:p-8 bg-white dark:bg-stone-900/40 border border-stone-200/80 dark:border-stone-800 rounded-2xl space-y-3 shadow-sm hover:border-purple-500/40 transition-colors">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xs font-bold shadow-inner">4</div>
                    <h3 class="text-sm sm:text-base font-bold text-stone-900 dark:text-white">Limitation of Liability</h3>
                </div>
                <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300 leading-relaxed pl-14">
                    AMRAJ serves as a directory platform connecting users with local businesses. We do not assume responsibility or financial liability for direct transactions, service disputes, or agreements made between users and independent vendors.
                </p>
            </div>
        </div>

        <!-- Right Side: Sticky Policy Reference System -->
        <div class="space-y-6 lg:sticky lg:top-8">
            <div class="p-6 bg-stone-50 dark:bg-stone-900/30 border border-stone-200/80 dark:border-stone-800 rounded-2xl space-y-4">
                <h4 class="text-xs font-bold text-stone-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-shield-alt text-terracotta-500"></i> Compliance Notice
                </h4>
                <p class="text-xs text-stone-600 dark:text-stone-300 leading-relaxed">
                    Violations of these terms may lead to restricted account access or permanent removal from our directory database.
                </p>
                <div class="pt-2 border-t border-stone-200 dark:border-stone-800">
                    <a href="mailto:support@amraj.engine" class="flex items-center justify-between text-xs font-semibold text-terracotta-600 dark:text-terracotta-400 hover:underline">
                        <span>Contact Compliance Support</span>
                        <i class="fas fa-external-link-alt text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Scan Card -->
            <div class="p-6 bg-white dark:bg-stone-900/40 border border-stone-200/80 dark:border-stone-800 rounded-2xl space-y-3 shadow-sm">
                <h4 class="text-xs font-bold text-stone-900 dark:text-white uppercase tracking-wider">Policy Highlights</h4>
                <div class="space-y-2 text-xs font-medium">
                    <div class="flex items-center justify-between p-2.5 bg-stone-50 dark:bg-stone-950 rounded-xl border border-stone-100 dark:border-stone-800/60">
                        <span class="text-stone-500">Data Scraping</span>
                        <span class="text-red-500 bg-red-500/10 px-2 py-0.5 rounded text-[10px] font-bold">Prohibited</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 bg-stone-50 dark:bg-stone-950 rounded-xl border border-stone-100 dark:border-stone-800/60">
                        <span class="text-stone-500">Profile Ownership</span>
                        <span class="text-emerald-500 bg-emerald-500/10 px-2 py-0.5 rounded text-[10px] font-bold">Verified Only</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 bg-stone-50 dark:bg-stone-950 rounded-xl border border-stone-100 dark:border-stone-800/60">
                        <span class="text-stone-500">API Access</span>
                        <span class="text-terracotta-500 bg-terracotta-500/10 px-2 py-0.5 rounded text-[10px] font-bold">Authorized</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Policy Enforcement Table -->
    <div class="space-y-4 pt-4">
        <h3 class="text-xs font-bold text-stone-800 dark:text-stone-200 uppercase tracking-wider flex items-center gap-2">
            <i class="fas fa-gavel text-terracotta-500"></i> Violation & Resolution Guidelines
        </h3>
        <div class="overflow-hidden border border-stone-200/80 dark:border-stone-800 rounded-2xl bg-white dark:bg-stone-900/40 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-stone-50 dark:bg-stone-950 text-[10px] uppercase text-stone-400 font-bold border-b border-stone-200 dark:border-stone-800">
                            <th class="p-4">Infraction Type</th>
                            <th class="p-4">Initial Action</th>
                            <th class="p-4 text-right">Final Resolution</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 dark:divide-stone-800 text-stone-600 dark:text-stone-300 font-medium">
                        <tr>
                            <td class="p-4 font-semibold text-stone-900 dark:text-white">Fabricated Merchant Details</td>
                            <td class="p-4 text-stone-500">Temporary visibility restriction</td>
                            <td class="p-4 text-olive-600 dark:text-olive-400 font-semibold text-right">24-Hour Review / Removal</td>
                        </tr>
                        <tr>
                            <td class="p-4 font-semibold text-stone-900 dark:text-white">Review & Rating Manipulation</td>
                            <td class="p-4 text-stone-500">Score recalculation & reset</td>
                            <td class="p-4 text-red-600 dark:text-red-400 font-semibold text-right">Account Suspension</td>
                        </tr>
                        <tr>
                            <td class="p-4 font-semibold text-stone-900 dark:text-white">Spam or Abusive Content</td>
                            <td class="p-4 text-stone-500">Content removal</td>
                            <td class="p-4 text-emerald-600 dark:text-emerald-400 font-semibold text-right">IP Restriction / Ban</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php amraj_render_public_footer(); ?>