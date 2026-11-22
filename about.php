<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/article_helpers.php';

$pageTitle = 'About Us';
$pageDescription = 'Learn about K & S Domus Legalis, our approach to legal work, and our commitment to clear, purposeful legal advice.';
$isAdminPage = false;
$activeCategory = '';
$searchQuery = '';
$categories = [];

require __DIR__ . '/includes/header.php';
?>

<!-- =========================================================
     ABOUT HERO
========================================================= -->
<section class="relative overflow-hidden bg-[#24104f] text-white">

    <!-- Decorative elements -->
    <div class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full border border-white/10"></div>
    <div class="pointer-events-none absolute -right-12 -top-12 h-56 w-56 rounded-full border border-[#C391EE]/20"></div>
    <div class="pointer-events-none absolute bottom-0 left-0 h-1 w-40 bg-[#C391EE]"></div>

    <div class="site-container relative py-20 sm:py-28 lg:py-32">

        <div class="max-w-4xl reveal-on-scroll">

            <p class="mb-5 text-xs font-semibold uppercase tracking-[0.28em] text-[#D8B9F5]">
                K &amp; S Domus Legalis
            </p>

            <h1 class="font-serif text-4xl leading-tight sm:text-5xl lg:text-6xl">
                Legal insight.
                <br>
                <span class="italic text-[#D8B9F5]">
                    Purposeful counsel.
                </span>
            </h1>

            <p class="mt-7 max-w-2xl text-base leading-8 text-white/75 sm:text-lg">
                We believe good legal advice begins with listening, careful
                analysis, and a clear understanding of what matters to you.
                Our work is guided by diligence, integrity, and a practical
                approach to the law.
            </p>

        </div>

    </div>
</section>


<!-- =========================================================
     ABOUT INTRODUCTION
========================================================= -->
<section class="bg-[#faf9fc] py-16 sm:py-20 lg:py-24">

    <div class="site-container">

        <div class="grid gap-12 lg:grid-cols-[0.75fr_1.25fr] lg:items-start lg:gap-20">

            <!-- Section heading -->
            <div class="reveal-on-scroll">

                <p class="mb-4 text-xs font-bold uppercase tracking-[0.22em] text-[#9870bd]">
                    Who we are
                </p>

                <h2 class="font-serif text-3xl leading-tight text-[#24104f] sm:text-4xl">
                    A considered approach to every legal matter.
                </h2>

                <div class="mt-7 h-px w-20 bg-[#C391EE]"></div>

            </div>

            <!-- Content -->
            <div class="space-y-5 text-[15px] leading-8 text-[#625b6a] sm:text-base reveal-on-scroll">

                <p>
                    K &amp; S Domus Legalis is built around a simple belief:
                    legal guidance should be thoughtful, transparent, and
                    grounded in the realities of each client's circumstances.
                </p>

                <p>
                    We approach every matter with attention to detail and
                    a commitment to understanding the facts before advising
                    on the way forward. Our focus is not simply on legal
                    answers, but on helping clients understand their position,
                    their choices, and the considerations that shape the
                    next step.
                </p>

                <p>
                    Whether a matter calls for careful research, strategic
                    planning, negotiation, or litigation support, we aim
                    to bring clarity and purpose to the process.
                </p>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     OUR PRINCIPLES
