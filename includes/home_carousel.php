<?php

declare(strict_types=1);

$slides = [
    [
        'eyebrow' => 'K & S DOMUS LEGALIS',
        'title' => 'Law. Perspective. Purpose.',
        'description' => 'Exploring legal questions with clarity, context and a commitment to thoughtful analysis.',
        'image' => 'assets/images/hero/law-slide-1.png',
        'button' => 'Explore our approach',
        'link' => '#why-choose-us',
    ],
    [
        'eyebrow' => 'OUR PRACTICE AREAS',
        'title' => 'Navigating complex legal matters.',
        'description' => 'Explore the legal disciplines and subjects at the heart of our work.',
        'image' => 'assets/images/hero/law-slide-2.png',
        'button' => 'Explore practice areas',
        'link' => '#practice-areas',
    ],
    [
        'eyebrow' => 'THE LEGAL JOURNAL',
        'title' => 'Informed by law. Driven by insight.',
        'description' => 'Discover legal developments, judgments and perspectives published by our editorial team.',
        'image' => 'assets/images/hero/law-slide-3.png',
        'button' => 'Read latest insights',
        'link' => '#latest-news',
    ],
];
?>

<section class="home-carousel" aria-label="Featured highlights">
    <div class="home-carousel-track">
        <?php foreach ($slides as $index => $slide): ?>
            <div
                class="home-slide <?= $index === 0 ? 'is-active' : '' ?>"
                aria-hidden="<?= $index === 0 ? 'false' : 'true' ?>"
                style="background-image:linear-gradient(90deg,rgba(8,24,22,.90),rgba(8,24,22,.25)),url('<?= e(url($slide['image'])) ?>')">
                <div class="site-container home-slide-content">
                    <span class="home-slide-eyebrow">
                        <?= e($slide['eyebrow']) ?>
                    </span>

                    <h1><?= e($slide['title']) ?></h1>

                    <p><?= e($slide['description']) ?></p>

                    <a href="<?= e($slide['link']) ?>" class="home-slide-button">
                        <?= e($slide['button']) ?>
                        <span aria-hidden="true">↗</span>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="home-carousel-controls">
        <button type="button" class="home-carousel-arrow" data-carousel-prev aria-label="Previous slide">
            ←
        </button>

        <div class="home-carousel-dots" aria-label="Choose a slide">
            <?php foreach ($slides as $index => $slide): ?>
                <button
                    type="button"
                    data-carousel-dot="<?= $index ?>"
                    class="<?= $index === 0 ? 'is-active' : '' ?>"
                    aria-label="Go to slide <?= $index + 1 ?>"
                    aria-current="<?= $index === 0 ? 'true' : 'false' ?>"></button>
            <?php endforeach; ?>
        </div>

        <button type="button" class="home-carousel-arrow" data-carousel-next aria-label="Next slide">
            →
        </button>
    </div>

    <a class="home-carousel-scroll" href="#practice-areas">
        Scroll to explore <span aria-hidden="true">↓</span>
    </a>
</section>
