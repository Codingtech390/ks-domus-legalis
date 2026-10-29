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

    'SELECT id, name, slug FROM categories ORDER BY name ASC'

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

<!-- PRACTICE AREAS -->
<section class="ks-section ks-practices" id="practice-areas">
    <div class="site-container">
        <div class="ks-section-intro">
            <span class="ks-kicker">01 / OUR PRACTICE AREAS</span>
            <h2>Understanding the law.<br><span>Exploring its impact.</span></h2>
            <p>Explore key legal disciplines, emerging developments and the issues shaping businesses, institutions and individuals.</p>
        </div>

        <?php
        // Illustrative practice areas. Confirm these reflect the firm's actual services before publishing.
        $practiceAreas = [
            ['01', 'Corporate & Commercial', 'Company law, commercial agreements and regulatory considerations.'],
            ['02', 'Dispute Resolution', 'Litigation, arbitration and alternative dispute resolution.'],
            ['03', 'Constitutional Law', 'Fundamental rights, constitutional interpretation and public law.'],
            ['04', 'Intellectual Property', 'Trademarks, copyrights and the protection of creative assets.'],
            ['05', 'Criminal Law', 'Criminal justice, procedure and important legal developments.'],
            ['06', 'Real Estate & Property', 'Property transactions, documentation and related disputes.'],
        ];
        ?>

        <div class="ks-practice-grid">
            <?php foreach ($practiceAreas as [$number, $name, $description]): ?>
                <article class="ks-practice-card">
                    <span class="ks-practice-number"><?= e($number) ?></span>
                    <div class="ks-practice-symbol" aria-hidden="true">§</div>
                    <h3><?= e($name) ?></h3>
                    <p><?= e($description) ?></p>
                    <span class="ks-practice-rule" aria-hidden="true"></span>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ADMIN-PUBLISHED ARTICLES -->
<section class="ks-section ks-insights" id="latest-news">
    <div class="site-container">
        <div class="ks-section-heading">
            <div>
                <span class="ks-kicker">02 / LEGAL JOURNAL</span>
                <h2>
                    <?php if ($searchQuery !== ''): ?>
                        Search <span>results.</span>
                    <?php elseif ($activeCategory !== ''): ?>
                        Perspectives on <span>this topic.</span>
                    <?php else: ?>
                        Insights &amp; <span>perspectives.</span>
                    <?php endif; ?>
                </h2>
                <p>
                    <?= number_format($displayTotal) ?>
                    article<?= $displayTotal === 1 ? '' : 's' ?> in the journal
                    <?php if ($searchQuery !== ''): ?>
                        for “<?= e($searchQuery) ?>”
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="ks-filter-row">
            <div class="ks-filter-links">
                <a
                    href="<?= e(url('index.php#latest-news')) ?>"
                    class="<?= $activeCategory === '' ? 'is-active' : '' ?>"
                    <?= $activeCategory === '' ? 'aria-current="page"' : '' ?>>All articles</a>

                <?php foreach ($allCategories as $category): ?>
                    <a
                        href="<?= e(url('index.php?' . http_build_query(['category' => $category['slug']]) . '#latest-news')) ?>"
                        class="<?= $activeCategory === $category['slug'] ? 'is-active' : '' ?>"
                        <?= $activeCategory === $category['slug'] ? 'aria-current="page"' : '' ?>><?= e($category['name']) ?></a>
                <?php endforeach; ?>
            </div>

            <form method="get" action="<?= e(url('index.php')) ?>" class="ks-article-search">
                <input
                    type="search"
                    name="q"
                    value="<?= e($searchQuery) ?>"
                    maxlength="150"
                    aria-label="Search articles"
                    placeholder="Search articles...">
                <?php if ($activeCategory !== ''): ?>
                    <input type="hidden" name="category" value="<?= e($activeCategory) ?>">
                <?php endif; ?>
                <button type="submit">Search ↗</button>
            </form>
        </div>

        <?php if ($articles !== []): ?>
            <div class="article-grid">
                <?php foreach ($articles as $article): ?>
                    <?php article_card($article); ?>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <?php
                $pageParams = [];

                if ($searchQuery !== '') {
                    $pageParams['q'] = $searchQuery;
                }

                if ($activeCategory !== '') {
                    $pageParams['category'] = $activeCategory;
                }
                ?>
                <nav class="pagination" aria-label="Article pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?= e(url('index.php?' . http_build_query($pageParams + ['page' => $page - 1]) . '#latest-news')) ?>">
                            ← Previous
                        </a>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);

                    for ($pageNumber = $startPage; $pageNumber <= $endPage; $pageNumber++):
                        $pageUrl = url(
                            'index.php?' .
                                http_build_query($pageParams + ['page' => $pageNumber]) .
                                '#latest-news'
                        );
                    ?>
                        <a
                            href="<?= e($pageUrl) ?>"
                            class="<?= $pageNumber === $page ? 'is-current' : '' ?>"
                            <?= $pageNumber === $page ? 'aria-current="page"' : '' ?>><?= e((string) $pageNumber) ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= e(url('index.php?' . http_build_query($pageParams + ['page' => $page + 1]) . '#latest-news')) ?>">
                            Next →
                        </a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <span>§</span>
                <h3>No articles found.</h3>
                <p>Try another search term or select a different legal topic.</p>
                <a class="button button-dark" href="<?= e(url('index.php#latest-news')) ?>">
                    View all coverage <span>↗</span>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- WHY CHOOSE US -->