========================================================= -->
<section class="bg-white py-14 sm:py-18 lg:py-20">

    <div class="site-container">

        <!-- Heading -->
        <div class="mb-10 max-w-2xl reveal-on-scroll">

            <p class="mb-3 text-xs font-bold uppercase tracking-[0.22em] text-[#9870bd]">
                Our principles
            </p>

            <h2 class="font-serif text-3xl text-[#24104f] sm:text-4xl">
                The values behind our work.
            </h2>

            <p class="mt-5 max-w-xl text-sm leading-7 text-[#77717e] sm:text-base">
                Our approach is shaped by the principles that guide
                how we understand, assess, and work through legal matters.
            </p>

        </div>


        <!-- Principle cards -->
        <div class="grid gap-4 md:grid-cols-3">

            <!-- Clarity -->
            <article class="group rounded-2xl border border-[#eee7f4] bg-[#faf9fc] p-7 transition-all duration-300 hover:-translate-y-1 hover:border-[#C391EE]/50 hover:shadow-xl hover:shadow-[#24104f]/5 sm:p-8 reveal-on-scroll">

                <span class="mb-6 flex h-11 w-11 items-center justify-center rounded-xl bg-[#f0e5f8] text-lg text-[#704b91] transition-colors group-hover:bg-[#C391EE] group-hover:text-white">
                    01
                </span>

                <h3 class="font-serif text-xl text-[#24104f]">
                    Clarity
                </h3>

                <p class="mt-3 text-sm leading-7 text-[#625b6a]">
                    Making complex legal issues understandable, so decisions
                    can be made with confidence and context.
                </p>

            </article>


            <!-- Diligence -->
            <article class="group rounded-2xl border border-[#eee7f4] bg-[#faf9fc] p-7 transition-all duration-300 hover:-translate-y-1 hover:border-[#C391EE]/50 hover:shadow-xl hover:shadow-[#24104f]/5 sm:p-8 reveal-on-scroll">

                <span class="mb-6 flex h-11 w-11 items-center justify-center rounded-xl bg-[#f0e5f8] text-lg text-[#704b91] transition-colors group-hover:bg-[#C391EE] group-hover:text-white">
                    02
                </span>

                <h3 class="font-serif text-xl text-[#24104f]">
                    Diligence
                </h3>

                <p class="mt-3 text-sm leading-7 text-[#625b6a]">
                    Giving every matter the attention it deserves through
                    careful study, preparation, and thoughtful analysis.
                </p>

            </article>


            <!-- Integrity -->
            <article class="group rounded-2xl border border-[#eee7f4] bg-[#faf9fc] p-7 transition-all duration-300 hover:-translate-y-1 hover:border-[#C391EE]/50 hover:shadow-xl hover:shadow-[#24104f]/5 sm:p-8 reveal-on-scroll">

                <span class="mb-6 flex h-11 w-11 items-center justify-center rounded-xl bg-[#f0e5f8] text-lg text-[#704b91] transition-colors group-hover:bg-[#C391EE] group-hover:text-white">
                    03
                </span>

                <h3 class="font-serif text-xl text-[#24104f]">
                    Integrity
                </h3>

                <p class="mt-3 text-sm leading-7 text-[#625b6a]">
                    Offering candid advice, realistic assessments, and
                    recommendations shaped around each client's interests.
                </p>

            </article>

        </div>

    </div>

</section>


<!-- =========================================================
     CONSULTATION CTA
========================================================= -->
<section class="bg-white py-16 sm:py-20">

    <div class="site-container">

        <div class="relative overflow-hidden rounded-3xl bg-[#24104f] p-8 text-white shadow-[0_20px_60px_rgba(36,16,79,0.12)] sm:p-10 lg:p-12">

            <!-- Decorative elements -->
            <div class="pointer-events-none absolute -right-20 -top-20 h-72 w-72 rounded-full border border-white/[0.08]"></div>

            <div class="pointer-events-none absolute -right-8 -top-8 h-48 w-48 rounded-full border border-white/[0.08]"></div>

            <div class="pointer-events-none absolute -bottom-24 -left-20 h-64 w-64 rounded-full bg-[#C391EE]/[0.10] blur-3xl"></div>


            <div class="relative z-10 flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">


                <!-- Left content -->
                <div class="max-w-2xl">

                    <span class="inline-flex items-center gap-2 rounded-full border border-[#C391EE]/30 bg-[#C391EE]/10 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.2em] text-[#D8B9F5]">

                        <span class="h-1.5 w-1.5 rounded-full bg-[#C391EE]"></span>

                        Legal Consultation

                    </span>


                    <h2 class="mt-6 font-serif text-3xl leading-tight tracking-[-0.03em] sm:text-4xl lg:text-5xl">
                        Start with a conversation.
                    </h2>


                    <p class="mt-5 max-w-xl text-sm leading-7 text-white/65 sm:text-base">
                        Every matter begins somewhere. Take the first step
                        towards understanding your legal position and
                        exploring the options available to you.
                    </p>

                </div>


                <!-- Right CTA -->
                <div class="relative z-10 shrink-0">

                    <button
                        type="button"
                        data-consultation-open
                        class="group inline-flex w-full items-center justify-center gap-3 rounded-xl bg-[#C391EE] px-7 py-4 text-sm font-semibold text-[#24104f] transition-all duration-300 hover:bg-white hover:shadow-[0_10px_30px_rgba(195,145,238,0.2)] sm:w-auto"
                    >

                        Book a Consultation

                        <span class="transition-transform duration-300 group-hover:translate-x-1">
                            ↗
                        </span>

                    </button>


                    <p class="mt-3 text-center text-xs text-white/45">
                        Begin with a conversation
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>


<?php require __DIR__ . '/includes/footer.php'; ?>
