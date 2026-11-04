<?php



declare(strict_types=1);


$pageTitle       = $pageTitle ?? 'Legal News & Analysis';

$pageDescription = $pageDescription ?? 'Legal news, court judgments, legal analysis and regulatory updates.';

$isAdminPage     = (bool) ($isAdminPage ?? false);

$activeCategory  = (string) ($activeCategory ?? '');

$searchQuery     = (string) ($searchQuery ?? '');



$categories = $categories ?? [];



if (!$isAdminPage && isset($pdo) && $pdo instanceof PDO) {

    try {

        $categoryStatement = $pdo->query(

            'SELECT name, slug FROM categories ORDER BY name ASC'

        );



        $categories = $categoryStatement->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $exception) {

        error_log('Category navigation query failed: ' . $exception->getMessage());
    }
}



$pageTitle = trim($pageTitle) !== '' ? trim($pageTitle) : 'Legal News & Analysis';

$pageDescription = trim($pageDescription) !== '' ? trim($pageDescription) : 'Legal news, court judgments, legal analysis and regulatory updates.';

$searchQuery = mb_substr($searchQuery, 0, 150);



$flash = get_flash();

$flashType = 'error';

$flashMessage = '';



if (is_array($flash)) {

    $flashType = ($flash['type'] ?? '') === 'success' ? 'success' : 'error';

    $flashMessage = (string) ($flash['message'] ?? '');
}



$brandName = 'K & S Domus_Legalis';

$documentTitle = $pageTitle . ' | ' . $brandName;



$homeUrl = url('index.php');

$adminLoginUrl = url('admin/login.php');



$brandEmblemUrl = url('assets/css/cropped_circle_image%20(5)%20(1).png');

$faviconUrl = url('assets/images/favicon.png');

$stylesheetUrl = url('assets/css/style.css');



?>



<!doctype html>

<html lang="en">



<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">



    <meta name="description" content="<?= e($pageDescription) ?>">

    <meta name="theme-color" content="#24104f">

    <meta name="color-scheme" content="light">

    <meta name="robots" content="<?= $isAdminPage ? 'noindex,nofollow' : 'index,follow' ?>">



    <title><?= e($documentTitle) ?></title>



    <link rel="icon" type="image/png" sizes="512x512" href="<?= e($faviconUrl) ?>">

    <link rel="apple-touch-icon" href="<?= e($faviconUrl) ?>">





    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">


    <script src="https://cdn.tailwindcss.com"></script>



    <script>
        tailwind.config = {

            theme: {

                extend: {

                    colors: {

                        brand: {

                            purple: '#24104f',

                            lavender: '#C391EE',

                            mauve: '#9870bd',

                            charcoal: '#211c29',

                            ink: '#37313e',

                            muted: '#77717e',

                            paper: '#faf9fc',

                            line: '#eee7f4',

                        }

                    },

                    fontFamily: {

                        serif: ['"Playfair Display"', 'Georgia', 'serif'],

                        sans: ['Inter', 'system-ui', 'sans-serif'],

                        poppins: ['Poppins', 'sans-serif'],

                    }

                }

            }

        };
    </script>



    <link rel="stylesheet" href="<?= e($stylesheetUrl) ?>">



    <!-- Brand typography safeguard -->

    <style>
        .brand-lockup,

        .brand-lockup .brand-copy,

        .brand-lockup .brand-copy strong,

        .brand-lockup .brand-copy .brand-sub,

        .brand-lockup .brand-copy small {

            font-family: "Poppins", sans-serif !important;

        }



        .brand-lockup .brand-copy strong {

            font-weight: 700;

            letter-spacing: -0.075em;

        }



        .brand-lockup .brand-copy .brand-sub {

            font-weight: 600;

        }



        .brand-lockup .brand-copy small {

            font-weight: 600;

        }



        .nav-dropdown-menu[hidden],

        .mobile-nav[hidden] {

            display: none !important;

        }



        @media (prefers-reduced-motion: reduce) {



            *,

            *::before,

            *::after {

                scroll-behavior: auto !important;

                animation-duration: 0.01ms !important;

                animation-iteration-count: 1 !important;

                transition-duration: 0.01ms !important;

            }

        }
    </style>

</head>



