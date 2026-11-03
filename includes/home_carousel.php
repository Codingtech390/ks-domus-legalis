<?php
declare(strict_types=1);

/**
 * K & S Domus Legalis — Editorial hero carousel
 * Include this file from the homepage after the shared header.
 */

$slides = [
    [
        'eyebrow' => 'K & S DOMUS LEGALIS',
        'title' => 'Law. Perspective. Purpose.',
        'description' => 'Exploring legal questions with clarity, context and a commitment to thoughtful analysis.',
        'image' => 'assets/images/hero/law-slide-1.png',
        'button' => 'Explore our approach',
        'link' => url('index.php#why-choose-us'),
        'number' => '01',
        'symbol' => '§',
        'tag' => 'A considered approach to law',
    ],
    [
        'eyebrow' => 'OUR PRACTICE AREAS',
        'title' => 'Navigating complex legal matters.',
        'description' => 'Explore the legal disciplines and subjects at the heart of our work.',
        'image' => 'assets/images/hero/law-slide-2.png',
        'button' => 'Explore practice areas',
        'link' => url('index.php#practice-areas'),
        'number' => '02',
        'symbol' => '¶',
        'tag' => 'Clarity across every discipline',
    ],
    [
        'eyebrow' => 'THE LEGAL JOURNAL',
        'title' => 'Informed by law. Driven by insight.',
        'description' => 'Discover legal developments, judgments and perspectives published by our editorial team.',
        'image' => 'assets/images/hero/law-slide-3.png',
        'button' => 'Read latest insights',
        'link' => url('index.php#latest-news'),
        'number' => '03',
        'symbol' => '§',
        'tag' => 'Ideas, analysis and perspective',
    ],
];

$carouselId = 'ks-editorial-carousel';
$totalSlides = count($slides);
?>

<section
    id="<?= e($carouselId) ?>"
    class="ks-hero"
    aria-label="Featured legal perspectives"
    aria-roledescription="carousel"
    data-ks-carousel
    data-interval="6500">

    <div class="ks-hero__slides">
        <?php foreach ($slides as $index => $slide): ?>
            <article
                class="ks-hero__slide<?= $index === 0 ? ' is-active' : '' ?>"
                data-ks-slide
                aria-label="Slide <?= $index + 1 ?> of <?= $totalSlides ?>"
                aria-roledescription="slide"
                aria-hidden="<?= $index === 0 ? 'false' : 'true' ?>"
                <?= $index === 0 ? '' : 'inert' ?>>

                <img
                    class="ks-hero__image"
                    src="<?= e(url($slide['image'])) ?>"
                    alt=""
                    aria-hidden="true"
                    <?= $index === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>

                <div class="ks-hero__image-shade"></div>
                <div class="ks-hero__grain"></div>

                <div class="ks-hero__inner">
                    <div class="ks-hero__copy">
                        <h1 class="ks-hero__title"><?= e($slide['title']) ?></h1>

                        <p class="ks-hero__description"><?= e($slide['description']) ?></p>

                        <div class="ks-hero__actions">
                            <a class="ks-hero__cta" href="<?= e($slide['link']) ?>">
                                <span><?= e($slide['button']) ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="ks-hero__footer">
        <div class="ks-hero__footer-meta">
            <span class="ks-hero__meta-label">Featured perspective</span>
            <span class="ks-hero__counter">
                <span data-ks-current>01</span>
                <span class="ks-hero__counter-divider">/</span>
                <span><?= str_pad((string) $totalSlides, 2, '0', STR_PAD_LEFT) ?></span>
            </span>
        </div>

        <div class="ks-hero__navigation" aria-label="Carousel controls">
            <button class="ks-hero__arrow" type="button" data-ks-prev aria-label="Previous slide">
                <span aria-hidden="true">←</span>
            </button>

            <div class="ks-hero__pagination" role="group" aria-label="Choose a slide">
                <?php foreach ($slides as $index => $slide): ?>
                    <button
                        class="ks-hero__dot<?= $index === 0 ? ' is-active' : '' ?>"
                        type="button"
                        data-ks-dot="<?= $index ?>"
                        aria-label="Show slide <?= $index + 1 ?>"
                        aria-current="<?= $index === 0 ? 'true' : 'false' ?>">
                        <span></span>
                    </button>
                <?php endforeach; ?>
            </div>

            <button class="ks-hero__arrow" type="button" data-ks-next aria-label="Next slide">
                <span aria-hidden="true">→</span>
            </button>
        </div>

        <div class="ks-hero__footer-caption">Thoughtful legal analysis for a changing world.</div>
    </div>

    <div class="ks-hero__progress-track" aria-hidden="true">
        <span data-ks-progress></span>
    </div>

    <p class="ks-hero__sr-only" data-ks-announcement aria-live="polite" aria-atomic="true">
        Slide 1 of <?= $totalSlides ?>.
    </p>
