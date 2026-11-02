<?php

declare(strict_types=1);

$slides = [
    [
        'eyebrow'     => 'K & S DOMUS LEGALIS',
        'title'       => 'Law. Perspective. Purpose.',
        'description' => 'Exploring legal questions with clarity, context and a commitment to thoughtful analysis.',
        'image'       => 'assets/images/hero/law-slide-1.png',
        'button'      => 'Explore our approach',
        'link'        => url('index.php#why-choose-us'),
    ],
    [
        'eyebrow'     => 'OUR PRACTICE AREAS',
        'title'       => 'Navigating complex legal matters.',
        'description' => 'Explore the legal disciplines and subjects at the heart of our work.',
        'image'       => 'assets/images/hero/law-slide-2.png',
        'button'      => 'Explore practice areas',
        'link'        => url('index.php#practice-areas'),
    ],
    [
        'eyebrow'     => 'THE LEGAL JOURNAL',
        'title'       => 'Informed by law. Driven by insight.',
        'description' => 'Discover legal developments, judgments and perspectives published by our editorial team.',
        'image'       => 'assets/images/hero/law-slide-3.png',
        'button'      => 'Read latest insights',
        'link'        => url('index.php#latest-news'),
    ],
];

?>

<!-- FULL-SCREEN HERO CAROUSEL -->
<section
    class="home-carousel relative isolate h-[100svh] min-h-[650px] w-full overflow-hidden bg-[#24104f]"
    aria-label="Featured highlights"
    aria-roledescription="carousel">

    <!-- CAROUSEL TRACK -->
    <div
        class="home-carousel-track relative h-full min-h-[650px]"
        tabindex="0"
        aria-label="Use arrow keys to navigate slides">

        <?php foreach ($slides as $index => $slide): ?>

            <!-- SLIDE -->
            <div
                class="home-slide <?= $index === 0 ? 'is-active' : '' ?> relative isolate h-full min-h-[650px] overflow-hidden"
                role="group"
                aria-label="Slide <?= $index + 1 ?> of <?= count($slides) ?>"
                aria-roledescription="slide"
                aria-hidden="<?= $index === 0 ? 'false' : 'true' ?>"
                <?= $index === 0 ? '' : 'inert' ?>
                style="background-image:
                    linear-gradient(
                        90deg,
                        rgba(25, 10, 49, 0.96) 0%,
                        rgba(36, 16, 79, 0.88) 35%,
                        rgba(36, 16, 79, 0.48) 68%,
                        rgba(20, 10, 35, 0.20) 100%
                    ),
                    url('<?= e(url($slide['image'])) ?>');
                    background-position: center;
                    background-size: cover;">

                <!-- IMAGE OVERLAY -->
                <div class="pointer-events-none absolute inset-0 z-0 bg-gradient-to-t from-[#180b30]/60 via-transparent to-[#24104f]/10"></div>

                <!-- DECORATIVE CIRCLES -->
                <div class="pointer-events-none absolute -right-24 -top-32 z-0 h-96 w-96 rounded-full border border-white/[0.08]"></div>
                <div class="pointer-events-none absolute -right-10 -top-16 z-0 h-64 w-64 rounded-full border border-white/[0.07]"></div>

                <!-- SLIDE CONTENT -->
                <div class="site-container home-slide-content relative z-10 flex min-h-[100svh] flex-col items-start justify-center pb-36 pt-24 sm:pb-40 sm:pt-28">

                    <!-- EYEBROW -->
                    <span class="home-slide-eyebrow inline-flex items-center gap-3 text-[10px] font-semibold tracking-[0.25em] text-[#d8b9f5] sm:text-xs">

                        <span class="h-px w-9 bg-[#C391EE]"></span>

                        <?= e($slide['eyebrow']) ?>

                    </span>


                    <!-- HEADING -->
                    <h1
                        style="font-family: 'Poppins', sans-serif !important;"
                        class="mt-8 max-w-4xl text-4xl font-semibold leading-[1.12] tracking-[-0.045em] text-white sm:text-5xl md:text-6xl lg:text-7xl">

                        <?= e($slide['title']) ?>

                    </h1>


                    <!-- DESCRIPTION -->
                    <p class="mt-7 max-w-xl text-sm leading-8 text-white/70 sm:text-base md:leading-9">

                        <?= e($slide['description']) ?>

                    </p>


                    <!-- CTA BUTTON -->
                    <a
                        href="<?= e($slide['link']) ?>"
                        class="home-slide-button group mt-10 inline-flex min-h-14 items-center justify-center gap-5 rounded-full border border-[#C391EE]/60 bg-[#C391EE] px-8 py-4 text-xs font-semibold tracking-wide text-[#24104f] shadow-[0_10px_35px_rgba(195,145,238,0.20)] transition-all duration-300 hover:-translate-y-1 hover:border-white hover:bg-white hover:shadow-[0_15px_40px_rgba(0,0,0,0.20)]">

                        <?= e($slide['button']) ?>



                    </a>


                    <!-- EDITORIAL SIGNATURE -->
                    <div class="mt-14 hidden items-center gap-4 border-t border-white/[0.18] pt-5 sm:flex">

                        <span class="font-semibold tracking-[0.18em] text-[#d8b9f5]">
                            0<?= $index + 1 ?>
                        </span>

                        <span class="h-px w-12 bg-white/30"></span>

                        <span class="text-[9px] font-medium tracking-[0.2em] text-white/45">
                            K &amp; S DOMUS LEGALIS
                        </span>

                    </div>

                </div>


                <!-- DECORATIVE LEGAL SYMBOL -->
                <span
                    class="pointer-events-none absolute bottom-8 right-10 z-0 hidden select-none font-serif text-[180px] leading-none text-white/[0.045] lg:block"
                    aria-hidden="true">§</span>

            </div>

        <?php endforeach; ?>

    </div>


    <!-- CAROUSEL CONTROLS -->
    <div class="home-carousel-controls absolute bottom-7 left-0 right-0 z-20 flex items-center justify-center gap-5 sm:bottom-10">

        <!-- PREVIOUS BUTTON -->
        <button
            class="home-carousel-btn flex h-11 w-11 items-center justify-center rounded-full border border-white/25 bg-white/[0.06] text-lg text-white backdrop-blur-md transition-all duration-300 hover:border-[#C391EE] hover:bg-[#C391EE] hover:text-[#24104f]"
            id="carousel-prev"
            type="button"
            aria-label="Previous slide">
            ←
        </button>


        <!-- SLIDE INDICATORS -->
        <div
            class="home-carousel-dots flex items-center justify-center gap-3 rounded-full border border-white/[0.12] bg-[#24104f]/50 px-5 py-3 backdrop-blur-md"
            role="tablist"
            aria-label="Slide navigation">

            <?php foreach ($slides as $index => $slide): ?>

                <button
                    class="home-dot <?= $index === 0 ? 'is-active' : '' ?> h-2 rounded-full transition-all duration-300 <?= $index === 0 ? 'w-8 bg-[#C391EE]' : 'w-2 bg-white/35 hover:bg-white/70' ?>"
                    type="button"
                    role="tab"
                    aria-selected="<?= $index === 0 ? 'true' : 'false' ?>"
                    aria-label="Go to slide <?= $index + 1 ?>">
                </button>

            <?php endforeach; ?>

        </div>


        <!-- NEXT BUTTON -->
        <button
            class="home-carousel-btn flex h-11 w-11 items-center justify-center rounded-full border border-white/25 bg-white/[0.06] text-lg text-white backdrop-blur-md transition-all duration-300 hover:border-[#C391EE] hover:bg-[#C391EE] hover:text-[#24104f]"
            id="carousel-next"
            type="button"
            aria-label="Next slide">
            →
        </button>

    </div>

</section>
