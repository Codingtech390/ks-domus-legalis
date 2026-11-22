<?php

declare(strict_types=1);

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

        <!-- =========================================================*

     LEGAL DISCLAIMER POPUP*

*========================================================= -->

<div

    id="legal-disclaimer-modal"

    class="legal-disclaimer-overlay"

    role="dialog"

    aria-modal="true"

    aria-labelledby="legal-disclaimer-title"

    aria-describedby="legal-disclaimer-description"

    hidden>

    <div class="legal-disclaimer-panel">

        <!-- HEADER -->

        <div class="legal-disclaimer-header">

            <div class="legal-disclaimer-brand">

                <img

                    src="<?= e($logoUrl) ?>"

                    alt="K & S Domus Legalis"

                    class="legal-disclaimer-logo"

                    width="72"

                    height="72">

                <div class="legal-disclaimer-brand-text">

                    <span>K &amp; S</span>

                    <small>DOMUS_LEGALIS</small>

                </div>

            </div>

            <span class="legal-disclaimer-eyebrow">

                Website Access Notice

            </span>

            <h2 id="legal-disclaimer-title" class="text-white">

                Disclaimer &amp; Acknowledgment

            </h2>

            <p id="legal-disclaimer-description">

                Please read the following notice carefully before entering our website.

            </p>

        </div>

        <!-- DISCLAIMER CONTENT -->

        <div class="legal-disclaimer-content">

            <p>

                This website is maintained by <strong>K &amp; S Domus Legalis</strong>

                for informational purposes only.

            </p>

            <p>

                In accordance with the applicable rules and regulations of the

                Bar Council of India, including Rule 36 of the Bar Council of

                India Rules, advocates are prohibited from advertising or

                soliciting work, directly or indirectly.

            </p>

            <h3>By accessing this website, you acknowledge and agree that:</h3>

            <div class="legal-disclaimer-point">

                <span class="legal-disclaimer-number">01</span>

                <div>

                    <h4>Voluntary Access</h4>

                    <p>

                        You are accessing this website voluntarily, on your own

                        initiative, and without any solicitation, invitation,

                        inducement, or advertisement by K &amp; S Domus Legalis

                        or its members.

                    </p>

                </div>

            </div>

            <div class="legal-disclaimer-point">

                <span class="legal-disclaimer-number">02</span>

                <div>

                    <h4>Informational Purposes Only</h4>

                    <p>

                        The information contained on this website, including

                        articles, legal updates, case summaries, commentary,

                        and other material, is provided solely for general

                        informational purposes. It is not intended to

                        constitute legal advice.

                    </p>

                </div>

            </div>

            <div class="legal-disclaimer-point">

                <span class="legal-disclaimer-number">03</span>

                <div>

                    <h4>No Advocate-Client Relationship</h4>

                    <p>

                        Accessing this website, reading its content, or

                        communicating through its contact forms does not,

                        by itself, establish an advocate-client relationship.

                    </p>

                </div>

            </div>

            <div class="legal-disclaimer-point">

                <span class="legal-disclaimer-number">04</span>

                <div>

                    <h4>No Guarantee or Reliance</h4>

                    <p>

                        While reasonable efforts may be made to ensure the

                        accuracy and currency of information, no representation

                        or warranty is made regarding its completeness,

                        accuracy, or applicability to any particular matter.

                        No action should be taken solely in reliance upon

                        information available on this website without

                        obtaining appropriate legal advice.

                    </p>

                </div>

            </div>

            <div class="legal-disclaimer-point">

                <span class="legal-disclaimer-number">05</span>

                <div>

                    <h4>Professional Conduct</h4>

                    <p>

                        K &amp; S Domus Legalis seeks to maintain this website

                        in accordance with applicable professional conduct

                        and ethical obligations governing advocates in India.

                    </p>

                </div>

            </div>

            <div class="legal-disclaimer-acknowledgment">

                By clicking <strong>“I Agree &amp; Enter”</strong>, you confirm

                that you have read, understood, and acknowledged this disclaimer.

            </div>

        </div>

        <!-- ACTIONS -->

        <div class="legal-disclaimer-actions">

            <button

                type="button"

                id="legal-disclaimer-agree"

                class="legal-disclaimer-agree">

                <span>I Agree &amp; Enter</span>

                <span aria-hidden="true">→</span>

            </button>

            <a

                href="https://www\.barcouncilofindia.org/"

                id="legal-disclaimer-exit"

                class="legal-disclaimer-exit">

                Exit Website

            </a>

        </div>

        <p class="legal-disclaimer-footer-note">

            K &amp; S Domus Legalis · Professional Responsibility

        </p>

    </div>