</section>

<style>
    #<?= e($carouselId) ?> {
        --ks-lavender: #c391ee;
        --ks-lavender-light: #e4cfff;
        --ks-purple: #24104f;
        --ks-ink: #160d22;
        position: relative;
        isolation: isolate;
        width: 100%;
        height: clamp(650px, calc(100svh - 120px), 850px);
        min-height: 650px;
        overflow: hidden;
        color: #fff;
        background: var(--ks-ink);
        font-family: "Poppins", sans-serif;
    }

    #<?= e($carouselId) ?> *,
    #<?= e($carouselId) ?> *::before,
    #<?= e($carouselId) ?> *::after { box-sizing: border-box; }

    #<?= e($carouselId) ?> .ks-hero__slides,
    #<?= e($carouselId) ?> .ks-hero__slide { position: absolute; inset: 0; }

    #<?= e($carouselId) ?> .ks-hero__slide {
        z-index: 1;
        visibility: hidden;
        opacity: 0;
        pointer-events: none;
        transition: opacity 1000ms cubic-bezier(.22, 1, .36, 1), visibility 1000ms;
    }

    #<?= e($carouselId) ?> .ks-hero__slide.is-active {
        z-index: 2;
        visibility: visible;
        opacity: 1;
        pointer-events: auto;
    }

    #<?= e($carouselId) ?> .ks-hero__image {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        max-width: none;
        object-fit: cover;
        object-position: center;
        transform: scale(1.07);
        transition: transform 7500ms cubic-bezier(.2, .65, .25, 1);
    }

    #<?= e($carouselId) ?> .ks-hero__slide.is-active .ks-hero__image { transform: scale(1); }

    #<?= e($carouselId) ?> .ks-hero__image-shade {
        position: absolute;
        inset: 0;
        background:
            linear-gradient(90deg, rgba(17, 8, 31, .91) 0%, rgba(36, 16, 79, .72) 43%, rgba(26, 11, 47, .22) 100%),
            linear-gradient(0deg, rgba(14, 7, 25, .78) 0%, transparent 38%, rgba(16, 8, 30, .20) 100%);
    }

    #<?= e($carouselId) ?> .ks-hero__grain {
        position: absolute;
        inset: 0;
        opacity: .12;
        pointer-events: none;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 180 180' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.22'/%3E%3C/svg%3E");
        mix-blend-mode: soft-light;
    }

    #<?= e($carouselId) ?> .ks-hero__inner {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        width: min(1320px, calc(100% - 112px));
        height: 100%;
        margin: 0 auto;
        padding: 110px 0 145px;
    }

    #<?= e($carouselId) ?> .ks-hero__copy { width: min(760px, 75%); }

    #<?= e($carouselId) ?> .ks-hero__eyebrow {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 30px;
        color: var(--ks-lavender-light);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .25em;
        text-transform: uppercase;
    }

    #<?= e($carouselId) ?> .ks-hero__eyebrow-line {
        display: inline-block;
        width: 42px;
        height: 1px;
        background: var(--ks-lavender);
    }

    #<?= e($carouselId) ?> .ks-hero__overline {
        margin: 0 0 17px;
        color: rgba(255,255,255,.57);
        font-size: 10px;
        font-weight: 500;
        letter-spacing: .22em;
        text-transform: uppercase;
    }

    #<?= e($carouselId) ?> .ks-hero__title {
        max-width: 850px;
        margin: 0;
        color: #fff;
        font-family: "Playfair Display", Georgia, serif;
        font-size: clamp(48px, 6.1vw, 88px);
        font-weight: 600;
        line-height: 1.04;
        letter-spacing: -.045em;
        text-wrap: balance;
        text-shadow: 0 4px 30px rgba(0,0,0,.18);
    }

    #<?= e($carouselId) ?> .ks-hero__description {
        max-width: 590px;
        margin: 27px 0 0;
        color: rgba(255,255,255,.78);
        font-size: 15px;
        font-weight: 400;
        line-height: 1.95;
    }

    #<?= e($carouselId) ?> .ks-hero__actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 25px;
        margin-top: 39px;
    }

    #<?= e($carouselId) ?> .ks-hero__cta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 26px;
        min-height: 56px;
        padding: 0 25px;
        border: 1px solid var(--ks-lavender);
        border-radius: 3px;
        background: var(--ks-lavender);
        color: #211039;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .035em;
        text-decoration: none;
        box-shadow: 0 14px 38px rgba(0,0,0,.18);
        transition: background .25s ease, color .25s ease, transform .25s ease, box-shadow .25s ease;
    }

    #<?= e($carouselId) ?> .ks-hero__cta:hover {
        transform: translateY(-3px);
        background: #fff;
        border-color: #fff;
        color: var(--ks-purple);
        box-shadow: 0 18px 45px rgba(0,0,0,.25);
    }

    #<?= e($carouselId) ?> .ks-hero__cta-arrow { font-size: 17px; font-weight: 400; }

    #<?= e($carouselId) ?> .ks-hero__signature {
        color: rgba(255,255,255,.53);
        font-size: 9px;
        font-weight: 600;
        letter-spacing: .19em;
        text-transform: uppercase;
    }

    #<?= e($carouselId) ?> .ks-hero__watermark {
        position: absolute;
        right: 4%;
        bottom: 9%;
        color: rgba(255,255,255,.075);
        font-family: Georgia, serif;
        font-size: clamp(180px, 25vw, 360px);
        font-weight: 400;
        line-height: .8;
        pointer-events: none;
    }

    #<?= e($carouselId) ?> .ks-hero__side-note {
        position: absolute;
        top: 50%;
        right: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        color: rgba(255,255,255,.48);
        font-size: 8px;
        letter-spacing: .2em;
        writing-mode: vertical-rl;
        transform: translateY(-50%);
    }

    #<?= e($carouselId) ?> .ks-hero__topline {
        position: absolute;
        z-index: 5;
        top: 30px;
        left: max(56px, calc((100% - 1320px) / 2));
        right: max(56px, calc((100% - 1320px) / 2));
        display: flex;
        align-items: center;
        gap: 12px;
        color: rgba(255,255,255,.56);
        font-size: 9px;
        font-weight: 600;
        letter-spacing: .19em;
        text-transform: uppercase;
        pointer-events: none;
    }

    #<?= e($carouselId) ?> .ks-hero__live-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--ks-lavender);
        box-shadow: 0 0 15px var(--ks-lavender);
    }

    #<?= e($carouselId) ?> .ks-hero__topline-rule {
        width: 54px;
        height: 1px;
        margin-left: 7px;
        background: rgba(255,255,255,.23);
    }

    #<?= e($carouselId) ?> .ks-hero__footer {
        position: absolute;
        z-index: 6;
        right: 0;
        bottom: 0;
        left: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        width: min(1320px, calc(100% - 112px));
        min-height: 105px;
        margin: 0 auto;
        border-top: 1px solid rgba(255,255,255,.19);
    }

    #<?= e($carouselId) ?> .ks-hero__footer-meta { display: flex; flex-direction: column; gap: 5px; }

    #<?= e($carouselId) ?> .ks-hero__meta-label {
        color: rgba(255,255,255,.45);
        font-size: 8px;
        font-weight: 600;
        letter-spacing: .2em;
        text-transform: uppercase;
    }

    #<?= e($carouselId) ?> .ks-hero__counter {
        display: flex;
        align-items: baseline;
        gap: 9px;
        color: #fff;
        font-family: "Playfair Display", Georgia, serif;
        font-size: 25px;
    }

    #<?= e($carouselId) ?> .ks-hero__counter-divider { color: var(--ks-lavender); font-size: 15px; }

    #<?= e($carouselId) ?> .ks-hero__navigation { display: flex; align-items: center; gap: 20px; }

    #<?= e($carouselId) ?> .ks-hero__arrow {
        display: grid;
        width: 43px;
        height: 43px;
        place-items: center;
        border: 1px solid rgba(255,255,255,.28);
        border-radius: 50%;
        background: rgba(255,255,255,.055);
        color: #fff;
        font-size: 19px;
        cursor: pointer;
        transition: background .2s ease, color .2s ease, border-color .2s ease;
    }

    #<?= e($carouselId) ?> .ks-hero__arrow:hover {
        border-color: var(--ks-lavender);
        background: var(--ks-lavender);
        color: var(--ks-purple);
    }

    #<?= e($carouselId) ?> .ks-hero__pagination { display: flex; align-items: center; gap: 9px; }

    #<?= e($carouselId) ?> .ks-hero__dot {
        display: grid;
        width: 27px;
        height: 30px;
        place-items: center;
        padding: 0;
        background: transparent;
        cursor: pointer;
    }

    #<?= e($carouselId) ?> .ks-hero__dot span {
        display: block;
        width: 6px;
        height: 6px;
        border-radius: 10px;
        background: rgba(255,255,255,.43);
        transition: width .35s ease, background .35s ease;
    }

    #<?= e($carouselId) ?> .ks-hero__dot.is-active span {
        width: 27px;
        background: var(--ks-lavender);
    }

    #<?= e($carouselId) ?> .ks-hero__footer-caption {
        color: rgba(255,255,255,.43);
        font-size: 9px;
        letter-spacing: .08em;
    }

    #<?= e($carouselId) ?> .ks-hero__progress-track {
        position: absolute;
        z-index: 7;
        right: 0;
        bottom: 0;
        left: 0;
        height: 2px;
        background: rgba(255,255,255,.12);
    }

    #<?= e($carouselId) ?> .ks-hero__progress-track span {
        display: block;
        width: 100%;
        height: 100%;
        background: var(--ks-lavender);
        transform: scaleX(0);
        transform-origin: left center;
    }

    #<?= e($carouselId) ?> .ks-hero__sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0,0,0,0);
        white-space: nowrap;
        border: 0;
    }

    #<?= e($carouselId) ?> button:focus-visible,
    #<?= e($carouselId) ?> a:focus-visible {
        outline: 2px solid var(--ks-lavender-light);
        outline-offset: 4px;
    }

    @media (max-width: 760px) {
        #<?= e($carouselId) ?> { height: min(780px, 88svh); min-height: 650px; }
        #<?= e($carouselId) ?> .ks-hero__inner { width: calc(100% - 44px); padding: 100px 0 145px; }
        #<?= e($carouselId) ?> .ks-hero__copy { width: 100%; }
        #<?= e($carouselId) ?> .ks-hero__title { font-size: clamp(43px, 10vw, 66px); }
        #<?= e($carouselId) ?> .ks-hero__description { max-width: 95%; font-size: 13px; }
        #<?= e($carouselId) ?> .ks-hero__topline { top: 24px; left: 22px; right: 22px; font-size: 8px; }
        #<?= e($carouselId) ?> .ks-hero__topline-rule,
        #<?= e($carouselId) ?> .ks-hero__topline-rule + span,
        #<?= e($carouselId) ?> .ks-hero__side-note,
        #<?= e($carouselId) ?> .ks-hero__footer-caption { display: none; }
        #<?= e($carouselId) ?> .ks-hero__footer { width: calc(100% - 44px); min-height: 94px; }
        #<?= e($carouselId) ?> .ks-hero__navigation { gap: 10px; }
        #<?= e($carouselId) ?> .ks-hero__arrow { width: 38px; height: 38px; }
        #<?= e($carouselId) ?> .ks-hero__signature { display: none; }
        #<?= e($carouselId) ?> .ks-hero__image-shade {
            background:
                linear-gradient(90deg, rgba(17,8,31,.88), rgba(36,16,79,.54)),
                linear-gradient(0deg, rgba(14,7,25,.78), transparent 55%);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #<?= e($carouselId) ?> .ks-hero__slide,
        #<?= e($carouselId) ?> .ks-hero__image,
        #<?= e($carouselId) ?> .ks-hero__dot span,
        #<?= e($carouselId) ?> .ks-hero__cta { transition-duration: .01ms !important; }
    }
