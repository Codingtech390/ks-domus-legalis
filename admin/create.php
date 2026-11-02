<?php

// declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| K & S Domus Legalis — Create Article
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/article_helpers.php';
require_once __DIR__ . '/../includes/category_helpers.php';

/*
 * Page metadata.
 */
$pageTitle = 'Create Article';
$pageDescription = 'Write and publish legal news.';
$isAdminPage = true;

/*
 * Form state.
 */
$errors = [];
$categories = [];
$categoryLoadError = false;

$values = [
    'title' => '',
    'category_id' => '',
    'excerpt' => '',
    'content' => '',
    'status' => 'draft',
];

/*
|--------------------------------------------------------------------------
| Load categories
|--------------------------------------------------------------------------
| Load the categories once. Explicit associative fetching ensures that
| the template can consistently access $category['id'] and ['name'].
|--------------------------------------------------------------------------
*/

try {
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new RuntimeException(
            'The database connection is not available.'
        );
    }

    /*
     * Preserve the existing default-category helper. If the helper fails,
     * log the failure and still attempt to read existing categories.
     */
    try {
        ensure_default_categories($pdo);
    } catch (Throwable $exception) {
        error_log(
            'Default category initialization failed: '
                . $exception->getMessage()
        );
    }

    $categoryStatement = $pdo->query(
        'SELECT id, name FROM categories ORDER BY name ASC'
    );

    $categories = $categoryStatement->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Normalize and validate the result structure.
     */
    $categories = array_values(array_filter(
        $categories,
        static function (array $category): bool {
            return isset($category['id'], $category['name'])
                && (string) $category['id'] !== ''
                && trim((string) $category['name']) !== '';
        }
    ));
} catch (Throwable $exception) {
    $categoryLoadError = true;
    $categories = [];

    error_log(
        'Category loading failed in admin/create.php: '
            . $exception->getMessage()
    );

    /*
     * Log the database name as diagnostic context without exposing
     * connection credentials or database details to the browser.
     */
    if (isset($pdo) && $pdo instanceof PDO) {
        try {
            $databaseStatement = $pdo->query('SELECT DATABASE()');
            $databaseName = $databaseStatement->fetchColumn();

            error_log(
                'Category query database: '
                    . (is_string($databaseName) ? $databaseName : '(unknown)')
            );
        } catch (Throwable $diagnosticException) {
            error_log(
                'Unable to determine active database: '
                    . $diagnosticException->getMessage()
            );
        }
    }
}

