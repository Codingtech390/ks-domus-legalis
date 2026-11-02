<?php



// declare(strict_types=1);



require_once __DIR__ . '/config/database.php';

require_once __DIR__ . '/includes/article_card.php';



$pageTitle = 'Legal News, Court Judgments & Analysis';

$pageDescription = 'Stay informed with legal news, court judgments, legislation and expert legal analysis.';



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

    'SELECT id, name, slug, description FROM categories ORDER BY name ASC'

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

     WHERE $whereSql"

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

     LIMIT :limit OFFSET :offset"

);



foreach ($params as $key => $value) {

    $listStatement->bindValue(':' . $key, $value, PDO::PARAM_STR);
}



$listStatement->bindValue(':limit', $perPage, PDO::PARAM_INT);

$listStatement->bindValue(':offset', $offset, PDO::PARAM_INT);

$listStatement->execute();



$articles = $listStatement->fetchAll();



$displayTotal = $totalArticles;



require_once __DIR__ . '/includes/header.php';
?>
<?php require __DIR__ . '/includes/home_carousel.php'; ?>

<!-- JOURNAL COVERAGE -->

<?php
$coverageImages = [
    'corporate-law' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=900&q=85',
    'corporate' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=900&q=85',
    'litigation' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=900&q=85',
    'dispute-resolution' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=900&q=85',
    'intellectual-property' => 'https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=900&q=85',
    'technology' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=900&q=85',
    'real-estate' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=900&q=85',
    'employment' => 'https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=900&q=85',
    'taxation' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=900&q=85',
    'banking' => 'https://images.unsplash.com/photo-1444653614773-995cb1ef9efa?auto=format&fit=crop&w=900&q=85',
    'environment' => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=900&q=85',
];

$defaultCoverageImage = 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=900&q=85';
?>

<!-- JOURNAL COVERAGE -->

<?php
$coverageImages = [
    'corporate-law' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=900&q=85',
    'corporate' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=900&q=85',
    'litigation' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=900&q=85',
    'dispute-resolution' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=900&q=85',
    'intellectual-property' => 'https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=900&q=85',
    'technology' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=900&q=85',
    'real-estate' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=900&q=85',
    'employment' => 'https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=900&q=85',
    'taxation' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=900&q=85',
    'banking' => 'https://images.unsplash.com/photo-1444653614773-995cb1ef9efa?auto=format&fit=crop&w=900&q=85',
    'environment' => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=900&q=85',
];

$defaultCoverageImage = 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=900&q=85';
?>

