<?php

declare(strict_types=1);

/**
 * --------------------------------------------------------------------------
 * K & S Domus Legalis — Shared Footer
 * --------------------------------------------------------------------------
 * Expected existing helpers: e(), url()
 * This file closes the main element opened in includes/header.php.
 * --------------------------------------------------------------------------
 */

$isAdminPage = (bool) ($isAdminPage ?? false);

$currentYear = date('Y');

$homeUrl = url('index.php');
$stylesheetScriptUrl = url('assets/js/app.js');

$scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
$currentScript = basename($scriptName);

$isHomepage = $currentScript === 'index.php';

?>

</main>

<?php if (!$isAdminPage): ?>

    <footer
        id="contact"
        class="relative isolate overflow-hidden bg-[#24104f] text-white"
        style="font-family: 'Poppins', sans-serif;">

        <!-- DECORATIVE BACKGROUND -->
        <div class="pointer-events-none absolute -right-32 -top-32 h-96 w-96 rounded-full border border-white/[0.06]"></div>
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full border border-white/[0.06]"></div>
        <div class="pointer-events-none absolute -bottom-40 -left-32 h-96 w-96 rounded-full bg-[#9870bd]/[0.12] blur-3xl"></div>

        <!-- TOP ACCENT -->
        <div class="absolute left-0 right-0 top-0 h-px bg-gradient-to-r from-transparent via-[#C391EE] to-transparent"></div>

        <div class="site-container relative z-10">

            <!-- MAIN FOOTER -->
            <div class="grid grid-cols-1 gap-12 py-16 sm:grid-cols-2 sm:gap-10 md:py-20 lg:grid-cols-[1.3fr_0.8fr_1.2fr] lg:gap-16">

                <!-- BRAND -->
                <div>

                    <a
                        href="<?= e($homeUrl) ?>"
                        class="group inline-flex items-center gap-4"
                        aria-label="K and S Domus Legalis home">

                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-[#C391EE]/30 bg-[#C391EE]/[0.12] text-xl font-semibold tracking-tight text-[#e3c8ff] transition-all duration-300 group-hover:border-[#C391EE] group-hover:bg-[#C391EE]/20">
                            K<span class="text-[#C391EE]">&amp;</span>S
                        </span>

                        <span class="flex flex-col">
                            <span class="text-xl font-semibold tracking-tight text-white">
                                K &amp; S
                            </span>

                            <span class="mt-0.5 text-sm font-medium tracking-wide text-[#cda5f1]">
                                Domus Legalis
                            </span>
                        </span>

                    </a>

                    <p class="mt-7 max-w-sm text-sm leading-8 text-white/55">
                        A considered perspective on law, policy and the institutions that shape our world.
                    </p>

                    <div class="mt-8 inline-flex flex-col gap-2 border-l-2 border-[#C391EE]/50 pl-4">

                        <span class="text-[10px] font-semibold tracking-[0.2em] text-[#d8b9f5]">
                            SOVEREIGNTY · LIBERTY · JUSTICE
                        </span>

                        <span class="text-[10px] tracking-[0.16em] text-white/35">
                            THOUGHTFUL LAW. CLEAR PERSPECTIVE.
                        </span>

                    </div>

                </div>


                <!-- JOURNAL NAVIGATION -->
                <div>

                    <div class="mb-7">

                        <span class="text-[10px] font-semibold tracking-[0.22em] text-[#cda5f1]">
                            DISCOVER
                        </span>

                        <h2 class="mt-3 text-lg font-semibold tracking-tight text-white">
                            Explore the journal
                        </h2>

                        <div class="mt-4 h-px w-10 bg-[#C391EE]"></div>

                    </div>

                    <nav class="flex flex-col gap-4 text-sm text-white/55" aria-label="Footer navigation">

                        <a
                            href="<?= e($homeUrl) ?>"
                            class="group inline-flex w-fit items-center gap-2 transition-colors duration-300 hover:text-[#d8b9f5]">
                            <span class="h-px w-0 bg-[#C391EE] transition-all duration-300 group-hover:w-4"></span>
                            Latest analysis
                        </a>

                        <a
                            href="<?= e(url('index.php#practice-areas')) ?>"
                            class="group inline-flex w-fit items-center gap-2 transition-colors duration-300 hover:text-[#d8b9f5]">
                            <span class="h-px w-0 bg-[#C391EE] transition-all duration-300 group-hover:w-4"></span>
                            Coverage areas
                        </a>

                        <a
                            href="<?= e(url('index.php#latest-news')) ?>"
                            class="group inline-flex w-fit items-center gap-2 transition-colors duration-300 hover:text-[#d8b9f5]">
                            <span class="h-px w-0 bg-[#C391EE] transition-all duration-300 group-hover:w-4"></span>
                            Latest articles
                        </a>

                        <a
                            href="<?= e(url('index.php#why-choose-us')) ?>"
                            class="group inline-flex w-fit items-center gap-2 transition-colors duration-300 hover:text-[#d8b9f5]">
                            <span class="h-px w-0 bg-[#C391EE] transition-all duration-300 group-hover:w-4"></span>
                            Our approach
                        </a>

                        <a
                            href="<?= e(url('index.php#newsletter')) ?>"
                            class="group inline-flex w-fit items-center gap-2 transition-colors duration-300 hover:text-[#d8b9f5]">
                            <span class="h-px w-0 bg-[#C391EE] transition-all duration-300 group-hover:w-4"></span>
                            Newsletter
                        </a>

                        <a
                            href="<?= e(url('contact.php')) ?>"
                            class="group inline-flex w-fit items-center gap-2 transition-colors duration-300 hover:text-[#d8b9f5]">
                            <span class="h-px w-0 bg-[#C391EE] transition-all duration-300 group-hover:w-4"></span>
                            Contact the editorial team
                        </a>

                    </nav>

                </div>


                <!-- EDITORIAL PRINCIPLES -->
                <div>

                    <div class="mb-7">

                        <span class="text-[10px] font-semibold tracking-[0.22em] text-[#cda5f1]">
                            OUR RESPONSIBILITY
                        </span>

                        <h2 class="mt-3 text-lg font-semibold tracking-tight text-white">
                            Editorial principles
                        </h2>

                        <div class="mt-4 h-px w-10 bg-[#C391EE]"></div>

                    </div>

                    <p class="max-w-md text-sm leading-8 text-white/50">
                        Content is published for general information and educational purposes only. It is not legal advice and does not create an advocate–client relationship. Please seek advice from a qualified legal professional regarding your particular circumstances.
                    </p>

                    <div class="mt-7 flex flex-col items-start gap-4">

                        <a
                            href="<?= e(url('admin/login.php')) ?>"
                            class="group inline-flex items-center gap-2 text-xs font-medium text-white/60 transition-colors duration-300 hover:text-[#d8b9f5]">
                            Editorial sign in
                            <span class="transition-transform duration-300 group-hover:translate-x-1 group-hover:-translate-y-0.5" aria-hidden="true">↗</span>
                        </a>

                        <a
                            href="<?= e(url('contact.php')) ?>"
                            class="group inline-flex items-center gap-2 text-xs font-medium text-white/60 transition-colors duration-300 hover:text-[#d8b9f5]">
                            Send an editorial enquiry
                            <span class="transition-transform duration-300 group-hover:translate-x-1 group-hover:-translate-y-0.5" aria-hidden="true">↗</span>
                        </a>

                    </div>

                </div>

            </div>


            <!-- FOOTER BOTTOM -->
            <div class="border-t border-white/[0.10]">

                <div class="flex flex-col items-center justify-between gap-4 py-7 text-center sm:flex-row sm:text-left">

                    <span class="text-[11px] leading-6 text-white/40">
                        &copy; <?= e($currentYear) ?>
                        K &amp; S Domus Legalis. All rights reserved.
                    </span>

                    <span class="inline-flex items-center gap-2 text-[10px] font-medium tracking-[0.12em] text-white/40">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#C391EE]"></span>
                        THOUGHTFUL LAW. CLEAR PERSPECTIVE.
                    </span>

                </div>

            </div>

        </div>

    </footer>

<?php else: ?>

    <footer class="admin-footer">

        <div class="site-container">
            <span>
                K &amp; S Domus Legalis
                &middot;
                Editorial management
            </span>

            <span>
                &copy; <?= e($currentYear) ?>
            </span>
        </div>

    </footer>

<?php endif; ?>


<!-- SHARED SITE JAVASCRIPT -->
<script
    src="<?= e($stylesheetScriptUrl) ?>"
    defer></script>

<?php if ($isHomepage && !$isAdminPage): ?>

    <!-- HOMEPAGE CAROUSEL AND INTERACTIONS -->
    <script
        src="<?= e(url('assets/js/home.js')) ?>"
        defer></script>

<?php endif; ?>

</body>

</html>
