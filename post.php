<?php



declare(strict_types=1);



require_once __DIR__ . '/config/database.php';

require_once __DIR__ . '/includes/content_sanitizer.php';

require_once __DIR__ . '/includes/article_card.php';



$slug = trim((string) ($_GET['slug'] ?? ''));



if ($slug === '' || strlen($slug) > 280) {

    http_response_code(404);

    $pageTitle = 'Article Not Found';

    $article = null;
} else {

    $statement = $pdo->prepare(

        "SELECT a.id, a.title, a.slug, a.excerpt, a.content,

                a.featured_image, a.published_at, a.created_at,

                c.id AS category_id, c.name AS category_name,

                c.slug AS category_slug, ad.name AS author_name

         FROM articles a

         INNER JOIN categories c ON c.id = a.category_id

         INNER JOIN admins ad ON ad.id = a.admin_id

         WHERE a.slug = :slug

           AND a.status = 'published'

           AND a.published_at <= NOW()

         LIMIT 1"

    );



    $statement->execute(['slug' => $slug]);

    $article = $statement->fetch() ?: null;



    if ($article === null) {

        http_response_code(404);

        $pageTitle = 'Article Not Found';
    } else {

        $pageTitle = $article['title'];
    }
}



$pageDescription = $article['excerpt'] ?? 'The requested legal article could not be found.';



$relatedArticles = [];



if ($article !== null) {

    $relatedStatement = $pdo->prepare(

        "SELECT a.id, a.title, a.slug, a.excerpt, a.featured_image,

                a.published_at, a.content, c.name AS category_name,

                c.slug AS category_slug, ad.name AS author_name

         FROM articles a

         INNER JOIN categories c ON c.id = a.category_id

         INNER JOIN admins ad ON ad.id = a.admin_id

         WHERE a.category_id = :category_id

           AND a.id <> :article_id

           AND a.status = 'published'

           AND a.published_at <= NOW()

         ORDER BY a.published_at DESC, a.id DESC

         LIMIT 3"

    );



    $relatedStatement->execute([

        'category_id' => (int) $article['category_id'],

        'article_id' => (int) $article['id'],

    ]);



    $relatedArticles = $relatedStatement->fetchAll();
}



require_once __DIR__ . '/includes/header.php';
?>
<?php if ($article === null): ?>
    <section class="not-found-page">
        <div class="site-container not-found-inner"><span class="not-found-number">404</span>
            <div class="eyebrow"><span class="eyebrow-line"></span> LOST IN THE RECORDS</div>
            <h1>This page could not<br><em>be found.</em></h1>
            <p>The article may have been removed, unpublished or moved to another location.</p><a href="<?= e(url('index.php')) ?>" class="button button-dark">Return to the journal <span>↗</span></a>
        </div>
    </section>
<?php else: $articleUrl = url('post.php?slug=' . rawurlencode((string) $article['slug'])); ?>
    <div class="article-breadcrumb">
        <div class="site-container"><a href="<?= e(url('index.php')) ?>">Journal</a><span>/</span><a href="<?= e(url('index.php?category=' . rawurlencode($article['category_slug']))) ?>"><?= e($article['category_name']) ?></a><span>/</span><span>Article</span></div>
    </div>
    <article class="article-detail">
        <div class="site-container article-detail-container">
            <header class="article-detail-header">
                <div class="eyebrow"><span class="eyebrow-line"></span><?= e($article['category_name']) ?></div>
                <h1><?= e($article['title']) ?></h1>
                <p class="article-deck"><?= e($article['excerpt']) ?></p>
                <div class="article-byline">
                    <div class="byline-avatar">K&amp;S</div>
                    <div class="byline-copy"><strong><?= e($article['author_name']) ?></strong><span>Published <?= e(format_date($article['published_at'])) ?> <i>·</i> <?= e((string) reading_time((string)$article['content'])) ?> min read</span></div>
                    <div class="article-share-top"><button type="button" data-share="copy" class="share-button">Copy link</button><a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode($articleUrl) ?>" target="_blank" rel="noopener noreferrer">LinkedIn ↗</a><a href="https://wa.me/?text=<?= rawurlencode($article['title'] . ' ' . $articleUrl) ?>" target="_blank" rel="noopener noreferrer">WhatsApp ↗</a></div>
                </div>
            </header>
            <?php if (!empty($article['featured_image'])): ?><figure class="article-cover"><img src="<?= e(image_url($article['featured_image'])) ?>" alt="<?= e($article['title']) ?>" fetchpriority="high"></figure><?php else: ?><div class="article-cover-placeholder"><span>§</span><small>K&amp;S · LEGAL JOURNAL</small></div><?php endif; ?>
            <div class="article-detail-columns">
                <div class="article-main-column">
                    <div class="article-prose" id="article-content"><?= sanitize_article_html((string)$article['content']) ?></div>
                    <div class="article-share-bottom"><span class="eyebrow">PASS IT ON</span>
                        <h3>Worth a second perspective?</h3>
                        <p>Share this briefing with your colleagues and professional network.</p>
                        <div class="share-actions"><button type="button" data-share="copy" class="button button-dark">Copy article link <span>↗</span></button><a href="mailto:?subject=<?= rawurlencode($article['title']) ?>&body=<?= rawurlencode($articleUrl) ?>" class="button button-outline">Share by email <span>↗</span></a></div>
                        <p id="share-status" class="share-status" role="status" aria-live="polite"></p>
                    </div>
                    <aside class="editorial-disclaimer"><strong>Editorial note</strong>
                        <p>This article is provided for general information and educational purposes. It is not a substitute for advice from a qualified legal professional on your specific circumstances.</p>
                    </aside>
                </div>
                <aside class="article-reading-rail">
                    <div class="reading-rail-card"><span>IN THIS BRIEFING</span>
                        <p>Read the complete report, review related coverage and share this article with your professional network.</p><a href="#related-articles">Continue reading <span>↓</span></a>
                        <div class="reading-progress"><i id="reading-progress-bar"></i></div><small>READING PROGRESS</small>
                    </div>
                </aside>
            </div>
        </div>
    </article>
    <section id="related-articles" class="related-section">
        <div class="site-container">
            <div class="section-heading">
                <div>
                    <div class="eyebrow"><span class="eyebrow-line"></span> CONTINUE EXPLORING</div>
                    <h2>More to <em>consider.</em></h2>
                </div><a href="<?= e(url('index.php?category=' . rawurlencode($article['category_slug']))) ?>" class="text-link-dark">More in <?= e($article['category_name']) ?> <span>↗</span></a>
            </div><?php if ($relatedArticles !== []): ?><div class="article-grid"><?php foreach ($relatedArticles as $related): ?><?php article_card($related); ?><?php endforeach; ?></div><?php else: ?><div class="empty-related">There are no other published articles in this category yet. <a href="<?= e(url('index.php')) ?>">Explore the journal →</a></div><?php endif; ?>
        </div>
    </section>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
