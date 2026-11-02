<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Newsletter Requests';
$pageDescription = 'Review newsletter requests and manage subscription status.';
$isAdminPage = true;
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    $subscriberId = filter_var($_POST['subscriber_id'] ?? null, FILTER_VALIDATE_INT);
    $status = (string) ($_POST['status'] ?? '');

    if (!is_int($subscriberId) || $subscriberId < 1 || !in_array($status, ['pending', 'active', 'unsubscribed'], true)) {
        $errors[] = 'Choose a valid subscriber and status.';
    } else {
        $update = $pdo->prepare('UPDATE newsletter_subscribers SET status = :status WHERE id = :id');
        $update->execute(['status' => $status, 'id' => $subscriberId]);
        set_flash('success', 'Newsletter status updated.');
        redirect('admin/newsletter.php');
    }
}

$statusFilter = (string) ($_GET['status'] ?? '');
if (!in_array($statusFilter, ['', 'pending', 'active', 'unsubscribed'], true)) {
    $statusFilter = '';
}
$where = '';
$parameters = [];
if ($statusFilter !== '') {
    $where = 'WHERE status = :status';
    $parameters['status'] = $statusFilter;
}

$statement = $pdo->prepare(
    "SELECT id, email, status, consent_at, created_at, updated_at
     FROM newsletter_subscribers $where
     ORDER BY created_at DESC, id DESC
     LIMIT 200"
);
$statement->execute($parameters);
$subscribers = $statement->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<section class="admin-page-wrap">
    <div class="admin-page-heading">
        <div>
            <a href="<?= e(url('admin/index.php')) ?>" class="admin-back-link">← Back to dashboard</a>
            <p class="admin-eyebrow">SUBSCRIPTION REGISTER</p>
            <h1>Newsletter<span>.</span></h1>
            <p class="admin-heading-description">Manage pending, active and withdrawn requests. No newsletter email is sent by this site yet.</p>
        </div>
        <div class="admin-heading-stat"><strong><?= number_format(count($subscribers)) ?></strong><span>Subscribers shown</span></div>
    </div>

    <?php foreach ($errors as $error): ?>
        <div class="admin-alert admin-alert-error" role="alert"><?= e($error) ?></div>
    <?php endforeach; ?>

    <div class="admin-inbox-toolbar">
        <form method="get" action="<?= e(url('admin/newsletter.php')) ?>" class="admin-inbox-filter">
            <label for="newsletter-status-filter">Show</label>
            <select id="newsletter-status-filter" name="status">
                <option value="">All requests</option>
                <?php foreach (['pending', 'active', 'unsubscribed'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="admin-button admin-button-secondary" type="submit">Filter</button>
        </form>
    </div>

    <section class="admin-panel admin-subscriber-panel">
        <?php if ($subscribers === []): ?>
            <div class="admin-empty-state"><span>§</span><h2>No newsletter requests</h2><p>New registrations will appear here.</p></div>
        <?php else: ?>
            <div class="admin-responsive-table">
                <table class="admin-data-table">
                    <thead><tr><th>Email</th><th>Status</th><th>Requested</th><th>Update status</th></tr></thead>
                    <tbody>
                        <?php foreach ($subscribers as $subscriber): ?>
                            <tr>
                                <td><?= e($subscriber['email']) ?></td>
                                <td><span class="admin-count-pill admin-status-<?= e($subscriber['status']) ?>"><?= e($subscriber['status']) ?></span></td>
                                <td><?= e(format_date($subscriber['consent_at'])) ?></td>
                                <td>
                                    <form method="post" action="<?= e(url('admin/newsletter.php')) ?>" class="admin-subscriber-actions">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="subscriber_id" value="<?= e((string) $subscriber['id']) ?>">
                                        <label class="sr-only" for="subscriber-status-<?= e((string) $subscriber['id']) ?>">Status for <?= e($subscriber['email']) ?></label>
                                        <select id="subscriber-status-<?= e((string) $subscriber['id']) ?>" name="status">
                                            <?php foreach (['pending', 'active', 'unsubscribed'] as $status): ?>
                                                <option value="<?= e($status) ?>" <?= $subscriber['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="admin-button admin-button-secondary">Save</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
