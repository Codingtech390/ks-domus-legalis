<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/content_sanitizer.php';
require_once __DIR__ . '/includes/article_card.php';

/*
|--------------------------------------------------------------------------
| ARTICLE LOOKUP
|--------------------------------------------------------------------------
*/

$slug = trim((string) ($_GET['slug'] ?? ''));

$article = null;

if ($slug === '' || strlen($slug) > 280) {
    http_response_code(404);
    $pageTitle = 'Article Not Found';
} else {
    $statement = $pdo->prepare(
        "SELECT
            a.id,
            a.title,
            a.slug,
            a.excerpt,
            a.content,
            a.featured_image,
            a.published_at,
            a.created_at,
            c.id AS category_id,
            c.name AS category_name,
            c.slug AS category_slug,
            ad.name AS author_name
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

/*
|--------------------------------------------------------------------------
| RELATED ARTICLES
|--------------------------------------------------------------------------
*/

$relatedArticles = [];

if ($article !== null) {
    $relatedStatement = $pdo->prepare(
        "SELECT
            a.id,
            a.title,
            a.slug,
            a.excerpt,
            a.featured_image,
            a.published_at,
            a.content,
            c.name AS category_name,
            c.slug AS category_slug,
            ad.name AS author_name
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

/*
|--------------------------------------------------------------------------
| ARTICLE METADATA
|--------------------------------------------------------------------------
*/

$articleUrl = '';

if ($article !== null) {
    $articleUrl = url(
        'post.php?slug=' . rawurlencode((string) $article['slug'])
    );
}

require_once __DIR__ . '/includes/header.php';

?>

<style>
    /* =========================================================
       ARTICLE PAGE — K&S DOMUS LEGALIS
       ========================================================= */

    .post-page {
        --post-purple: #24104f;
        --post-mauve: #9870bd;
        --post-lavender: #C391EE;
        --post-ink: #211c29;
        --post-muted: #77717e;
        --post-line: #eee7f4;
        --post-paper: #faf9fc;

        background: var(--post-paper);
        color: var(--post-ink);
        font-family: "Inter", sans-serif;
    }

    .post-page h1,
    .post-page h2,
    .post-page h3,
    .post-page h4,
    .post-page .post-heading-font {
        font-family: "Poppins", sans-serif;
    }

    /* Breadcrumb */

    .post-breadcrumb {
        border-bottom: 1px solid var(--post-line);
        background: #fff;
    }

    .post-breadcrumb-inner {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        min-height: 54px;
        font-size: 11px;
        color: #99919f;
    }

    .post-breadcrumb a {
        color: #77717e;
        transition: color .25s ease;
    }

    .post-breadcrumb a:hover {
        color: var(--post-mauve);
    }

    .post-breadcrumb .breadcrumb-current {
        color: var(--post-purple);
        font-weight: 700;
    }

    /* Article introduction */

    .post-intro {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at 90% 5%, rgba(195, 145, 238, .17), transparent 28%),
            linear-gradient(135deg, #f7f2fb 0%, #fcfaff 55%, #f3edf9 100%);
        padding: 85px 0 72px;
    }

    .post-intro::before {
        position: absolute;
        content: "";
        width: 450px;
        height: 450px;
        right: -190px;
        bottom: -270px;
        border: 1px solid rgba(152, 112, 189, .17);
        border-radius: 50%;
        pointer-events: none;
    }

    .post-intro::after {
        position: absolute;
        content: "";
        width: 330px;
        height: 330px;
        right: -130px;
        bottom: -210px;
        border: 1px solid rgba(152, 112, 189, .17);
        border-radius: 50%;
        pointer-events: none;
    }

    .post-intro-inner {
        position: relative;
        z-index: 1;
        max-width: 970px;
        margin: 0 auto;
    }

    .post-category-label {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 9px 15px;
        border: 1px solid rgba(152, 112, 189, .2);
        border-radius: 999px;
        background: rgba(255, 255, 255, .72);
        color: var(--post-mauve);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .17em;
        text-transform: uppercase;
    }

    .post-category-label::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--post-lavender);
        box-shadow: 0 0 0 4px rgba(195, 145, 238, .12);
    }

    .post-title {
        margin: 27px 0 0;
        color: var(--post-ink);
        font-size: clamp(34px, 5.3vw, 62px);
        font-weight: 700;
        letter-spacing: -.055em;
        line-height: 1.17;
    }

    .post-deck {
        max-width: 780px;
        margin: 24px 0 0;
        color: #716a79;
        font-size: 16px;
        line-height: 1.95;
    }

    /* Author and sharing */

    .post-meta-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 24px;
        margin-top: 38px;
        padding-top: 25px;
        border-top: 1px solid rgba(152, 112, 189, .18);
    }

    .post-author {
        display: flex;
        align-items: center;
        gap: 13px;
    }

    .post-author-avatar {
        display: flex;
        width: 46px;
        height: 46px;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 1px solid rgba(195, 145, 238, .35);
        border-radius: 50%;
        background: var(--post-purple);
        color: #fff;
        font-family: "Poppins", sans-serif;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: -.04em;
    }

    .post-author-info strong {
        display: block;
        color: var(--post-ink);
        font-size: 12px;
        font-weight: 700;
    }

    .post-author-info span {
        display: block;
        margin-top: 5px;
        color: #89818f;
        font-size: 10px;
        line-height: 1.7;
    }

    .post-share-top {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }

    .post-share-top a,
    .post-share-top button {
        display: inline-flex;
        min-height: 35px;
        align-items: center;
        justify-content: center;
        padding: 0 12px;
        border: 1px solid #e7ddef;
        border-radius: 8px;
        background: rgba(255, 255, 255, .75);
        color: #655d6d;
        font-size: 10px;
        font-weight: 600;
        transition: all .25s ease;
    }

    .post-share-top a:hover,
    .post-share-top button:hover {
        border-color: var(--post-mauve);
        background: #fff;
        color: var(--post-purple);
        transform: translateY(-2px);
    }

    /* Cover image */

    .post-cover-wrap {
        position: relative;
        z-index: 2;
        margin-top: -1px;
        padding-bottom: 15px;
        background: var(--post-paper);
    }

    .post-cover {
        position: relative;
        overflow: hidden;
        max-height: 560px;
        border-radius: 22px;
        background: #eee7f4;
        box-shadow: 0 20px 55px rgba(36, 16, 79, .09);
    }

    .post-cover img {
        display: block;
        width: 100%;
        max-height: 560px;
        aspect-ratio: 16 / 7;
        object-fit: cover;
        object-position: center;
    }

    .post-cover-placeholder {
        display: flex;
        min-height: 350px;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-radius: 22px;
        background:
            radial-gradient(circle at 50% 40%, rgba(195, 145, 238, .2), transparent 35%),
            linear-gradient(135deg, #24104f, #4b2b70);
        color: white;
    }

    .post-cover-placeholder span {
        color: #d9b9f3;
        font-family: "Playfair Display", serif;
        font-size: 100px;
        line-height: 1;
    }

    .post-cover-placeholder small {
        margin-top: 18px;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .25em;
    }

    /* Main article columns */

    .post-content-section {
        padding: 55px 0 95px;
        background: var(--post-paper);
    }

    .post-content-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 275px;
        align-items: start;
        gap: 75px;
    }

    .post-main-content {
        min-width: 0;
        max-width: 790px;
    }

    /* Article prose */

    .post-prose {
        color: #4c4653;
        font-family: "Inter", sans-serif;
        font-size: 15px;
        line-height: 2.05;
        overflow-wrap: anywhere;
    }

    .post-prose>*:first-child {
        margin-top: 0;
    }

    .post-prose p {
        margin: 0 0 25px;
    }

    .post-prose h2 {
        margin: 53px 0 20px;
        color: var(--post-purple);
        font-family: "Poppins", sans-serif;
        font-size: clamp(23px, 3vw, 29px);
        font-weight: 700;
        letter-spacing: -.04em;
        line-height: 1.35;
    }

    .post-prose h3 {
        margin: 38px 0 16px;
        color: var(--post-ink);
        font-family: "Poppins", sans-serif;
        font-size: 20px;
        font-weight: 600;
        letter-spacing: -.025em;
        line-height: 1.5;
    }

    .post-prose h4 {
        margin: 30px 0 12px;
        color: var(--post-purple);
        font-family: "Poppins", sans-serif;
        font-size: 16px;
        font-weight: 600;
    }

    .post-prose strong,
    .post-prose b {
        color: #30243e;
        font-weight: 700;
    }

    .post-prose a {
        color: var(--post-mauve);
        font-weight: 600;
        text-decoration: underline;
        text-decoration-color: rgba(152, 112, 189, .35);
        text-underline-offset: 4px;
    }

    .post-prose a:hover {
        color: var(--post-purple);
        text-decoration-color: var(--post-purple);
    }

    .post-prose ul,
    .post-prose ol {
        margin: 20px 0 28px;
        padding-left: 26px;
    }

    .post-prose ul {
        list-style: disc;
    }

    .post-prose ol {
        list-style: decimal;
    }

    .post-prose li {
        margin-bottom: 10px;
        padding-left: 5px;
    }

    .post-prose li::marker {
        color: var(--post-mauve);
        font-weight: 700;
    }

    .post-prose blockquote {
        position: relative;
        margin: 38px 0;
        padding: 26px 30px;
        border-left: 4px solid var(--post-mauve);
        border-radius: 0 14px 14px 0;
        background: #f2ebf8;
        color: #50435d;
        font-family: "Playfair Display", serif;
        font-size: 19px;
        font-style: italic;
        line-height: 1.8;
    }

    .post-prose blockquote p:last-child {
        margin-bottom: 0;
    }

    .post-prose img {
        display: block;
        max-width: 100%;
        height: auto;
        margin: 35px auto;
        border-radius: 14px;
    }

    .post-prose figure {
        margin: 35px 0;
    }

    .post-prose figcaption {
        margin-top: 10px;
        color: #8a8291;
        font-size: 11px;
        text-align: center;
    }

    .post-prose table {
        display: block;
        width: 100%;
        margin: 30px 0;
        overflow-x: auto;
        border-collapse: collapse;
        font-size: 13px;
    }

    .post-prose th,
    .post-prose td {
        padding: 13px 15px;
        border: 1px solid #e9e1ef;
        text-align: left;
    }

    .post-prose th {
        background: #f1eaf7;
        color: var(--post-purple);
        font-weight: 700;
    }

    .post-prose hr {
        margin: 40px 0;
        border: 0;
        border-top: 1px solid var(--post-line);
    }

    /* Reading rail */

    .post-reading-rail {
        position: sticky;
        top: 35px;
    }

    .post-rail-card {
        overflow: hidden;
        padding: 26px 23px;
        border: 1px solid #e9def1;
        border-radius: 18px;
        background:
            radial-gradient(circle at 100% 0%, rgba(195, 145, 238, .15), transparent 42%),
            #fff;
        box-shadow: 0 12px 35px rgba(36, 16, 79, .045);
    }

    .post-rail-label {
        color: var(--post-mauve);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .19em;
    }

    .post-rail-card h3 {
        margin: 17px 0 10px;
        color: var(--post-purple);
        font-family: "Poppins", sans-serif;
        font-size: 20px;
        font-weight: 700;
        letter-spacing: -.04em;
        line-height: 1.4;
    }

    .post-rail-card p {
        color: #79717f;
        font-size: 11px;
        line-height: 1.9;
    }

    .post-rail-link {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        margin-top: 20px;
        color: var(--post-mauve);
        font-size: 11px;
        font-weight: 700;
        transition: gap .25s ease;
    }

    .post-rail-link:hover {
        gap: 14px;
        color: var(--post-purple);
    }

    .post-progress-track {
        height: 4px;
        margin-top: 25px;
        overflow: hidden;
        border-radius: 10px;
        background: #eee7f4;
    }

    .post-progress-track i {
        display: block;
        width: 0;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #9870bd, #C391EE);
        transition: width .15s linear;
    }

    .post-progress-label {
        display: block;
        margin-top: 10px;
        color: #a098a7;
        font-size: 8px;
        font-weight: 700;
        letter-spacing: .18em;
    }

    /* Sharing panel */

    .post-share-panel {
        margin-top: 65px;
        padding: 34px;
        border: 1px solid #e8ddf0;
        border-radius: 18px;
        background:
            radial-gradient(circle at 100% 0%, rgba(195, 145, 238, .16), transparent 40%),
            #f4eef9;
    }

    .post-share-kicker {
        color: var(--post-mauve);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .2em;
    }

    .post-share-panel h3 {
        margin: 14px 0 10px;
        color: var(--post-purple);
        font-family: "Poppins", sans-serif;
        font-size: 25px;
        font-weight: 700;
        letter-spacing: -.04em;
    }

    .post-share-panel>p {
        color: #766e7d;
        font-size: 12px;
        line-height: 1.8;
    }

    .post-share-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 11px;
        margin-top: 23px;
    }

    .post-button {
        display: inline-flex;
        min-height: 46px;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 0 20px;
        border: 1px solid transparent;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .04em;
        transition: all .25s ease;
    }

    .post-button-primary {
        background: var(--post-purple);
        color: #fff;
        box-shadow: 0 7px 18px rgba(36, 16, 79, .15);
    }

    .post-button-primary:hover {
        background: var(--post-mauve);
        color: #fff;
        transform: translateY(-2px);
    }

    .post-button-outline {
        border-color: #d9c7e7;
        background: rgba(255, 255, 255, .7);
        color: var(--post-purple);
    }

    .post-button-outline:hover {
        border-color: var(--post-mauve);
        background: #fff;
        transform: translateY(-2px);
    }

    .post-share-status {
        min-height: 18px;
        margin-top: 13px;
        color: #684b84;
        font-size: 11px;
    }

    /* Disclaimer */

    .post-disclaimer {
        display: flex;
        gap: 15px;
        margin-top: 28px;
        padding: 21px 23px;
        border: 1px solid #eee7f4;
        border-radius: 13px;
        background: #fff;
    }

    .post-disclaimer-icon {
        display: flex;
        width: 34px;
        height: 34px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #f1e9f7;
        color: var(--post-mauve);
        font-family: "Playfair Display", serif;
        font-size: 20px;
    }

    .post-disclaimer strong {
        color: var(--post-purple);
        font-size: 11px;
        font-weight: 700;
    }

    .post-disclaimer p {
        margin-top: 6px;
        color: #85808b;
        font-size: 10px;
        line-height: 1.85;
    }

    /* Related articles */

    .post-related-section {
        padding: 85px 0 100px;
        border-top: 1px solid #eee7f4;
        background: #fff;
    }

    .post-related-heading {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 25px;
        margin-bottom: 40px;
    }

    .post-related-kicker {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: var(--post-mauve);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .2em;
    }

    .post-related-kicker::before {
        content: "";
        width: 25px;
        height: 1px;
        background: var(--post-lavender);
    }

    .post-related-heading h2 {
        margin-top: 15px;
        color: var(--post-ink);
        font-family: "Poppins", sans-serif;
        font-size: clamp(29px, 4vw, 42px);
        font-weight: 700;
        letter-spacing: -.055em;
        line-height: 1.2;
    }

    .post-related-heading h2 span {
        color: var(--post-mauve);
    }

    .post-related-link {
        display: inline-flex;
        flex-shrink: 0;
        align-items: center;
        gap: 9px;
        color: var(--post-mauve);
        font-size: 11px;
        font-weight: 700;
        transition: gap .25s ease;
    }

    .post-related-link:hover {
        gap: 14px;
        color: var(--post-purple);
    }

    .post-empty-related {
        padding: 35px;
        border: 1px dashed #dfd0eb;
        border-radius: 15px;
        background: #faf7fc;
        color: #817989;
        font-size: 13px;
        text-align: center;
    }

    .post-empty-related a {
        color: var(--post-mauve);
        font-weight: 700;
    }

    /* 404 */

    .post-not-found {
        position: relative;
        overflow: hidden;
        min-height: 650px;
        display: flex;
        align-items: center;
        background:
            radial-gradient(circle at 80% 20%, rgba(195, 145, 238, .17), transparent 28%),
            linear-gradient(135deg, #f6f0fa, #fff 70%);
    }

    .post-not-found-inner {
        position: relative;
        z-index: 1;
        max-width: 720px;
        padding-top: 90px;
        padding-bottom: 100px;
        text-align: center;
    }

    .post-not-found-number {
        display: block;
        color: rgba(152, 112, 189, .14);
        font-family: "Poppins", sans-serif;
        font-size: clamp(110px, 22vw, 190px);
        font-weight: 800;
        letter-spacing: -.09em;
        line-height: .9;
    }

    .post-not-found-kicker {
        margin-top: 35px;
        color: var(--post-mauve);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .22em;
    }

    .post-not-found h1 {
        margin-top: 20px;
        color: var(--post-ink);
        font-family: "Poppins", sans-serif;
        font-size: clamp(34px, 5vw, 52px);
        font-weight: 700;
        letter-spacing: -.055em;
        line-height: 1.2;
    }

    .post-not-found h1 span {
        color: var(--post-mauve);
    }

    .post-not-found p {
        max-width: 480px;
        margin: 20px auto 30px;
        color: #77717e;
        font-size: 13px;
        line-height: 1.9;
    }

    /* Responsive */

    @media (max-width: 1023px) {
        .post-content-grid {
            grid-template-columns: minmax(0, 1fr) 235px;
            gap: 35px;
        }

        .post-reading-rail {
            top: 20px;
        }
    }

    @media (max-width: 767px) {
        .post-intro {
            padding: 60px 0 48px;
        }

        .post-title {
            font-size: clamp(33px, 9vw, 46px);
        }

        .post-deck {
            font-size: 13px;
            line-height: 1.9;
        }

        .post-meta-row {
            align-items: flex-start;
            flex-direction: column;
            gap: 20px;
        }

        .post-cover img {
            aspect-ratio: 4 / 3;
        }

        .post-cover,
        .post-cover-placeholder {
            border-radius: 14px;
        }

        .post-content-section {
            padding: 35px 0 65px;
        }

        .post-content-grid {
            display: flex;
            flex-direction: column;
            gap: 35px;
        }

        .post-main-content {
            width: 100%;
        }

        .post-prose {
            font-size: 14px;
            line-height: 1.95;
        }

        .post-prose h2 {
            margin-top: 40px;
        }

        .post-reading-rail {
            position: static;
            width: 100%;
        }

        .post-share-panel {
            margin-top: 45px;
            padding: 24px 20px;
        }

        .post-share-panel h3 {
            font-size: 22px;
        }

        .post-related-section {
            padding: 65px 0 75px;
        }

        .post-related-heading {
            align-items: flex-start;
            flex-direction: column;
            gap: 18px;
            margin-bottom: 30px;
        }

        .post-disclaimer {
            padding: 17px;
        }
    }

    @media (max-width: 420px) {
        .post-share-actions {
            flex-direction: column;
        }

        .post-button {
            width: 100%;
        }

        .post-share-top {
            gap: 6px;
        }

        .post-share-top a,
        .post-share-top button {
            padding: 0 9px;
            font-size: 9px;
        }
    }
</style>

<div class="post-page">

    <?php if ($article === null): ?>

        <!-- =====================================================
             ARTICLE NOT FOUND
             ===================================================== -->

        <section class="post-not-found">
            <div class="site-container post-not-found-inner">

                <span class="post-not-found-number">404</span>

                <div class="post-not-found-kicker">
                    K&amp;S DOMUS LEGALIS / LOST IN THE RECORDS
                </div>

                <h1>
                    This page could not
                    <span>be found.</span>
                </h1>

                <p>
                    The article may have been removed, unpublished or moved
                    to another location. Return to the journal to explore
                    our latest legal insights.
                </p>

                <a href="<?= e(url('index.php')) ?>"
                    class="post-button post-button-primary">

                    Return to the journal
                    <span aria-hidden="true">↗</span>
                </a>

            </div>
        </section>

    <?php else: ?>

        <!-- =====================================================
             BREADCRUMB
             ===================================================== -->

        <div class="post-breadcrumb">
            <div class="site-container post-breadcrumb-inner">

                <a href="<?= e(url('index.php')) ?>">Journal</a>

                <span aria-hidden="true">/</span>

                <a href="<?= e(url('index.php?category=' . rawurlencode($article['category_slug']))) ?>">
                    <?= e($article['category_name']) ?>
                </a>

                <span aria-hidden="true">/</span>

                <span class="breadcrumb-current">Article</span>

            </div>
        </div>

        <!-- =====================================================
             ARTICLE HEADER
             ===================================================== -->

        <article>

            <header class="post-intro">
                <div class="site-container">
                    <div class="post-intro-inner">

                        <span class="post-category-label">
                            <?= e($article['category_name']) ?>
                        </span>

                        <h1 class="post-title">
                            <?= e($article['title']) ?>
                        </h1>

                        <?php if (!empty($article['excerpt'])): ?>
                            <p class="post-deck">
                                <?= e($article['excerpt']) ?>
                            </p>
                        <?php endif; ?>

                        <div class="post-meta-row">

                            <div class="post-author">

                                <div class="post-author-avatar" aria-hidden="true">
                                    K&amp;S
                                </div>

                                <div class="post-author-info">

                                    <strong>
                                        <?= e($article['author_name']) ?>
                                    </strong>

                                    <span>
                                        Published
                                        <?= e(format_date($article['published_at'])) ?>

                                        <span aria-hidden="true"> · </span>

                                        <?= e((string) reading_time((string) $article['content'])) ?>
                                        min read
                                    </span>

                                </div>
                            </div>

                            <div class="post-share-top">

                                <button
                                    type="button"
                                    data-share="copy"
                                    class="share-button">

                                    Copy link
                                </button>

                                <a
                                    href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode($articleUrl) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer">

                                    LinkedIn ↗
                                </a>

                                <a
                                    href="https://wa.me/?text=<?= rawurlencode($article['title'] . ' ' . $articleUrl) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer">

                                    WhatsApp ↗
                                </a>

                            </div>

                        </div>

                    </div>
                </div>
            </header>

            <!-- =================================================
                 FEATURED IMAGE
                 ================================================= -->

            <div class="post-cover-wrap">
                <div class="site-container">

                    <?php if (!empty($article['featured_image'])): ?>

                        <figure class="post-cover">
                            <img
                                src="<?= e(image_url($article['featured_image'])) ?>"
                                alt="<?= e($article['title']) ?>"
                                fetchpriority="high">
                        </figure>

                    <?php else: ?>

                        <div class="post-cover-placeholder">
                            <span aria-hidden="true">§</span>
                            <small>K&amp;S · LEGAL JOURNAL</small>
                        </div>

                    <?php endif; ?>

                </div>
            </div>

            <!-- =================================================
                 ARTICLE CONTENT
                 ================================================= -->

            <section class="post-content-section">

                <div class="site-container">

                    <div class="post-content-grid">

                        <!-- MAIN ARTICLE -->
                        <div class="post-main-content">

                            <div class="post-prose" id="article-content">
                                <?= sanitize_article_html((string) $article['content']) ?>
                            </div>

                            <!-- SHARE ARTICLE -->
                            <section class="post-share-panel">

                                <span class="post-share-kicker">
                                    PASS IT ON / SHARE THE PERSPECTIVE
                                </span>

                                <h3>Worth a second perspective?</h3>

                                <p>
                                    Share this briefing with your colleagues
                                    and professional network.
                                </p>

                                <div class="post-share-actions">

                                    <button
                                        type="button"
                                        data-share="copy"
                                        class="post-button post-button-primary">

                                        Copy article link
                                        <span aria-hidden="true">↗</span>
                                    </button>

                                    <a
                                        href="mailto:?subject=<?= rawurlencode($article['title']) ?>&body=<?= rawurlencode($articleUrl) ?>"
                                        class="post-button post-button-outline">

                                        Share by email
                                        <span aria-hidden="true">↗</span>
                                    </a>

                                </div>

                                <p
                                    id="share-status"
                                    class="post-share-status"
                                    role="status"
                                    aria-live="polite">
                                </p>

                            </section>

                            <!-- EDITORIAL DISCLAIMER -->
                            <aside class="post-disclaimer">

                                <div class="post-disclaimer-icon" aria-hidden="true">
                                    §
                                </div>

                                <div>
                                    <strong>Editorial note</strong>

                                    <p>
                                        This article is provided for general
                                        information and educational purposes.
                                        It is not a substitute for advice from
                                        a qualified legal professional on your
                                        specific circumstances.
                                    </p>
                                </div>

                            </aside>

                        </div>

                        <!-- READING RAIL -->
                        <aside class="post-reading-rail">

                            <div class="post-rail-card">

                                <span class="post-rail-label">
                                    IN THIS BRIEFING
                                </span>

                                <h3>
                                    A closer look at the subject.
                                </h3>

                                <p>
                                    Read the complete report, review related
                                    coverage and share this article with your
                                    professional network.
                                </p>

                                <a href="#related-articles" class="post-rail-link">
                                    Continue exploring
                                    <span aria-hidden="true">↓</span>
                                </a>

                                <div class="post-progress-track">
                                    <i id="reading-progress-bar"></i>
                                </div>

                                <small class="post-progress-label">
                                    READING PROGRESS
                                </small>

                            </div>

                        </aside>

                    </div>

                </div>

            </section>

        </article>

        <!-- =====================================================
             RELATED ARTICLES
             ===================================================== -->

        <section id="related-articles" class="post-related-section">

            <div class="site-container">

                <div class="post-related-heading">

                    <div>

                        <span class="post-related-kicker">
                            CONTINUE EXPLORING
                        </span>

                        <h2>
                            More to <span>consider.</span>
                        </h2>

                    </div>

                    <a
                        href="<?= e(url('index.php?category=' . rawurlencode($article['category_slug']))) ?>"
                        class="post-related-link">

                        More in <?= e($article['category_name']) ?>

                        <span aria-hidden="true">↗</span>
                    </a>

                </div>

                <?php if ($relatedArticles !== []): ?>

                    <div class="article-grid">

                        <?php foreach ($relatedArticles as $related): ?>
                            <?php article_card($related); ?>
                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <div class="post-empty-related">

                        There are no other published articles in this category yet.

                        <a href="<?= e(url('index.php')) ?>">
                            Explore the journal →
                        </a>

                    </div>

                <?php endif; ?>

            </div>

        </section>

    <?php endif; ?>

</div>

<!-- =========================================================
     ARTICLE INTERACTIONS
     ========================================================= -->

<script>
    (function() {
        'use strict';

        const article = document.getElementById('article-content');
        const progressBar = document.getElementById('reading-progress-bar');

        /*
         * Reading progress
         */
        function updateReadingProgress() {
            if (!article || !progressBar) return;

            const rect = article.getBoundingClientRect();
            const articleTop = window.scrollY + rect.top;
            const articleHeight = article.offsetHeight;
            const viewportBottom = window.scrollY + window.innerHeight;

            const distance = viewportBottom - articleTop;
            const progress = articleHeight > 0 ?
                Math.min(100, Math.max(0, (distance / articleHeight) * 100)) :
                0;

            progressBar.style.width = progress + '%';
        }

        if (article && progressBar) {
            window.addEventListener('scroll', updateReadingProgress, {
                passive: true
            });
            window.addEventListener('resize', updateReadingProgress);
            updateReadingProgress();
        }

        /*
         * Copy article link
         */
        const shareButtons = document.querySelectorAll('[data-share="copy"]');
        const shareStatus = document.getElementById('share-status');

        shareButtons.forEach(function(button) {
            button.addEventListener('click', async function() {
                const articleLink = window.location.href;

                try {
                    if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(articleLink);
                    } else {
                        const temporaryInput = document.createElement('textarea');

                        temporaryInput.value = articleLink;
                        temporaryInput.style.position = 'fixed';
                        temporaryInput.style.opacity = '0';

                        document.body.appendChild(temporaryInput);
                        temporaryInput.select();

                        const copied = document.execCommand('copy');
                        temporaryInput.remove();

                        if (!copied) {
                            throw new Error('Copy command failed');
                        }
                    }

                    if (shareStatus) {
                        shareStatus.textContent = 'Article link copied successfully.';
                    }

                    shareButtons.forEach(function(shareButton) {
                        shareButton.textContent = 'Copied ✓';
                    });

                } catch (error) {
                    if (shareStatus) {
                        shareStatus.textContent = 'Unable to copy automatically. Please copy the page URL manually.';
                    }
                }
            });
        });

    })();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
