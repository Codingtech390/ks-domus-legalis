<?php

require_once __DIR__ . '/config/database.php';

require_once __DIR__ . '/includes/article_card.php';

$pageTitle = 'Legal News, Court Judgments & Analysis';

$pageDescription =
    'Stay informed with legal news, court judgments, legislation and expert legal analysis.';

$searchQuery = trim((string) ($_GET['q'] ?? ''));

$activeCategory = trim((string) ($_GET['category'] ?? ''));

if (strlen($searchQuery) > 150) {
    $searchQuery = substr($searchQuery, 0, 150);
}

if (strlen($activeCategory) > 120) {
    $activeCategory = '';
}

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);

$page = is_int($page) && $page > 0 ? $page : 1;

$perPage = 9;

$offset = ($page - 1) * $perPage;

$categoryStatement = $pdo->query(
    'SELECT id, name, slug, description FROM categories ORDER BY name ASC',
);

$allCategories = $categoryStatement->fetchAll();

$where = ["a.status = 'published'", 'a.published_at <= NOW()'];

$params = [];

if ($searchQuery !== '') {
    $where[] = '(a.title LIKE :q_title OR a.excerpt LIKE :q_excerpt OR a.content LIKE :q_content)';

    $searchTerm = '%' . $searchQuery . '%';

    $params['q_title'] = $searchTerm;

    $params['q_excerpt'] = $searchTerm;

    $params['q_content'] = $searchTerm;
}

if ($activeCategory !== '') {
    $where[] = 'c.slug = :category_slug';

    $params['category_slug'] = $activeCategory;
}

$whereSql = implode(' AND ', $where);

$countStatement = $pdo->prepare(
    "SELECT COUNT(*)



     FROM articles a



     INNER JOIN categories c ON c.id = a.category_id



     WHERE $whereSql",
);

$countStatement->execute($params);

$totalArticles = (int) $countStatement->fetchColumn();

$totalPages = max(1, (int) ceil($totalArticles / $perPage));

if ($page > $totalPages) {
    $page = $totalPages;

    $offset = ($page - 1) * $perPage;
}

$listStatement = $pdo->prepare(
    "SELECT a.id, a.title, a.slug, a.excerpt, a.featured_image,



            a.published_at, a.created_at, a.content,



            c.name AS category_name, c.slug AS category_slug,



            ad.name AS author_name



     FROM articles a



     INNER JOIN categories c ON c.id = a.category_id



     INNER JOIN admins ad ON ad.id = a.admin_id



     WHERE $whereSql



     ORDER BY a.published_at DESC, a.id DESC



     LIMIT :limit OFFSET :offset",
);

foreach ($params as $key => $value) {
    $listStatement->bindValue(':' . $key, $value, PDO::PARAM_STR);
}

$listStatement->bindValue(':limit', $perPage, PDO::PARAM_INT);

$listStatement->bindValue(':offset', $offset, PDO::PARAM_INT);

$listStatement->execute();

$articles = $listStatement->fetchAll();

$displayTotal = $totalArticles;

$caseHighlights = $pdo
    ->query(
        "SELECT

        id,

        case_type,

        title,

        court,

        case_reference,

        decision_year,

        role_description,

        summary,

        outcome,

        public_url

     FROM case_highlights

     WHERE status = 'published'

     ORDER BY case_type ASC, display_order ASC, decision_year DESC, id DESC",
    )
    ->fetchAll(PDO::FETCH_ASSOC);

$importantCases = array_values(
    array_filter(
        $caseHighlights,

        static fn(array $case): bool => $case['case_type'] === 'important',
    ),
);

$proBonoCases = array_values(
    array_filter(
        $caseHighlights,

        static fn(array $case): bool => $case['case_type'] === 'pro_bono',
    ),
);

require_once __DIR__ . '/includes/header.php';
?>