</style>

<script>
(() => {
    'use strict';

    const root = document.getElementById('<?= e($carouselId) ?>');
    if (!root || root.dataset.carouselReady === 'true') return;
    root.dataset.carouselReady = 'true';

    const slides = Array.from(root.querySelectorAll('[data-ks-slide]'));
    const dots = Array.from(root.querySelectorAll('[data-ks-dot]'));
    const previous = root.querySelector('[data-ks-prev]');
    const next = root.querySelector('[data-ks-next]');
    const counter = root.querySelector('[data-ks-current]');
    const progress = root.querySelector('[data-ks-progress]');
    const announcement = root.querySelector('[data-ks-announcement]');

    if (slides.length < 2) return;

    const interval = Math.max(3000, Number(root.dataset.interval) || 6500);
    let activeIndex = 0;
    let timer = null;
    let animationFrame = null;
    let startedAt = 0;
    let remaining = interval;
    let pausedForHiddenTab = document.hidden;

    function stopClock() {
        if (timer !== null) {
            clearTimeout(timer);
            timer = null;
        }
        if (animationFrame !== null) {
            cancelAnimationFrame(animationFrame);
            animationFrame = null;
        }
    }

    function render(index) {
        activeIndex = (index + slides.length) % slides.length;

        slides.forEach((slide, i) => {
            const active = i === activeIndex;
            slide.classList.toggle('is-active', active);
            slide.setAttribute('aria-hidden', String(!active));
            if (active) slide.removeAttribute('inert');
            else slide.setAttribute('inert', '');
        });

        dots.forEach((dot, i) => {
            const active = i === activeIndex;
            dot.classList.toggle('is-active', active);
            dot.setAttribute('aria-current', active ? 'true' : 'false');
        });

        if (counter) counter.textContent = String(activeIndex + 1).padStart(2, '0');
        if (announcement) announcement.textContent = `Slide ${activeIndex + 1} of ${slides.length}.`;

        if (progress) {
            progress.style.transition = 'none';
            progress.style.transform = 'scaleX(0)';
            void progress.offsetWidth;
        }

        remaining = interval;
    }

    function animateProgress() {
        if (pausedForHiddenTab || document.hidden) return;
        const elapsed = performance.now() - startedAt;
        const fraction = Math.min(elapsed / interval, 1);
        if (progress) progress.style.transform = `scaleX(${fraction})`;
        if (fraction < 1) animationFrame = requestAnimationFrame(animateProgress);
    }

    function schedule() {
        stopClock();
        if (pausedForHiddenTab || document.hidden) return;

        startedAt = performance.now();
        if (progress) {
            progress.style.transition = `transform ${remaining}ms linear`;
            progress.style.transform = 'scaleX(1)';
        }
        animationFrame = requestAnimationFrame(animateProgress);

        timer = window.setTimeout(() => {
            render(activeIndex + 1);
            schedule();
        }, remaining);
    }

    function goTo(index) {
        render(index);
        schedule();
    }

    previous?.addEventListener('click', () => goTo(activeIndex - 1));
    next?.addEventListener('click', () => goTo(activeIndex + 1));

    dots.forEach((dot) => {
        dot.addEventListener('click', () => {
            const index = Number(dot.dataset.ksDot);
            if (Number.isInteger(index)) goTo(index);
        });
    });

    root.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowRight') {
            event.preventDefault();
            goTo(activeIndex + 1);
        } else if (event.key === 'ArrowLeft') {
            event.preventDefault();
            goTo(activeIndex - 1);
        }
    });

    document.addEventListener('visibilitychange', () => {
        pausedForHiddenTab = document.hidden;
        if (pausedForHiddenTab) {
            if (timer !== null) {
                remaining = Math.max(0, remaining - (performance.now() - startedAt));
            }
            stopClock();
        } else {
            schedule();
        }
    });

    // Start immediately. Hover and keyboard focus intentionally do NOT stop autoplay.
    render(0);
    schedule();
})();
</script>
