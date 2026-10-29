<?php






$pageTitle = $pageTitle ?? 'Legal News & Analysis';

$pageDescription = $pageDescription

    ?? 'Legal news, court judgments, legal analysis and regulatory updates.';



$isAdminPage = (bool) ($isAdminPage ?? false);

$activeCategory = (string) ($activeCategory ?? '');

$searchQuery = (string) ($searchQuery ?? '');



$categories = $categories ?? [];



if (!$isAdminPage && isset($pdo) && $pdo instanceof PDO) {

    try {

        $categoryStatement = $pdo->query(

            'SELECT name, slug FROM categories ORDER BY name ASC'

        );



        $categories = $categoryStatement->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $exception) {

        error_log(

            'Category navigation query failed: '

                . $exception->getMessage()

        );
    }
}



/*

Normalize metadata and user-provided values before rendering.

*/

$pageTitle = trim($pageTitle) !== ''

    ? trim($pageTitle)

    : 'Legal News & Analysis';



$pageDescription = trim($pageDescription) !== ''

    ? trim($pageDescription)

    : 'Legal news, court judgments, legal analysis and regulatory updates.';



$searchQuery = mb_substr($searchQuery, 0, 150);



$flash = get_flash();



$flashType = 'error';

$flashMessage = '';



if (is_array($flash)) {

    $flashType = ($flash['type'] ?? '') === 'success'

        ? 'success'

        : 'error';



    $flashMessage = (string) ($flash['message'] ?? '');
}



$brandName = 'K & S Domus Legalis';

$documentTitle = $pageTitle . ' | ' . $brandName;



$homeUrl = url('index.php');

$adminLoginUrl = url('admin/login.php');



$brandEmblemPath = 'assets/css/cropped_circle_image%20(5)%20(1).png';

$brandEmblemUrl = url($brandEmblemPath);

$faviconUrl = url('assets/images/favicon.png');

$stylesheetUrl = url('assets/css/style.css');



?>

<!doctype html>

<html lang="en">