<?php require __DIR__ . '/includes/home_carousel.php'; ?>
<!-- CASEWORK SHOWCASE -->
<section
    class="relative overflow-hidden bg-white py-20 md:py-28 lg:py-32"
    id="practice-areas">

    <div class="pointer-events-none absolute -right-40 top-20 h-[420px] w-[420px] rounded-full border border-[#C391EE]/15"></div>
    <div class="pointer-events-none absolute -right-20 top-40 h-[260px] w-[260px] rounded-full border border-[#C391EE]/15"></div>

    <div class="site-container relative z-10">

        <!-- SECTION INTRO -->
        <div class="mx-auto mb-16 max-w-3xl text-center">

            <span class="inline-flex items-center justify-center gap-3 text-[10px] font-bold uppercase tracking-[0.24em] text-[#9870bd]">
                <span class="h-px w-8 bg-[#C391EE]"></span>
                01 / SELECTED CASEWORK
                <span class="h-px w-8 bg-[#C391EE]"></span>
            </span>

            <h2 class="mt-6 text-4xl font-semibold leading-[1.1] tracking-[-0.045em] text-[#211c29] sm:text-5xl lg:text-6xl">
                The work behind
                <span class="block text-[#9870bd]">the principles.</span>
            </h2>

            <p class="mx-auto mt-6 max-w-2xl text-sm leading-8 text-[#77717e] md:text-base">
                A selection of matters and public-interest work, presented with
                care, context and respect for confidentiality.
            </p>

        </div>


        <!-- IMPORTANT CASES -->
        <div class="mb-20">

            <div class="mb-8 flex flex-col gap-4 border-b border-[#211c29]/10 pb-6 sm:flex-row sm:items-end sm:justify-between">

                <div>
                    <span class="text-[10px] font-bold uppercase tracking-[0.22em] text-[#9870bd]">
                        01 / IMPORTANT MATTERS
                    </span>

                    <h3 class="mt-3 text-3xl font-semibold tracking-tight text-[#211c29] sm:text-4xl">
                        Important cases<span class="text-[#C391EE]">.</span>
                    </h3>

                    <p class="mt-3 max-w-xl text-sm leading-7 text-[#77717e]">
                        Selected matters and legal developments of particular
                        significance.
                    </p>
                </div>

                <span class="text-xs font-semibold tracking-wide text-[#9870bd]">
                    <?= number_format(count($importantCases)) ?> PUBLISHED
                </span>

            </div>


            <?php if ($importantCases !== []): ?>

                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">

                    <?php foreach ($importantCases as $index => $case): ?>

                        <article class="group relative flex h-full flex-col overflow-hidden border border-[#24104f]/10 bg-white shadow-[0_12px_40px_rgba(36,16,79,0.045)] transition duration-500 hover:-translate-y-1 hover:border-[#C391EE]/60 hover:shadow-[0_22px_55px_rgba(36,16,79,0.10)]">

                            <div class="h-1 w-full bg-gradient-to-r from-[#24104f] via-[#9870bd] to-[#C391EE]"></div>

                            <div class="flex flex-1 flex-col p-6 sm:p-7">

                                <div class="mb-6 flex items-center justify-between gap-3">

                                    <span class="flex h-10 w-10 items-center justify-center rounded-full border border-[#C391EE]/40 bg-[#f8f4fc] text-xs font-bold text-[#9870bd]">
                                        <?= e(
                                            str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                                        ) ?>
                                    </span>

                                    <?php if (!empty($case['decision_year'])): ?>
                                        <span class="text-[10px] font-bold tracking-[0.16em] text-[#9870bd]">
                                            <?= e((string) $case['decision_year']) ?>
                                        </span>
                                    <?php endif; ?>

                                </div>


                                <div class="flex flex-wrap gap-2">

                                    <?php if (!empty($case['court'])): ?>
                                        <span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#77717e]">
                                            <?= e($case['court']) ?>
                                        </span>
                                    <?php endif; ?>

                                </div>


                                <h4 class="mt-4 text-xl font-semibold leading-snug tracking-tight text-[#211c29] transition-colors duration-300 group-hover:text-[#9870bd]">
                                    <?= e($case['title']) ?>
                                </h4>


                                <?php if (!empty($case['case_reference'])): ?>
                                    <p class="mt-3 text-xs leading-6 text-[#9870bd]">
                                        Reference: <?= e($case['case_reference']) ?>
                                    </p>
                                <?php endif; ?>


                                <p class="mt-5 flex-1 text-sm leading-7 text-[#77717e]">
                                    <?= nl2br(e($case['summary'])) ?>
                                </p>


                                <?php if (!empty($case['role_description'])): ?>
                                    <div class="mt-6 border-t border-[#211c29]/10 pt-5">
                                        <p class="text-[9px] font-bold uppercase tracking-[0.18em] text-[#9870bd]">
                                            Our role
                                        </p>

                                        <p class="mt-2 text-xs leading-6 text-[#5f5867]">
                                            <?= e($case['role_description']) ?>
                                        </p>
                                    </div>
                                <?php endif; ?>


                                <?php if (!empty($case['outcome'])): ?>
                                    <div class="mt-5 border-l-2 border-[#C391EE] bg-[#f8f6fb] px-4 py-3">
                                        <p class="text-[9px] font-bold uppercase tracking-[0.18em] text-[#9870bd]">
                                            Outcome / significance
                                        </p>

                                        <p class="mt-2 text-xs leading-6 text-[#5f5867]">
                                            <?= nl2br(e($case['outcome'])) ?>
                                        </p>
                                    </div>
                                <?php endif; ?>


                                <?php if (!empty($case['public_url'])): ?>
                                    <a
                                        href="<?= e($case['public_url']) ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="mt-7 inline-flex items-center gap-3 text-[10px] font-bold uppercase tracking-[0.16em] text-[#24104f] transition hover:text-[#9870bd]">

                                        View public reference

                                        <span class="flex h-8 w-8 items-center justify-center rounded-full border border-[#C391EE]/50 transition duration-300 group-hover:rotate-45 group-hover:bg-[#C391EE] group-hover:text-white">
                                            ↗
                                        </span>

                                    </a>
                                <?php endif; ?>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="border border-dashed border-[#C391EE]/50 bg-[#faf8fc] px-6 py-12 text-center">
                    <span class="font-serif text-4xl text-[#C391EE]">§</span>

                    <p class="mt-4 text-lg font-semibold text-[#211c29]">
                        Selected matters will appear here.
                    </p>

                    <p class="mt-2 text-sm text-[#77717e]">
                        Published case highlights will be displayed in this section.
                    </p>
                </div>

            <?php endif; ?>

        </div>


        <!-- PRO BONO WORK -->
        <div class="relative overflow-hidden bg-[#24104f] px-6 py-10 text-white shadow-[0_20px_60px_rgba(36,16,79,0.12)] sm:px-10 sm:py-14 lg:px-14">

            <div class="pointer-events-none absolute -right-20 -top-24 h-80 w-80 rounded-full border border-white/10"></div>
            <div class="pointer-events-none absolute -right-5 -top-10 h-52 w-52 rounded-full border border-white/10"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-20 h-72 w-72 rounded-full bg-[#C391EE]/10 blur-3xl"></div>

            <div class="relative z-10">

                <div class="mb-10 flex flex-col gap-5 border-b border-white/15 pb-8 sm:flex-row sm:items-end sm:justify-between">

                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-[0.24em] text-[#D8B9F5]">
                            02 / ACCESS TO JUSTICE
                        </span>

                        <h3 class="mt-4 text-3xl font-semibold leading-tight tracking-[-0.04em] sm:text-4xl lg:text-5xl">
                            Pro bono.
                            <span class="text-[#C391EE]">Purpose beyond practice.</span>
                        </h3>

                        <p class="mt-4 max-w-2xl text-sm leading-8 text-white/65">
                            A selection of public-interest matters and work
                            undertaken in the service of access to justice.
                        </p>
                    </div>

                    <span class="shrink-0 text-xs font-semibold tracking-[0.16em] text-[#D8B9F5]">
                        <?= number_format(count($proBonoCases)) ?> PUBLISHED
                    </span>

                </div>


                <?php if ($proBonoCases !== []): ?>

                    <div class="grid gap-4 lg:grid-cols-2">

                        <?php foreach ($proBonoCases as $index => $case): ?>

                            <article class="group border border-white/15 bg-white/[0.045] p-6 transition duration-300 hover:border-[#C391EE]/60 hover:bg-white/[0.075] sm:p-7">

                                <div class="flex items-center justify-between gap-4">

                                    <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#C391EE]">
                                        PUBLIC INTEREST MATTER
                                    </span>

                                    <?php if (!empty($case['decision_year'])): ?>
                                        <span class="text-xs text-white/50">
                                            <?= e((string) $case['decision_year']) ?>
                                        </span>
                                    <?php endif; ?>

                                </div>


                                <h4 class="mt-5 text-xl font-semibold leading-snug text-white transition-colors group-hover:text-[#D8B9F5]">
                                    <?= e($case['title']) ?>
                                </h4>


                                <?php if (!empty($case['court'])): ?>
                                    <p class="mt-2 text-xs text-[#D8B9F5]/80">
                                        <?= e($case['court']) ?>
                                    </p>
                                <?php endif; ?>


                                <p class="mt-5 text-sm leading-7 text-white/65">
                                    <?= nl2br(e($case['summary'])) ?>
                                </p>


                                <?php if (!empty($case['role_description'])): ?>
                                    <div class="mt-5 border-t border-white/10 pt-5">
                                        <p class="text-[9px] font-bold uppercase tracking-[0.18em] text-[#C391EE]">
                                            Nature of involvement
                                        </p>

                                        <p class="mt-2 text-xs leading-6 text-white/70">
                                            <?= e($case['role_description']) ?>
                                        </p>
                                    </div>
                                <?php endif; ?>


                                <?php if (!empty($case['outcome'])): ?>
                                    <p class="mt-4 text-xs leading-6 text-white/60">
                                        <?= nl2br(e($case['outcome'])) ?>
                                    </p>
                                <?php endif; ?>


                                <?php if (!empty($case['public_url'])): ?>
                                    <a
                                        href="<?= e($case['public_url']) ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="mt-6 inline-flex items-center gap-3 text-[10px] font-bold uppercase tracking-[0.16em] text-[#D8B9F5] transition hover:text-white">

                                        Read public reference
                                        <span aria-hidden="true">↗</span>

                                    </a>
                                <?php endif; ?>

                            </article>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <div class="border border-dashed border-white/20 bg-white/[0.04] px-6 py-10 text-center">
                        <p class="text-lg font-semibold text-white">
                            Public-interest work will be featured here.
                        </p>

                        <p class="mt-2 text-sm leading-7 text-white/60">
                            Published pro bono records will appear in this space.
                        </p>
                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

</section>


<!-- ADMIN-PUBLISHED ARTICLES -->



<section class="ks-section ks-insights relative overflow-hidden py-20 md:py-28 lg:py-32" id="latest-news">



    <div class="site-container">
        <!-- SECTION HEADING -->

        <div class="mb-12 flex flex-col items-center gap-7 text-center lg:mb-14">



            <div class="flex w-full max-w-3xl flex-col items-center">



                <span class="ks-kicker inline-flex items-center justify-center gap-3">

                    <span class="h-px w-8 bg-[#C391EE]"></span>

                    02 / LEGAL JOURNAL

                </span>



                <h2 class="mt-6 text-4xl font-semibold leading-[1.1] tracking-[-0.045em] text-[#211c29] sm:text-5xl lg:text-6xl">



                    <?php if ($searchQuery !== ''): ?>

                        Search <span class="text-[#9870bd]">results.</span>



                    <?php elseif ($activeCategory !== ''): ?>

                        Perspectives on <span class="text-[#9870bd]">this topic.</span>



                    <?php else: ?>

                        Insights &amp; <span class="text-[#9870bd]">perspectives.</span>

                    <?php endif; ?>



                </h2>



                <p class="mt-5 text-sm leading-8 text-[#77717e] md:text-base">

                    <span class="font-semibold text-[#211c29]"><?= number_format(
                        $displayTotal,
                    ) ?></span>

                    article<?= $displayTotal === 1 ? '' : 's' ?> in the journal



                    <?php if ($searchQuery !== ''): ?>

                        for <span class="font-medium text-[#9870bd]">“<?= e($searchQuery) ?>”</span>

                    <?php endif; ?>

                </p>



            </div>



        </div>
        <!-- FILTERS AND SEARCH -->

        <div class="mb-10 border-y border-black/[0.09] py-6">



            <div class="flex flex-col items-center gap-6">
                <!-- CATEGORY FILTERS -->

                <div class="flex w-full flex-wrap items-center justify-center gap-2">



                    <a

                        href="<?= e(url('index.php#latest-news')) ?>"

                        class="<?= $activeCategory === '' && $searchQuery === ''
                            ? 'border-[#C391EE] bg-[#C391EE] text-white shadow-[0_5px_15px_rgba(195,145,238,0.22)]'
                            : 'border-black/[0.10] bg-white text-[#5e5865] hover:border-[#C391EE] hover:text-[#9866c5]' ?>

                            inline-flex items-center rounded-full border px-4 py-2.5 text-xs font-semibold transition-all duration-300"

                        <?= $activeCategory === '' && $searchQuery === ''
                            ? 'aria-current="page"'
                            : '' ?>>

                        All articles

                    </a>



                    <?php foreach ($allCategories as $category): ?>



                        <a

                            href="<?= e(
                                url(
                                    'index.php?' .
                                        http_build_query(['category' => $category['slug']]) .
                                        '#latest-news',
                                ),
                            ) ?>"

                            class="<?= $activeCategory === $category['slug']
                                ? 'border-[#C391EE] bg-[#C391EE] text-white shadow-[0_5px_15px_rgba(195,145,238,0.22)]'
                                : 'border-black/[0.10] bg-white text-[#5e5865] hover:border-[#C391EE] hover:text-[#9866c5]' ?>

                                inline-flex items-center rounded-full border px-4 py-2.5 text-xs font-semibold transition-all duration-300"

                            <?= $activeCategory === $category['slug']
                                ? 'aria-current="page"'
                                : '' ?>>

                            <?= e($category['name']) ?>

                        </a>



                    <?php endforeach; ?>



                </div>
                <!-- SEARCH -->

                <form

                    method="get"

                    action="<?= e(url('index.php')) ?>"

                    class="flex w-full max-w-[400px] items-center gap-2 rounded-full border border-black/[0.10] bg-white p-1.5 transition-all duration-300 focus-within:border-[#C391EE] focus-within:shadow-[0_0_0_3px_rgba(195,145,238,0.12)]">



                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#f6f2fa] text-[#9870bd]">

                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">

                            <circle cx="11" cy="11" r="7"></circle>

                            <path d="m20 20-4-4"></path>

                        </svg>

                    </span>



                    <input

                        type="search"

                        name="q"

                        value="<?= e($searchQuery) ?>"

                        maxlength="150"

                        aria-label="Search articles"

                        placeholder="Search the journal..."

                        class="min-w-0 flex-1 border-0 bg-transparent px-1 text-xs text-[#211c29] outline-none placeholder:text-[#a49da9] focus:ring-0">



                    <?php if ($activeCategory !== ''): ?>

                        <input type="hidden" name="category" value="<?= e($activeCategory) ?>">

                    <?php endif; ?>



                    <button

                        type="submit"

                        class="flex h-10 shrink-0 items-center justify-center gap-2 rounded-full bg-[#24104f] px-5 text-xs font-semibold text-white transition-all duration-300 hover:bg-[#C391EE]">

                        Search <span aria-hidden="true">↗</span>

                    </button>



                </form>



            </div>



        </div>
        <!-- ARTICLES -->

        <?php if ($articles !== []): ?>



            <div class="grid grid-cols-1 gap-7 sm:grid-cols-2 xl:grid-cols-3 xl:gap-8">



                <?php foreach ($articles as $article): ?>



                    <?php article_card($article); ?>



                <?php endforeach; ?>



            </div>
            <!-- PAGINATION -->

            <?php if ($totalPages > 1): ?>



                <?php
                $pageParams = [];

                if ($searchQuery !== '') {
                    $pageParams['q'] = $searchQuery;
                }

                if ($activeCategory !== '') {
                    $pageParams['category'] = $activeCategory;
                }

                $startPage = max(1, $page - 2);

                $endPage = min($totalPages, $page + 2);
                ?>



                <nav

                    class="mt-14 flex flex-wrap items-center justify-center gap-2 border-t border-black/[0.08] pt-9"

                    aria-label="Article pagination">



                    <?php if ($page > 1): ?>



                        <a

                            href="<?= e(
                                url(
                                    'index.php?' .
                                        http_build_query($pageParams + ['page' => $page - 1]) .
                                        '#latest-news',
                                ),
                            ) ?>"

                            class="inline-flex h-11 items-center gap-2 rounded-full border border-black/10 px-5 text-xs font-semibold text-[#5e5865] transition-all hover:border-[#C391EE] hover:bg-[#f6f2fa] hover:text-[#9866c5]">

                            ← Previous

                        </a>



                    <?php endif; ?>





                    <?php for ($pageNumber = $startPage; $pageNumber <= $endPage; $pageNumber++): ?>



                        <?php $pageUrl = url(
                            'index.php?' .
                                http_build_query($pageParams + ['page' => $pageNumber]) .
                                '#latest-news',
                        ); ?>



                        <a

                            href="<?= e($pageUrl) ?>"

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

                            href="<?= e(
                                url(
                                    'index.php?' .
                                        http_build_query($pageParams + ['page' => $page + 1]) .
                                        '#latest-news',
                                ),
                            ) ?>"

                            class="inline-flex h-11 items-center gap-2 rounded-full border border-black/10 px-5 text-xs font-semibold text-[#5e5865] transition-all hover:border-[#C391EE] hover:bg-[#f6f2fa] hover:text-[#9866c5]">

                            Next →

                        </a>



                    <?php endif; ?>



                </nav>



            <?php endif; ?>





        <?php else: ?>
            <!-- EMPTY STATE -->

            <div class="mx-auto flex max-w-2xl flex-col items-center justify-center border border-black/[0.08] bg-white px-6 py-16 text-center shadow-[0_15px_50px_rgba(25,16,38,0.04)] md:px-12 md:py-20">



                <span class="mb-7 flex h-16 w-16 items-center justify-center rounded-full bg-[#f6f2fa] text-3xl font-serif text-[#9870bd]">

                    §

                </span>



                <span class="text-[10px] font-semibold tracking-[0.22em] text-[#9870bd]">

                    JOURNAL SEARCH

                </span>



                <h3 class="mt-4 text-3xl font-semibold tracking-tight text-[#211c29]">

                    No articles found.

                </h3>



                <p class="mt-4 max-w-sm text-sm leading-7 text-[#77717e]">

                    We couldn't find any articles matching your selection. Try another search term or explore a different legal topic.

                </p>



                <a

                    href="<?= e(url('index.php#latest-news')) ?>"

                    class="mt-8 inline-flex items-center gap-3 rounded-full bg-[#24104f] px-7 py-3.5 text-xs font-semibold text-white transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#C391EE]">

                    View all coverage

                    <span aria-hidden="true">↗</span>

                </a>



            </div>



        <?php endif; ?>



    </div>



</section>


<!-- =========================================================
   MEET OUR TEAM
   ========================================================= -->

<section id="meet-our-team" class="ks-team-section relative overflow-hidden bg-white py-20 md:py-28 lg:py-32">



    <div class="site-container relative z-10">
        <!-- SECTION HEADING -->

        <div class="mx-auto mb-14 max-w-3xl text-center md:mb-16">
            <span class="ks-kicker inline-flex items-center justify-center gap-3">
                <span class="h-px w-8 bg-[#C391EE]"></span>
                03 / THE PEOPLE BEHIND OUR WORK
                <span class="h-px w-8 bg-[#C391EE]"></span>
            </span>

            <h2 class="mt-6 text-4xl font-semibold leading-[1.1] tracking-[-0.045em] text-[#211c29] sm:text-5xl lg:text-6xl">

                Meet our

                <span class="text-[#9870bd]">team.</span>

            </h2>



            <p class="mx-auto mt-6 max-w-2xl text-sm leading-8 text-[#77717e] md:text-base">

                The people behind our work bring together legal knowledge,

                thoughtful perspectives and a commitment to responsible

                legal communication.

            </p>



        </div>
        <!-- TEAM GRID -->

        <div class="grid grid-cols-1 gap-7 sm:grid-cols-2 lg:grid-cols-4 lg:gap-6">
            <!-- MEMBER 01 -->

            <article class="ks-team-card" data-team-card>



                <div class="ks-team-card__inner">
                    <!-- FRONT -->

                    <div class="ks-team-card__face ks-team-card__front">



                        <div class="ks-team-card__image-wrap">



                            <img

                                src="<?= e(url('assets/images/team/team-1.jpeg')) ?>"

                                alt="Team member portrait"

                                class="ks-team-card__image"

                                loading="lazy">



                            <span class="ks-team-card__number">01</span>



                            <div class="ks-team-card__image-overlay"></div>



                        </div>



                        <div class="ks-team-card__front-content">



                            <p class="ks-team-card__role">
                                FOUNDER
                            </p>

                            <h3 class="ks-team-card__name">
                                Adv Kumud Jha
                            </h3>

                            <div class="ks-team-card__bottom">
                                <span>Discover profile</span>

                                <button

                                    type="button"

                                    class="ks-team-card__flip-button"

                                    data-team-flip

                                    aria-label="View Member One's profile"

                                    aria-pressed="false">

                                    ↗

                                </button>



                            </div>



                        </div>



                    </div>
                    <!-- BACK -->

                    <div class="ks-team-card__face ks-team-card__back">



                        <div class="ks-team-card__back-top">



                            <span class="ks-team-card__back-number">01 / PROFILE</span>



                            <span class="font-serif text-4xl text-[#C391EE]/50">§</span>



                        </div>



                        <div class="ks-team-card__back-content">



                            <p class="ks-team-card__role">
                                Founder
                            </p>



                            <h3 class="ks-team-card__back-name">

                                Adv Kumud Jha

                            </h3>

                            <div class="ks-team-card__separator"></div>



                            <p class="ks-team-card__bio">

                                Add a short professional biography describing

                                this person's experience, areas of work,

                                qualifications and contribution to the firm.

                            </p>



                        </div>



                        <button

                            type="button"

                            class="ks-team-card__back-button"

                            data-team-flip

                            aria-pressed="true">

                            <span>Back to photograph</span>

                            <span>↶</span>

                        </button>



                    </div>



                </div>



            </article>
            <!-- MEMBER 02 -->

            <article class="ks-team-card" data-team-card>



                <div class="ks-team-card__inner">



                    <div class="ks-team-card__face ks-team-card__front">



                        <div class="ks-team-card__image-wrap">



                            <img

                                src="<?= e(url('assets/images/team/team-2.jpeg')) ?>"

                                alt="Team member portrait"

                                class="ks-team-card__image"

                                loading="lazy">



                            <span class="ks-team-card__number">02</span>



                            <div class="ks-team-card__image-overlay"></div>



                        </div>



                        <div class="ks-team-card__front-content">



                            <p class="ks-team-card__role">

                                FOUNDER

                            </p>

                            <h3 class="ks-team-card__name">
                                Adv. Subham Kumar
                            </h3>



                            <div class="ks-team-card__bottom">



                                <span>Discover profile</span>



                                <button

                                    type="button"

                                    class="ks-team-card__flip-button"

                                    data-team-flip

                                    aria-label="View Member Two's profile"

                                    aria-pressed="false">

                                    ↗

                                </button>



                            </div>



                        </div>



                    </div>





                    <div class="ks-team-card__face ks-team-card__back">



                        <div class="ks-team-card__back-top">



                            <span class="ks-team-card__back-number">02 / PROFILE</span>



                            <span class="font-serif text-4xl text-[#C391EE]/50">§</span>



                        </div>



                        <div class="ks-team-card__back-content">



                            <p class="ks-team-card__role">

                            Founder

                            </p>



                            <h3 class="ks-team-card__back-name">
                                Adv. Subham Kumar
                            </h3>


                            <div class="ks-team-card__separator"></div>



                            <p class="ks-team-card__bio">
                                Add this person's professional background,
                                legal expertise, practice areas and
                                professional achievements.
                            </p>



                        </div>



                        <button

                            type="button"

                            class="ks-team-card__back-button"

                            data-team-flip

                            aria-pressed="true">

                            <span>Back to photograph</span>

                            <span>↶</span>

                        </button>



                    </div>



                </div>



            </article>
            <!-- MEMBER 03 -->

            <article class="ks-team-card" data-team-card>



                <div class="ks-team-card__inner">



                    <div class="ks-team-card__face ks-team-card__front">



                        <div class="ks-team-card__image-wrap">



                            <img

                                src="<?= e(url('assets/images/team/team-3.png')) ?>"

                                alt="Team member portrait"

                                class="ks-team-card__image"

                                loading="lazy">



                            <span class="ks-team-card__number">03</span>



                            <div class="ks-team-card__image-overlay"></div>



                        </div>



                        <div class="ks-team-card__front-content">



                            <p class="ks-team-card__role">

                                PARTNER

                            </p>



                            <h3 class="ks-team-card__name">

                                Adv. Amar Pandey

                            </h3>



                            <div class="ks-team-card__bottom">



                                <span>Discover profile</span>



                                <button

                                    type="button"

                                    class="ks-team-card__flip-button"

                                    data-team-flip

                                    aria-label="View Member Three's profile"

                                    aria-pressed="false">

                                    ↗

                                </button>



                            </div>



                        </div>



                    </div>





                    <div class="ks-team-card__face ks-team-card__back">



                        <div class="ks-team-card__back-top">



                            <span class="ks-team-card__back-number">03 / PROFILE</span>



                            <span class="font-serif text-4xl text-[#C391EE]/50">§</span>



                        </div>



                        <div class="ks-team-card__back-content">



                            <p class="ks-team-card__role">

                                PARTNER

                            </p>



                            <h3 class="ks-team-card__back-name">

                                Adv. Amar Pandey

                            </h3>


                            <div class="ks-team-card__separator"></div>



                            <p class="ks-team-card__bio">

                                Describe this person's research experience,

                                analytical strengths and contribution to

                                legal publications and editorial work.

                            </p>



                        </div>



                        <button

                            type="button"

                            class="ks-team-card__back-button"

                            data-team-flip

                            aria-pressed="true">

                            <span>Back to photograph</span>

                            <span>↶</span>

                        </button>



                    </div>



                </div>



            </article>
            <!-- MEMBER 04 -->

            <article class="ks-team-card" data-team-card>



                <div class="ks-team-card__inner">



                    <div class="ks-team-card__face ks-team-card__front">



                        <div class="ks-team-card__image-wrap">



                            <img

                                src="<?= e(url('assets/images/team/team-4.png')) ?>"

                                alt="Team member portrait"

                                class="ks-team-card__image"

                                loading="lazy">



                            <span class="ks-team-card__number">04</span>



                            <div class="ks-team-card__image-overlay"></div>



                        </div>



                        <div class="ks-team-card__front-content">



                            <p class="ks-team-card__role">

                                PARTNER

                            </p>



                            <h3 class="ks-team-card__name">

                                Adv. Vishal Singh

                            </h3>



                            <div class="ks-team-card__bottom">



                                <span>Discover profile</span>



                                <button

                                    type="button"

                                    class="ks-team-card__flip-button"

                                    data-team-flip

                                    aria-label="View Member Four's profile"

                                    aria-pressed="false">

                                    ↗

                                </button>



                            </div>



                        </div>



                    </div>





                    <div class="ks-team-card__face ks-team-card__back">



                        <div class="ks-team-card__back-top">



                            <span class="ks-team-card__back-number">04 / PROFILE</span>



                            <span class="font-serif text-4xl text-[#C391EE]/50">§</span>



                        </div>



                        <div class="ks-team-card__back-content">



                            <p class="ks-team-card__role">

                                PARTNER

                            </p>



                            <h3 class="ks-team-card__back-name">

                                Adv. Vishal Singh

                            </h3>







                            <div class="ks-team-card__separator"></div>



                            <p class="ks-team-card__bio">

                                Add this person's editorial background,

                                communication experience and role in

                                developing the journal's content.

                            </p>



                        </div>



                        <button

                            type="button"

                            class="ks-team-card__back-button"

                            data-team-flip

                            aria-pressed="true">

                            <span>Back to photograph</span>

                            <span>↶</span>

                        </button>



                    </div>



                </div>



            </article>



        </div>

    </div>



</section>
<!-- =========================================================
 TEAM FLIP CARD STYLES
========================================================= -->

<style>
    .ks-team-card {
        position: relative;
        min-width: 0;
        height: 450px;
        perspective: 1400px;
        outline: none;
    }

    .ks-team-card__inner {
        position: relative;
        width: 100%;
        height: 100%;
        transform-style: preserve-3d;
        transition: transform 850ms cubic-bezier(.2, .75, .2, 1);
    }

    .ks-team-card.is-flipped .ks-team-card__inner {
        transform: rotateY(180deg);
    }

    .ks-team-card__face {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid rgba(36, 16, 79, .10);
        background: #fff;
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
        box-shadow: 0 15px 45px rgba(36, 16, 79, .07);
    }

    .ks-team-card__front {
        transform: rotateY(0deg);
    }

    .ks-team-card__back {
        transform: rotateY(180deg);
        padding: 25px;
        background:
            radial-gradient(circle at 100% 0%, rgba(195, 145, 238, .17), transparent 38%),
            #24104f;
        color: #fff;
    }

    .ks-team-card__image-wrap {
        position: relative;
        height: 310px;
        flex-shrink: 0;
        overflow: hidden;
        background: #eee8f5;
    }

    .ks-team-card__image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center top;
        transition: transform 900ms cubic-bezier(.2, .75, .2, 1);
    }
    .ks-team-card:hover .ks-team-card__image {
        transform: scale(1.055);
    }

    .ks-team-card__image-overlay {
        position: absolute;
        inset: 45% 0 0;
        pointer-events: none;
        background: linear-gradient(to bottom, transparent, rgba(20, 10, 35, .35));
    }
    .ks-team-card__number {
        position: absolute;
        top: 17px;
        left: 17px;
        z-index: 2;
        display: grid;
        width: 35px;
        height: 35px;
        place-items: center;
        border: 1px solid rgba(255, 255, 255, .5);
        border-radius: 50%;
        background: rgba(36, 16, 79, .55);
        color: #fff;
        font-size: 10px;
        backdrop-filter: blur(10px);
    }

    .ks-team-card__front-content {

        display: flex;

        flex: 1;

        flex-direction: column;

        justify-content: center;

        padding: 19px 21px;

    }



    .ks-team-card__role {

        color: #9870bd;

        font-size: 9px;

        font-weight: 700;

        letter-spacing: .19em;

        line-height: 1.5;

    }



    .ks-team-card__name {

        margin-top: 7px;

        color: #211c29;

        font-family: "Playfair Display", Georgia, serif;

        font-size: 23px;

        font-weight: 600;

        line-height: 1.2;

    }



    .ks-team-card__bottom {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 10px;

        margin-top: 15px;

        padding-top: 12px;

        border-top: 1px solid rgba(33, 28, 41, .10);

        color: #77717e;

        font-size: 10px;

        font-weight: 500;

    }



    .ks-team-card__flip-button {

        display: grid;

        width: 34px;

        height: 34px;

        flex-shrink: 0;

        place-items: center;

        border: 1px solid rgba(195, 145, 238, .5);

        border-radius: 50%;

        background: #f8f6fb;

        color: #7544a5;

        font-size: 17px;

        cursor: pointer;

        transition: background .25s ease, color .25s ease, transform .25s ease;

    }



    .ks-team-card__flip-button:hover {

        transform: rotate(45deg);

        background: #C391EE;

        color: #24104f;

    }



    .ks-team-card__back-top {

        display: flex;

        align-items: center;

        justify-content: space-between;

        border-bottom: 1px solid rgba(255, 255, 255, .16);

        padding-bottom: 15px;

    }



    .ks-team-card__back-number {

        color: rgba(255, 255, 255, .52);

        font-size: 9px;

        font-weight: 600;

        letter-spacing: .2em;

    }



    .ks-team-card__back-content {

        display: flex;

        flex: 1;

        flex-direction: column;

        justify-content: center;

        padding: 15px 0;

    }



    .ks-team-card__back .ks-team-card__role {

        color: #D8B9F5;

    }



    .ks-team-card__back-name {

        margin-top: 12px;

        color: #fff;

        font-family: "Playfair Display", Georgia, serif;

        font-size: 27px;

        font-weight: 600;

        line-height: 1.2;

    }



    .ks-team-card__designation {

        margin-top: 9px;

        color: #D8B9F5;

        font-size: 11px;

        line-height: 1.6;

    }



    .ks-team-card__separator {

        width: 40px;

        height: 2px;

        margin: 22px 0;

        background: #C391EE;

    }



    .ks-team-card__bio {

        color: rgba(255, 255, 255, .74);

        font-size: 12px;

        line-height: 1.9;

    }



    .ks-team-card__back-button {

        display: flex;

        width: 100%;

        align-items: center;

        justify-content: space-between;

        gap: 12px;

        padding: 14px 0 2px;

        border: 0;

        border-top: 1px solid rgba(255, 255, 255, .17);

        background: transparent;

        color: #D8B9F5;

        font-size: 10px;

        font-weight: 600;

        letter-spacing: .04em;

        cursor: pointer;

        transition: color .2s ease;

    }



    .ks-team-card__back-button:hover {

        color: #fff;

    }



    .ks-team-card button:focus-visible {

        outline: 2px solid #C391EE;

        outline-offset: 4px;

    }



    @media (max-width: 640px) {

        .ks-team-card {

            height: 470px;

        }



        .ks-team-card__image-wrap {

            height: 330px;

        }



        .ks-team-card__back {

            padding: 24px;

        }

    }



    @media (prefers-reduced-motion: reduce) {



        .ks-team-card__inner,

        .ks-team-card__image,

        .ks-team-card__flip-button {

            transition-duration: .01ms !important;

        }

    }
    /* =========================================================
   TEAM PORTRAITS — CIRCULAR PREMIUM DESIGN
   Safe override: preserves card flip functionality
========================================================= */