<section class="ks-section ks-why" id="why-choose-us">
    <div class="site-container ks-why-layout">
        <div class="ks-why-left">
            <span class="ks-kicker">03 / OUR APPROACH</span>
            <h2>Legal thinking.<br><span>Human understanding.</span></h2>
            <p>Good legal work begins with careful listening, clear communication and attention to the details that matter.</p>
            <div class="ks-why-decoration" aria-hidden="true">§</div>
        </div>

        <div class="ks-why-right">
            <div class="ks-why-item">
                <span>01</span>
                <div>
                    <h3>Thoughtful analysis</h3>
                    <p>Considering legal questions carefully, with attention to their wider context and practical implications.</p>
                </div>
            </div>

            <div class="ks-why-item">
                <span>02</span>
                <div>
                    <h3>Clarity in communication</h3>
                    <p>Making complex legal subjects easier to understand without losing important detail.</p>
                </div>
            </div>

            <div class="ks-why-item">
                <span>03</span>
                <div>
                    <h3>Research-led perspective</h3>
                    <p>Placing developments, decisions and legislation within a broader legal framework.</p>
                </div>
            </div>

            <div class="ks-why-item">
                <span>04</span>
                <div>
                    <h3>Commitment to integrity</h3>
                    <p>Recognising the importance of accuracy, responsibility and professional ethics in legal communication.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- NEWSLETTER -->
<section class="ks-newsletter" id="newsletter">
    <div class="site-container ks-newsletter-inner">
        <div>
            <span class="ks-kicker">04 / THE K&amp;S BRIEFING</span>
            <h2>Stay informed.<br><span>Stay ahead.</span></h2>
            <p>Receive selected legal insights and updates in your inbox. Newsletter delivery requires a configured email service and confirmed subscription workflow.</p>
        </div>

        <div class="ks-newsletter-form-wrap">
            <p>Newsletter registration</p>
            <form method="post" action="<?= e(url('newsletter/subscribe.php')) ?>">
                <?= csrf_field() ?>
                <label class="sr-only" for="newsletter-email">Email address</label>
                <input
                    id="newsletter-email"
                    type="email"
                    name="email"
                    maxlength="254"
                    autocomplete="email"
                    placeholder="Your email address"
                    required>
                <button type="submit">Subscribe ↗</button>
            </form>
            <small>By submitting your email, you request information about the K&amp;S newsletter. You can withdraw your request at any time.</small>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