<head>

    <meta charset="UTF-8">



    <meta

        name="viewport"

        content="width=device-width, initial-scale=1.0">



    <meta

        name="description"

        content="<?= e($pageDescription) ?>">



    <meta name="theme-color" content="#132c2a">

    <meta name="color-scheme" content="light">



    <meta name="robots" content="index, follow">



    <title><?= e($documentTitle) ?></title>



    <link

        rel="icon"

        type="image/png"

        sizes="512x512"

        href="<?= e($faviconUrl) ?>">



    <link

        rel="apple-touch-icon"

        href="<?= e($faviconUrl) ?>">



    <link rel="preconnect" href="https://fonts.googleapis.com">



    <link

        rel="preconnect"

        href="https://fonts.gstatic.com"

        crossorigin>



    <link

        href="https://fonts.googleapis.com/css2?family=Montserrat:wght\@400;500;600;700;800&family=Poppins:wght\@300;400;500;600;700;800&display=swap"

        rel="stylesheet">



    <script src="https://cdn.tailwindcss.com"></script>



    <script>
        tailwind.config = {

            theme: {

                extend: {

                    colors: {

                        brand: {

                            forest: '#132c2a',

                            ink: '#1b2826',

                            gold: '#b9975b',

                            paper: '#f7f5ef',

                            muted: '#65716d'

                        }

                    },



                    fontFamily: {

                        sans: ['Poppins', 'Arial', 'sans-serif'],

                        display: ['Montserrat', 'Arial', 'sans-serif']

                    },



                    boxShadow: {

                        editorial: '0 24px 70px rgba(19, 44, 42, 0.10)'

                    }

                }

            }

        };
    </script>



    <link

        rel="stylesheet"

        href="<?= e($stylesheetUrl) ?>">



    <style>

        .skip-link {

            position: fixed;

            top: 12px;

            left: 12px;

            z-index: 10000;

            padding: 12px 18px;

            border-radius: 8px;

            background: #132c2a;

            color: #fff;

            font-weight: 600;

            transform: translateY(-160%);

            transition: transform 180ms ease;

        }



        .skip-link:focus {

            transform: translateY(0);

        }



        .brand-emblem {

            display: block;

            flex: 0 0 auto;

            width: 56px;

            height: 56px;

            object-fit: contain;

        }



        .mobile-nav[hidden] {

            display: none !important;

        }



        .mobile-nav a:focus-visible,

        .desktop-nav a:focus-visible,

        .search-form button:focus-visible,

        .search-form input:focus-visible,

        .menu-toggle:focus-visible,

        .admin-topbar a:focus-visible,

        .admin-topbar button:focus-visible {

            outline: 3px solid #b9975b;

            outline-offset: 3px;

        }



        .flash-wrap {

            margin-top: 1rem;

            margin-bottom: 1rem;

        }



        @media (prefers-reduced-motion: reduce) {

            .skip-link {

                transition: none;

            }

        }


        /* Public header styling matching the supplied Maheshwari & Co. reference */
        .announcement-bar,
        .site-header .search-strip {
            display: none;
        }

        .site-header {
            position: relative;
            top: auto;
            z-index: 100;
            background: #fff;
            border-bottom: 0;
            box-shadow: none;
            backdrop-filter: none;
        }

        .site-header .header-main {
            width: 100%;
            max-width: none;
            min-height: 90px;
            margin: 0;
            padding: 12px 30px 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .site-header .brand-lockup {
            position: relative;
            align-self: stretch;
            flex: 0 0 260px;
            min-width: 0;
            display: flex;
            align-items: flex-start;
            gap: 0;
            padding-top: 1px;
        }

        .site-header .brand-copy {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .site-header .brand-copy strong {
            color: #f3343b;
            font-family: "Montserrat", Arial, sans-serif;
            font-size: 18px;
            font-weight: 500;
            line-height: 1.25;
            letter-spacing: -0.45px;
            white-space: nowrap;
        }

        .site-header .brand-copy small {
            color: #8c3337;
            font-family: "Poppins", Arial, sans-serif;
            font-size: 10px;
            font-weight: 400;
            line-height: 1.4;
            letter-spacing: 1.45px;
            white-space: nowrap;
        }

        .site-header .brand-emblem {
            position: absolute;
            right: 0;
            bottom: -4px;
            display: block;
            flex: 0 0 auto;
            width: 42px;
            height: 42px;
            object-fit: contain;
        }

        .site-header .desktop-nav {
            flex: 1 1 auto;
            min-width: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            font-family: "Poppins", Arial, sans-serif;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }

        .site-header .desktop-nav>a:not(.nav-cta) {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 12px 0;
            color: #10172a;
            font-size: inherit;
            font-weight: 600;
            text-decoration: none;
        }

        .site-header .desktop-nav>a:not(.nav-cta):after {
            display: none;
        }

        .site-header .desktop-nav>a:not(.nav-cta):hover {
            color: #f3343b;
        }

        .site-header .nav-chevron {
            display: inline-block;
            width: 0;
            height: 0;
            margin-top: 3px;
            border-top: 5px solid currentColor;
            border-right: 4px solid transparent;
            border-left: 4px solid transparent;
        }

        .site-header .desktop-nav .nav-cta {
            flex: 0 0 auto;
            min-height: 54px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 24px;
            border: 0;
            border-radius: 999px;
            background: #f33138;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0;
        }

        .site-header .desktop-nav .nav-cta span {
            margin: 0;
            color: #f5c6a7;
        }

        .site-header .desktop-nav .nav-cta:hover {
            background: #e5252d;
            color: #fff;
            transform: none;
        }

        .site-header .menu-toggle {
            display: none;
        }

        @media (max-width: 1180px) {
            .site-header .header-main {
                gap: 18px;
                padding-right: 20px;
            }

            .site-header .brand-lockup {
                flex-basis: 235px;
            }

            .site-header .brand-copy strong {
                font-size: 16px;
            }

            .site-header .brand-copy small {
                font-size: 9px;
                letter-spacing: 1px;
            }

            .site-header .desktop-nav {
                gap: 12px;
                font-size: 10px;
            }

            .site-header .desktop-nav .nav-cta {
                min-height: 48px;
                padding: 0 15px;
                font-size: 10px;
            }
        }

        @media (max-width: 940px) {
            .site-header .header-main {
                min-height: 76px;
                padding: 10px 16px;
            }

            .site-header .brand-lockup {
                flex: 1 1 auto;
                align-self: center;
                min-height: 52px;
            }

            .site-header .brand-emblem {
                position: static;
                width: 38px;
                height: 38px;
                margin-left: 8px;
            }

            .site-header .desktop-nav {
                display: none;
            }

            .site-header .menu-toggle {
                display: flex;
            }
        }

        @media (max-width: 480px) {
            .site-header .header-main {
                min-height: 70px;
                padding: 10px 14px;
            }

            .site-header .brand-copy strong {
                font-size: 14px;
                letter-spacing: -0.3px;
            }

            .site-header .brand-copy small {
                font-size: 8px;
                letter-spacing: 0.6px;
            }

            .site-header .brand-emblem {
                width: 32px;
                height: 32px;
                margin-left: 5px;
            }
        }
    </style>

</head>



<body

    class="min-h-screen bg-brand-paper font-sans text-brand-ink antialiased <?= $isAdminPage ? 'admin-body' : 'site-body' ?>">

    <a href="#main-content" class="skip-link">

        Skip to main content

    </a>



    <div

        id="page-loader"

        class="page-loader"

        role="status"

        aria-live="polite"

        aria-label="Loading page">

        <div class="loader-content">

            <div class="loader-mark" aria-hidden="true">

                K<span>&amp;</span>S

            </div>



            <div class="loader-spinner" aria-hidden="true"></div>



            <p>Preparing your briefing</p>

        </div>

    </div>



    <?php if ($isAdminPage): ?>



        <header class="admin-topbar">

            <div class="admin-topbar-inner">



                <a

                    href="<?= e(url('admin/index.php')) ?>"

                    class="admin-brand"

                    aria-label="K and S Domus Legalis administration home">

                    <span class="admin-brand-mark">K&amp;S</span>



                    <span>

                        <strong>K &amp; S DOMUS LEGALIS</strong>

                        <small>EDITORIAL CONTROL ROOM</small>

                    </span>

                </a>



                <?php if (!empty($_SESSION['admin_id'])): ?>





                    <nav class="admin-top-actions" aria-label="Administration navigation">



                        <details class="admin-avatar-menu">

                            <summary

                                class="admin-avatar-trigger"

                                aria-label="Open administrator menu">



                                <span class="admin-avatar-circle" aria-hidden="true">KS</span>



                                <span class="admin-avatar-label">

                                    <strong>Administrator</strong>

                                    <small>Manage website</small>

                                </span>



                                <span class="admin-avatar-chevron" aria-hidden="true"></span>

                            </summary>



                            <div class="admin-avatar-dropdown">

                                <a href="<?= e(url('admin/index.php')) ?>">

                                    <span aria-hidden="true">▦</span>

                                    Dashboard

                                </a>



                                <a href="<?= e(url('admin/create.php')) ?>">

                                    <span aria-hidden="true">＋</span>

                                    Create article

                                </a>



                                <a href="<?= e(url('admin/categories.php')) ?>">

                                    <span aria-hidden="true">▤</span>

                                    Manage categories

                                </a>



                                <div class="admin-dropdown-divider"></div>



                                <a href="<?= e($homeUrl) ?>" target="_blank" rel="noopener noreferrer">

                                    <span aria-hidden="true">↗</span>

                                    View website

                                </a>



                                <div class="admin-dropdown-divider"></div>



                                <form action="<?= e(url('logout.php')) ?>" method="post">

                                    <?= csrf_field() ?>



                                    <button type="submit" class="admin-signout">

                                        <span aria-hidden="true">⇥</span>

                                        Sign out

                                    </button>

                                </form>

                            </div>

                        </details>



                    </nav>





                <?php endif; ?>



            </div>

        </header>



    <?php else: ?>





        <div class="announcement-bar">

            <div class="site-container announcement-inner">

                <span>

                    <i class="status-dot" aria-hidden="true"></i>

                    Independent legal intelligence

                </span>

                <span class="announcement-date">

                    <?= e(date('l, F j, Y')) ?>

                </span>

            </div>

        </div>









    <?php endif; ?>



    <?php if ($flashMessage !== ''): ?>



        <div class="site-container flash-wrap">



            <div

                class="flash-message <?= $flashType === 'success' ? 'flash-success' : 'flash-error' ?>"

                role="status"

                aria-live="polite">

                <?= e($flashMessage) ?>

            </div>



        </div>



    <?php endif; ?>



    <main id="main-content">