.ks-team-card__image-wrap {
    position: relative !important;

    height: 270px !important;
    flex-shrink: 0 !important;

    display: flex !important;
    align-items: center !important;
    justify-content: center !important;

    overflow: hidden !important;

    background:
        radial-gradient(
            circle,
            rgba(195, 145, 238, 0.12) 0%,
            rgba(195, 145, 238, 0.04) 55%,
            transparent 75%
        ) !important;
}

/* Circular outer frame */
.ks-team-card__image-wrap::before {
    content: "" !important;

    position: absolute !important;

    width: 224px !important;
    height: 224px !important;

    border-radius: 50% !important;

    background: linear-gradient(
        145deg,
        #ffffff,
        #f5effb
    ) !important;

    border: 1px solid rgba(195, 145, 238, 0.45) !important;

    box-shadow:
        0 0 0 7px rgba(195, 145, 238, 0.09),
        0 15px 35px rgba(36, 16, 79, 0.09) !important;

    z-index: 0 !important;
}

/* Actual portrait */
.ks-team-card__image {
    position: relative !important;
    z-index: 1 !important;

    display: block !important;

    width: 214px !important;
    height: 214px !important;

    flex-shrink: 0 !important;

    border-radius: 50% !important;

    object-fit: contain !important;
    object-position: center center !important;

    background: transparent !important;

    padding: 5px !important;

    transform: none !important;

    transition:
        transform 500ms ease,
        filter 500ms ease !important;
}