<!-- JOURNAL COVERAGE -->
<section class="ks-section ks-practices relative overflow-hidden py-20 md:py-28 lg:py-32" id="practice-areas">

    <div class="site-container">

        <!-- SECTION INTRO -->
        <div class="mx-auto mb-14 max-w-3xl text-center md:mb-16">

            <span class="ks-kicker inline-flex items-center justify-center gap-3">
                <span class="h-px w-8 bg-[#C391EE]"></span>
                01 / COVERAGE AREAS
                <span class="h-px w-8 bg-[#C391EE]"></span>
            </span>

            <h2 class="mt-6 text-4xl font-semibold leading-[1.12] tracking-[-0.045em] text-[#211c29] sm:text-5xl lg:text-6xl">
                Legal developments,
                <br>
                <span class="text-[#9870bd]">clearly in context.</span>
            </h2>

            <p class="mx-auto mt-6 max-w-xl text-sm leading-8 text-[#77717e] md:text-base">
                Explore the evolving legal landscape through our carefully curated coverage of the subjects that shape business, policy and society.
            </p>

        </div>

        <!-- COVERAGE GRID -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3 xl:gap-7">

            <?php foreach ($allCategories as $index => $coverageCategory): ?>

                <?php
                $slug = strtolower(trim($coverageCategory['slug'] ?? ''));
                $categoryName = strtolower(trim($coverageCategory['name'] ?? ''));

                $categoryImage = $coverageCategory['image'] ?? '';

                if (!$categoryImage) {
                    $categoryImage = $coverageImages[$slug] ?? null;
                }

                if (!$categoryImage) {
                    foreach ($coverageImages as $keyword => $image) {
                        if (
                            str_contains($slug, $keyword) ||
                            str_contains($categoryName, str_replace('-', ' ', $keyword))
                        ) {
                            $categoryImage = $image;
                            break;
                        }
                    }
                }

                $categoryImage = $categoryImage ?: $defaultCoverageImage;
                ?>

                <a
                    href="<?= e(url('index.php?' . http_build_query(['category' => $coverageCategory['slug']]) . '#latest-news')) ?>"
                    class="group relative isolate flex min-h-[390px] flex-col overflow-hidden rounded-[4px] bg-[#211c29] text-white no-underline shadow-[0_8px_30px_rgba(25,16,38,0.08)] transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_25px_60px_rgba(25,16,38,0.20)] sm:min-h-[430px] lg:min-h-[460px]">

                    <!-- BACKGROUND IMAGE -->
                    <img
                        src="<?= e($categoryImage) ?>"
                        alt="<?= e($coverageCategory['name']) ?>"
                        loading="lazy"
                        onerror="this.onerror=null;this.src='<?= e($defaultCoverageImage) ?>';"
                        class="absolute inset-0 -z-20 h-full w-full object-cover transition-transform duration-1000 ease-out group-hover:scale-110">

                    <!-- IMAGE OVERLAY -->
                    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-[#160e20]/45 via-[#160e20]/45 to-[#160e20]/90 transition-colors duration-500 group-hover:from-[#160e20]/55 group-hover:via-[#160e20]/55 group-hover:to-[#160e20]/95"></div>

                    <!-- TOP DETAILS -->
                    <div class="flex items-center justify-between px-6 pt-6 md:px-8 md:pt-8">

                        <span class="flex h-10 w-10 items-center justify-center rounded-full border border-white/40 bg-white/10 text-xs font-semibold tracking-widest text-white backdrop-blur-md">
                            <?= e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?>
                        </span>

                        <span class="text-[9px] font-semibold tracking-[0.24em] text-white/75">
                            K&amp;S JOURNAL
                        </span>

                    </div>

                    <!-- CENTERED CONTENT -->
                    <div class="flex flex-1 flex-col items-center justify-center px-7 py-10 text-center md:px-9">

                        <span class="mb-5 h-px w-10 bg-[#C391EE] transition-all duration-500 group-hover:w-20"></span>

                        <h3 class="max-w-sm text-2xl font-semibold leading-tight tracking-[-0.035em] text-white transition-transform duration-500 group-hover:-translate-y-1 sm:text-3xl lg:text-[32px]">
                            <?= e($coverageCategory['name']) ?>
                        </h3>

                        <p class="mt-5 max-w-[290px] text-[13px] leading-7 text-white/80 transition-colors duration-500 group-hover:text-white">
                            <?= e(
                                $coverageCategory['description']
                                    ?: 'Explore the latest legal developments, insights and analysis in this area.'
                            ) ?>
                        </p>

                        <span class="mt-7 inline-flex items-center gap-3 text-[9px] font-semibold tracking-[0.2em] text-white/90">
                            EXPLORE THIS AREA
                            <span class="flex h-8 w-8 items-center justify-center rounded-full border border-white/50 text-base transition-all duration-300 group-hover:rotate-45 group-hover:border-[#C391EE] group-hover:bg-[#C391EE]">
                                ↗
                            </span>
                        </span>

                    </div>

                    <!-- BOTTOM ACCENT -->
                    <div class="h-[3px] w-0 bg-[#C391EE] transition-all duration-700 group-hover:w-full"></div>

                </a>

            <?php endforeach; ?>

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
                    <span class="font-semibold text-[#211c29]"><?= number_format($displayTotal) ?></span>
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
                        <?= $activeCategory === '' && $searchQuery === '' ? 'aria-current="page"' : '' ?>>
                        All articles
                    </a>

                    <?php foreach ($allCategories as $category): ?>

                        <a
                            href="<?= e(url('index.php?' . http_build_query(['category' => $category['slug']]) . '#latest-news')) ?>"
                            class="<?= $activeCategory === $category['slug']
                                        ? 'border-[#C391EE] bg-[#C391EE] text-white shadow-[0_5px_15px_rgba(195,145,238,0.22)]'
                                        : 'border-black/[0.10] bg-white text-[#5e5865] hover:border-[#C391EE] hover:text-[#9866c5]' ?>
                                inline-flex items-center rounded-full border px-4 py-2.5 text-xs font-semibold transition-all duration-300"
                            <?= $activeCategory === $category['slug'] ? 'aria-current="page"' : '' ?>>
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
                            href="<?= e(url('index.php?' . http_build_query($pageParams + ['page' => $page - 1]) . '#latest-news')) ?>"
                            class="inline-flex h-11 items-center gap-2 rounded-full border border-black/10 px-5 text-xs font-semibold text-[#5e5865] transition-all hover:border-[#C391EE] hover:bg-[#f6f2fa] hover:text-[#9866c5]">
                            ← Previous
                        </a>

                    <?php endif; ?>


                    <?php for ($pageNumber = $startPage; $pageNumber <= $endPage; $pageNumber++): ?>

                        <?php
                        $pageUrl = url(
                            'index.php?' .
                                http_build_query($pageParams + ['page' => $pageNumber]) .
                                '#latest-news'
                        );
                        ?>

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
                            href="<?= e(url('index.php?' . http_build_query($pageParams + ['page' => $page + 1]) . '#latest-news')) ?>"
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


<!-- WHY CHOOSE US -->
<section class="ks-section relative overflow-hidden bg-[#f8f6fb] py-20 md:py-28 lg:py-32" id="why-choose-us">

    <div class="site-container">

        <!-- CENTERED SECTION HEADING -->
        <div class="mx-auto mb-14 max-w-3xl text-center md:mb-16">

            <span class="ks-kicker inline-flex items-center justify-center gap-3">
                <span class="h-px w-8 bg-[#C391EE]"></span>
                03 / OUR APPROACH
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
                        Understanding the law starts with understanding its context.
                    </h3>

                    <p class="mt-6 max-w-md text-sm leading-8 text-white/65">
                        We look beyond headlines to explore the reasoning, implications and wider significance of legal developments.
                    </p>

                </div>

                <div class="relative z-10 mt-12 flex items-end justify-between border-t border-white/[0.15] pt-7">

                    <div>
                        <p class="text-[10px] font-semibold tracking-[0.2em] text-white/45">
                            OUR EDITORIAL PRINCIPLE
                        </p>
                        <p class="mt-2 text-sm font-medium text-white/90">
                            Clarity. Context. Responsibility.
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
                            Context before conclusion
                        </h3>
                        <p class="mt-2 max-w-lg text-sm leading-7 text-[#77717e]">
                            We connect decisions and developments to the legal questions behind them.
                        </p>
                    </div>

                    <span class="hidden shrink-0 self-center text-xl text-[#C391EE] transition-transform duration-300 group-hover:translate-x-1 sm:block" aria-hidden="true">↗</span>

                </div>


                <!-- ITEM 02 -->
                <div class="group flex gap-5 border-b border-[#211c29]/[0.10] py-7 sm:gap-7">

                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-[#C391EE]/40 bg-white text-xs font-semibold text-[#9870bd] transition-all duration-300 group-hover:border-[#C391EE] group-hover:bg-[#C391EE] group-hover:text-white">
                        02
                    </span>

                    <div class="min-w-0 flex-1">
                        <h3 class="text-lg font-semibold tracking-tight text-[#211c29] transition-colors duration-300 group-hover:text-[#9870bd] sm:text-xl">
                            Clear explanations
                        </h3>
                        <p class="mt-2 max-w-lg text-sm leading-7 text-[#77717e]">
                            We make complex subjects easier to follow while retaining the details that matter.
                        </p>
                    </div>

                    <span class="hidden shrink-0 self-center text-xl text-[#C391EE] transition-transform duration-300 group-hover:translate-x-1 sm:block" aria-hidden="true">↗</span>

                </div>


                <!-- ITEM 03 -->
                <div class="group flex gap-5 border-b border-[#211c29]/[0.10] py-7 sm:gap-7">

                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-[#C391EE]/40 bg-white text-xs font-semibold text-[#9870bd] transition-all duration-300 group-hover:border-[#C391EE] group-hover:bg-[#C391EE] group-hover:text-white">
                        03
                    </span>

                    <div class="min-w-0 flex-1">
                        <h3 class="text-lg font-semibold tracking-tight text-[#211c29] transition-colors duration-300 group-hover:text-[#9870bd] sm:text-xl">
                            Editorial perspective
                        </h3>
                        <p class="mt-2 max-w-lg text-sm leading-7 text-[#77717e]">
                            Coverage places judgments, legislation and policy within a broader legal framework.
                        </p>
                    </div>

                    <span class="hidden shrink-0 self-center text-xl text-[#C391EE] transition-transform duration-300 group-hover:translate-x-1 sm:block" aria-hidden="true">↗</span>

                </div>


                <!-- ITEM 04 -->
                <div class="group flex gap-5 border-b border-[#211c29]/[0.10] py-7 sm:gap-7">

                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-[#C391EE]/40 bg-white text-xs font-semibold text-[#9870bd] transition-all duration-300 group-hover:border-[#C391EE] group-hover:bg-[#C391EE] group-hover:text-white">
                        04
                    </span>

                    <div class="min-w-0 flex-1">
                        <h3 class="text-lg font-semibold tracking-tight text-[#211c29] transition-colors duration-300 group-hover:text-[#9870bd] sm:text-xl">
                            Care with the record
                        </h3>
                        <p class="mt-2 max-w-lg text-sm leading-7 text-[#77717e]">
                            Accuracy and responsibility guide how legal information is presented.
                        </p>
                    </div>

                    <span class="hidden shrink-0 self-center text-xl text-[#C391EE] transition-transform duration-300 group-hover:translate-x-1 sm:block" aria-hidden="true">↗</span>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- NEWSLETTER -->

<!-- NEWSLETTER -->
<section class="ks-newsletter relative isolate overflow-hidden bg-[#f8f6fb] py-20 md:py-28 lg:py-32" id="newsletter">

    <div class="site-container">

        <!-- CENTERED SECTION HEADING -->
        <div class="mx-auto mb-14 max-w-3xl text-center md:mb-16">

            <span class="ks-kicker inline-flex items-center justify-center gap-3 text-[10px] font-semibold tracking-[0.22em] text-[#e34b50]">
                <span class="h-px w-8 bg-[#C391EE]"></span>
                04 / THE K&amp;S BRIEFING
                <span class="h-px w-8 bg-[#C391EE]"></span>
            </span>

            <h2
                
                class="mt-8 text-4xl font-semibold leading-[1.02] tracking-[-0.055em] text-[#211c29] sm:text-5xl lg:text-[60px]">
                Stay informed.
                <span class="block text-[#9870bd]">Stay ahead.</span>
            </h2>

            <p class="mx-auto mt-7 max-w-2xl text-sm leading-7 text-[#77717e] md:text-base">
                Register your interest in selected legal insights and updates. Submissions are recorded for editorial review; email delivery is not currently enabled.
            </p>

        </div>


        <!-- NEWSLETTER PANEL -->
        <div class="relative mx-auto max-w-5xl overflow-hidden rounded-[30px] border border-[#C391EE]/20 bg-[#24104f] shadow-[0_25px_80px_rgba(36,16,79,0.16)]">

            <!-- DECORATIVE BACKGROUND -->
            <div class="pointer-events-none absolute -right-24 -top-32 h-96 w-96 rounded-full border border-white/[0.07]"></div>
            <div class="pointer-events-none absolute -right-10 -top-16 h-64 w-64 rounded-full border border-white/[0.07]"></div>
            <div class="pointer-events-none absolute -bottom-40 -left-24 h-96 w-96 rounded-full bg-[#C391EE]/[0.12] blur-3xl"></div>

            <!-- TOP ACCENT -->
            <div class="absolute left-0 right-0 top-0 h-1 bg-gradient-to-r from-transparent via-[#C391EE] to-transparent"></div>

            <div class="relative z-10 grid grid-cols-1 lg:grid-cols-[0.85fr_1.15fr]">

                <!-- LEFT EDITORIAL PANEL -->
                <div class="flex flex-col justify-between border-b border-white/[0.10] p-8 sm:p-10 lg:border-b-0 lg:border-r lg:p-12">

                    <div>

                        <span class="inline-flex items-center gap-2 text-[10px] font-semibold tracking-[0.22em] text-[#d8b9f5]">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#C391EE]"></span>
                            THE K&amp;S EDITORIAL
                        </span>

                        <h3
                            style="font-family: 'Poppins', sans-serif !important;"
                            class="mt-8 text-3xl font-semibold leading-[1.2] tracking-[-0.035em] text-white sm:text-4xl">
                            Insight that
                            <span class="block text-[#cda5f1]">keeps you ahead.</span>
                        </h3>

                        <p class="mt-6 max-w-sm text-sm leading-8 text-white/60">
                            Stay connected with selected legal developments, thoughtful perspectives and updates from our journal.
                        </p>

                    </div>

                    <div class="mt-10 border-t border-white/[0.12] pt-6">

                        <span
                            style="font-family: 'Poppins', sans-serif !important;"
                            class="text-5xl font-semibold tracking-tight text-white/[0.12]">
                            K&amp;S
                        </span>

                        <p class="mt-2 text-[10px] font-medium tracking-[0.18em] text-white/40">
                            DOMUS LEGALIS
                        </p>

                    </div>

                </div>


                <!-- RIGHT SUBSCRIPTION FORM -->
                <div class="flex flex-col justify-center p-8 sm:p-10 lg:p-12">

                    <div class="mb-8">

                        <!-- EMAIL ICON -->
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl border border-[#C391EE]/30 bg-[#C391EE]/[0.12] text-[#d8b9f5]">

                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                <path d="m3 7 9 6 9-6"></path>
                            </svg>

                        </span>

                        <h3
                            style="font-family: 'Poppins', sans-serif !important;"
                            class="mt-6 text-2xl font-semibold tracking-tight text-white sm:text-3xl">
                            Join the briefing.
                        </h3>

                        <p class="mt-3 text-sm leading-7 text-white/55">
                            Register your email address for selected editorial updates.
                        </p>

                    </div>


                    <!-- SUBSCRIPTION FORM -->
                    <form
                        method="post"
                        action="<?= e(url('newsletter/subscribe.php')) ?>"
                        class="flex flex-col gap-3">

                        <?= csrf_field() ?>

                        <label class="sr-only" for="newsletter-email">Email address</label>

                        <input
                            id="newsletter-email"
                            type="email"
                            name="email"
                            maxlength="254"
                            autocomplete="email"
                            placeholder="Enter your email address"
                            required
                            class="h-14 w-full rounded-xl border border-white/[0.14] bg-white px-5 text-sm text-[#211c29] outline-none transition-all duration-300 placeholder:text-[#a49da9] focus:border-[#C391EE] focus:ring-4 focus:ring-[#C391EE]/[0.15]">

                        <button
                            type="submit"
                            class="group inline-flex h-14 w-full items-center justify-center gap-3 rounded-xl bg-[#C391EE] px-7 text-xs font-semibold tracking-wide text-[#24104f] shadow-[0_8px_25px_rgba(195,145,238,0.18)] transition-all duration-300 hover:-translate-y-0.5 hover:bg-white hover:shadow-[0_12px_30px_rgba(0,0,0,0.15)]">

                            Subscribe to the briefing

                            <span class="text-base transition-transform duration-300 group-hover:translate-x-1" aria-hidden="true">↗</span>

                        </button>

                    </form>


                    <!-- DISCLOSURE -->
                    <div class="mt-6 flex items-start gap-2.5 border-t border-white/[0.10] pt-5">

                        <svg class="mt-0.5 shrink-0 text-[#C391EE]" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 16v-4"></path>
                            <path d="M12 8h.01"></path>
                        </svg>

                        <small class="text-[11px] leading-6 text-white/45">
                            We record your request as pending. Email delivery and automated confirmation are not currently enabled.
                        </small>

                    </div>

                </div>

            </div>

            <!-- DECORATIVE LEGAL SYMBOL -->
            <span
                class="pointer-events-none absolute bottom-3 right-6 text-8xl font-semibold leading-none text-white/[0.035]"
                style="font-family: 'Poppins', sans-serif !important;"
                aria-hidden="true">
                §
            </span>

        </div>

    </div>

</section>







<?php require_once __DIR__ . '/includes/footer.php'; ?>
