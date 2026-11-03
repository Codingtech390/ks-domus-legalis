<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/category_helpers.php';

/*
|--------------------------------------------------------------------------
| Page metadata
|--------------------------------------------------------------------------
*/

$pageTitle = 'Manage Categories';
$pageDescription = 'Manage the legal journal categories.';
$isAdminPage = true;

/*
|--------------------------------------------------------------------------
| Form state
|--------------------------------------------------------------------------
*/

$errors = [];
$oldName = '';
$oldDescription = '';

/*
|--------------------------------------------------------------------------
| Ensure default categories
|--------------------------------------------------------------------------
*/

ensure_default_categories($pdo);

/*
|--------------------------------------------------------------------------
| Handle category actions
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = (string) ($_POST['action'] ?? '');

    try {

        /*
        |--------------------------------------------------------------------------
        | Create category
        |--------------------------------------------------------------------------
        */

        if ($action === 'create') {

            $name = trim((string) ($_POST['name'] ?? ''));
            $description = trim((string) ($_POST['description'] ?? ''));

            $oldName = $name;
            $oldDescription = $description;

            if ($name === '' || mb_strlen($name) > 100) {
                throw new RuntimeException(
                    'Enter a category name of 1–100 characters.'
                );
            }

            if (mb_strlen($description) > 500) {
                throw new RuntimeException(
                    'The description cannot exceed 500 characters.'
                );
            }

            $slug = make_slug($name);

            $check = $pdo->prepare(
                'SELECT id FROM categories
                 WHERE name = :name OR slug = :slug
                 LIMIT 1'
            );

            $check->execute([
                'name' => $name,
                'slug' => $slug,
            ]);

            if ($check->fetch()) {
                throw new RuntimeException(
                    'A category with that name or URL slug already exists.'
                );
            }

            $insert = $pdo->prepare(
                'INSERT INTO categories (name, slug, description)
                 VALUES (:name, :slug, :description)'
            );

            $insert->execute([
                'name' => $name,
                'slug' => $slug,
                'description' => $description !== '' ? $description : null,
            ]);

            set_flash('success', 'Category created successfully.');

            redirect('admin/categories.php');
        }

        /*
        |--------------------------------------------------------------------------
        | Rename category
        |--------------------------------------------------------------------------
        */

        if ($action === 'rename') {

            $categoryId = filter_var(
                $_POST['category_id'] ?? null,
                FILTER_VALIDATE_INT
            );

            $name = trim((string) ($_POST['name'] ?? ''));
            $description = trim((string) ($_POST['description'] ?? ''));

            if (!is_int($categoryId) || $categoryId < 1) {
                throw new RuntimeException('Invalid category selected.');
            }

            if ($name === '' || mb_strlen($name) > 100) {
                throw new RuntimeException(
                    'Enter a category name of 1–100 characters.'
                );
            }

            if (mb_strlen($description) > 500) {
                throw new RuntimeException(
                    'The description cannot exceed 500 characters.'
                );
            }

            $existingStatement = $pdo->prepare(
                'SELECT id FROM categories WHERE id = :id LIMIT 1'
            );

            $existingStatement->execute([
                'id' => $categoryId,
            ]);

            if (!$existingStatement->fetch()) {
                throw new RuntimeException('Category not found.');
            }

            $slug = make_slug($name);

            $duplicate = $pdo->prepare(
                'SELECT id FROM categories
                 WHERE (name = :name OR slug = :slug)
                   AND id <> :id
                 LIMIT 1'
            );

            $duplicate->execute([
                'name' => $name,
                'slug' => $slug,
                'id' => $categoryId,
            ]);

            if ($duplicate->fetch()) {
                throw new RuntimeException(
                    'Another category already uses that name or slug.'
                );
            }

            $update = $pdo->prepare(
                'UPDATE categories
                 SET name = :name,
                     slug = :slug,
                     description = :description
                 WHERE id = :id'
            );

            $update->execute([
                'name' => $name,
                'slug' => $slug,
                'description' => $description !== '' ? $description : null,
                'id' => $categoryId,
            ]);

            set_flash('success', 'Category updated successfully.');

            redirect('admin/categories.php');
        }

        /*
        |--------------------------------------------------------------------------
        | Delete category
        |--------------------------------------------------------------------------
        */

        if ($action === 'delete') {

            $categoryId = filter_var(
                $_POST['category_id'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!is_int($categoryId) || $categoryId < 1) {
                throw new RuntimeException('Invalid category selected.');
            }

            $countStatement = $pdo->prepare(
                'SELECT COUNT(*) FROM articles WHERE category_id = :id'
            );

            $countStatement->execute([
                'id' => $categoryId,
            ]);

            $articleCount = (int) $countStatement->fetchColumn();

            if ($articleCount > 0) {
                throw new RuntimeException(
                    'This category is used by ' . $articleCount .
                        ' article(s). Reassign those articles before deleting it.'
                );
            }

            $delete = $pdo->prepare(
                'DELETE FROM categories WHERE id = :id'
            );

            $delete->execute([
                'id' => $categoryId,
            ]);

            if ($delete->rowCount() === 0) {
                throw new RuntimeException('Category not found.');
            }

            set_flash('success', 'Category deleted successfully.');

            redirect('admin/categories.php');
        }

        throw new RuntimeException('Unsupported category action.');
    } catch (RuntimeException $exception) {

        $errors[] = $exception->getMessage();
    } catch (PDOException $exception) {

        error_log(
            'Category management failed: ' . $exception->getMessage()
        );

        if ($exception->getCode() === '23000') {
            $errors[] = 'That category name or slug already exists, or the category is still in use.';
        } else {
            $errors[] = 'The category could not be saved. Please try again.';
        }
    }
}