/* Prevent previous zoom effect */
.ks-team-card:hover .ks-team-card__image {
    transform: scale(1.025) !important;
}

/* Keep portrait image itself circular */
.ks-team-card__image {
    clip-path: circle(50%) !important;
}

/* Remove old image gradient */
.ks-team-card__image-overlay {
    display: none !important;
}

/* Keep number badge above portrait */
.ks-team-card__number {
    position: absolute !important;

    top: 18px !important;
    left: 18px !important;

    z-index: 5 !important;

    width: 35px !important;
    height: 35px !important;

    display: flex !important;
    align-items: center !important;
    justify-content: center !important;

    border-radius: 50% !important;

    background: #24104f !important;
    color: #ffffff !important;

    border: 2px solid #ffffff !important;

    box-shadow:
        0 5px 15px rgba(36, 16, 79, 0.18) !important;
}

/* Front card */
.ks-team-card__front {
    background: #ffffff !important;
}

/* Refined card border */
.ks-team-card__face {
    border-color: rgba(195, 145, 238, 0.20) !important;
}

/* Portrait area on mobile */
@media (max-width: 640px) {

    .ks-team-card__image-wrap {
        height: 290px !important;
    }

    .ks-team-card__image-wrap::before {
        width: 210px !important;
        height: 210px !important;
    }

    .ks-team-card__image {
        width: 200px !important;
        height: 200px !important;
    }
}

