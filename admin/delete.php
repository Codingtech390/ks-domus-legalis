<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/article_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method not allowed.');
}

verify_csrf();

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

if (!is_int($id) || $id < 1) {
    set_flash('error', 'The requested article ID is invalid.');
    redirect('admin/index.php');
}

$statement = $pdo->prepare(
    'SELECT id, title, featured_image FROM articles WHERE id = :id LIMIT 1'
);
$statement->execute(['id' => $id]);

$article = $statement->fetch();

if (!$article) {
    set_flash('error', 'The article was not found. It may already have been deleted.');
    redirect('admin/index.php');
}

try {
    $delete = $pdo->prepare('DELETE FROM articles WHERE id = :id');
    $delete->execute(['id' => $id]);

    if ($delete->rowCount() !== 1) {
        throw new RuntimeException('The article could not be deleted.');
    }

    delete_uploaded_image($article['featured_image']);

    set_flash('success', 'Article deleted successfully.');
} catch (Throwable $exception) {
    error_log('Article deletion failed: ' . $exception->getMessage());
    set_flash('error', 'Unable to delete the article. Please try again.');
}

redirect('admin/index.php');
