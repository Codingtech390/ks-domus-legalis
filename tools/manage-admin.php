<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$mode = $argv[1] ?? '';
$email = strtolower(trim((string) ($argv[2] ?? '')));
$name = trim((string) ($argv[3] ?? ''));

if (!in_array($mode, ['create', 'reset-password'], true)) {
    fwrite(STDERR, "Usage: manage-admin.php <create|reset-password> <email> [name]\n");
    exit(2);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
    fwrite(STDERR, "Provide a valid administrator email address.\n");
    exit(2);
}

$nameLength = preg_match_all('/./us', $name);

if (
    $mode === 'create'
    && ($name === '' || $nameLength === false || $nameLength > 120)
) {
    fwrite(STDERR, "Creating an account requires a name of 1–120 characters.\n");
    exit(2);
}

$passwordInput = stream_get_contents(STDIN);

if (!is_string($passwordInput)) {
    fwrite(STDERR, "Could not read the password from standard input.\n");
    exit(1);
}

$password = preg_replace('/(?:\r\n|\n|\r)\z/', '', $passwordInput);

if (!is_string($password) || strlen($password) < 16 || strlen($password) > 4096) {
    fwrite(STDERR, "Password must be at least 16 bytes and no more than 4096 bytes.\n");
    exit(2);
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
unset($password, $passwordInput);

if (!is_string($passwordHash)) {
    fwrite(STDERR, "Could not hash the password.\n");
    exit(1);
}

require_once __DIR__ . '/../config/database.php';

try {
    if ($mode === 'create') {
        $statement = $pdo->prepare(
            'INSERT INTO admins (name, email, password)
             VALUES (:name, :email, :password)'
        );
        $statement->execute([
            'name' => $name,
            'email' => $email,
            'password' => $passwordHash,
        ]);
        fwrite(STDOUT, "Administrator account created.\n");
        exit(0);
    }

    $statement = $pdo->prepare(
        'UPDATE admins SET password = :password WHERE email = :email'
    );
    $statement->execute([
        'password' => $passwordHash,
        'email' => $email,
    ]);

    if ($statement->rowCount() !== 1) {
        fwrite(STDERR, "No administrator account was updated; check the email address.\n");
        exit(1);
    }

    fwrite(STDOUT, "Administrator password updated.\n");
} catch (PDOException $exception) {
    error_log('Administrator account management failed: ' . $exception->getMessage());
    fwrite(STDERR, "The account could not be changed. Check the PHP error log.\n");
    exit(1);
}