</div>

<!-- CONSULTATION PAYMENT MODAL -->

<div

    id="consultation-payment-modal"

    class="fixed inset-0 z-[9999] hidden items-start justify-center overflow-y-auto bg-[#120824]/80 p-3 backdrop-blur-sm sm:p-4"

    role="dialog"

    aria-modal="true"

    aria-labelledby="consultation-modal-title">

    <div

    id="consultation-payment-panel"

    class="relative my-auto w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-[0_30px_100px_rgba(0,0,0,0.25)]">

        <!-- CLOSE BUTTON -->

        <button

            type="button"

            id="consultation-payment-close"

            class="absolute right-4 top-4 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-[#f5f0fa] text-[#24104f] transition hover:bg-[#C391EE]"

            aria-label="Close payment window">

            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">

                <path d="M18 6 6 18M6 6l12 12"></path>

            </svg>

        </button>

        <!-- MODAL HEADER -->

        <div class="bg-[#24104f] px-7 pb-7 pt-8 text-center text-white">

            <span class="text-[10px] font-semibold uppercase tracking-[0.22em] text-[#d8b9f5]">

                Online Consultation

            </span>

            <h3

                id="consultation-modal-title"

                class="mt-3 font-serif text-2xl">

                Complete your payment

            </h3>

            <p class="mt-2 text-sm text-white/60">

                Scan the QR code using your UPI app

            </p>

        </div>

        <!-- PAYMENT CONTENT -->

        <div class="px-5 py-5 sm:px-7 sm:py-6">

            <!-- AMOUNT -->

            <div class="text-center">

                <p class="text-xs font-medium uppercase tracking-[0.15em] text-[#77717e]">

                    Consultation fee

                </p>

                <p class="mt-2 text-4xl font-semibold tracking-tight text-[#24104f]">

                    ₹2,500

                </p>

                <p class="mt-2 text-sm text-[#77717e]">

                    30-minute legal consultation

                </p>

            </div>

            <!-- QR CODE -->

           <div class="mx-auto mt-5 flex w-fit rounded-2xl border border-[#eee7f4] bg-white p-3 shadow-sm">

                <img

                    src="<?= e(url('assets/images/consultation-qr.png')) ?>"

                    alt="QR code for paying ₹2,500 consultation fee"

                    class="h-40 w-40 object-contain sm:h-44 sm:w-44 md:h-48 md:w-48"

                    loading="lazy">

            </div>

            <!-- INSTRUCTIONS -->

            <div class="mt-6 rounded-2xl bg-[#f8f6fb] p-4">

                <p class="text-sm font-semibold text-[#24104f]">

                    How to proceed

                </p>

                <ol class="mt-3 space-y-2 text-xs leading-6 text-[#625b6a]">

                    <li>

                        <span class="font-semibold text-[#9870bd]">01.</span>

                        Scan the QR code using your UPI payment app.

                    </li>

                    <li>

                        <span class="font-semibold text-[#9870bd]">02.</span>

                        Pay ₹2,500 to complete your consultation booking request.

                    </li>

                    <li>

                        <span class="font-semibold text-[#9870bd]">03.</span>

                        Keep your payment confirmation or transaction ID.

                    </li>

                    <li>

                        <span class="font-semibold text-[#9870bd]">04.</span>

                        Contact our office with your payment confirmation to coordinate your appointment.

                    </li>

                </ol>

            </div>

            <p class="mt-4 text-center text-[11px] leading-5 text-[#898290]">

                Payment alone does not confirm an appointment. Please contact us after completing payment to arrange your consultation time.

            </p>

        </div>

    </div>

</div>

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
