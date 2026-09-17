<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once '../backend/config.php';
require_once __DIR__ . '/public_layout.php';
amraj_render_public_header('About Platform - AMRAJ', 'about', false);
?>

<!-- Main Wrapper Layer -->
<main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-16 space-y-10 md:space-y-16">
    
    <!-- Hero Section Block -->
    <section class="bg-stone-50 dark:bg-stone-950/40 border border-stone-200/80 dark:border-stone-800/60 rounded-3xl p-6 md:p-12 shadow-sm relative overflow-hidden backdrop-blur-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-terracotta-500/5 via-transparent to-olive-500/5 pointer-events-none"></div>
        
        <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between relative z-10">
            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-terracotta-500/10 text-terracotta-600 dark:text-terracotta-400 text-xs font-semibold border border-terracotta-500/20 mb-4">
                    <i class="fas fa-building text-[10px]"></i> Corporate Overview
                </span>
                <h1 class="text-3xl sm:text-4xl font-bold text-stone-900 dark:text-white tracking-tight leading-tight">
                    Building a Trusted Local Directory Ecosystem for <span class="text-terracotta-700 dark:text-terracotta-400">AMRAJ</span>
                </h1>
                <p class="mt-4 text-sm sm:text-base text-stone-600 dark:text-stone-300 max-w-2xl leading-relaxed">
                    AMRAJ empowers local discovery by connecting users with verified merchant listings, trusted reviews, and a smooth utility directory experience designed for speed and reliability.
                </p>
            </div>
            
            <!-- Features Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full lg:max-w-xl">
                <div class="rounded-2xl bg-white dark:bg-stone-900/80 border border-stone-200/60 dark:border-stone-800 p-5 shadow-sm transition-transform hover:-translate-y-1 duration-300">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-terracotta-500/10 text-terracotta-600 dark:text-terracotta-400 text-sm shadow-inner"><i class="fas fa-shield-alt"></i></span>
                    <p class="mt-3 text-xs font-bold text-stone-800 dark:text-stone-200">Secure</p>
                    <p class="mt-1.5 text-xs text-stone-500 dark:text-stone-400 leading-normal">Protected user sessions and safe form submission pipelines.</p>
                </div>
                <div class="rounded-2xl bg-white dark:bg-stone-900/80 border border-stone-200/60 dark:border-stone-800 p-5 shadow-sm transition-transform hover:-translate-y-1 duration-300">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-olive-500/10 text-olive-600 dark:text-olive-400 text-sm shadow-inner"><i class="fas fa-rocket"></i></span>
                    <p class="mt-3 text-xs font-bold text-stone-800 dark:text-stone-200">Fast</p>
                    <p class="mt-1.5 text-xs text-stone-500 dark:text-stone-400 leading-normal">Optimized search indexes for instant local business discovery.</p>
                </div>
                <div class="rounded-2xl bg-white dark:bg-stone-900/80 border border-stone-200/60 dark:border-stone-800 p-5 shadow-sm transition-transform hover:-translate-y-1 duration-300">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-sm shadow-inner"><i class="fas fa-user-check"></i></span>
                    <p class="mt-3 text-xs font-bold text-stone-800 dark:text-stone-200">Trusted</p>
                    <p class="mt-1.5 text-xs text-stone-500 dark:text-stone-400 leading-normal">Verified merchant profiles with genuine consumer reviews.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Platform Performance Counters Stats Section -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <div class="p-5 bg-stone-50 dark:bg-stone-900/20 border border-stone-200/60 dark:border-stone-800/60 rounded-2xl flex items-center gap-4">
            <div class="p-3.5 rounded-xl bg-terracotta-500/10 text-terracotta-600 dark:text-terracotta-400 text-base"><i class="fas fa-server"></i></div>
            <div>
                <p class="text-xl font-bold text-stone-900 dark:text-white">99.9%</p>
                <p class="text-[10px] font-bold text-stone-400 uppercase">Uptime Reliability</p>
            </div>
        </div>
        <div class="p-5 bg-stone-50 dark:bg-stone-900/20 border border-stone-200/60 dark:border-stone-800/60 rounded-2xl flex items-center gap-4">
            <div class="p-3.5 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-base"><i class="fas fa-bolt"></i></div>
            <div>
                <p class="text-xl font-bold text-stone-900 dark:text-white">&lt; 100ms</p>
                <p class="text-[10px] font-bold text-stone-400 uppercase">Avg Response Time</p>
            </div>
        </div>
        <div class="p-5 bg-stone-50 dark:bg-stone-900/20 border border-stone-200/60 dark:border-stone-800/60 rounded-2xl flex items-center gap-4">
            <div class="p-3.5 rounded-xl bg-olive-500/10 text-olive-600 dark:text-olive-400 text-base"><i class="fas fa-store-alt"></i></div>
            <div>
                <p class="text-xl font-bold text-stone-900 dark:text-white">250K+</p>
                <p class="text-[10px] font-bold text-stone-400 uppercase">Verified Merchants</p>
            </div>
        </div>
        <div class="p-5 bg-stone-50 dark:bg-stone-900/20 border border-stone-200/60 dark:border-stone-800/60 rounded-2xl flex items-center gap-4">
            <div class="p-3.5 rounded-xl bg-terracotta-500/10 text-terracotta-600 dark:text-terracotta-400 text-base"><i class="fas fa-users"></i></div>
            <div>
                <p class="text-xl font-bold text-stone-900 dark:text-white">4.2M+</p>
                <p class="text-[10px] font-bold text-stone-400 uppercase">Monthly Searches</p>
            </div>
        </div>
    </section>

    <!-- Core Layout Split Grid -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8 items-start">
        
        <!-- Large Left Content Block -->
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-3xl bg-white dark:bg-stone-900/40 border border-stone-200/80 dark:border-stone-800/60 p-6 md:p-8 shadow-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-terracotta-600 dark:text-terracotta-400 mb-2">Our Mission</h3>
                <h2 class="text-lg md:text-xl font-bold text-stone-900 dark:text-white mb-3 leading-snug">
                    Simplifying how people discover local businesses and services every day.
                </h2>
                <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300 leading-relaxed">
                    AMRAJ neatly organizes local vendor profiles, business categories, and location data into a fast, user-friendly interface. We believe finding quality local services should be seamless, transparent, and accessible to everyone.
                </p>
            </div>

            <!-- Platform Specifications Dynamic Module -->
            <div class="rounded-3xl bg-white dark:bg-stone-900/40 border border-stone-200/80 dark:border-stone-800/60 p-6 md:p-8 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-stone-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-layer-group text-terracotta-500 text-xs"></i> Platform Standards
                    </h3>
                    <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-md">Live Active</span>
                </div>
                
                <div class="overflow-x-auto rounded-xl border border-stone-200 dark:border-stone-800">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-stone-50 dark:bg-stone-950 text-[10px] uppercase text-stone-400 font-bold border-b border-stone-200 dark:border-stone-800">
                                <th class="p-3">Feature Area</th>
                                <th class="p-3">Implementation Details</th>
                                <th class="p-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-stone-800 text-stone-600 dark:text-stone-300 font-medium">
                            <tr>
                                <td class="p-3 font-semibold text-stone-900 dark:text-white">Search Performance</td>
                                <td class="p-3 text-stone-500">Indexed database queries for instant results</td>
                                <td class="p-3 text-emerald-600 dark:text-emerald-400 font-semibold text-right">Optimized</td>
                            </tr>
                            <tr>
                                <td class="p-3 font-semibold text-stone-900 dark:text-white">Data Protection</td>
                                <td class="p-3 text-stone-500">Secure input sanitization & CSRF validation</td>
                                <td class="p-3 text-emerald-600 dark:text-emerald-400 font-semibold text-right">Protected</td>
                            </tr>
                            <tr>
                                <td class="p-3 font-semibold text-stone-900 dark:text-white">Responsive Layout</td>
                                <td class="p-3 text-stone-500">Tailwind CSS mobile-first grid system</td>
                                <td class="p-3 text-emerald-600 dark:text-emerald-400 font-semibold text-right">Seamless</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sticky Right Sidebar Asset -->
        <aside class="rounded-3xl bg-stone-50 dark:bg-stone-950/30 border border-stone-200/80 dark:border-stone-800/60 p-6 shadow-sm space-y-6">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-stone-400 dark:text-stone-500 mb-3">Why Choose AMRAJ?</h3>
                <div class="space-y-3 text-xs text-stone-600 dark:text-stone-300 leading-relaxed">
                    <p class="font-semibold text-stone-800 dark:text-stone-200">Verified Local Directory</p>
                    <p>Easily navigate clean categories and find reliable businesses in your area.</p>
                    <hr class="border-stone-200 dark:border-stone-800"/>
                    <p class="font-semibold text-stone-800 dark:text-stone-200">Cross-Device Ready</p>
                    <p>Enjoy a consistent experience whether you are browsing on mobile, tablet, or desktop.</p>
                </div>
            </div>
            
            <!-- System Health Panel -->
            <div class="bg-white dark:bg-stone-900 p-4 rounded-2xl border border-stone-200/60 dark:border-stone-800 space-y-2.5">
                <p class="text-[10px] uppercase font-bold text-stone-400">System Status</p>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-stone-500">API Gateway</span>
                        <span class="flex items-center gap-1.5 text-emerald-500 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Online</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-stone-500">Database Engine</span>
                        <span class="flex items-center gap-1.5 text-emerald-500 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Synced</span>
                    </div>
                </div>
            </div>
        </aside>
    </section>

    <!-- Triple Highlights Section -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-6 bg-white dark:bg-stone-900/40 border border-stone-200/80 dark:border-stone-800/60 rounded-2xl space-y-3 shadow-sm hover:border-terracotta-500/40 transition-colors">
            <div class="w-10 h-10 rounded-xl bg-terracotta-500/10 text-terracotta-600 dark:text-terracotta-400 flex items-center justify-center text-sm shadow-inner"><i class="fas fa-bullseye"></i></div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200">Our Vision</h3>
            <p class="text-xs text-stone-500 dark:text-stone-400 leading-relaxed">
                To build the most reliable and user-friendly regional directory network for both businesses and consumers.
            </p>
        </div>
        
        <div class="p-6 bg-white dark:bg-stone-900/40 border border-stone-200/80 dark:border-stone-800/60 rounded-2xl space-y-3 shadow-sm hover:border-olive-500/40 transition-colors">
            <div class="w-10 h-10 rounded-xl bg-olive-500/10 text-olive-600 dark:text-olive-400 flex items-center justify-center text-sm shadow-inner"><i class="fas fa-fingerprint"></i></div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200">Data Integrity</h3>
            <p class="text-xs text-stone-500 dark:text-stone-400 leading-relaxed">
                We review listings carefully to ensure contact info and business details remain accurate and up-to-date.
            </p>
        </div>

        <div class="p-6 bg-white dark:bg-stone-900/40 border border-stone-200/80 dark:border-stone-800/60 rounded-2xl space-y-3 shadow-sm hover:border-emerald-500/40 transition-colors">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm shadow-inner"><i class="fas fa-users-viewfinder"></i></div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200">User First</h3>
            <p class="text-xs text-stone-500 dark:text-stone-400 leading-relaxed">
                Real customer feedback and open rating metrics to help you make informed decisions.
            </p>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="bg-stone-50 dark:bg-stone-950/20 border border-stone-200/60 dark:border-stone-800/60 rounded-3xl p-6 md:p-10 space-y-6">
        <div class="max-w-2xl">
            <h3 class="text-xs font-bold uppercase tracking-wider text-terracotta-600 dark:text-terracotta-400 mb-1">Help & Information</h3>
            <h3 class="text-lg md:text-xl font-bold text-stone-900 dark:text-white">Frequently Asked Questions</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs sm:text-sm">
            <div class="bg-white dark:bg-stone-900/60 border border-stone-200/60 dark:border-stone-800 p-5 rounded-2xl space-y-2">
                <h4 class="font-bold text-stone-900 dark:text-white flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-terracotta-500"></span> How are listings verified?
                </h4>
                <p class="text-stone-500 dark:text-stone-400 leading-relaxed pl-3.5">
                    Listings go through verification checks to confirm valid contact numbers, categories, and operational status.
                </p>
            </div>
            
            <div class="bg-white dark:bg-stone-900/60 border border-stone-200/60 dark:border-stone-800 p-5 rounded-2xl space-y-2">
                <h4 class="font-bold text-stone-900 dark:text-white flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-terracotta-500"></span> Is the platform mobile friendly?
                </h4>
                <p class="text-stone-500 dark:text-stone-400 leading-relaxed pl-3.5">
                    Yes, our entire layout uses responsive Tailwind classes so it adapts smoothly to phones, tablets, and desktops.
                </p>
            </div>

            <div class="bg-white dark:bg-stone-900/60 border border-stone-200/60 dark:border-stone-800 p-5 rounded-2xl space-y-2">
                <h4 class="font-bold text-stone-900 dark:text-white flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-olive-500"></span> How is data secured?
                </h4>
                <p class="text-stone-500 dark:text-stone-400 leading-relaxed pl-3.5">
                    We use standard secure form validation, data sanitization, and CSRF protection to safeguard user interactions.
                </p>
            </div>

            <div class="bg-white dark:bg-stone-900/60 border border-stone-200/60 dark:border-stone-800 p-5 rounded-2xl space-y-2">
                <h4 class="font-bold text-stone-900 dark:text-white flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Can reviews be manipulated?
                </h4>
                <p class="text-stone-500 dark:text-stone-400 leading-relaxed pl-3.5">
                    No, review ratings are calculated naturally to maintain fairness and transparency for all merchants.
                </p>
            </div>
        </div>
    </section>

</main>

<?php amraj_render_public_footer(); ?>