/*
|--------------------------------------------------------------------------
| Handle article submission
|--------------------------------------------------------------------------
*/

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();

    /*
     * Retain submitted values when validation or insertion fails.
     */
    foreach ($values as $key => $default) {
        $submittedValue = $_POST[$key] ?? $default;

        $values[$key] = is_scalar($submittedValue)
            ? (string) $submittedValue
            : (string) $default;
    }

    /*
     * The action buttons explicitly request draft or publish.
     * If a button was not used, retain the selected status field.
     */
    $submitAction = $_POST['submit_action'] ?? '';

    if ($submitAction === 'draft') {
        $values['status'] = 'draft';
    } elseif ($submitAction === 'publish') {
        $values['status'] = 'published';
    }

    $uploadedImage = null;

    try {
        if ($categoryLoadError) {
            throw new RuntimeException(
                'Categories could not be loaded. Check the database connection '
                    . 'and the PHP error log, then try again.'
            );
        }

        if ($categories === []) {
            throw new RuntimeException(
                'No categories are available. Check the categories table '
                    . 'in the database connected to this website.'
            );
        }

        /*
         * Validate the submitted article fields using the existing helper.
         */
        $postData = $_POST;
        $postData['status'] = $values['status'];

        $data = validate_article_input($postData);

        /*
         * Verify that the chosen category exists in the current database.
         * Do not trust a category ID supplied by the browser.
         */
        $selectedCategoryId = filter_var(
            $data['category_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($selectedCategoryId === false || $selectedCategoryId === null) {
            throw new RuntimeException(
                'Please choose a valid category.'
            );
        }

        $categoryExists = false;

        foreach ($categories as $category) {
            if ((int) $category['id'] === $selectedCategoryId) {
                $categoryExists = true;
                break;
            }
        }

        if (!$categoryExists) {
            throw new RuntimeException(
                'The selected category no longer exists. '
                    . 'Refresh the page and choose another category.'
            );
        }

        /*
         * Verify the authenticated administrator is available.
         */
        if (
            !isset($admin)
            || !is_array($admin)
            || !isset($admin['id'])
            || (int) $admin['id'] < 1
        ) {
            throw new RuntimeException(
                'Your administrator session could not be verified. '
                    . 'Please sign in again.'
            );
        }

        /*
         * Validate status independently.
         */
        if (!in_array($data['status'], ['draft', 'published'], true)) {
            throw new RuntimeException(
                'Please select a valid publication status.'
            );
        }

        /*
         * Upload a featured image if one was supplied.
         */
        $uploadedImage = upload_featured_image(
            $_FILES['featured_image'] ?? []
        );

        /*
         * Generate a unique slug using the existing article helper.
         */
        $slug = unique_article_slug($pdo, $data['title']);

        $publishedAt = $data['status'] === 'published'
            ? date('Y-m-d H:i:s')
            : null;

        /*
         * Insert the article.
         */
        $insert = $pdo->prepare(
            'INSERT INTO articles
                (
                    admin_id,
                    category_id,
                    title,
                    slug,
                    excerpt,
                    content,
                    featured_image,
                    status,
                    published_at
                )
             VALUES
                (
                    :admin_id,
                    :category_id,
                    :title,
                    :slug,
                    :excerpt,
                    :content,
                    :featured_image,
                    :status,
                    :published_at
                )'
        );

        $insert->execute([
            'admin_id' => (int) $admin['id'],
            'category_id' => $selectedCategoryId,
            'title' => $data['title'],
            'slug' => $slug,
            'excerpt' => $data['excerpt'],
            'content' => normalize_article_content($data['content']),
            'featured_image' => $uploadedImage,
            'status' => $data['status'],
            'published_at' => $publishedAt,
        ]);

        /*
         * Clear the local image reference after a successful insert.
         * This prevents the exception handler from deleting an image
         * that is now referenced by a saved article.
         */
        $uploadedImage = null;

        set_flash(
            'success',
            $data['status'] === 'published'
                ? 'Article published successfully.'
                : 'Draft saved successfully.'
        );

        redirect('admin/index.php');
    } catch (Throwable $exception) {
        /*
         * If article creation failed after uploading the image, attempt
         * to remove the unreferenced upload.
         */
        if ($uploadedImage !== null) {
            try {
                delete_uploaded_image($uploadedImage);
            } catch (Throwable $imageException) {
                error_log(
                    'Failed to clean up featured image after article creation '
                        . 'failed: '
                        . $imageException->getMessage()
                );
            }
        }

        if ($exception instanceof RuntimeException) {
            $errors[] = $exception->getMessage();
        } else {
            error_log(
                'Article creation failed: '
                    . $exception->getMessage()
            );

            $errors[] =
                'The article could not be saved. Please check the fields '
                . 'and try again. If the problem continues, check the PHP '
                . 'error log.';
        }
    }
}