<body class="<?= $isAdminPage ? 'admin-body' : 'site-body' ?>">



    <!-- Skip link -->

    <a href="#main-content" class="skip-link">Skip to main content</a>



    <!-- Page loader timeout -->

    <script>
        window.setTimeout(function() {

            var loader = document.getElementById('page-loader');

            if (loader) loader.classList.add('is-hidden');

        }, 5000);
    </script>



    <noscript>

        <style>
            #page-loader {

                display: none !important;

            }
        </style>

    </noscript>



    <!-- Branded loader -->

    <div

        id="page-loader"

        class="page-loader"

        role="status"

        aria-live="polite"

        aria-label="Loading page">



        <div class="loader-content">

            <img

                src="<?= e($brandEmblemUrl) ?>"

                alt=""

                class="loader-logo"

                width="104"

                height="104"

                fetchpriority="high">



            <div class="loader-spinner" aria-hidden="true"></div>

            <p>Preparing your briefing</p>

        </div>

    </div>



    <?php if ($isAdminPage): ?>



        <!-- ADMIN TOPBAR -->

        <header class="admin-topbar" role="banner">

            <div class="admin-topbar-inner">



                <a href="<?= e(url('admin/index.php')) ?>"

                    class="admin-brand"

                    aria-label="K and S Domus Legalis administration home">



                    <span class="admin-brand-mark">K&amp;S</span>



                    <span>

                        <strong>K &amp; S DOMUS_LEGALIS</strong>

                        <small>EDITORIAL CONTROL ROOM</small>

                    </span>

                </a>



                <?php if (!empty($_SESSION['admin_id'])): ?>



                    <nav class="admin-sidebar-nav" aria-label="Admin sections">



                        <a href="<?= e(url('admin/index.php')) ?>"

                            class="<?= basename($_SERVER['SCRIPT_NAME'] ?? '') === 'index.php' ? 'is-active' : '' ?>">



                            <svg width="16" height="16" viewBox="0 0 24 24"

                                fill="none" stroke="currentColor" stroke-width="2"

                                aria-hidden="true">

                                <rect x="3" y="3" width="7" height="7" />

                                <rect x="14" y="3" width="7" height="7" />

                                <rect x="14" y="14" width="7" height="7" />

                                <rect x="3" y="14" width="7" height="7" />

                            </svg>



                            Dashboard

                        </a>



                        <a href="<?= e(url('admin/create.php')) ?>"

                            class="<?= basename($_SERVER['SCRIPT_NAME'] ?? '') === 'create.php' ? 'is-active' : '' ?>">



                            <svg width="16" height="16" viewBox="0 0 24 24"

                                fill="none" stroke="currentColor" stroke-width="2"

                                aria-hidden="true">

                                <line x1="12" y1="5" x2="12" y2="19" />

                                <line x1="5" y1="12" x2="19" y2="12" />

                            </svg>



                            New article

                        </a>



                        <a href="<?= e(url('admin/categories.php')) ?>"

                            class="<?= basename($_SERVER['SCRIPT_NAME'] ?? '') === 'categories.php' ? 'is-active' : '' ?>">



                            <svg width="16" height="16" viewBox="0 0 24 24"

                                fill="none" stroke="currentColor" stroke-width="2"

                                aria-hidden="true">

                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" />

                            </svg>



                            Categories

                        </a>

                        <a
                            href="<?= e(url('admin/case-highlights.php')) ?>"
                            class="<?= basename($_SERVER['SCRIPT_NAME'] ?? '') === 'case-highlights.php' ? 'is-active' : '' ?>">

                            <svg width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2"
                                aria-hidden="true">
                                <path d="M4 4h16v16H4z" />
                                <path d="M8 9h8M8 13h8M8 17h5" />
                            </svg>

                            Casework

                        </a>



                        <a href="<?= e($homeUrl) ?>" target="_blank" rel="noopener noreferrer">

                            <svg width="16" height="16" viewBox="0 0 24 24"

                                fill="none" stroke="currentColor" stroke-width="2"

                                aria-hidden="true">

                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />

                                <polyline points="15 3 21 3 21 9" />

                                <line x1="10" y1="14" x2="21" y2="3" />

                            </svg>



                            View site

                        </a>

                    </nav>



                    <!-- ADMIN ACCOUNT -->

                    <nav class="admin-top-actions" aria-label="Administrator account">

                        <details class="admin-avatar-menu">



                            <summary class="admin-avatar-trigger"

                                aria-label="Open administrator menu">



                                <span class="admin-avatar-circle" aria-hidden="true">

                                    <?= e(mb_strtoupper(mb_substr($_SESSION['admin_name'] ?? 'A', 0, 1))) ?>

                                </span>



                                <span class="admin-avatar-label">

                                    <strong><?= e($_SESSION['admin_name'] ?? 'Administrator') ?></strong>

                                    <small>Manage website</small>

                                </span>



                                <span class="admin-avatar-chevron" aria-hidden="true"></span>

                            </summary>



                            <div class="admin-avatar-dropdown">



                                <a href="<?= e(url('admin/index.php')) ?>">

                                    <span aria-hidden="true">▦</span> Dashboard

                                </a>



                                <a href="<?= e(url('admin/create.php')) ?>">

                                    <span aria-hidden="true">＋</span> Create article

                                </a>



                                <a href="<?= e(url('admin/categories.php')) ?>">

                                    <span aria-hidden="true">▤</span> Manage categories

                                </a>

                                <a href="<?= e(url('admin/case-highlights.php')) ?>">
                                    <span aria-hidden="true">§</span> Manage casework
                                </a>



                                <a href="<?= e(url('admin/contact.php')) ?>">

                                    <span aria-hidden="true">✉</span> Editorial enquiries

                                </a>



                                <a href="<?= e(url('admin/newsletter.php')) ?>">

                                    <span aria-hidden="true">◉</span> Newsletter requests

                                </a>



                                <div class="admin-dropdown-divider"></div>



                                <a href="<?= e($homeUrl) ?>" target="_blank" rel="noopener noreferrer">

                                    <span aria-hidden="true">↗</span> View website

                                </a>



                                <div class="admin-dropdown-divider"></div>



                                <form action="<?= e(url('logout.php')) ?>"

                                    method="post"

                                    data-no-loader>



                                    <?= csrf_field() ?>



                                    <button type="submit" class="admin-signout">

                                        <span aria-hidden="true">⇥</span> Sign out

                                    </button>

                                </form>

                            </div>

                        </details>

                    </nav>



                <?php endif; ?>



            </div>

        </header>



    <?php else: ?>



        <!-- PUBLIC ANNOUNCEMENT BAR -->

        <div class="announcement-bar" role="note" aria-label="Site announcement">

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



        <!-- PUBLIC SITE HEADER -->

        <header class="site-header relative z-40 w-full border-b border-[#eee7f4] bg-[#fffefe] shadow-[0_3px_18px_rgba(36,16,79,0.035)]"

            id="site-header"

            role="banner">



            <div class="site-container header-main flex min-h-[88px] items-center justify-between gap-5 py-3">



                <!-- BRAND LOCKUP -->

                <a href="<?= e($homeUrl) ?>"

                    class="brand-lockup group flex shrink-0 items-center gap-3"

                    aria-label="K and S Domus Legalis — home">



                    <span class="relative flex h-[54px] w-[54px] shrink-0 items-center justify-center">

                        <span class="absolute inset-0 rounded-full border border-[#d9c6ea] transition-transform duration-500 group-hover:rotate-45"></span>

                        <span class="absolute inset-[4px] rounded-full bg-[#f4edf9]"></span>



                        <img

                            src="<?= e($brandEmblemUrl) ?>"

                            alt="K and S Domus Legalis emblem"

                            class="brand-emblem relative z-10 h-[43px] w-[43px] rounded-full object-cover"

                            width="43"

                            height="43">

                    </span>



                    <span class="brand-copy flex flex-col leading-none">



                        <strong class="text-[35px] font-bold tracking-[-0.075em] text-[#24104f]"

                            style="font-family:'Poppins',sans-serif !important;">

                            K <span class="font-medium text-[#9870bd]">&amp;</span> S

                        </strong>



                        <span class="brand-sub mt-1 text-[14px] font-bold tracking-[0.18em] text-[#37313e]"

                            style="font-family:'Poppins',sans-serif !important;">

                            DOMUS_LEGALIS

                        </span>



                    </span>

                </a>



                <!-- DESKTOP NAVIGATION -->

                <nav class="desktop-nav hidden items-center gap-1 lg:flex"

                    aria-label="Primary navigation">



                    <a href="<?= e(url('index.php')) ?>"

                        class="relative rounded-lg px-3 py-3 text-[12px] font-semibold text-[#625b6a] transition-colors duration-300 hover:bg-[#f6f0fa] hover:text-[#24104f] <?= (!$activeCategory && !$searchQuery) ? 'is-active bg-[#f6f0fa] text-[#24104f]' : '' ?>"

                        <?= (!$activeCategory && !$searchQuery) ? 'aria-current="page"' : '' ?>>

                        Home

                    </a>



                    <?php if (!empty($categories)): ?>



                        <!-- COVERAGE AREAS DROPDOWN -->

                        <div class="nav-dropdown-wrap group relative">



                            <button

                                type="button"

                                class="nav-dropdown-trigger flex items-center gap-2 rounded-lg px-3 py-3 text-[12px] font-semibold text-[#625b6a] transition-colors duration-300 hover:bg-[#f6f0fa] hover:text-[#24104f]"

                                aria-haspopup="true"

                                aria-expanded="false"

                                aria-controls="nav-categories-menu"

                                id="nav-categories-btn">



                                Coverage Areas



                                <span class="nav-chevron" aria-hidden="true"></span>

                            </button>



                            <ul

                                class="nav-dropdown-menu absolute left-0 top-full z-50 mt-2 w-[260px] rounded-2xl border border-[#eee5f5] bg-white p-2 shadow-[0_18px_45px_rgba(36,16,79,0.12)]"

                                id="nav-categories-menu"

                                role="menu"

                                aria-labelledby="nav-categories-btn">



                                <li class="px-3 pb-2 pt-2">

                                    <span class="text-[9px] font-bold uppercase tracking-[0.2em] text-[#9870bd]">

                                        Explore our coverage

                                    </span>

                                </li>



                                <?php foreach ($categories as $cat): ?>

                                    <li role="none">

                                        <a

                                            href="<?= e(url('index.php?category=' . rawurlencode($cat['slug']))) ?>"

                                            role="menuitem"

                                            class="block rounded-xl px-3 py-3 text-[12px] font-medium text-[#51495b] transition-all duration-200 hover:bg-[#f5effa] hover:text-[#24104f] <?= $activeCategory === $cat['slug'] ? 'is-active bg-[#f5effa] text-[#24104f]' : '' ?>">



                                            <?= e($cat['name']) ?>

                                        </a>

                                    </li>

                                <?php endforeach; ?>



                            </ul>

                        </div>



                    <?php else: ?>



                        <a href="<?= e(url('index.php#practice-areas')) ?>"

                            class="rounded-lg px-3 py-3 text-[12px] font-semibold text-[#625b6a] transition-colors hover:bg-[#f6f0fa] hover:text-[#24104f]">

                            Coverage Areas

                        </a>



                    <?php endif; ?>



                    <a href="<?= e(url('index.php#latest-news')) ?>"

                        class="rounded-lg px-3 py-3 text-[12px] font-semibold text-[#625b6a] transition-colors hover:bg-[#f6f0fa] hover:text-[#24104f]">

                        Insights

                    </a>



                    <a href="<?= e(url('index.php#why-choose-us')) ?>"

                        class="rounded-lg px-3 py-3 text-[12px] font-semibold text-[#625b6a] transition-colors hover:bg-[#f6f0fa] hover:text-[#24104f]">

                        Our Approach

                    </a>



                    <a href="<?= e(url('contact.php')) ?>"

                        class="rounded-lg px-3 py-3 text-[12px] font-semibold text-[#625b6a] transition-colors hover:bg-[#f6f0fa] hover:text-[#24104f]">

                        Contact

                    </a>



                    <?php if (!empty($_SESSION['admin_id'])): ?>

                        <a href="<?= e(url('admin/index.php')) ?>"

                            class="nav-admin-badge ml-1 rounded-full border border-[#e5d5f1] bg-[#f5effa] px-3 py-2 text-[10px] font-bold text-[#704b91]">

                            Admin ↗

                        </a>

                    <?php endif; ?>



                    <!-- SUBSCRIBE BUTTON -->

                    <a href="<?= e(url('index.php#newsletter')) ?>"

                        class="nav-cta group ml-2 inline-flex items-center gap-2 rounded-xl bg-[#24104f] px-4 py-3 text-[11px] font-bold tracking-[0.04em] text-white shadow-[0_7px_18px_rgba(36,16,79,0.15)] transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#9870bd]">



                        <span aria-hidden="true">✉</span>

                        Subscribe

                    </a>



                </nav>



                <!-- MOBILE MENU BUTTON -->

                <button

                    class="menu-toggle group flex h-11 w-11 shrink-0 flex-col items-center justify-center gap-[5px] rounded-xl border border-[#e9dff0] bg-[#f8f4fb] transition-all duration-300 hover:border-[#c9a9e0] hover:bg-[#f0e5f8] lg:hidden"

                    id="mobile-menu-button"

                    type="button"

                    aria-expanded="false"

                    aria-controls="mobile-menu"

                    aria-label="Open navigation menu">



                    <span class="hamburger-line h-[1.5px] w-5 rounded-full bg-[#24104f] transition-all duration-300" aria-hidden="true"></span>

                    <span class="hamburger-line h-[1.5px] w-5 rounded-full bg-[#24104f] transition-all duration-300" aria-hidden="true"></span>

                    <span class="hamburger-line h-[1.5px] w-5 rounded-full bg-[#24104f] transition-all duration-300" aria-hidden="true"></span>



                </button>



            </div>



            <!-- MOBILE NAVIGATION -->

            <nav

                id="mobile-menu"

                class="mobile-nav border-t border-[#eee7f4] bg-[#fffefe] shadow-[0_18px_30px_rgba(36,16,79,0.07)] lg:hidden"

                aria-label="Mobile navigation"

                hidden>



                <div class="mobile-nav-inner site-container space-y-1 px-4 py-5">



                    <a href="<?= e(url('index.php')) ?>"

                        class="block rounded-xl px-4 py-3.5 text-sm font-semibold text-[#37313e] transition-colors hover:bg-[#f5effa] hover:text-[#704b91]">

                        Home

                    </a>



                    <?php if (!empty($categories)): ?>



                        <div class="mobile-nav-section rounded-xl bg-[#faf7fc] p-4">



                            <span class="mobile-nav-heading mb-3 block text-[10px] font-bold uppercase tracking-[0.2em] text-[#9870bd]">

                                Coverage Areas

                            </span>



                            <?php foreach ($categories as $cat): ?>

                                <a

                                    href="<?= e(url('index.php?category=' . rawurlencode($cat['slug']))) ?>"

                                    class="mobile-nav-sub mb-1 block rounded-lg px-3 py-2.5 text-xs font-medium text-[#625b6a] transition-colors hover:bg-white hover:text-[#24104f] <?= $activeCategory === $cat['slug'] ? 'is-active bg-white text-[#24104f]' : '' ?>">



                                    <?= e($cat['name']) ?>

                                </a>

                            <?php endforeach; ?>



                        </div>



                    <?php endif; ?>



                    <a href="<?= e(url('index.php#latest-news')) ?>"

                        class="block rounded-xl px-4 py-3.5 text-sm font-semibold text-[#37313e] transition-colors hover:bg-[#f5effa] hover:text-[#704b91]">

                        Insights

                    </a>



                    <a href="<?= e(url('index.php#why-choose-us')) ?>"

                        class="block rounded-xl px-4 py-3.5 text-sm font-semibold text-[#37313e] transition-colors hover:bg-[#f5effa] hover:text-[#704b91]">

                        Our Approach

                    </a>



                    <a href="<?= e(url('contact.php')) ?>"

                        class="block rounded-xl px-4 py-3.5 text-sm font-semibold text-[#37313e] transition-colors hover:bg-[#f5effa] hover:text-[#704b91]">

                        Contact

                    </a>



                    <?php if (!empty($_SESSION['admin_id'])): ?>

                        <a href="<?= e(url('admin/index.php')) ?>"

                            class="mobile-nav-admin block rounded-xl px-4 py-3.5 text-sm font-semibold text-[#704b91] transition-colors hover:bg-[#f5effa]">

                            Admin dashboard ↗

                        </a>

                    <?php endif; ?>



                    <a href="<?= e(url('index.php#newsletter')) ?>"

                        class="mobile-nav-cta mt-3 flex items-center justify-center gap-3 rounded-xl bg-[#24104f] px-5 py-4 text-xs font-bold uppercase tracking-[0.12em] text-white shadow-[0_8px_20px_rgba(36,16,79,0.16)] transition-colors hover:bg-[#9870bd]">



                        Subscribe to newsletter

                        <span class="text-[#d9b9f3]" aria-hidden="true">↗</span>

                    </a>



                    <!-- MOBILE SEARCH -->

                    <form

                        method="get"

                        action="<?= e(url('index.php')) ?>"

                        class="mobile-search flex gap-2 border-t border-[#eee7f4] pt-5"

                        data-no-loader>



                        <input

                            type="search"

                            name="q"

                            value="<?= e($searchQuery) ?>"

                            maxlength="150"

                            placeholder="Search articles..."

                            aria-label="Search articles"

                            class="min-w-0 flex-1 rounded-xl border border-[#e9e2ef] bg-[#faf8fc] px-4 py-3 text-xs text-[#24104f] outline-none transition focus:border-[#9870bd] focus:ring-4 focus:ring-[#C391EE]/15">



                        <button

                            type="submit"

                            class="rounded-xl bg-[#eee5f7] px-4 py-3 text-xs font-bold text-[#704b91] transition-colors hover:bg-[#e3d2f0]">

                            Search

                        </button>



                    </form>



                </div>

            </nav>



        </header>



    <?php endif; ?>



    <!-- FLASH MESSAGES -->

    <?php if ($flashMessage !== ''): ?>



        <div class="site-container flash-wrap" role="status" aria-live="polite">

            <div class="flash-message <?= $flashType === 'success' ? 'flash-success' : 'flash-error' ?>">

                <?= e($flashMessage) ?>

            </div>

        </div>



    <?php endif; ?>



    <main id="main-content">
