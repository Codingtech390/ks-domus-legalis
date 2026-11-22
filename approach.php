<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/article_helpers.php';

$pageTitle = 'Our Approach';
$pageDescription =
    'Learn about the editorial principles and legal approach of K & S Domus Legalis, grounded in clarity, context, diligence and purposeful advice.';
$isAdminPage = false;
$activeCategory = '';
$searchQuery = '';
$categories = [];

require __DIR__ . '/includes/header.php';
?>

<!-- OUR APPROACH HERO -->
<section class="relative overflow-hidden bg-[#24104f] text-white">
    <div class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full border border-white/[0.08]"></div>
    <div class="pointer-events-none absolute -right-12 -top-12 h-56 w-56 rounded-full border border-[#C391EE]/20"></div>
    <div class="pointer-events-none absolute bottom-0 left-0 h-1 w-40 bg-[#C391EE]"></div>

    <div class="site-container relative py-20 sm:py-28 lg:py-32">
        <div class="max-w-4xl">
            <p class="mb-5 text-xs font-semibold uppercase tracking-[0.28em] text-[#D8B9F5]">K &amp; S Domus Legalis · Our Approach</p>
            <h1 class="font-serif text-4xl leading-tight sm:text-5xl lg:text-6xl">
                Law in context.<br>
                <span class="italic text-[#D8B9F5]">Ideas made clear.</span>
            </h1>
            <p class="mt-7 max-w-2xl text-base leading-8 text-white/75 sm:text-lg">
                Our work and our editorial perspective share a common principle: clarity begins with context. We approach legal questions with careful study, responsible analysis, and an appreciation of the practical realities behind every matter.
            </p>
        </div>
    </div>
</section>

