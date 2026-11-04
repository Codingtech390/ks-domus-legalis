<?php

declare(strict_types=1);

/**
 * --------------------------------------------------------------------------
 * K & S Domus Legalis — Shared Footer
 * --------------------------------------------------------------------------
 * Expected helpers: e(), url()
 * Closes the main element opened in includes/header.php.
 * --------------------------------------------------------------------------
 */

$isAdminPage = (bool) ($isAdminPage ?? false);

$currentYear = date('Y');
$homeUrl = url('index.php');
$stylesheetScriptUrl = url('assets/js/app.js');

$scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
$currentScript = basename($scriptName);
$isHomepage = $currentScript === 'index.php';

$logoUrl = url('assets/css/cropped_circle_image (5) (1).png');
?>

</main>

<?php if (!$isAdminPage): ?>

    <footer
        id="contact"
        class="relative isolate overflow-hidden bg-[#C391EE] text-[#24104f]"
        style="font-family:'Poppins',sans-serif;">

        <!-- DECORATIVE BACKGROUND -->
        <div class="pointer-events-none absolute -right-32 -top-32 h-96 w-96 rounded-full border border-[#24104f]/10"></div>
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full border border-[#24104f]/10"></div>
        <div class="pointer-events-none absolute -bottom-40 -left-32 h-96 w-96 rounded-full bg-white/25 blur-3xl"></div>
        <div class="pointer-events-none absolute left-1/2 top-1/2 h-[500px] w-[500px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-white/[0.12] blur-3xl"></div>

        <!-- TOP ACCENT -->
        <div class="absolute left-0 right-0 top-0 h-px bg-white/60"></div>

        <div class="site-container relative z-10">

            <!-- MAIN FOOTER -->
            <div class="grid grid-cols-1 gap-12 py-16 sm:grid-cols-2 sm:gap-10 md:py-20 lg:grid-cols-[1.3fr_0.8fr_1.2fr] lg:gap-16">

                <!-- BRAND -->
                <div>

                    <a
                        href="<?= e($homeUrl) ?>"
                        class="group inline-flex items-center gap-4"
                        aria-label="K and S Domus Legalis home">

                        <!-- YOUR LOGO -->
                        <span class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border border-white/70 bg-white p-1.5 shadow-[0_8px_25px_rgba(36,16,79,0.14)] transition duration-300 group-hover:-translate-y-1 group-hover:shadow-[0_12px_30px_rgba(36,16,79,0.2)]">

                            <img
                                src="<?= e($logoUrl) ?>"
                                alt="K & S Domus Legalis logo"
                                class="h-full w-full rounded-full object-contain"
                                loading="lazy">

                        </span>

                        <span class="flex flex-col">

                            <span class="text-2xl font-bold tracking-tight text-[#24104f]">
                                K &amp; S
                            </span>

                            <span class="mt-0.5 text-sm font-bold tracking-[0.12em] text-[#49306b]">
                                DOMUS_LEGALIS
                            </span>

                        </span>

                    </a>


                    <p class="mt-7 max-w-sm text-sm leading-8 text-[#24104f]/75">
                        A considered perspective on law, policy and the institutions
                        that shape our world.
                    </p>


                    <div class="mt-8 inline-flex flex-col gap-2 border-l-2 border-[#24104f]/40 pl-4">

                        <span class="text-[10px] font-bold tracking-[0.2em] text-[#24104f]">
                            SOVEREIGNTY · LIBERTY · JUSTICE
                        </span>

                        <span class="text-[10px] tracking-[0.16em] text-[#24104f]/60">
                            THOUGHTFUL LAW. CLEAR PERSPECTIVE.
                        </span>

                    </div>

                </div>


                <!-- JOURNAL NAVIGATION -->
                <div>

                    <div class="mb-7">

                        <span class="text-[10px] font-bold tracking-[0.22em] text-[#49306b]">
                            DISCOVER
                        </span>

                        <h2 class="mt-3 text-lg font-bold tracking-tight text-[#24104f]">
                            Explore the journal
                        </h2>

                        <div class="mt-4 h-0.5 w-10 bg-[#24104f]"></div>

                    </div>


                    <nav
                        class="flex flex-col gap-4 text-sm text-[#24104f]/75"
                        aria-label="Footer navigation">

                        <a
                            href="<?= e($homeUrl) ?>"
                            class="group inline-flex w-fit items-center gap-2 transition-colors duration-300 hover:text-white">
                            <span class="h-px w-0 bg-[#24104f] transition-all duration-300 group-hover:w-4"></span>
                            Latest analysis
                        </a>

                        <a
                            href="<?= e(url('index.php#practice-areas')) ?>"
                            class="group inline-flex w-fit items-center gap-2 transition-colors duration-300 hover:text-white">
                            <span class="h-px w-0 bg-[#24104f] transition-all duration-300 group-hover:w-4"></span>
                            Coverage areas
                        </a>

                        <a
                            href="<?= e(url('index.php#latest-news')) ?>"
                            class="group inline-flex w-fit items-center gap-2 transition-colors duration-300 hover:text-white">
                            <span class="h-px w-0 bg-[#24104f] transition-all duration-300 group-hover:w-4"></span>
                            Latest articles
                        </a>

                        <a
                            href="<?= e(url('index.php#why-choose-us')) ?>"
                            class="group inline-flex w-fit items-center gap-2 transition-colors duration-300 hover:text-white">
                            <span class="h-px w-0 bg-[#24104f] transition-all duration-300 group-hover:w-4"></span>
                            Our approach
                        </a>

                        <a
                            href="<?= e(url('index.php#meet-our-team')) ?>"
                            class="group inline-flex w-fit items-center gap-2 transition-colors duration-300 hover:text-white">
                            <span class="h-px w-0 bg-[#24104f] transition-all duration-300 group-hover:w-4"></span>
                            Meet our team
                        </a>

                        <a
                            href="<?= e(url('index.php#newsletter')) ?>"
                            class="group inline-flex w-fit items-center gap-2 transition-colors duration-300 hover:text-white">
                            <span class="h-px w-0 bg-[#24104f] transition-all duration-300 group-hover:w-4"></span>
                            Newsletter
                        </a>

                        <a
                            href="<?= e(url('contact.php')) ?>"
                            class="group inline-flex w-fit items-center gap-2 transition-colors duration-300 hover:text-white">
                            <span class="h-px w-0 bg-[#24104f] transition-all duration-300 group-hover:w-4"></span>
                            Contact the editorial team
                        </a>

                    </nav>

                </div>


                <!-- EDITORIAL PRINCIPLES -->
                <div>

                    <div class="mb-7">

                        <span class="text-[10px] font-bold tracking-[0.22em] text-[#49306b]">
                            OUR RESPONSIBILITY
                        </span>

                        <h2 class="mt-3 text-lg font-bold tracking-tight text-[#24104f]">
                            Editorial principles
                        </h2>

                        <div class="mt-4 h-0.5 w-10 bg-[#24104f]"></div>

                    </div>


                    <p class="max-w-md text-sm leading-8 text-[#24104f]/75">
                        Content is published for general information and educational
                        purposes only. It is not legal advice and does not create an
                        advocate–client relationship. Please seek advice from a
                        qualified legal professional regarding your particular
                        circumstances.
                    </p>


                    <div class="mt-7 flex flex-col items-start gap-4">

                        <a
                            href="<?= e(url('admin/login.php')) ?>"
                            class="group inline-flex items-center gap-2 text-xs font-semibold text-[#24104f]/75 transition-colors duration-300 hover:text-white">
                            Editorial sign in
                            <span class="transition-transform duration-300 group-hover:translate-x-1 group-hover:-translate-y-0.5" aria-hidden="true">↗</span>
                        </a>

                        <a
                            href="<?= e(url('contact.php')) ?>"
                            class="group inline-flex items-center gap-2 text-xs font-semibold text-[#24104f]/75 transition-colors duration-300 hover:text-white">
                            Send an editorial enquiry
                            <span class="transition-transform duration-300 group-hover:translate-x-1 group-hover:-translate-y-0.5" aria-hidden="true">↗</span>
                        </a>

                    </div>

                </div>

            </div>


            <!-- FOOTER BOTTOM -->
            <div class="border-t border-[#24104f]/20">

                <div class="flex flex-col items-center justify-between gap-4 py-7 text-center sm:flex-row sm:text-left">

                    <span class="text-[11px] leading-6 text-[#24104f]/65">
                        &copy; <?= e($currentYear) ?>
                        K &amp; S Domus_Legalis. All rights reserved.
                    </span>

                    <span class="inline-flex items-center gap-2 text-[10px] font-semibold tracking-[0.12em] text-[#24104f]/65">

                        <span class="h-1.5 w-1.5 rounded-full bg-[#24104f]"></span>

                        WHERE LAW MEETS STRATEGY.

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
