
<?php

// declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| K & S Domus Legalis — Shared Footer
|--------------------------------------------------------------------------
| Expected existing helpers:
| e(), url()
|
| This file closes the main element opened in includes/header.php.
| It intentionally does not start a session or initialize the database.
|--------------------------------------------------------------------------
*/

$isAdminPage = (bool) ($isAdminPage ?? false);

$currentYear = date('Y');

$homeUrl = url('index.php');
$stylesheetScriptUrl = url('assets/js/app.js');

$scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
$currentScript = basename($scriptName);

$isHomepage = $currentScript === 'index.php';

?>

</main>

<?php if (!$isAdminPage): ?>

    <footer class="site-footer" id="contact">

        <div class="site-container footer-main">

            <!-- Brand and introduction -->
            <div class="footer-brand-col">

                <a
                    href="<?= e($homeUrl) ?>"
                    class="footer-brand"
                    aria-label="K and S Domus Legalis home">
                    <span class="footer-monogram" aria-hidden="true">
                        K<span>&amp;</span>S
                    </span>

                    <span>
                        K &amp; S
                        <em>Domus Legalis</em>
                    </span>
                </a>

                <p>
                    A considered perspective on law, policy and the
                    institutions that shape our world.
                </p>

                <span class="footer-motto">
                    SOVEREIGNTY · LIBERTY · JUSTICE
                </span>

            </div>

            <!-- Journal navigation -->
            <div class="footer-navigation">

                <h2>Explore the journal</h2>

                <a href="<?= e($homeUrl) ?>">
                    Latest analysis
                </a>

                <a href="<?= e(url('index.php#practice-areas')) ?>">
                    Practice areas
                </a>

                <a href="<?= e(url('index.php#latest-news')) ?>">
                    Latest articles
                </a>

                <a href="<?= e(url('index.php#why-choose-us')) ?>">
                    Our approach
                </a>

                <a href="<?= e(url('index.php#newsletter')) ?>">
                    Newsletter
                </a>

            </div>

            <!-- Editorial disclaimer -->
            <div class="footer-editorial">

                <h2>Editorial principles</h2>

                <p class="footer-note">
                    Content is published for general information and
                    educational purposes only. It is not legal advice
                    and does not create an advocate–client relationship.
                    Please seek advice from a qualified legal professional
                    regarding your particular circumstances.
                </p>

                <a
                    class="footer-admin-link"
                    href="<?= e(url('admin/login.php')) ?>">
                    Editorial sign in
                    <span aria-hidden="true">↗</span>
                </a>

            </div>

        </div>

        <!-- Copyright -->
        <div class="footer-bottom">

            <div class="site-container footer-bottom-inner">

                <span>
                    &copy; <?= e($currentYear) ?>
                    K &amp; S Domus Legalis.
                    All rights reserved.
                </span>

                <span>
                    Thoughtful law. Clear perspective.
                </span>

            </div>

        </div>

    </footer>

<?php else: ?>

    <footer class="admin-footer">

        <div class="site-container">
            <span>
                K &amp; S Domus Legalis
                &middot;
                Editorial management
            </span>

            <span>
                &copy; <?= e($currentYear) ?>
            </span>
        </div>

    </footer>

<?php endif; ?>

<!-- Shared site JavaScript -->
<script
    src="<?= e($stylesheetScriptUrl) ?>"
    defer></script>

<?php if ($isHomepage && !$isAdminPage): ?>

    <!-- Homepage carousel and homepage-specific interactions -->
    <script
        src="<?= e(url('assets/js/home.js')) ?>"
        defer></script>

<?php endif; ?>

</body>

</html>
