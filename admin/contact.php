<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Editorial Enquiries';
$pageDescription = 'Review messages submitted through the contact form.';
$isAdminPage = true;
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();

    $messageId = filter_var($_POST['message_id'] ?? null, FILTER_VALIDATE_INT);
    $status = (string) ($_POST['status'] ?? '');

    if (!is_int($messageId) || $messageId < 1 || !in_array($status, ['new', 'read', 'archived'], true)) {
        $errors[] = 'Choose a valid message and status.';
    } else {
        $update = $pdo->prepare('UPDATE contact_messages SET status = :status WHERE id = :id');
        $update->execute(['status' => $status, 'id' => $messageId]);
        set_flash('success', 'Enquiry status updated.');
        redirect('admin/contact.php');
    }
}

$statusFilter = (string) ($_GET['status'] ?? '');
if (!in_array($statusFilter, ['', 'new', 'read', 'archived'], true)) {
    $statusFilter = '';
}

$where = '';
$parameters = [];
if ($statusFilter !== '') {
    $where = 'WHERE status = :status';
    $parameters['status'] = $statusFilter;
}

$statement = $pdo->prepare(
    "SELECT id, name, email, subject, message, status, created_at
     FROM contact_messages $where
     ORDER BY created_at DESC, id DESC
     LIMIT 100"
);
$statement->execute($parameters);
$messages = $statement->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<section class="admin-page-wrap">
    <div class="admin-page-heading">
        <div>
            <a href="<?= e(url('admin/index.php')) ?>" class="admin-back-link">← Back to dashboard</a>
            <p class="admin-eyebrow">PRIVATE EDITORIAL INBOX</p>
            <h1>Enquiries<span>.</span></h1>
            <p class="admin-heading-description">Contact form messages are visible only to signed-in administrators.</p>
        </div>
        <div class="admin-heading-stat"><strong><?= number_format(count($messages)) ?></strong><span>Messages shown</span></div>
    </div>

    <?php foreach ($errors as $error): ?>
        <div class="admin-alert admin-alert-error" role="alert"><?= e($error) ?></div>
    <?php endforeach; ?>

    <div class="admin-inbox-toolbar">
        <form method="get" action="<?= e(url('admin/contact.php')) ?>" class="admin-inbox-filter">
            <label for="contact-status-filter">Show</label>
            <select id="contact-status-filter" name="status">
                <option value="">All messages</option>
                <?php foreach (['new', 'read', 'archived'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="admin-button admin-button-secondary" type="submit">Filter</button>
        </form>
    </div>

    <?php if ($messages === []): ?>
        <div class="admin-panel admin-empty-state"><span>§</span><h2>No enquiries found</h2><p>New contact form messages will appear here.</p></div>
    <?php else: ?>
        <div class="admin-inbox-list">
            <?php foreach ($messages as $message): ?>
                <article class="admin-inbox-message">
                    <div class="admin-inbox-message-head">
                        <div>
                            <p class="admin-eyebrow"><?= e(format_date($message['created_at'])) ?> · <?= e($message['email']) ?></p>
                            <h2><?= e($message['subject']) ?></h2>
                            <p class="admin-inbox-sender">From <?= e($message['name']) ?></p>
                        </div>
                        <span class="admin-count-pill admin-status-<?= e($message['status']) ?>"><?= e($message['status']) ?></span>
                    </div>
                    <div class="admin-inbox-body"><?= nl2br(e($message['message'])) ?></div>
                    <form method="post" action="<?= e(url('admin/contact.php')) ?>" class="admin-inbox-actions">
                        <?= csrf_field() ?>
                        <input type="hidden" name="message_id" value="<?= e((string) $message['id']) ?>">
                        <label for="message-status-<?= e((string) $message['id']) ?>">Status</label>
                        <select id="message-status-<?= e((string) $message['id']) ?>" name="status">
                            <?php foreach (['new', 'read', 'archived'] as $status): ?>
                                <option value="<?= e($status) ?>" <?= $message['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="admin-button admin-button-secondary">Save status</button>
                        <a href="mailto:<?= e($message['email']) ?>?subject=<?= rawurlencode('Re: ' . $message['subject']) ?>" class="admin-text-action">Reply by email ↗</a>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
