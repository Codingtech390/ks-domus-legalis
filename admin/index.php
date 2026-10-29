<?php

// declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Editorial Dashboard';
$pageDescription = 'Manage legal news articles.';
$isAdminPage = true;

$searchQuery = trim((string) ($_GET['q'] ?? ''));
$statusFilter = (string) ($_GET['status'] ?? '');

if (strlen($searchQuery) > 150) {
    $searchQuery = substr($searchQuery, 0, 150);
}

if (!in_array($statusFilter, ['', 'draft', 'published'], true)) {
    $statusFilter = '';
}

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$page = is_int($page) && $page > 0 ? $page : 1;

$perPage = 10;

$metricsStatement = $pdo->query(
    "SELECT COUNT(*) AS total,
            SUM(status = 'published') AS published,
            SUM(status = 'draft') AS drafts
     FROM articles"
);
$metrics = $metricsStatement->fetch();

$todayStatement = $pdo->query(
    "SELECT COUNT(*) FROM articles
     WHERE status = 'published'
       AND published_at >= CURDATE()
       AND published_at < CURDATE() + INTERVAL 1 DAY"
);
$publishedToday = (int) $todayStatement->fetchColumn();

$where = ['1 = 1'];
$params = [];

if ($searchQuery !== '') {
    $where[] = '(a.title LIKE :title OR a.excerpt LIKE :excerpt)';
    $params['title'] = '%' . $searchQuery . '%';
    $params['excerpt'] = '%' . $searchQuery . '%';
}

if ($statusFilter !== '') {
    $where[] = 'a.status = :status';
    $params['status'] = $statusFilter;
}

$whereSql = implode(' AND ', $where);

$countStatement = $pdo->prepare(
    "SELECT COUNT(*) FROM articles a
     INNER JOIN categories c ON c.id = a.category_id
     WHERE $whereSql"
);
$countStatement->execute($params);

$totalRows = (int) $countStatement->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $perPage));

