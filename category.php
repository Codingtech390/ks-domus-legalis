<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/article_card.php';

/*
|--------------------------------------------------------------------------
| Category configuration
|--------------------------------------------------------------------------
*/

$coverageImages = [
    'civil-law' => url('assets/images/coverage/civil-law.png'),
    'constitutional-law' => url('assets/images/coverage/constitutional-law.png'),
    'corporate-law' => url('assets/images/coverage/corporate-law.png'),
    'criminal-law' => url('assets/images/coverage/criminal-law.png'),
    'legal-analysis' => url('assets/images/coverage/legal-analysis.png'),
    'supreme-court' => url('assets/images/coverage/supreme-court.png'),
];

$defaultCoverageImage = url('assets/images/coverage/default-legal.jpg');

/*
|--------------------------------------------------------------------------
| Read and validate category slug
|--------------------------------------------------------------------------
*/

$slug = trim((string) ($_GET['slug'] ?? ''));

if (
    $slug === '' ||
    strlen($slug) > 120 ||
    !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)
) {
    http_response_code(404);
    $slug = '';
}

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$page = is_int($page) && $page > 0 ? $page : 1;

$perPage = 9;
$offset = ($page - 1) * $perPage;

/*
|--------------------------------------------------------------------------
| Find category
|--------------------------------------------------------------------------
*/

$category = null;
$articles = [];
$totalArticles = 0;
$totalPages = 1;

if ($slug !== '') {
    $categoryStatement = $pdo->prepare(
        'SELECT id, name, slug, description
         FROM categories
         WHERE slug = :slug
         LIMIT 1'
    );

    $categoryStatement->execute([
        'slug' => $slug,
    ]);

    $category = $categoryStatement->fetch(PDO::FETCH_ASSOC) ?: null;
}

if (!$category) {
    http_response_code(404);
    $pageTitle = 'Coverage Area Not Found';
    $pageDescription = 'The requested legal coverage area could not be found.';
} else {
    /*
    |--------------------------------------------------------------------------
    | Page metadata
    |--------------------------------------------------------------------------
    */

    $pageTitle = $category['name'] . ' | K & S Domus Legalis';

    $pageDescription = trim((string) ($category['description'] ?? ''));

    if ($pageDescription === '') {
        $pageDescription = 'Explore the latest legal developments, articles and analysis covering '
            . $category['name'] . '.';
    }

    /*
    |--------------------------------------------------------------------------
    | Count published articles
    |--------------------------------------------------------------------------
    */

    $countStatement = $pdo->prepare(
        "SELECT COUNT(*)
         FROM articles a
         INNER JOIN categories c ON c.id = a.category_id
         WHERE c.id = :category_id
           AND a.status = 'published'
           AND a.published_at <= NOW()"
    );

    $countStatement->execute([
        'category_id' => (int) $category['id'],
    ]);

    $totalArticles = (int) $countStatement->fetchColumn();

    $totalPages = max(1, (int) ceil($totalArticles / $perPage));

    if ($page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $perPage;
    }

    /*
    |--------------------------------------------------------------------------
    | Fetch category articles
    |--------------------------------------------------------------------------
    */

    $articleStatement = $pdo->prepare(
        "SELECT
            a.id,
            a.title,
            a.slug,
            a.excerpt,
            a.featured_image,
            a.published_at,
            a.created_at,
            a.content,
            c.name AS category_name,
            c.slug AS category_slug,
            ad.name AS author_name
         FROM articles a
         INNER JOIN categories c ON c.id = a.category_id
         INNER JOIN admins ad ON ad.id = a.admin_id
         WHERE c.id = :category_id
           AND a.status = 'published'
           AND a.published_at <= NOW()
         ORDER BY a.published_at DESC, a.id DESC
         LIMIT :limit OFFSET :offset"
    );

    $articleStatement->bindValue(
        ':category_id',
        (int) $category['id'],
        PDO::PARAM_INT
    );

    $articleStatement->bindValue(
        ':limit',
        $perPage,
        PDO::PARAM_INT
    );

    $articleStatement->bindValue(
        ':offset',
        $offset,
        PDO::PARAM_INT
    );

    $articleStatement->execute();

    $articles = $articleStatement->fetchAll(PDO::FETCH_ASSOC);
}

