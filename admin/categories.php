<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/category_helpers.php';

$pageTitle = 'Manage Categories';
$pageDescription = 'Manage the legal journal categories.';
$isAdminPage = true;

$errors = [];
$oldName = '';
$oldDescription = '';

ensure_default_categories($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = (string) ($_POST['action'] ?? '');

    try {
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

            $existingStatement->execute(['id' => $categoryId]);

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

            $countStatement->execute(['id' => $categoryId]);

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

            $delete->execute(['id' => $categoryId]);

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
        error_log('Category management failed: ' . $exception->getMessage());

        if ($exception->getCode() === '23000') {
            $errors[] = 'That category name or slug already exists, or the category is still in use.';
        } else {
            $errors[] = 'The category could not be saved. Please try again.';
        }
    }
}

$categoryStatement = $pdo->query(
    'SELECT c.id, c.name, c.slug, c.description,
            COUNT(a.id) AS article_count
     FROM categories c
     LEFT JOIN articles a ON a.category_id = c.id
     GROUP BY c.id, c.name, c.slug, c.description
     ORDER BY c.name ASC'
);

$categories = $categoryStatement->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<section class="admin-page-wrap">
    <div class="admin-page-heading">
        <div>
            <a href="<?= e(url('admin/index.php')) ?>" class="admin-back-link">
                ← Back to dashboard
            </a>

            <p class="admin-eyebrow">EDITORIAL TAXONOMY</p>

            <h1>Manage categories<span>.</span></h1>

            <p class="admin-heading-description">
                Organise the legal journal by subject. Categories with articles
                cannot be deleted until those articles have been reassigned.
            </p>
        </div>

        <div class="admin-heading-stat">
            <strong><?= number_format(count($categories)) ?></strong>
            <span>Categories</span>
        </div>
    </div>

    <?php foreach ($errors as $error): ?>
        <div class="admin-alert admin-alert-error" role="alert">
            <?= e($error) ?>
        </div>
    <?php endforeach; ?>

    <div class="admin-content-grid">
        <section class="admin-panel">
            <div class="admin-panel-heading">
                <div>
                    <p class="admin-eyebrow">NEW SUBJECT</p>
                    <h2>Create a category</h2>
                </div>
                <span class="admin-panel-icon">+</span>
            </div>

            <form method="post" action="<?= e(url('admin/categories.php')) ?>"
                class="admin-form">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create">

                <div class="admin-field">
                    <label for="new-category-name">Category name</label>
                    <input
                        id="new-category-name"
                        type="text"
                        name="name"
                        required
                        maxlength="100"
                        value="<?= e($oldName) ?>"
                        placeholder="e.g. Intellectual Property">
                </div>

                <div class="admin-field">
                    <label for="new-category-description">Description</label>
                    <textarea
                        id="new-category-description"
                        name="description"
                        maxlength="500"
                        rows="4"
                        placeholder="What kind of legal coverage belongs here?"><?= e($oldDescription) ?></textarea>
                </div>

                <p class="admin-field-help">
                    The URL slug is generated from the category name.
                </p>

                <button type="submit" class="admin-button admin-button-primary">
                    Create category <span>↗</span>
                </button>
            </form>
        </section>

        <section class="admin-panel admin-category-list-panel">
            <div class="admin-panel-heading">
                <div>
                    <p class="admin-eyebrow">CURRENT SUBJECTS</p>
                    <h2>Category library</h2>
                </div>
            </div>

            <?php if ($categories === []): ?>
                <div class="admin-empty-state">
                    <span>§</span>
                    <h3>No categories yet</h3>
                    <p>Create a category to organise your articles.</p>
                </div>
            <?php else: ?>
                <div class="admin-category-list">
                    <?php foreach ($categories as $category): ?>
                        <article class="admin-category-item">
                            <div class="admin-category-top">
                                <div class="admin-category-mark">§</div>
                                <div class="admin-category-summary">
                                    <h3><?= e($category['name']) ?></h3>
                                    <p>/<?= e($category['slug']) ?></p>
                                </div>
                                <span class="admin-count-pill">
                                    <?= number_format((int) $category['article_count']) ?>
                                    <?= (int) $category['article_count'] === 1 ? 'article' : 'articles' ?>
                                </span>
                            </div>

                            <form method="post"
                                action="<?= e(url('admin/categories.php')) ?>"
                                class="admin-form admin-category-edit">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="rename">
                                <input type="hidden" name="category_id"
                                    value="<?= e((string) $category['id']) ?>">

                                <div class="admin-field">
                                    <label for="category-name-<?= e((string) $category['id']) ?>">
                                        Name
                                    </label>
                                    <input
                                        id="category-name-<?= e((string) $category['id']) ?>"
                                        type="text"
                                        name="name"
                                        required
                                        maxlength="100"
                                        value="<?= e($category['name']) ?>">
                                </div>

                                <div class="admin-field">
                                    <label for="category-description-<?= e((string) $category['id']) ?>">
                                        Description
                                    </label>
                                    <textarea
                                        id="category-description-<?= e((string) $category['id']) ?>"
                                        name="description"
                                        maxlength="500"
                                        rows="2"><?= e((string) ($category['description'] ?? '')) ?></textarea>
                                </div>

                                <div class="admin-category-actions">
                                    <button type="submit" class="admin-button admin-button-secondary">
                                        Save changes
                                    </button>
                                </div>
                            </form>

                            <form method="post"
                                action="<?= e(url('admin/categories.php')) ?>"
                                class="admin-category-delete"
                                data-confirm="Delete this category? Categories in use cannot be deleted.">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="category_id"
                                    value="<?= e((string) $category['id']) ?>">

                                <button type="submit" class="admin-button admin-button-danger">
                                    Delete category
                                </button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