if ($page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $perPage;

$listStatement = $pdo->prepare(
    "SELECT a.id, a.title, a.slug, a.excerpt, a.featured_image,
            a.status, a.published_at, a.created_at, a.updated_at,
            c.name AS category_name, ad.name AS author_name
     FROM articles a
     INNER JOIN categories c ON c.id = a.category_id
     INNER JOIN admins ad ON ad.id = a.admin_id
     WHERE $whereSql
     ORDER BY a.updated_at DESC, a.id DESC
     LIMIT :limit OFFSET :offset"
);

foreach ($params as $key => $value) {
    $listStatement->bindValue(':' . $key, $value, PDO::PARAM_STR);
}

$listStatement->bindValue(':limit', $perPage, PDO::PARAM_INT);
$listStatement->bindValue(':offset', $offset, PDO::PARAM_INT);
$listStatement->execute();

$articles = $listStatement->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-red-600">Editorial operations</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
                Dashboard<span class="text-red-600">.</span>
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Welcome back, <?= e($_SESSION['admin_name'] ?? $admin['name']) ?>. Manage your legal coverage here.
            </p>
        </div>

        <a href="<?= e(url('admin/create.php')) ?>" class="inline-flex items-center justify-center gap-2 bg-red-600 px-5 py-3.5 text-sm font-extrabold text-white hover:bg-red-700">
            <span class="text-lg">+</span> Create article
        </a>
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card">
            <p class="stat-card-label">All articles</p>
            <p class="stat-card-value"><?= number_format((int) ($metrics['total'] ?? 0)) ?></p>
            <p class="mt-3 text-xs text-slate-500">Total editorial records</p>
        </div>

        <div class="stat-card">
            <p class="stat-card-label">Published</p>
            <p class="stat-card-value text-green-700"><?= number_format((int) ($metrics['published'] ?? 0)) ?></p>
            <p class="mt-3 text-xs text-slate-500">Articles marked published</p>
        </div>

        <div class="stat-card">
            <p class="stat-card-label">Drafts</p>
            <p class="stat-card-value text-amber-600"><?= number_format((int) ($metrics['drafts'] ?? 0)) ?></p>
            <p class="mt-3 text-xs text-slate-500">Unpublished work in progress</p>
        </div>

        <div class="stat-card">
            <p class="stat-card-label">Published today</p>
            <p class="stat-card-value text-red-600"><?= number_format($publishedToday) ?></p>
            <p class="mt-3 text-xs text-slate-500">Based on the server's configured timezone</p>
        </div>
    </div>

    <section class="mt-10 border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-5 sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-xl font-black text-slate-950">Article management</h2>
                    <p class="mt-1 text-sm text-slate-500">Search, edit or remove editorial content.</p>
                </div>

                <form method="get" action="<?= e(url('admin/index.php')) ?>" class="flex flex-col gap-2 sm:flex-row">
                    <input
                        type="search"
                        name="q"
                        value="<?= e($searchQuery) ?>"
                        maxlength="150"
                        placeholder="Search articles..."
                        class="min-w-0 border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-red-600">

                    <select name="status" class="border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-red-600">
                        <option value="">All statuses</option>
                        <option value="published" <?= $statusFilter === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
                    </select>

                    <button type="submit" class="bg-slate-950 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700">Filter</button>
                </form>
            </div>
        </div>

        <?php if ($articles === []): ?>
            <div class="px-6 py-16 text-center">
                <p class="text-lg font-extrabold text-slate-900">No matching articles</p>
                <p class="mt-2 text-sm text-slate-500">Create an article or adjust your filters.</p>
                <a href="<?= e(url('admin/create.php')) ?>" class="mt-5 inline-flex bg-red-600 px-5 py-3 text-sm font-bold text-white hover:bg-red-700">Create your first article</a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[850px] text-left">
                    <thead class="bg-slate-50 text-[10px] font-extrabold uppercase tracking-[0.14em] text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Article</th>
                            <th class="px-5 py-4">Category</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Updated</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($articles as $article): ?>
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <?php if (!empty($article['featured_image'])): ?>
                                            <img
                                                src="<?= e(image_url($article['featured_image'])) ?>"
                                                alt=""
                                                class="h-14 w-20 shrink-0 object-cover"
                                                loading="lazy">
                                        <?php else: ?>
                                            <div class="flex h-14 w-20 shrink-0 items-center justify-center bg-slate-950 text-xl font-black text-red-500">L.</div>
                                        <?php endif; ?>

                                        <div class="max-w-md">
                                            <p class="font-bold text-slate-900"><?= e($article['title']) ?></p>
                                            <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500"><?= e($article['excerpt']) ?></p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-600"><?= e($article['category_name']) ?></td>

                                <td class="px-5 py-4">
                                    <span class="<?= $article['status'] === 'published' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' ?> inline-flex px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider">
                                        <?= e($article['status']) ?>
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-xs text-slate-500"><?= e(format_date($article['updated_at'])) ?></td>

                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <?php if ($article['status'] === 'published'): ?>
                                            <a
                                                href="<?= e(url('post.php?slug=' . rawurlencode($article['slug']))) ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="border border-slate-200 px-3 py-2 text-xs font-bold hover:border-slate-400">View</a>
                                        <?php endif; ?>

                                        <a
                                            href="<?= e(url('admin/edit.php?id=' . (int) $article['id'])) ?>"
                                            class="border border-slate-200 px-3 py-2 text-xs font-bold hover:border-red-600 hover:text-red-700">Edit</a>

                                        <form method="post" action="<?= e(url('admin/delete.php')) ?>" data-confirm="Delete this article permanently? This action cannot be undone.">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= e((string) $article['id']) ?>">
                                            <button type="submit" class="border border-red-200 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-50">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500">
                    Showing <?= number_format(min($totalRows, $offset + 1)) ?>–<?= number_format(min($totalRows, $offset + count($articles))) ?>
                    of <?= number_format($totalRows) ?> records
                </p>

                <?php
                $paginationQuery = [];

                if ($searchQuery !== '') {
                    $paginationQuery['q'] = $searchQuery;
                }

                if ($statusFilter !== '') {
                    $paginationQuery['status'] = $statusFilter;
                }
                ?>

                <nav aria-label="Dashboard pagination" class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a href="<?= e(url('admin/index.php?' . http_build_query($paginationQuery + ['page' => $page - 1]))) ?>" class="border border-slate-200 px-3 py-2 text-xs font-bold hover:border-red-600">← Previous</a>
                    <?php endif; ?>

                    <span class="px-3 py-2 text-xs font-bold text-slate-500">Page <?= e((string) $page) ?> of <?= e((string) $totalPages) ?></span>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= e(url('admin/index.php?' . http_build_query($paginationQuery + ['page' => $page + 1]))) ?>" class="border border-slate-200 px-3 py-2 text-xs font-bold hover:border-red-600">Next →</a>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    </section>
</section>

<script>
    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) return;

        const message = form.getAttribute('data-confirm');

        if (message && !window.confirm(message)) {
            event.preventDefault();
        }
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