/*
|--------------------------------------------------------------------------
| Category banner image
|--------------------------------------------------------------------------
*/

$categoryImage = $category
    ? ($coverageImages[$category['slug']] ?? $defaultCoverageImage)
    : $defaultCoverageImage;

require_once __DIR__ . '/includes/header.php';
?>

<main class="min-h-screen bg-[#faf9fc]">

    <?php if (!$category): ?>

        <!-- NOT FOUND -->
        <section class="site-container py-24 text-center md:py-32">

            <span class="font-serif text-7xl text-[#C391EE]">§</span>

            <p class="mt-6 text-[10px] font-semibold tracking-[0.25em] text-[#9870bd]">
                COVERAGE AREA
            </p>

            <h1 class="mt-4 text-4xl font-semibold tracking-tight text-[#211c29]">
                Page not found.
            </h1>

            <p class="mx-auto mt-5 max-w-lg text-sm leading-7 text-[#77717e]">
                The legal coverage area you are looking for may have been removed
                or may no longer be available.
            </p>

            <a
                href="<?= e(url('index.php#practice-areas')) ?>"
                class="mt-9 inline-flex items-center gap-3 rounded-full bg-[#24104f] px-7 py-3.5 text-xs font-semibold text-white transition hover:bg-[#C391EE] hover:text-[#24104f]">
                Explore coverage areas
                <span aria-hidden="true">↗</span>
            </a>

        </section>

    <?php else: ?>

        <!-- CATEGORY HERO BANNER -->
        <section class="relative isolate flex min-h-[600px] items-center justify-center overflow-hidden bg-[#160d22] md:min-h-[680px] lg:min-h-[740px]">

            <!-- Background image -->
            <img
                src="<?= e($categoryImage) ?>"
                alt="<?= e($category['name']) ?>"
                class="absolute inset-0 -z-20 h-full w-full object-cover"
                fetchpriority="high"
                onerror="this.onerror=null;this.src='<?= e($defaultCoverageImage) ?>';">

            <!-- Image overlays -->
            <div class="absolute inset-0 -z-10 bg-gradient-to-r from-[#160d22]/90 via-[#24104f]/65 to-[#24104f]/20"></div>

            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-[#160d22]/95 via-[#160d22]/20 to-[#160d22]/20"></div>

            <!-- Decorative circles -->
            <div class="pointer-events-none absolute -right-32 top-10 h-[420px] w-[420px] rounded-full border border-white/10 md:h-[600px] md:w-[600px]"></div>

            <div class="pointer-events-none absolute -right-12 top-28 h-[300px] w-[300px] rounded-full border border-white/10 md:h-[430px] md:w-[430px]"></div>

            <!-- Hero content -->

            <div class="site-container relative z-10 mx-auto flex w-full flex-col items-center px-5 pb-20 pt-32 text-center md:pb-24 md:pt-36 lg:pb-28">

                <!-- BREADCRUMB -->
                <nav
                    class="mb-12 flex flex-wrap items-center justify-center gap-3 text-[10px] font-medium tracking-[0.16em] text-white/65"
                    aria-label="Breadcrumb">

                    <a
                        href="<?= e(url('index.php')) ?>"
                        class="transition hover:text-[#D8B9F5]">
                        HOME
                    </a>

                    <span aria-hidden="true">/</span>

                    <a
                        href="<?= e(url('index.php#practice-areas')) ?>"
                        class="transition hover:text-[#D8B9F5]">
                        COVERAGE AREAS
                    </a>

                    <span aria-hidden="true">/</span>

                    <span class="text-[#D8B9F5]">
                        <?= e(strtoupper($category['name'])) ?>
                    </span>

                </nav>


                <!-- EYEBROW -->
                <div class="mb-7 flex items-center justify-center gap-3">

                    <span class="h-px w-9 bg-[#C391EE]"></span>

                    <span class="text-[10px] font-semibold tracking-[0.25em] text-[#D8B9F5]">
                        K &amp; S DOMUS LEGALIS / LEGAL JOURNAL
                    </span>

                    <span class="h-px w-9 bg-[#C391EE]"></span>

                </div>


                <!-- CENTERED HEADING -->
                <h1 class="mx-auto max-w-5xl font-serif text-5xl font-semibold leading-[1.05] tracking-[-0.045em] text-white sm:text-6xl md:text-7xl lg:text-[88px]">

                    <?= e($category['name']) ?>

                    <span class="mt-5 block font-sans text-base font-normal leading-relaxed tracking-normal text-white/80 sm:text-xl md:text-2xl">
                        Legal developments. Thoughtful perspectives.
                    </span>

                </h1>


                <!-- DESCRIPTION -->
                <p class="mx-auto mt-7 max-w-2xl text-sm leading-8 text-white/80 md:text-base">

                    <?= e(
                        $category['description']
                            ?: 'Explore the latest legal developments, articles, judgments and thoughtful analysis in '
                            . $category['name'] . '.'
                    ) ?>

                </p>


                <!-- CATEGORY STATISTICS -->
                <div class="mt-12 flex w-full max-w-xl flex-wrap items-center justify-center gap-10 border-t border-white/20 pt-8 sm:gap-16">

                    <div class="min-w-[120px] text-center">

                        <p class="text-[9px] font-semibold tracking-[0.2em] text-white/50">
                            PUBLISHED ARTICLES
                        </p>

                        <p class="mt-3 font-serif text-3xl text-white">
                            <?= number_format($totalArticles) ?>
                        </p>

                    </div>


                    <div class="hidden h-12 w-px bg-white/20 sm:block"></div>


                    <div class="min-w-[120px] text-center">

                        <p class="text-[9px] font-semibold tracking-[0.2em] text-white/50">
                            COVERAGE
                        </p>

                        <p class="mt-3 text-sm font-medium text-[#D8B9F5]">
                            <?= e($category['name']) ?>
                        </p>

                    </div>

                </div>

            </div>

            <!-- Decorative symbol -->
            <span
                class="pointer-events-none absolute bottom-0 right-8 font-serif text-[220px] leading-none text-white/[0.045] md:right-20 md:text-[320px]"
                aria-hidden="true">
                §
            </span>

        </section>


        <!-- CATEGORY ARTICLES -->
        <section class="py-20 md:py-28 lg:py-32">

            <div class="site-container">

                <!-- Section heading -->
                <div class="mb-12 flex flex-col gap-6 border-b border-[#211c29]/10 pb-9 md:flex-row md:items-end md:justify-between">

                    <div>

                        <span class="inline-flex items-center gap-3 text-[10px] font-semibold tracking-[0.22em] text-[#9870bd]">

                            <span class="h-px w-8 bg-[#C391EE]"></span>

                            THE K&amp;S JOURNAL

                        </span>

                        <h2 class="mt-5 text-3xl font-semibold tracking-[-0.045em] text-[#211c29] sm:text-4xl lg:text-5xl">

                            Latest

                            <span class="text-[#9870bd]">articles.</span>

                        </h2>

                        <p class="mt-4 text-sm leading-7 text-[#77717e]">
                            Explore our latest published articles in
                            <?= e($category['name']) ?>.
                        </p>

                    </div>

                    <div class="text-xs font-medium text-[#77717e]">

                        Showing

                        <span class="font-semibold text-[#24104f]">
                            <?= number_format($totalArticles) ?>
                        </span>

                        <?= $totalArticles === 1 ? 'article' : 'articles' ?>

                    </div>

                </div>


                <?php if ($articles !== []): ?>

                    <!-- Article cards -->
                    <div class="grid grid-cols-1 gap-7 sm:grid-cols-2 xl:grid-cols-3 xl:gap-8">

                        <?php foreach ($articles as $article): ?>

                            <?php article_card($article); ?>

                        <?php endforeach; ?>

                    </div>


                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>

                        <nav
                            class="mt-14 flex flex-wrap items-center justify-center gap-2 border-t border-black/[0.08] pt-9"
                            aria-label="Article pagination">

                            <?php if ($page > 1): ?>

                                <a
                                    href="<?= e(url('category.php?' . http_build_query([
                                                'slug' => $category['slug'],
                                                'page' => $page - 1,
                                            ]))) ?>"
                                    class="inline-flex h-11 items-center gap-2 rounded-full border border-black/10 px-5 text-xs font-semibold text-[#5e5865] transition hover:border-[#C391EE] hover:bg-[#f6f2fa] hover:text-[#9866c5]">
                                    ← Previous
                                </a>

                            <?php endif; ?>


                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            ?>

                            <?php for ($pageNumber = $startPage; $pageNumber <= $endPage; $pageNumber++): ?>

                                <a
                                    href="<?= e(url('category.php?' . http_build_query([
                                                'slug' => $category['slug'],
                                                'page' => $pageNumber,
                                            ]))) ?>"
                                    class="<?= $pageNumber === $page
                                                ? 'border-[#C391EE] bg-[#C391EE] text-white shadow-[0_5px_15px_rgba(195,145,238,0.22)]'
                                                : 'border-black/10 bg-white text-[#5e5865] hover:border-[#C391EE] hover:text-[#9866c5]' ?>
                                        flex h-11 min-w-11 items-center justify-center rounded-full border px-4 text-xs font-semibold transition-all duration-300"
                                    <?= $pageNumber === $page ? 'aria-current="page"' : '' ?>>

                                    <?= e((string) $pageNumber) ?>

                                </a>

                            <?php endfor; ?>


                            <?php if ($page < $totalPages): ?>

                                <a
                                    href="<?= e(url('category.php?' . http_build_query([
                                                'slug' => $category['slug'],
                                                'page' => $page + 1,
                                            ]))) ?>"
                                    class="inline-flex h-11 items-center gap-2 rounded-full border border-black/10 px-5 text-xs font-semibold text-[#5e5865] transition hover:border-[#C391EE] hover:bg-[#f6f2fa] hover:text-[#9866c5]">
                                    Next →
                                </a>

                            <?php endif; ?>

                        </nav>

                    <?php endif; ?>


                <?php else: ?>

                    <!-- Empty state -->
                    <div class="mx-auto flex max-w-2xl flex-col items-center border border-black/[0.08] bg-white px-6 py-16 text-center shadow-[0_15px_50px_rgba(25,16,38,0.04)] md:px-12 md:py-20">

                        <span class="mb-7 flex h-16 w-16 items-center justify-center rounded-full bg-[#f6f2fa] font-serif text-3xl text-[#9870bd]">
                            §
                        </span>

                        <span class="text-[10px] font-semibold tracking-[0.22em] text-[#9870bd]">
                            <?= e(strtoupper($category['name'])) ?>
                        </span>

                        <h3 class="mt-4 text-3xl font-semibold tracking-tight text-[#211c29]">
                            No articles yet.
                        </h3>

                        <p class="mt-4 max-w-sm text-sm leading-7 text-[#77717e]">
                            There are currently no published articles in this coverage area.
                            Please check back as new legal insights are added to the journal.
                        </p>

                        <a
                            href="<?= e(url('index.php#practice-areas')) ?>"
                            class="mt-8 inline-flex items-center gap-3 rounded-full bg-[#24104f] px-7 py-3.5 text-xs font-semibold text-white transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#C391EE] hover:text-[#24104f]">
                            Explore other coverage areas
                            <span aria-hidden="true">↗</span>
                        </a>

                    </div>

                <?php endif; ?>

            </div>

        </section>


        <!-- BACK TO COVERAGE -->
        <section class="border-t border-[#211c29]/10 bg-white py-12">

            <div class="site-container flex flex-col items-center justify-between gap-5 text-center sm:flex-row sm:text-left">

                <div>
                    <p class="text-[10px] font-semibold tracking-[0.2em] text-[#9870bd]">
                        EXPLORE MORE
                    </p>

                    <p class="mt-2 text-lg font-semibold text-[#211c29]">
                        Discover other areas of legal coverage.
                    </p>
                </div>

                <a
                    href="<?= e(url('index.php#practice-areas')) ?>"
                    class="inline-flex items-center gap-3 rounded-full border border-[#24104f]/20 px-7 py-3.5 text-xs font-semibold text-[#24104f] transition hover:border-[#C391EE] hover:bg-[#C391EE]">
                    All coverage areas
                    <span aria-hidden="true">↗</span>
                </a>

            </div>

        </section>

    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
