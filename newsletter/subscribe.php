<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

verify_csrf();

$email = strtolower(trim((string) ($_POST['email'] ?? '')));

if (
    strlen($email) > 254 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    set_flash('error', 'Please enter a valid email address.');
    redirect('index.php#newsletter');
}

try {
    $statement = $pdo->prepare(
        "INSERT INTO newsletter_subscribers
         (email, status, consent_at)
         VALUES (:email, 'pending', NOW())
         ON DUPLICATE KEY UPDATE
           consent_at = IF(status = 'unsubscribed', NOW(), consent_at),
           status = IF(status = 'unsubscribed', 'pending', status),
           updated_at = NOW()"
    );

    $statement->execute(['email' => $email]);

    set_flash(
        'success',
        'Your newsletter request has been recorded. Withdrawn requests return to pending review; email delivery is not enabled.'
    );
} catch (PDOException $exception) {
    error_log('Newsletter signup failed: ' . $exception->getMessage());

    set_flash(
        'error',
        'We could not process your request. Please try again.'
    );
}

redirect('index.php#newsletter');