/* Respect reduced motion */
@media (prefers-reduced-motion: reduce) {

    .ks-team-card__image {
        transition: none !important;
    }

    .ks-team-card:hover .ks-team-card__image {
        transform: none !important;
    }
}
</style>
<!-- =========================================================*
  TEAM CARD INTERACTION
*========================================================= -->



<script>
    (() => {

        'use strict';



        const cards = document.querySelectorAll('[data-team-card]');



        cards.forEach((card) => {

            const buttons = card.querySelectorAll('[data-team-flip]');



            buttons.forEach((button) => {

                button.addEventListener('click', () => {

                    const flipped = card.classList.toggle('is-flipped');



                    card.querySelectorAll('[data-team-flip]').forEach((control) => {

                        control.setAttribute('aria-pressed', String(flipped));

                    });

                });

            });

        });

    })();
</script>


<!-- NEWSLETTER + CONSULTATION -->
<section
    class="ks-newsletter relative isolate overflow-hidden bg-[#f8f6fb] py-16 md:py-20 lg:py-24"
    id="newsletter"
>

    <div class="site-container">

        <!-- SECTION HEADING -->
        <div class="mx-auto mb-12 max-w-3xl text-center md:mb-14">

            <span class="ks-kicker inline-flex items-center justify-center gap-3 text-[10px] font-semibold tracking-[0.22em] text-[#9870bd]">

                <span class="h-px w-8 bg-[#C391EE]"></span>

                04 / CONNECT WITH K&amp;S

                <span class="h-px w-8 bg-[#C391EE]"></span>

            </span>

            <h2
                class="mt-6 text-4xl font-semibold leading-[1.1] tracking-[-0.045em] text-[#211c29] sm:text-5xl lg:text-6xl"
            >
                Stay informed.
                <span class="block text-[#9870bd]">Stay connected.</span>
            </h2>

            <p class="mx-auto mt-6 max-w-2xl text-sm leading-7 text-[#77717e] md:text-base">
                Follow our legal insights or take the next step towards
                a professional consultation.
            </p>

        </div>


        <!-- TWO-PANEL LAYOUT -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">


            <!-- =====================================================
                 LEFT PANEL — NEWSLETTER
            ====================================================== -->

            <div
                class="ks-newsletter-panel relative flex h-full flex-col overflow-hidden rounded-[28px] border border-[#C391EE]/20 bg-[#24104f] shadow-[0_25px_70px_rgba(36,16,79,0.12)]"
            >

                <!-- Decorative background -->
                <div
                    class="pointer-events-none absolute -right-24 -top-32 h-80 w-80 rounded-full border border-white/[0.07]"
                ></div>

                <div
                    class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full border border-white/[0.07]"
                ></div>

                <div
                    class="pointer-events-none absolute -bottom-40 -left-24 h-80 w-80 rounded-full bg-[#C391EE]/[0.12] blur-3xl"
                ></div>

                <!-- Accent -->
                <div
                    class="absolute left-0 right-0 top-0 h-1 bg-gradient-to-r from-transparent via-[#C391EE] to-transparent"
                ></div>


                <div class="relative z-10 flex flex-1 flex-col p-7 sm:p-9 lg:p-10">

                    <!-- Label -->
                    <span class="inline-flex items-center gap-2 text-[10px] font-semibold tracking-[0.22em] text-[#d8b9f5]">

                        <span class="h-1.5 w-1.5 rounded-full bg-[#C391EE]"></span>

                        THE K&amp;S EDITORIAL

                    </span>


                    <!-- Heading -->
                    <h3
                        style="font-family: 'Poppins', sans-serif !important;"
                        class="mt-7 text-3xl font-semibold leading-[1.2] tracking-[-0.035em] text-white sm:text-4xl"
                    >
                        Insight that
                        <span class="block text-[#cda5f1]">
                            keeps you ahead.
                        </span>
                    </h3>


                    <!-- Description -->
                    <p class="mt-5 max-w-md text-sm leading-8 text-white/65">
                        Stay connected with selected legal developments,
                        thoughtful perspectives and updates from our journal.
                    </p>


                    <!-- Newsletter form -->
                    <div class="mt-8">

                        <div class="mb-5 flex items-center gap-3">

                            <!-- Email icon -->
                            <span
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-[#C391EE]/30 bg-[#C391EE]/[0.12] text-[#d8b9f5]"
                            >

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="20"
                                    height="20"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                    <path d="m3 7 9 6 9-6"></path>
                                </svg>

                            </span>


                            <div>

                                <h4
                                    style="font-family: 'Poppins', sans-serif !important;"
                                    class="text-xl font-semibold tracking-tight text-white"
                                >
                                    Join the briefing.
                                </h4>

                                <p class="mt-1 text-xs leading-6 text-white/50">
                                    Selected editorial updates.
                                </p>

                            </div>

                        </div>


                        <!-- SUBSCRIPTION FORM -->
                        <form
                            method="post"
                            action="<?= e(url('newsletter/subscribe.php')) ?>"
                            class="flex flex-col gap-3"
                        >

                            <?= csrf_field() ?>


                            <label
                                class="sr-only"
                                for="newsletter-email"
                            >
                                Email address
                            </label>


                            <input
                                id="newsletter-email"
                                type="email"
                                name="email"
                                maxlength="254"
                                autocomplete="email"
                                placeholder="Enter your email address"
                                required
                                class="h-14 w-full rounded-xl border border-white/[0.14] bg-white px-5 text-sm text-[#211c29] outline-none transition-all duration-300 placeholder:text-[#a49da9] focus:border-[#C391EE] focus:ring-4 focus:ring-[#C391EE]/[0.15]"
                            >


                            <button
                                type="submit"
                                class="group inline-flex h-14 w-full items-center justify-center gap-3 rounded-xl bg-[#C391EE] px-7 text-xs font-semibold tracking-wide text-[#24104f] shadow-[0_8px_25px_rgba(195,145,238,0.18)] transition-all duration-300 hover:-translate-y-0.5 hover:bg-white hover:shadow-[0_12px_30px_rgba(0,0,0,0.15)]"
                            >

                                Subscribe to the briefing

                                <span
                                    class="text-base transition-transform duration-300 group-hover:translate-x-1"
                                    aria-hidden="true"
                                >
                                    ↗
                                </span>

                            </button>

                        </form>


                        <!-- Disclosure -->
                        <div class="mt-5 flex items-start gap-2.5 border-t border-white/[0.10] pt-4">

                            <svg
                                class="mt-1 shrink-0 text-[#C391EE]"
                                xmlns="http://www.w3.org/2000/svg"
                                width="15"
                                height="15"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M12 16v-4"></path>
                                <path d="M12 8h.01"></path>
                            </svg>


                            <small class="text-[11px] leading-6 text-white/45">
                                We record your request as pending.
                                Email delivery and automated confirmation
                                are not currently enabled.
                            </small>

                        </div>

                    </div>


                    <!-- Branding -->
                    <div class="mt-auto border-t border-white/[0.12] pt-7">

                        <span
                            style="font-family: 'Poppins', sans-serif !important;"
                            class="text-4xl font-semibold tracking-tight text-white/[0.13]"
                        >
                            K&amp;S
                        </span>

                        <p class="mt-1 text-[9px] font-medium tracking-[0.2em] text-white/40">
                            DOMUS LEGALIS
                        </p>

                    </div>

                </div>


                <!-- Decorative legal symbol -->
                <span
                    class="pointer-events-none absolute bottom-3 right-5 text-8xl font-semibold leading-none text-white/[0.035]"
                    style="font-family: 'Poppins', sans-serif !important;"
                    aria-hidden="true"
                >
                    §
                </span>

            </div>


            <!-- =====================================================
                 RIGHT PANEL — BOOK A CONSULTATION
            ====================================================== -->

            <div
                class="ks-consultation-panel relative flex h-full flex-col overflow-hidden rounded-[28px] border border-[#C391EE]/20 bg-white shadow-[0_25px_70px_rgba(36,16,79,0.08)]"
            >

                <!-- Decorative background -->
                <div
                    class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full bg-[#C391EE]/[0.08] blur-3xl"
                ></div>

                <div
                    class="pointer-events-none absolute -right-10 top-10 h-52 w-52 rounded-full border border-[#24104f]/[0.05]"
                ></div>

                <div
                    class="pointer-events-none absolute -right-2 top-20 h-36 w-36 rounded-full border border-[#24104f]/[0.05]"
                ></div>


                <!-- Accent -->
                <div
                    class="absolute left-0 right-0 top-0 h-1 bg-gradient-to-r from-transparent via-[#24104f] to-transparent"
                ></div>


                <div class="relative z-10 flex flex-1 flex-col p-7 sm:p-9 lg:p-10">


                    <!-- Label -->
                    <span class="inline-flex items-center gap-2 text-[10px] font-semibold tracking-[0.22em] text-[#9870bd]">

                        <span class="h-1.5 w-1.5 rounded-full bg-[#24104f]"></span>

                        PERSONAL LEGAL GUIDANCE

                    </span>


                    <!-- Icon -->
                    <div
                        class="mt-7 flex h-14 w-14 items-center justify-center rounded-2xl border border-[#C391EE]/25 bg-[#f7f2fc] text-[#24104f]"
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="25"
                            height="25"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.6"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M12 3v18"></path>
                            <path d="M5 7h14"></path>
                            <path d="m5 7-3 6h6L5 7Z"></path>
                            <path d="m19 7-3 6h6l-3-6Z"></path>
                            <path d="M8 21h8"></path>
                        </svg>

                    </div>


                    <!-- Heading -->
                    <h3
                        style="font-family: 'Poppins', sans-serif !important;"
                        class="mt-7 text-3xl font-semibold leading-[1.2] tracking-[-0.035em] text-[#24104f] sm:text-4xl"
                    >
                        Need clarity
                        <span class="block text-[#9870bd]">
                            on your next step?
                        </span>
                    </h3>


                    <!-- Description -->
                    <p class="mt-5 max-w-md text-sm leading-8 text-[#77717e]">
                        Speak with our team about your legal concerns,
                        understand your options and take the next step
                        with greater confidence.
                    </p>


                    <!-- Benefits -->
                    <div class="mt-7 space-y-4">

                        <div class="flex items-start gap-3">

                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#f2eafb] text-[#9870bd]">

                                <svg
                                    width="14"
                                    height="14"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path d="m5 12 4 4L19 6"></path>
                                </svg>

                            </span>

                            <p class="text-sm leading-7 text-[#625b6d]">
                                Discuss your matter with our team.
                            </p>

                        </div>


                        <div class="flex items-start gap-3">

                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#f2eafb] text-[#9870bd]">

                                <svg
                                    width="14"
                                    height="14"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path d="m5 12 4 4L19 6"></path>
                                </svg>

                            </span>

                            <p class="text-sm leading-7 text-[#625b6d]">
                                Explore the appropriate legal approach.
                            </p>

                        </div>


                        <div class="flex items-start gap-3">

                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#f2eafb] text-[#9870bd]">

                                <svg
                                    width="14"
                                    height="14"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path d="m5 12 4 4L19 6"></path>
                                </svg>

                            </span>

                            <p class="text-sm leading-7 text-[#625b6d]">
                                Understand the consultation process.
                            </p>

                        </div>

                    </div>


                    <!-- CONSULTATION BUTTON -->
                    <div class="mt-8">

                        <button
                            type="button"
                            data-consultation-open
                            class="group inline-flex min-h-[58px] w-full items-center justify-center gap-3 rounded-xl bg-[#24104f] px-7 py-4 text-sm font-semibold tracking-wide text-white shadow-[0_12px_30px_rgba(36,16,79,0.15)] transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#39206b] hover:shadow-[0_18px_35px_rgba(36,16,79,0.22)] focus:outline-none focus:ring-4 focus:ring-[#C391EE]/30"
                        >

                            Book a Consultation

                            <span
                                class="text-lg transition-transform duration-300 group-hover:translate-x-1 group-hover:-translate-y-0.5"
                                aria-hidden="true"
                            >
                                ↗
                            </span>

                        </button>


                        <p class="mt-4 text-center text-[11px] leading-6 text-[#958b9e]">
                            Begin with a conversation. Take your next step with clarity.
                        </p>

                    </div>


                    <!-- Bottom branding -->
                    <div class="mt-auto border-t border-[#24104f]/10 pt-7">

                        <div class="flex items-center justify-between gap-4">

                            <div>

                                <p class="text-[9px] font-semibold tracking-[0.2em] text-[#9870bd]">
                                    K&amp;S DOMUS LEGALIS
                                </p>

                                <p class="mt-2 text-xs text-[#77717e]">
                                    Where law meets strategy.
                                </p>

                            </div>


                            <span
                                class="text-5xl font-semibold text-[#24104f]/[0.06]"
                                style="font-family: 'Poppins', sans-serif !important;"
                                aria-hidden="true"
                            >
                                §
                            </span>

                        </div>

                    </div>

                </div>

            </div>


        </div>

    </div>

</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
