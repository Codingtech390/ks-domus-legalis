<?php

declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
if (!function_exists('article_card')) {
    function article_card(array $article): void
    {
        $articleUrl = url('post.php?slug=' . rawurlencode((string) $article['slug']));
        $publishedDate = $article['published_at'] ?? $article['created_at'] ?? null;
?>
        <article class="article-card reveal-on-scroll">
            <a href="<?= e($articleUrl) ?>" class="article-card-image-wrap" aria-label="Read <?= e($article['title']) ?>">
                <?php if (!empty($article['featured_image'])): ?><img src="<?= e(image_url($article['featured_image'])) ?>" alt="<?= e($article['title']) ?>" class="article-card-image" loading="lazy" decoding="async"><?php else: ?><div class="article-image-placeholder"><span>§</span><small>K&amp;S · LEGAL JOURNAL</small></div><?php endif; ?>
                <span class="image-arrow" aria-hidden="true">↗</span>
            </a>
            <div class="article-card-body">
                <div class="article-card-meta"><a href="<?= e(url('index.php?category=' . rawurlencode((string) $article['category_slug']))) ?>" class="category-kicker"><?= e($article['category_name']) ?></a><span><?= e(format_date($publishedDate)) ?></span></div>
                <h3><a href="<?= e($articleUrl) ?>"><?= e($article['title']) ?></a></h3>
                <p><?= e($article['excerpt']) ?></p>
                <div class="article-card-bottom"><span><?= e($article['author_name'] ?? 'K & S Editorial') ?></span><span><?= e((string) reading_time((string) ($article['content'] ?? ''))) ?> min read</span></div>
                <a class="article-read-link" href="<?= e($articleUrl) ?>">Read the analysis <span aria-hidden="true">↗</span></a>
            </div>
        </article>
<?php
    }
}
