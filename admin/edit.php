<?php

// declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/article_helpers.php';
require_once __DIR__ . '/../includes/category_helpers.php';

ensure_default_categories($pdo);
$categoryStatement = $pdo->query(
    'SELECT id, name FROM categories ORDER BY name ASC'
);

$categories = $categoryStatement->fetchAll();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
}

if (!is_int($id) || $id < 1) {
    http_response_code(400);
    exit('Invalid article ID.');
}

$articleStatement = $pdo->prepare(
    'SELECT * FROM articles WHERE id = :id LIMIT 1'
);
$articleStatement->execute(['id' => $id]);
$article = $articleStatement->fetch();

if (!$article) {
    http_response_code(404);
    exit('Article not found.');
}

$pageTitle = 'Edit Article';
$pageDescription = 'Edit a legal news article.';
$isAdminPage = true;

$values = [
    'title' => (string) $article['title'],
    'category_id' => (string) $article['category_id'],
    'excerpt' => (string) $article['excerpt'],
    'content' => (string) $article['content'],
    'status' => (string) $article['status'],
];

$errors = [];

$categoryStatement = $pdo->query(
    'SELECT id, name FROM categories ORDER BY name ASC'
);
$categories = $categoryStatement->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    foreach ($values as $key => $default) {
        $values[$key] = (string) ($_POST[$key] ?? $default);
    }

    $newImage = null;

    try {
        $data = validate_article_input($_POST);

        if ($categories === []) {
            throw new RuntimeException('No categories are available.');
        }

        $newImage = upload_featured_image($_FILES['featured_image'] ?? []);

        $slug = unique_article_slug($pdo, $data['title'], $id);

        $wasPublished = $article['status'] === 'published';
        $willPublish = $data['status'] === 'published';

        if ($willPublish) {
            $publishedAt = $wasPublished && $article['published_at'] !== null
                ? $article['published_at']
                : date('Y-m-d H:i:s');
        } else {
            $publishedAt = null;
        }

        $imageToSave = $newImage ?? $article['featured_image'];

        $update = $pdo->prepare(
            'UPDATE articles
             SET category_id = :category_id,
                 title = :title,
                 slug = :slug,
                 excerpt = :excerpt,
                 content = :content,
                 featured_image = :featured_image,
                 status = :status,
                 published_at = :published_at
             WHERE id = :id'
        );

        $update->execute([
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'slug' => $slug,
            'excerpt' => $data['excerpt'],
            'content' => normalize_article_content($data['content']),
            'featured_image' => $imageToSave,
            'status' => $data['status'],
            'published_at' => $publishedAt,
            'id' => $id,
        ]);

        if ($newImage !== null && !empty($article['featured_image'])) {
            delete_uploaded_image($article['featured_image']);
        }

        set_flash('success', 'Article updated successfully.');
        redirect('admin/index.php');
    } catch (Throwable $exception) {
        if ($newImage !== null) {
            delete_uploaded_image($newImage);
        }

        if ($exception instanceof RuntimeException) {
            $errors[] = $exception->getMessage();
        } else {
            error_log('Article update failed: ' . $exception->getMessage());
            $errors[] = 'The article could not be updated. Please try again.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">

<section class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8">
        <a href="<?= e(url('admin/index.php')) ?>" class="text-sm font-bold text-slate-500 hover:text-red-700">← Back to dashboard</a>
        <p class="mt-6 text-xs font-extrabold uppercase tracking-[0.2em] text-red-600">Editorial workspace</p>
        <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Edit article<span class="text-red-600">.</span></h1>
        <p class="mt-2 text-sm text-slate-500">Record #<?= e((string) $article['id']) ?> · Last updated <?= e(format_date($article['updated_at'])) ?></p>
    </div>

    <?php foreach ($errors as $error): ?>
        <div class="form-error mb-5" role="alert"><?= e($error) ?></div>
    <?php endforeach; ?>

    <form
        method="post"
        action="<?= e(url('admin/edit.php?id=' . $id)) ?>"
        enctype="multipart/form-data"
        id="article-form"
        class="space-y-6">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= e((string) $id) ?>">

        <section class="border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
            <h2 class="text-lg font-black">Article details</h2>

            <div class="mt-6">
                <label for="title" class="mb-2 block text-sm font-bold">Headline <span class="text-red-600">*</span></label>
                <input
                    id="title"
                    name="title"
                    required
                    maxlength="255"
                    value="<?= e($values['title']) ?>"
                    class="w-full border border-slate-200 px-4 py-3 text-sm outline-none focus:border-red-600">
            </div>

            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <div>
                    <label for="category_id" class="mb-2 block text-sm font-bold">Category <span class="text-red-600">*</span></label>
                    <select id="category_id" name="category_id" required class="w-full border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-red-600">
                        <option value="">Choose a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= e((string) $category['id']) ?>" <?= $values['category_id'] === (string) $category['id'] ? 'selected' : '' ?>>
                                <?= e($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="status" class="mb-2 block text-sm font-bold">Publication status</label>
                    <select id="status" name="status" class="w-full border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-red-600">
                        <option value="draft" <?= $values['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= $values['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                    </select>
                </div>
            </div>

            <div class="mt-5">
                <label for="excerpt" class="mb-2 block text-sm font-bold">Article excerpt <span class="text-red-600">*</span></label>
                <textarea
                    id="excerpt"
                    name="excerpt"
                    required
                    maxlength="600"
                    rows="3"
                    class="w-full resize-y border border-slate-200 px-4 py-3 text-sm leading-6 outline-none focus:border-red-600"><?= e($values['excerpt']) ?></textarea>
            </div>
        </section>

        <section class="border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
            <h2 class="text-lg font-black">Featured image</h2>

            <?php if (!empty($article['featured_image'])): ?>
                <div class="mt-4">
                    <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">Current image</p>
                    <img
                        src="<?= e(image_url($article['featured_image'])) ?>"
                        alt="Current article featured image"
                        class="max-h-64 max-w-full border border-slate-200 object-contain">
                </div>
            <?php endif; ?>

            <div class="mt-5">
                <label for="featured_image" class="mb-2 block text-sm font-bold">Replace featured image (optional)</label>
                <input
                    id="featured_image"
                    name="featured_image"
                    type="file"
                    accept="image/jpeg,image/png,image/webp,image/gif"
                    class="block w-full border border-dashed border-slate-300 bg-slate-50 p-4 text-sm file:mr-4 file:border-0 file:bg-red-600 file:px-4 file:py-2 file:font-bold file:text-white hover:file:bg-red-700">
                <img id="image-preview" src="" alt="New featured image preview" class="mt-4 hidden max-h-72 max-w-full border border-slate-200 object-contain">
            </div>
        </section>

        <section class="border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
            <h2 class="text-lg font-black">Article content</h2>
            <p class="mt-1 text-sm text-slate-500">Update the article using the rich-text editor.</p>

            <div id="editor" class="mt-5 bg-white"></div>
            <textarea id="content" name="content" class="hidden" aria-hidden="true"><?= e($values['content']) ?></textarea>
        </section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="<?= e(url('admin/index.php')) ?>" class="border border-slate-200 bg-white px-6 py-3.5 text-center text-sm font-bold hover:bg-slate-50">Cancel</a>
            <button type="submit" class="bg-red-600 px-6 py-3.5 text-sm font-extrabold text-white hover:bg-red-700">Save changes →</button>
        </div>
    </form>
</section>

<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
    (() => {
        const contentField = document.getElementById('content');
        const form = document.getElementById('article-form');

        const quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Write your legal briefing...',
            modules: {
                toolbar: [
                    [{
                        header: [1, 2, 3, false]
                    }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{
                        list: 'ordered'
                    }, {
                        list: 'bullet'
                    }],
                    [{
                        align: []
                    }],
                    ['blockquote', 'link'],
                    ['clean']
                ]
            }
        });

        const existingContent = contentField.value.trim();

        if (existingContent !== '') {
            quill.clipboard.dangerouslyPasteHTML(existingContent);
        }

        form.addEventListener('submit', (event) => {
            contentField.value = quill.root.innerHTML;

            if (quill.getText().trim().length < 10) {
                event.preventDefault();
                alert('Please write at least 10 characters of article content.');
            }

            const imageInput = document.getElementById('featured_image');

            if (imageInput.files.length > 0 && imageInput.files[0].size > 5 * 1024 * 1024) {
                event.preventDefault();
                alert('The featured image must be smaller than 5 MB.');
            }
        });

        const imageInput = document.getElementById('featured_image');
        const preview = document.getElementById('image-preview');

        imageInput.addEventListener('change', () => {
            const file = imageInput.files[0];

            if (!file) {
                preview.src = '';
                preview.classList.add('hidden');
                return;
            }

            if (!file.type.startsWith('image/')) {
                imageInput.value = '';
                alert('Please select a valid image file.');
                return;
            }

            preview.src = URL.createObjectURL(file);
            preview.classList.remove('hidden');
        });
    })();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