<!-- OUR APPROACH / EDITORIAL PRINCIPLES -->
<section class="ks-section relative overflow-hidden bg-[#f8f6fb] py-20 md:py-28 lg:py-32" id="our-approach">
    <div class="site-container">
        <!-- CENTERED SECTION HEADING -->
        <div class="mx-auto mb-14 max-w-3xl text-center md:mb-16">
            <span class="ks-kicker inline-flex items-center justify-center gap-3">
                <span class="h-px w-8 bg-[#C391EE]"></span>
                01 / OUR APPROACH
                <span class="h-px w-8 bg-[#C391EE]"></span>
            </span>

            <h2 class="mt-6 text-4xl font-semibold leading-[1.1] tracking-[-0.045em] text-[#211c29] sm:text-5xl lg:text-6xl">
                Law in context.
                <span class="block text-[#9870bd]">Ideas made clear.</span>
            </h2>

            <p class="mx-auto mt-6 max-w-2xl text-sm leading-8 text-[#77717e] md:text-base">
                Our journal presents legal developments with context, careful sourcing and accessible explanation.
            </p>
        </div>

        <!-- CONTENT -->
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-stretch lg:gap-16">
            <!-- LEFT FEATURE PANEL -->
            <div class="relative flex min-h-[400px] flex-col justify-between overflow-hidden rounded-[28px] bg-[#24104f] p-8 text-white shadow-[0_20px_60px_rgba(36,16,79,0.14)] sm:p-10 lg:p-12">
                <div class="pointer-events-none absolute -right-20 -top-20 h-72 w-72 rounded-full border border-white/[0.10]"></div>
                <div class="pointer-events-none absolute -right-8 -top-8 h-48 w-48 rounded-full border border-white/[0.10]"></div>
                <div class="pointer-events-none absolute -bottom-24 -left-20 h-64 w-64 rounded-full bg-[#C391EE]/[0.10] blur-3xl"></div>

                <div class="relative z-10">
                    <span class="text-[10px] font-semibold tracking-[0.24em] text-[#d8b9f5]">
                        OUR EDITORIAL COMMITMENT
                    </span>

                    <h3 class="mt-9 max-w-md text-3xl font-semibold leading-tight tracking-[-0.035em] sm:text-4xl">
                        Good counsel begins with the right questions.
                    </h3>

                    <p class="mt-6 max-w-md text-sm leading-8 text-white/65">
                        We look closely at the facts, test the legal position, and consider the consequences of each available path. Then we explain what we find, what remains uncertain, and what we recommend—so you can make decisions with a clearer view of the road ahead.
                    </p>
                </div>

                <div class="relative z-10 mt-12 flex items-end justify-between border-t border-white/[0.15] pt-7">
                    <div>
                        <p class="text-[10px] font-semibold tracking-[0.2em] text-white/45">
                            OUR EDITORIAL PRINCIPLE
                        </p>
                        <p class="mt-2 text-sm font-medium text-white/90">
                            Inquiry. Candour. Direction
                        </p>
                    </div>

                    <span class="font-serif text-6xl leading-none text-[#C391EE]/40" aria-hidden="true">
                        §
                    </span>
                </div>
            </div>

            <!-- RIGHT PRINCIPLES -->
            <div class="flex flex-col justify-center">
                <div class="mb-5">
                    <span class="text-[10px] font-semibold tracking-[0.22em] text-[#9870bd]">
                        WHAT GUIDES OUR WORK
                    </span>
                </div>

                <!-- ITEM 01 -->
                <div class="group flex gap-5 border-b border-[#211c29]/[0.10] py-7 sm:gap-7">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-[#C391EE]/40 bg-white text-xs font-semibold text-[#9870bd] transition-all duration-300 group-hover:border-[#C391EE] group-hover:bg-[#C391EE] group-hover:text-white">
                        01
                    </span>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-lg font-semibold tracking-tight text-[#211c29] transition-colors duration-300 group-hover:text-[#9870bd] sm:text-xl">
                            Study the case in depth
                        </h3>
                        <p class="mt-2 max-w-lg text-sm leading-7 text-[#77717e]">
                            We examine the facts, documents, evidence, and legal issues carefully—because strategy is only as strong as the understanding behind it.
                        </p>
                    </div>

                </div>

                <!-- ITEM 02 -->
                <div class="group flex gap-5 border-b border-[#211c29]/[0.10] py-7 sm:gap-7">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-[#C391EE]/40 bg-white text-xs font-semibold text-[#9870bd] transition-all duration-300 group-hover:border-[#C391EE] group-hover:bg-[#C391EE] group-hover:text-white">
                        02
                    </span>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-lg font-semibold tracking-tight text-[#211c29] transition-colors duration-300 group-hover:text-[#9870bd] sm:text-xl">
                           Research beyond the obvious
                        </h3>
                        <p class="mt-2 max-w-lg text-sm leading-7 text-[#77717e]">
                            We investigate the relevant law and test the arguments, risks, and possible responses that may shape the case.
                        </p>
                    </div>

                </div>

                <!-- ITEM 03 -->
                <div class="group flex gap-5 border-b border-[#211c29]/[0.10] py-7 sm:gap-7">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-[#C391EE]/40 bg-white text-xs font-semibold text-[#9870bd] transition-all duration-300 group-hover:border-[#C391EE] group-hover:bg-[#C391EE] group-hover:text-white">
                        03
                    </span>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-lg font-semibold tracking-tight text-[#211c29] transition-colors duration-300 group-hover:text-[#9870bd] sm:text-xl">
                           Give you our candid assessment
                        </h3>
                        <p class="mt-2 max-w-lg text-sm leading-7 text-[#77717e]">
                            We share our professional view clearly, including strengths, weaknesses, uncertainties, and what no one can promise.
                        </p>
                    </div>

                </div>

                <!-- ITEM 04 -->
                <div class="group flex gap-5 border-b border-[#211c29]/[0.10] py-7 sm:gap-7">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-[#C391EE]/40 bg-white text-xs font-semibold text-[#9870bd] transition-all duration-300 group-hover:border-[#C391EE] group-hover:bg-[#C391EE] group-hover:text-white">
                        04
                    </span>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-lg font-semibold tracking-tight text-[#211c29] transition-colors duration-300 group-hover:text-[#9870bd] sm:text-xl">
                         Recommend the strongest path
                        </h3>
                        <p class="mt-2 max-w-lg text-sm leading-7 text-[#77717e]">
                            We weigh the available options against your goals and circumstances, then explain which course we believe is best—and why.
                        </p>
                    </div>

                </div>
                <!-- ITEM 05 -->
                <div class="group flex gap-5 border-b border-[#211c29]/[0.10] py-7 sm:gap-7">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-[#C391EE]/40 bg-white text-xs font-semibold text-[#9870bd] transition-all duration-300 group-hover:border-[#C391EE] group-hover:bg-[#C391EE] group-hover:text-white">
                        05
                    </span>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-lg font-semibold tracking-tight text-[#211c29] transition-colors duration-300 group-hover:text-[#9870bd] sm:text-xl">
                            Keep you informed at every turn
                        </h3>
                        <p class="mt-2 max-w-lg text-sm leading-7 text-[#77717e]">
                            We provide clear updates as the case develops and revisit the strategy when new facts or decisions change the picture.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<!-- OUR EXPERTISE -->
<section class="relative overflow-hidden bg-white py-20 md:py-28 lg:py-32" id="our-expertise">
    <div class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full border border-[#C391EE]/20"></div>
    <div class="pointer-events-none absolute -right-12 -top-12 h-56 w-56 rounded-full border border-[#9870bd]/10"></div>

    <div class="site-container relative">
        <div class="mx-auto mb-14 max-w-3xl text-center md:mb-16">
            <span class="ks-kicker inline-flex items-center justify-center gap-3">
                <span class="h-px w-8 bg-[#C391EE]"></span>
                02 / OUR EXPERTISE
                <span class="h-px w-8 bg-[#C391EE]"></span>
            </span>
            <h2 class="mt-6 text-4xl font-semibold leading-[1.1] tracking-[-0.045em] text-[#211c29] sm:text-5xl lg:text-6xl">
                Expertise with purpose.
                <span class="block text-[#9870bd]">Advice with clarity.</span>
            </h2>
        </div>

        <div class="grid gap-10 lg:grid-cols-[0.7fr_1.3fr] lg:gap-16">
            <div class="relative flex min-h-[320px] flex-col justify-between overflow-hidden rounded-[28px] bg-[#24104f] p-8 text-white shadow-[0_20px_60px_rgba(36,16,79,0.12)] sm:p-10 lg:p-12">
                <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full border border-white/[0.10]"></div>
                <div class="pointer-events-none absolute -right-8 -top-8 h-44 w-44 rounded-full border border-[#C391EE]/20"></div>
                <div class="pointer-events-none absolute -bottom-20 -left-16 h-56 w-56 rounded-full bg-[#C391EE]/[0.10] blur-3xl"></div>

                <div class="relative z-10">
                    <p class="text-[10px] font-semibold tracking-[0.24em] text-[#D8B9F5]">A PAN-INDIA APPROACH</p>
                    <h3 class="mt-8 font-serif text-3xl leading-tight sm:text-4xl">Thoughtful counsel, grounded in your circumstances.</h3>
                    <p class="mt-5 text-sm leading-7 text-white/65">From careful legal study to clear communication, we focus on helping you understand your position and your options.</p>
                </div>
                <div class="relative z-10 mt-10 border-t border-white/[0.15] pt-6">
                    <p class="text-xs font-medium tracking-wide text-white/80">Delhi head office · Matters across jurisdictions</p>
                </div>
            </div>

            <div class="flex flex-col justify-center gap-7 border-l border-[#211c29]/10 pl-6 sm:pl-9 lg:pl-12">
                <p class="text-base leading-8 text-[#625b6a] sm:text-lg sm:leading-9">
                    With our head office in Delhi and a pan-India approach, we assist clients with legal matters across jurisdictions. Each matter receives careful attention: we study the facts, examine the documents, research the applicable law, and assess the practical consequences of the available options.
                </p>
                <p class="text-base leading-8 text-[#625b6a] sm:text-lg sm:leading-9">
                    We believe informed decisions begin with an honest assessment. We explain the strengths and challenges in your case, the uncertainties that may affect it, and the options open to you—in clear language and without promising outcomes beyond anyone’s control. Our recommendations are shaped around your circumstances, priorities, and objectives.
                </p>
                <p class="text-base leading-8 text-[#625b6a] sm:text-lg sm:leading-9">
                    Clear communication is part of our work at every stage. We can communicate in different languages according to your needs, discuss significant developments in detail, and keep you updated on progress and next steps. We aim to be responsive around the clock for case-related communication and coordination.
                </p>
                <div class="border-l-2 border-[#C391EE] bg-[#f8f6fb] py-5 pl-5 pr-5 sm:py-6 sm:pl-7">
                    <p class="font-serif text-xl leading-8 text-[#24104f] sm:text-2xl sm:leading-9">
                        We bring detailed legal study, candid advice, and purposeful strategy together—so you can move forward with a clearer understanding of your case.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CONTACT CTA -->

<!-- ONLINE LEGAL CONSULTATION -->
<section class="bg-[#f8f6fb] py-14 sm:py-18">
    <div class="site-container">

        <div class="relative overflow-hidden rounded-3xl bg-[#24104f] p-8 text-white shadow-[0_20px_60px_rgba(36,16,79,0.12)] sm:p-10 lg:p-12">

            <!-- Decorative elements -->
            <div class="pointer-events-none absolute -right-20 -top-20 h-72 w-72 rounded-full border border-white/[0.08]"></div>
            <div class="pointer-events-none absolute -right-8 -top-8 h-48 w-48 rounded-full border border-white/[0.08]"></div>
            <div class="pointer-events-none absolute -bottom-24 -left-20 h-64 w-64 rounded-full bg-[#C391EE]/[0.10] blur-3xl"></div>

            <div class="relative z-10 flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">

                <!-- LEFT CONTENT -->
                <div class="max-w-2xl">

                    <span class="inline-flex items-center gap-2 rounded-full border border-[#C391EE]/30 bg-[#C391EE]/10 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.2em] text-[#d8b9f5]">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#C391EE]"></span>
                        Online Legal Consultation
                    </span>

                    <h2 class="mt-6 font-serif text-3xl leading-tight tracking-[-0.03em] sm:text-4xl lg:text-5xl">
                        Need legal clarity?
                        <span class="block text-[#d8b9f5]">
                            Let's discuss your matter.
                        </span>
                    </h2>

                    <p class="mt-5 max-w-xl text-sm leading-8 text-white/65 sm:text-base">
                        Speak with us online for focused legal guidance, an initial assessment of your circumstances, and a discussion of the possible next steps.
                    </p>

                    <div class="mt-7 flex flex-wrap gap-4">

                        <div class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/[0.05] px-4 py-3">

                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#C391EE]/15 text-[#d8b9f5]">
                                ₹
                            </span>

                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-[0.15em] text-white/45">
                                    Consultation fee
                                </p>

                                <p class="mt-1 text-lg font-semibold text-white">
                                    ₹2,500
                                </p>
                            </div>

                        </div>

                        <div class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/[0.05] px-4 py-3">

                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#C391EE]/15 text-[#d8b9f5]">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <circle cx="12" cy="12" r="9"></circle>
                                    <path d="M12 7v5l3 2"></path>
                                </svg>
                            </span>

                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-[0.15em] text-white/45">
                                    Duration
                                </p>

                                <p class="mt-1 text-lg font-semibold text-white">
                                    30 minutes
                                </p>
                            </div>

                        </div>

                    </div>

                </div>

                <!-- RIGHT CTA -->
                <div class="relative z-10 shrink-0">

                    <button
                        type="button"
                        id="consultation-payment-open"
                        class="group inline-flex w-full items-center justify-center gap-3 rounded-xl bg-[#C391EE] px-7 py-4 text-sm font-semibold text-[#24104f] transition-all duration-300 hover:bg-white hover:shadow-[0_10px_30px_rgba(195,145,238,0.2)] sm:w-auto">

                        Pay & Book Consultation

                        <span class="transition-transform duration-300 group-hover:translate-x-1">
                            ↗
                        </span>

                    </button>

                    <p class="mt-3 text-center text-xs text-white/45">
                        Secure your consultation after payment
                    </p>

                </div>

            </div>

        </div>

    </div>
</section>


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


<?php require __DIR__ . '/includes/footer.php'; ?>