/*
|--------------------------------------------------------------------------
| Fetch categories
|--------------------------------------------------------------------------
*/

$categoryStatement = $pdo->query(
    'SELECT c.id, c.name, c.slug, c.description,
            COUNT(a.id) AS article_count
     FROM categories c
     LEFT JOIN articles a ON a.category_id = c.id
     GROUP BY c.id, c.name, c.slug, c.description
     ORDER BY c.name ASC'
);

$categories = $categoryStatement->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Shared header
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/header.php';

?>

<main class="min-h-screen bg-slate-50">

    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        <!-- Page heading -->

        <div class="mb-8">

            <a
                href="<?= e(url('admin/index.php')) ?>"
                class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 transition hover:text-red-700">
                <span aria-hidden="true">←</span>
                Back to dashboard
            </a>

            <div class="mt-6 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">

                <div>

                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-red-600">
                        Editorial workspace
                    </p>

                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
                        Manage categories<span class="text-red-600">.</span>
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        Organise the legal journal by subject. Manage your
                        categories, update their descriptions and keep your
                        editorial content structured.
                    </p>

                </div>

                <div class="flex items-center gap-4 border border-slate-200 bg-white px-5 py-4 shadow-sm sm:min-w-36">

                    <div class="flex h-11 w-11 items-center justify-center bg-red-50 text-lg font-black text-red-600">
                        #
                    </div>

                    <div>
                        <p class="text-2xl font-black leading-none text-slate-950">
                            <?= number_format(count($categories)) ?>
                        </p>

                        <p class="mt-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-500">
                            Categories
                        </p>
                    </div>

                </div>

            </div>

        </div>


        <!-- Error messages -->

        <?php foreach ($errors as $error): ?>

            <div
                class="mb-5 flex items-start gap-3 border border-red-200 border-l-4 border-l-red-600 bg-red-50 px-5 py-4 text-sm text-red-800"
                role="alert">

                <span class="font-black">!</span>

                <p class="leading-6">
                    <?= e($error) ?>
                </p>

            </div>

        <?php endforeach; ?>


        <!-- Main content -->

        <div class="grid items-start gap-6 lg:grid-cols-[0.85fr_1.15fr]">


            <!-- Create category panel -->

            <section class="border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-200 px-5 py-5 sm:px-7">

                    <div class="flex items-center justify-between gap-4">

                        <div>

                            <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-red-600">
                                New subject
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">
                                Create a category
                            </h2>

                        </div>

                        <span class="flex h-10 w-10 items-center justify-center bg-red-50 text-xl font-bold text-red-600">
                            +
                        </span>

                    </div>

                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Add a new subject to organise your legal articles.
                    </p>

                </div>


                <form
                    method="post"
                    action="<?= e(url('admin/categories.php')) ?>"
                    class="space-y-5 p-5 sm:p-7">

                    <?= csrf_field() ?>

                    <input
                        type="hidden"
                        name="action"
                        value="create">


                    <!-- Category name -->

                    <div>

                        <label
                            for="new-category-name"
                            class="mb-2 block text-sm font-bold text-slate-800">
                            Category name
                            <span class="text-red-600">*</span>
                        </label>

                        <input
                            id="new-category-name"
                            type="text"
                            name="name"
                            required
                            maxlength="100"
                            value="<?= e($oldName) ?>"
                            placeholder="e.g. Intellectual Property"
                            class="w-full border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-red-600 focus:ring-2 focus:ring-red-100">

                    </div>


                    <!-- Description -->

                    <div>

                        <label
                            for="new-category-description"
                            class="mb-2 block text-sm font-bold text-slate-800">
                            Description
                        </label>

                        <textarea
                            id="new-category-description"
                            name="description"
                            maxlength="500"
                            rows="5"
                            placeholder="What kind of legal coverage belongs here?"
                            class="w-full resize-y border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-red-600 focus:ring-2 focus:ring-red-100"><?= e($oldDescription) ?></textarea>

                        <p class="mt-2 text-xs leading-5 text-slate-400">
                            Maximum 500 characters.
                        </p>

                    </div>


                    <!-- Slug information -->

                    <div class="border border-slate-200 bg-slate-50 px-4 py-3">

                        <p class="text-xs font-bold text-slate-700">
                            Automatic URL generation
                        </p>

                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            The URL slug will automatically be generated from
                            the category name.
                        </p>

                    </div>


                    <!-- Submit -->

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 bg-red-600 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-100">
                        Create category
                        <span aria-hidden="true">↗</span>
                    </button>

                </form>

            </section>


            <!-- Existing categories panel -->

            <section class="min-w-0 border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-200 px-5 py-5 sm:px-7">

                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">

                        <div>

                            <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-red-600">
                                Content organisation
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">
                                Category library
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Manage your existing editorial subjects.
                            </p>

                        </div>

                        <span class="inline-flex w-fit items-center border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-600">
                            <?= number_format(count($categories)) ?> total
                        </span>

                    </div>

                </div>


                <?php if ($categories === []): ?>

                    <!-- Empty state -->

                    <div class="px-6 py-16 text-center">

                        <div class="mx-auto flex h-14 w-14 items-center justify-center border border-red-100 bg-red-50 text-2xl font-black text-red-600">
                            §
                        </div>

                        <h3 class="mt-5 text-lg font-black text-slate-950">
                            No categories yet
                        </h3>

                        <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-500">
                            Create your first category to start organising
                            your legal articles.
                        </p>

                    </div>

                <?php else: ?>

                    <div class="divide-y divide-slate-100">

                        <?php foreach ($categories as $category): ?>

                            <?php

                            $categoryId = (string) $category['id'];
                            $articleCount = (int) $category['article_count'];

                            ?>

                            <article class="min-w-0 p-5 transition hover:bg-slate-50/70 sm:p-6">

                                <!-- Category information -->

                                <div class="flex min-w-0 items-start gap-3">

                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center border border-red-100 bg-red-50 text-xl font-black text-red-600">
                                        §
                                    </div>


                                    <div class="min-w-0 flex-1">

                                        <div class="flex flex-wrap items-center gap-2">

                                            <h3 class="break-words text-base font-black text-slate-950">
                                                <?= e($category['name']) ?>
                                            </h3>

                                            <span class="inline-flex items-center border border-slate-200 bg-slate-50 px-2 py-1 text-[10px] font-bold text-slate-500">
                                                <?= number_format($articleCount) ?>
                                                <?= $articleCount === 1 ? 'article' : 'articles' ?>
                                            </span>

                                        </div>

                                        <p class="mt-1 break-all text-xs text-slate-400">
                                            /<?= e($category['slug']) ?>
                                        </p>

                                        <?php if (!empty($category['description'])): ?>

                                            <p class="mt-2 text-xs leading-5 text-slate-500">
                                                <?= e($category['description']) ?>
                                            </p>

                                        <?php endif; ?>

                                    </div>

                                </div>


                                <!-- Edit category form -->

                                <form
                                    method="post"
                                    action="<?= e(url('admin/categories.php')) ?>"
                                    class="mt-5 border-t border-slate-100 pt-5">

                                    <?= csrf_field() ?>

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="rename">

                                    <input
                                        type="hidden"
                                        name="category_id"
                                        value="<?= e($categoryId) ?>">


                                    <div class="grid gap-4 sm:grid-cols-2">

                                        <div>

                                            <label
                                                for="category-name-<?= e($categoryId) ?>"
                                                class="mb-2 block text-xs font-bold text-slate-700">
                                                Category name
                                            </label>

                                            <input
                                                id="category-name-<?= e($categoryId) ?>"
                                                type="text"
                                                name="name"
                                                required
                                                maxlength="100"
                                                value="<?= e($category['name']) ?>"
                                                class="w-full border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-red-600 focus:ring-2 focus:ring-red-100">

                                        </div>


                                        <div>

                                            <label
                                                for="category-description-<?= e($categoryId) ?>"
                                                class="mb-2 block text-xs font-bold text-slate-700">
                                                Description
                                            </label>

                                            <textarea
                                                id="category-description-<?= e($categoryId) ?>"
                                                name="description"
                                                maxlength="500"
                                                rows="2"
                                                class="w-full resize-y border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-red-600 focus:ring-2 focus:ring-red-100"><?= e((string) ($category['description'] ?? '')) ?></textarea>

                                        </div>

                                    </div>


                                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">

                                        <p class="text-[11px] text-slate-400">
                                            Updating the name also updates its URL slug.
                                        </p>

                                        <button
                                            type="submit"
                                            class="inline-flex items-center justify-center gap-2 bg-slate-950 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-100">
                                            Save changes
                                            <span aria-hidden="true">↗</span>
                                        </button>

                                    </div>

                                </form>


                                <!-- Delete category form -->

                                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">

                                    <p class="max-w-sm text-xs leading-5 text-slate-400">

                                        <?php if ($articleCount > 0): ?>

                                            This category cannot be deleted while articles are assigned to it.

                                        <?php else: ?>

                                            This category has no articles and can be deleted.

                                        <?php endif; ?>

                                    </p>


                                    <form
                                        method="post"
                                        action="<?= e(url('admin/categories.php')) ?>"
                                        class="admin-category-delete"
                                        data-confirm="Delete this category? Categories in use cannot be deleted.">

                                        <?= csrf_field() ?>

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="delete">

                                        <input
                                            type="hidden"
                                            name="category_id"
                                            value="<?= e($categoryId) ?>">

                                        <button
                                            type="submit"
                                            <?= $articleCount > 0 ? 'disabled' : '' ?>
                                            class="inline-flex items-center justify-center border border-red-200 px-4 py-2.5 text-xs font-bold text-red-700 transition hover:border-red-600 hover:bg-red-50 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-50 disabled:text-slate-400 disabled:hover:border-slate-200 disabled:hover:bg-slate-50">
                                            Delete category
                                        </button>

                                    </form>

                                </div>

                            </article>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </section>

        </div>

    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
