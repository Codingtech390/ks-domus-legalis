<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

if (!function_exists('article_card')) {
    /**
     * Render a premium editorial article card.
     */
    function article_card(array $article): void
    {
        $articleUrl = url('post.php?slug=' . rawurlencode((string) $article['slug']));

        $publishedDate = $article['published_at'] ?? $article['created_at'] ?? null;

        $imageUrl = image_url($article['featured_image'] ?? null);

        $readingMins = reading_time((string) ($article['content'] ?? ''));

        $categoryUrl = url(
            'index.php?' . http_build_query([
                'category' => $article['category_slug'] ?? ''
            ]) . '#latest-news'
        );

        $categoryName = trim((string) ($article['category_name'] ?? 'Legal Insights'));

        $authorName = trim((string) ($article['author_name'] ?? 'K & S Editorial'));

        $title = (string) ($article['title'] ?? 'Untitled article');

        $excerpt = trim((string) ($article['excerpt'] ?? ''));

        // Topic-related placeholder images.
        $placeholderImages = [
            'corporate' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1000&q=85',
            'litigation' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=1000&q=85',
            'dispute' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=1000&q=85',
            'intellectual' => 'https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=1000&q=85',
            'technology' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1000&q=85',
            'real estate' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1000&q=85',
            'employment' => 'https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=1000&q=85',
            'tax' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=1000&q=85',
            'banking' => 'https://images.unsplash.com/photo-1444653614773-995cb1ef9efa?auto=format&fit=crop&w=1000&q=85',
            'environment' => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=1000&q=85',
        ];

        $defaultImage = 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=1000&q=85';

        if ($imageUrl === '') {
            $normalizedCategory = strtolower($categoryName);

            $imageUrl = $defaultImage;

            foreach ($placeholderImages as $keyword => $placeholder) {
                if (str_contains($normalizedCategory, $keyword)) {
                    $imageUrl = $placeholder;
                    break;
                }
            }
        }

        $displayDate = format_date($publishedDate);
?>

        <article class="group relative flex h-full min-w-0 flex-col overflow-hidden rounded-[5px] border border-black/[0.08] bg-white shadow-[0_5px_25px_rgba(25,16,38,0.045)] transition-all duration-500 ease-out hover:-translate-y-2 hover:border-[#C391EE]/50 hover:shadow-[0_25px_65px_rgba(25,16,38,0.13)]">

            <!-- ARTICLE IMAGE -->
            <a
                href="<?= e($articleUrl) ?>"
                class="relative block aspect-[16/10] overflow-hidden bg-[#211c29]"
                aria-label="Read: <?= e($title) ?>">

                <img
                    src="<?= e($imageUrl) ?>"
                    alt="<?= e($title) ?>"
                    loading="lazy"
                    decoding="async"
                    onerror="this.onerror=null;this.src='<?= e($defaultImage) ?>';"
                    class="h-full w-full object-cover transition-transform duration-1000 ease-out group-hover:scale-110">

                <!-- Cinematic overlay -->
                <span class="pointer-events-none absolute inset-0 bg-gradient-to-t from-[#160e20]/70 via-[#160e20]/10 to-[#160e20]/10 transition-opacity duration-500 group-hover:opacity-90"></span>

                <!-- Category label -->
                <span class="absolute left-5 top-5 inline-flex max-w-[80%] items-center rounded-full border border-white/40 bg-[#211c29]/35 px-4 py-2 text-[9px] font-semibold uppercase tracking-[0.17em] text-white shadow-sm backdrop-blur-md">
                    <?= e($categoryName) ?>
                </span>

                <!-- Image bottom label -->
                <span class="absolute bottom-5 left-5 text-[9px] font-semibold tracking-[0.2em] text-white/80">
                    K&amp;S JOURNAL
                </span>

                <!-- Reading time -->
                <span class="absolute bottom-5 right-5 rounded-full border border-white/30 bg-black/25 px-3 py-1.5 text-[10px] font-medium text-white backdrop-blur-md">
                    <?= e((string) $readingMins) ?> MIN READ
                </span>

            </a>


            <!-- ARTICLE CONTENT -->
            <div class="flex flex-1 flex-col px-6 pb-6 pt-7 md:px-7 md:pb-7">

                <!-- Category link -->
                <a
                    href="<?= e($categoryUrl) ?>"
                    class="mb-4 w-fit text-[10px] font-semibold uppercase tracking-[0.16em] text-[#9870bd] transition-colors duration-300 hover:text-[#E83D59]">
                    <?= e($categoryName) ?>
                    <span class="ml-1 text-[#C391EE]">↗</span>
                </a>


                <!-- Headline -->
                <h3 class="text-xl font-semibold leading-[1.35] tracking-[-0.035em] text-[#211c29] transition-colors duration-300 group-hover:text-[#9870bd] md:text-[22px]">

                    <a
                        href="<?= e($articleUrl) ?>"
                        class="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C391EE]">
                        <?= e($title) ?>
                    </a>

                </h3>


                <!-- Excerpt -->
                <?php if ($excerpt !== ''): ?>

                    <p class="mt-4 line-clamp-3 text-[13px] leading-[1.9] text-[#77717e]">
                        <?= e($excerpt) ?>
                    </p>

                <?php endif; ?>


                <!-- ARTICLE FOOTER -->
                <div class="mt-auto pt-7">

                    <div class="mb-5 h-px w-full bg-black/[0.08]"></div>

                    <div class="flex flex-wrap items-center justify-between gap-4">

                        <!-- Author and date -->
                        <div class="min-w-0">

                            <p class="truncate text-[11px] font-semibold text-[#39323f]">
                                <?= e($authorName) ?>
                            </p>

                            <p class="mt-1.5 text-[10px] tracking-wide text-[#99919f]">
                                <?= e($displayDate) ?>
                            </p>

                        </div>


                        <!-- Read article -->
                        <a
                            href="<?= e($articleUrl) ?>"
                            aria-label="Read <?= e($title) ?>"
                            class="inline-flex shrink-0 items-center gap-2 text-[10px] font-semibold uppercase tracking-[0.12em] text-[#24104f] transition-colors duration-300 group-hover:text-[#9870bd]">
                            Read article

                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#f4eff9] text-base transition-all duration-300 group-hover:rotate-45 group-hover:bg-[#C391EE] group-hover:text-white">
                                ↗
                            </span>
                        </a>

                    </div>

                </div>

            </div>

            <!-- Bottom accent -->
            <div class="h-[3px] w-0 bg-[#C391EE] transition-all duration-700 group-hover:w-full"></div>

        </article>

<?php
    }
}
