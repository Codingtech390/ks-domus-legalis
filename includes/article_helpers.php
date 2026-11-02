<?php

// declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Validate a featured image and move it into the uploads directory.
 *
 * Returns the generated filename, or null if no new file was uploaded.
 * Throws RuntimeException when a selected file is invalid.
 */
function upload_featured_image(array $file): ?string
{
    $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The image upload failed. Please try again.');
    }

    $size = $file['size'] ?? null;
    $tmpName = $file['tmp_name'] ?? null;

    if (
        !is_int($size)
        || $size <= 0
        || $size > MAX_UPLOAD_BYTES
        || !is_string($tmpName)
        || !is_uploaded_file($tmpName)
    ) {
        throw new RuntimeException('Choose an image smaller than 5 MB.');
    }

    if (!extension_loaded('fileinfo')) {
        throw new RuntimeException('The PHP fileinfo extension must be enabled.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmpName);

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    if (!is_string($mime) || !isset($allowedTypes[$mime])) {
        throw new RuntimeException('Use a JPEG, PNG, WebP or GIF image.');
    }

    $imageInfo = @getimagesize($tmpName);

    if ($imageInfo === false) {
        throw new RuntimeException('The uploaded file is not a valid image.');
    }

    if (
        $imageInfo[0] < 1
        || $imageInfo[1] < 1
        || $imageInfo[0] > 10000
        || $imageInfo[1] > 10000
    ) {
        throw new RuntimeException('The image dimensions are not supported.');
    }

    if (!is_dir(UPLOAD_DIR)) {
        if (!mkdir(UPLOAD_DIR, 0755, true) && !is_dir(UPLOAD_DIR)) {
            throw new RuntimeException('The image upload directory could not be created.');
        }
    }

    $filename = bin2hex(random_bytes(20)) . '.' . $allowedTypes[$mime];
    $destination = UPLOAD_DIR . $filename;

    if (!move_uploaded_file($tmpName, $destination)) {
        throw new RuntimeException('The uploaded image could not be saved.');
    }

    return $filename;
}

/**
 * Remove a stored upload safely.
 */
function delete_uploaded_image(?string $filename): void
{
    if (
        !$filename
        || basename($filename) !== $filename
        || !preg_match('/^[a-f0-9]{40}\.(jpg|png|webp|gif)$/', $filename)
    ) {
        return;
    }

    $path = UPLOAD_DIR . $filename;

    if (is_file($path)) {
        @unlink($path);
    }
}

/**
 * Generate a unique article slug.
 *
 * $excludeId allows an article to keep its existing slug while editing.
 */
function unique_article_slug(
    PDO $pdo,
    string $title,
    ?int $excludeId = null
): string {
    $base = make_slug($title);
    $base = substr($base, 0, 230);
    $candidate = $base;
    $suffix = 2;

    while (true) {
        $sql = 'SELECT id FROM articles WHERE slug = :slug';

        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
        }

        $sql .= ' LIMIT 1';

        $statement = $pdo->prepare($sql);
        $statement->bindValue(':slug', $candidate, PDO::PARAM_STR);

        if ($excludeId !== null) {
            $statement->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        }

        $statement->execute();

        if (!$statement->fetch()) {
            return $candidate;
        }

        $candidate = substr($base, 0, 230) . '-' . $suffix;
        $suffix++;
    }
}

/**
 * Validate required article fields and return normalized values.
 *
 * @return array{title:string,category_id:int,excerpt:string,content:string,status:string}
 */
function validate_article_input(array $input): array
{
    $title = trim((string) ($input['title'] ?? ''));
    $excerpt = trim((string) ($input['excerpt'] ?? ''));
    $content = trim((string) ($input['content'] ?? ''));
    $categoryId = filter_var(
        $input['category_id'] ?? null,
        FILTER_VALIDATE_INT
    );
    $status = (string) ($input['status'] ?? 'draft');

    if ($title === '' || mb_strlen($title) > 255) {
        throw new RuntimeException('The title is required and must not exceed 255 characters.');
    }

    if ($excerpt === '' || mb_strlen($excerpt) > 600) {
        throw new RuntimeException('The excerpt is required and must not exceed 600 characters.');
    }

    if ($content === '' || mb_strlen(strip_tags($content)) < 10) {
        throw new RuntimeException('Write at least 10 characters of article content.');
    }

    if (!is_int($categoryId) || $categoryId < 1) {
        throw new RuntimeException('Select a valid category.');
    }

    if (!in_array($status, ['draft', 'published'], true)) {
        throw new RuntimeException('Select a valid publication status.');
    }

    $categoryStatement = $GLOBALS['pdo']->prepare(
        'SELECT id FROM categories WHERE id = :id LIMIT 1'
    );

    $categoryStatement->execute(['id' => $categoryId]);

    if (!$categoryStatement->fetch()) {
        throw new RuntimeException('The selected category does not exist.');
    }

    return [
        'title' => $title,
        'category_id' => $categoryId,
        'excerpt' => $excerpt,
        'content' => $content,
        'status' => $status,
    ];
}

/**
 * Store the article content as HTML.
 *
 * Public output is sanitized separately before rendering.
 */
function normalize_article_content(string $html): string
{
    return trim($html);
}