/*
|--------------------------------------------------------------------------
| Render the page
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/header.php';

?>

<link
    href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css"
    rel="stylesheet">

<section class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">

    <div class="mb-8">

        <a
            href="<?= e(url('admin/index.php')) ?>"
            class="text-sm font-bold text-slate-500 hover:text-red-700">
            &larr; Back to dashboard
        </a>

        <p
            class="mt-6 text-xs font-extrabold uppercase tracking-[0.2em] text-red-600">
            Editorial workspace
        </p>

        <h1
            class="mt-2 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
            Create an article<span class="text-red-600">.</span>
        </h1>

        <p class="mt-2 text-sm leading-6 text-slate-500">
            Write, format and publish your next legal briefing.
        </p>

    </div>

    <?php foreach ($errors as $error): ?>

        <div class="form-error mb-5" role="alert">
            <?= e($error) ?>
        </div>

    <?php endforeach; ?>

    <?php if ($categoryLoadError): ?>

        <div class="form-error mb-5" role="alert">
            Categories could not be loaded from the connected database.
            Check the PHP error log for the underlying database error.
            Your existing categories have not been deleted by this page.
        </div>

    <?php elseif ($categories === []): ?>

        <div class="form-error mb-5" role="alert">
            No categories were returned by the connected database.
            Verify that this website is connected to the
            <strong>legal_news_portal</strong> database and that its
            categories table contains records.
        </div>

    <?php endif; ?>

    <form
        method="post"
        action="<?= e(url('admin/create.php')) ?>"
        enctype="multipart/form-data"
        id="article-form"
        class="space-y-6">

        <?= csrf_field() ?>

        <input
            type="hidden"
            name="MAX_FILE_SIZE"
            value="<?= e((string) MAX_UPLOAD_BYTES) ?>">

        <!-- Article details -->
        <section class="border border-slate-200 bg-white p-5 shadow-sm sm:p-8">

            <h2 class="text-lg font-black">
                Article details
            </h2>

            <div class="mt-6">

                <label
                    for="title"
                    class="mb-2 block text-sm font-bold">
                    Headline
                    <span class="text-red-600">*</span>
                </label>

                <input
                    id="title"
                    name="title"
                    type="text"
                    required
                    maxlength="255"
                    value="<?= e($values['title']) ?>"
                    placeholder="Enter a clear, compelling legal headline"
                    class="w-full border border-slate-200 px-4 py-3 text-sm outline-none focus:border-red-600">

            </div>

            <div class="mt-5 grid gap-5 md:grid-cols-2">

                <div>

                    <label
                        for="category_id"
                        class="mb-2 block text-sm font-bold">
                        Category
                        <span class="text-red-600">*</span>
                    </label>

                    <select
                        id="category_id"
                        name="category_id"
                        required
                        <?= $categories === [] ? 'disabled' : '' ?>
                        class="w-full border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-red-600 disabled:cursor-not-allowed disabled:bg-slate-100">

                        <option value="">
                            Choose a category
                        </option>

                        <?php foreach ($categories as $category): ?>

                            <?php
                            $categoryId = (string) $category['id'];
                            $categoryName = (string) $category['name'];
                            ?>

                            <option
                                value="<?= e($categoryId) ?>"
                                <?= $values['category_id'] === $categoryId ? 'selected' : '' ?>>
                                <?= e($categoryName) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div>

                    <label
                        for="status"
                        class="mb-2 block text-sm font-bold">
                        Publication status
                    </label>

                    <select
                        id="status"
                        name="status"
                        class="w-full border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-red-600">

                        <option
                            value="draft"
                            <?= $values['status'] === 'draft' ? 'selected' : '' ?>>
                            Save as draft
                        </option>

                        <option
                            value="published"
                            <?= $values['status'] === 'published' ? 'selected' : '' ?>>
                            Publish article
                        </option>

                    </select>

                </div>

            </div>

            <div class="mt-5">

                <label
                    for="excerpt"
                    class="mb-2 block text-sm font-bold">
                    Article excerpt
                    <span class="text-red-600">*</span>
                </label>

                <textarea
                    id="excerpt"
                    name="excerpt"
                    required
                    maxlength="600"
                    rows="3"
                    placeholder="Summarize the key development in a few concise sentences."
                    class="w-full resize-y border border-slate-200 px-4 py-3 text-sm leading-6 outline-none focus:border-red-600"><?= e($values['excerpt']) ?></textarea>

                <p class="mt-1 text-xs text-slate-500">
                    Maximum 600 characters. Used in article cards and search previews.
                </p>

            </div>

        </section>

        <!-- Featured image -->
        <section class="border border-slate-200 bg-white p-5 shadow-sm sm:p-8">

            <h2 class="text-lg font-black">
                Featured image
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Choose a JPEG, PNG, WebP or GIF image, up to 5 MB.
            </p>

            <div class="mt-5">

                <label
                    for="featured_image"
                    class="mb-2 block text-sm font-bold">
                    Upload image
                </label>

                <input
                    id="featured_image"
                    name="featured_image"
                    type="file"
                    accept="image/jpeg,image/png,image/webp,image/gif"
                    aria-describedby="featured-image-help"
                    class="block w-full border border-dashed border-slate-300 bg-slate-50 p-4 text-sm file:mr-4 file:border-0 file:bg-red-600 file:px-4 file:py-2 file:font-bold file:text-white hover:file:bg-red-700">

                <p
                    id="featured-image-help"
                    class="mt-2 text-xs text-slate-500">
                    Maximum file size: 5 MB.
                </p>

                <img
                    id="image-preview"
                    src=""
                    alt="Selected featured image preview"
                    class="mt-4 hidden max-h-72 max-w-full border border-slate-200 object-contain">

            </div>

        </section>

        <!-- Article content -->
        <section class="border border-slate-200 bg-white p-5 shadow-sm sm:p-8">

            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">

                <div>

                    <h2 class="text-lg font-black">
                        Article content
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Use the editor to format headings, quotations, lists and links.
                    </p>

                </div>

                <span
                    class="text-xs font-bold uppercase tracking-wider text-red-600">
                    Rich text editor
                </span>

            </div>

            <div
                id="editor"
                class="mt-5 bg-white"></div>

            <textarea
                id="content"
                name="content"
                class="hidden"
                aria-hidden="true"
                tabindex="-1"><?= e($values['content']) ?></textarea>

        </section>

        <!-- Form actions -->
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

            <a
                href="<?= e(url('admin/index.php')) ?>"
                class="border border-slate-200 bg-white px-6 py-3.5 text-center text-sm font-bold hover:bg-slate-50">
                Cancel
            </a>

            <button
                type="submit"
                name="submit_action"
                value="draft"
                class="border border-slate-300 bg-white px-6 py-3.5 text-sm font-extrabold hover:border-slate-500">
                Save draft
            </button>

            <button
                type="submit"
                name="submit_action"
                value="publish"
                class="bg-red-600 px-6 py-3.5 text-sm font-extrabold text-white hover:bg-red-700">
                Publish article &rarr;
            </button>

        </div>

    </form>

</section>

<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

<script>
    (() => {
        'use strict';

        const editorElement = document.getElementById('editor');
        const contentField = document.getElementById('content');
        const form = document.getElementById('article-form');
        const imageInput = document.getElementById('featured_image');
        const preview = document.getElementById('image-preview');

        if (
            !editorElement ||
            !contentField ||
            !form ||
            !imageInput ||
            !preview
        ) {
            console.error('The article form could not initialize.');
            return;
        }

        /*
         * Prevent an editor initialization failure from silently losing content.
         */
        if (typeof Quill === 'undefined') {
            console.error('Quill failed to load.');

            const fallback = document.createElement('p');
            fallback.className = 'mt-3 text-sm text-red-600';
            fallback.textContent =
                'The rich text editor could not load. Check your internet connection and reload the page.';

            editorElement.parentNode.insertBefore(
                fallback,
                editorElement.nextSibling
            );

            return;
        }

        const toolbarOptions = [
            [{
                header: [1, 2, 3, false]
            }],
            ['bold', 'italic', 'underline', 'strike'],
            [{
                    list: 'ordered'
                },
                {
                    list: 'bullet'
                }
            ],
            [{
                align: []
            }],
            ['blockquote', 'link'],
            ['clean']
        ];

        const quill = new Quill(editorElement, {
            theme: 'snow',
            placeholder: 'Begin writing your legal briefing...',
            modules: {
                toolbar: toolbarOptions
            }
        });

        /*
         * Restore saved form content if the previous POST failed.
         * The editor starts empty, preventing duplicated initial content.
         */
        const initialContent = contentField.value.trim();

        if (initialContent !== '') {
            quill.clipboard.dangerouslyPasteHTML(initialContent);
        }

        const maxImageBytes = 5 * 1024 * 1024;

        /*
         * Synchronize rich-text content before submitting the form.
         */
        form.addEventListener('submit', (event) => {
            contentField.value = quill.root.innerHTML;

            const plainText = quill.getText().trim();

            if (plainText.length < 10) {
                event.preventDefault();

                alert(
                    'Please write at least 10 characters of article content.'
                );

                quill.focus();
                return;
            }

            const file = imageInput.files && imageInput.files[0];

            if (file && file.size > maxImageBytes) {
                event.preventDefault();

                alert('The featured image must not exceed 5 MB.');
                imageInput.focus();
                return;
            }

            if (file && !file.type.startsWith('image/')) {
                event.preventDefault();

                alert('Please select a valid image file.');
                imageInput.value = '';
                imageInput.focus();
            }
        });

        /*
         * Image preview with object URL cleanup.
         */
        let previewObjectUrl = null;

        imageInput.addEventListener('change', () => {
            const file = imageInput.files && imageInput.files[0];

            if (previewObjectUrl !== null) {
                URL.revokeObjectURL(previewObjectUrl);
                previewObjectUrl = null;
            }

            preview.removeAttribute('src');
            preview.classList.add('hidden');

            if (!file) {
                return;
            }

            const allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif'
            ];

            if (!allowedTypes.includes(file.type)) {
                imageInput.value = '';

                alert(
                    'Please select a JPEG, PNG, WebP or GIF image.'
                );

                return;
            }

            if (file.size > maxImageBytes) {
                imageInput.value = '';

                alert('The featured image must not exceed 5 MB.');
                return;
            }

            previewObjectUrl = URL.createObjectURL(file);
            preview.src = previewObjectUrl;
            preview.classList.remove('hidden');
        });

        /*
         * Release the preview object URL when leaving the page.
         */
        window.addEventListener('beforeunload', () => {
            if (previewObjectUrl !== null) {
                URL.revokeObjectURL(previewObjectUrl);
            }
        });
    })();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
