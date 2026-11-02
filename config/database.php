<?php

// declare(strict_types=1);

/**
 * K & S Domus Legalis
 * Application configuration and shared helper functions.
 *
 * Default configuration is intended for XAMPP on Windows.
 */

/*
|--------------------------------------------------------------------------
| Application configuration
|--------------------------------------------------------------------------
*/

const DB_HOST = '127.0.0.1';
const DB_NAME = 'legal_news_portal';
const DB_USER = 'root';
const DB_PASS = '';

const APP_NAME = 'K & S Domus Legalis';
const BASE_URL = '/legal-news-portal';

const UPLOAD_DIR = __DIR__ . '/../assets/uploads/';
const MAX_UPLOAD_BYTES = 5 * 1024 * 1024;

/*
|--------------------------------------------------------------------------
| Timezone
|--------------------------------------------------------------------------
*/

date_default_timezone_set('Asia/Kolkata');

/*
|--------------------------------------------------------------------------
| Session configuration
|--------------------------------------------------------------------------
| Start the session before any HTML output is sent.
|--------------------------------------------------------------------------
*/

$isHttps = isset($_SERVER['HTTPS'])
    && $_SERVER['HTTPS'] !== ''
    && strtolower((string) $_SERVER['HTTPS']) !== 'off';

if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

if (!defined('APP_INITIALIZED')) {
    define('APP_INITIALIZED', true);
}

/*
|--------------------------------------------------------------------------
| Database connection
|--------------------------------------------------------------------------
| Verify the selected database after connecting. Never expose database
| credentials or detailed connection errors to visitors.
|--------------------------------------------------------------------------
*/

try {
    $dsn = 'mysql:host=' . DB_HOST
        . ';dbname=' . DB_NAME
        . ';charset=utf8mb4';

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_TIMEOUT => 5,
    ]);

    /*
     * Confirm the database selected by MySQL.
     */
    $databaseStatement = $pdo->query('SELECT DATABASE()');
    $connectedDatabase = $databaseStatement->fetchColumn();

    if (!is_string($connectedDatabase) || $connectedDatabase === '') {
        throw new RuntimeException(
            'MySQL connected without selecting a database.'
        );
    }

    if ($connectedDatabase !== DB_NAME) {
        error_log(
            'Database configuration warning: expected database "'
            . DB_NAME
            . '" but connected to "'
            . $connectedDatabase
            . '".'
        );
    }

    /*
     * Log connection identity without logging passwords or credentials.
     */
    error_log(
        'Database connection established: host='
        . DB_HOST
        . ', database='
        . $connectedDatabase
    );
} catch (Throwable $exception) {
    error_log(
        'Database initialization failed: '
        . $exception->getMessage()
    );

    http_response_code(500);

    exit(
        'Unable to initialize the database connection. '
        . 'Check that MySQL is running and review the PHP error log.'
    );
}

/*
|--------------------------------------------------------------------------
| HTML escaping
|--------------------------------------------------------------------------
| Escape dynamic values before outputting them into HTML text or attributes.
|--------------------------------------------------------------------------
*/

function e(mixed $value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

/*
|--------------------------------------------------------------------------
| Application URLs
|--------------------------------------------------------------------------
*/

function url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
| Redirect to an application-relative path and terminate the request.
|--------------------------------------------------------------------------
*/

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

/*
|--------------------------------------------------------------------------
| CSRF protection
|--------------------------------------------------------------------------
*/

function csrf_token(): string
{
    if (
        !isset($_SESSION['_csrf_token'])
        || !is_string($_SESSION['_csrf_token'])
        || $_SESSION['_csrf_token'] === ''
    ) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

/**
 * Render a CSRF token field inside a POST form.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf_token" value="'
        . e(csrf_token())
        . '">';
}

/**
 * Verify the submitted CSRF token.
 */
function verify_csrf(): void
{
    $submitted = $_POST['_csrf_token'] ?? null;
    $stored = $_SESSION['_csrf_token'] ?? null;

    if (
        !is_string($submitted)
        || !is_string($stored)
        || $submitted === ''
        || $stored === ''
        || !hash_equals($stored, $submitted)
    ) {
        http_response_code(403);

        exit(
            'Invalid or expired form token. '
            . 'Please refresh the page and try again.'
        );
    }
}

/*
|--------------------------------------------------------------------------
| Flash messages
|--------------------------------------------------------------------------
*/

/**
 * Store a one-time notification for the next request.
 */
function set_flash(string $type, string $message): void
{
    $_SESSION['_flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

/**
 * Retrieve and clear a one-time notification.
 *
 * @return array{type:string,message:string}|null
 */
function get_flash(): ?array
{
    $flash = $_SESSION['_flash'] ?? null;

    unset($_SESSION['_flash']);

    if (
        !is_array($flash)
        || !isset($flash['type'], $flash['message'])
        || !is_scalar($flash['type'])
        || !is_scalar($flash['message'])
    ) {
        return null;
    }

    return [
        'type' => (string) $flash['type'],
        'message' => (string) $flash['message'],
    ];
}

/*
|--------------------------------------------------------------------------
| Article slug generation
|--------------------------------------------------------------------------
*/

/**
 * Create a URL-safe slug from an article title.
 */
function make_slug(string $title): string
{
    $title = trim($title);

    if (function_exists('iconv')) {
        $converted = iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            $title
        );

        if ($converted !== false) {
            $title = $converted;
        }
    }

    $slug = strtolower($title);

    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
    $slug = trim($slug, '-');

    return $slug !== ''
        ? substr($slug, 0, 250)
        : 'legal-article';
}

/*
|--------------------------------------------------------------------------
| Article reading time
|--------------------------------------------------------------------------
*/

/**
 * Estimate reading time using approximately 220 words per minute.
 */
function reading_time(string $html): int
{
    $plainText = html_entity_decode(
        strip_tags($html),
        ENT_QUOTES | ENT_HTML5,
        'UTF-8'
    );

    $wordCount = str_word_count($plainText);

    return max(1, (int) ceil($wordCount / 220));
}

/*
|--------------------------------------------------------------------------
| Date formatting
|--------------------------------------------------------------------------
*/

/**
 * Format a date for the public portal.
 */
function format_date(?string $date): string
{
    if ($date === null || trim($date) === '') {
        return 'Not published';
    }

    $timestamp = strtotime($date);

    return $timestamp === false
        ? 'Not published'
        : date('M j, Y', $timestamp);
}

/*
|--------------------------------------------------------------------------
| Uploaded image URLs
|--------------------------------------------------------------------------
*/

/**
 * Return the public URL for an uploaded image.
 *
 * Upload fields should contain generated filenames, not filesystem paths.
 */
function image_url(?string $image): string
{
    if ($image === null || trim($image) === '') {
        return url('assets/css/style.css');
    }

    $filename = basename($image);

    /*
     * Reject path-like or otherwise unexpected filenames.
     */
    if (
        $filename === '.'
        || $filename === '..'
        || str_contains($filename, "\0")
        || str_contains($filename, '/')
        || str_contains($filename, '\\')
    ) {
        return url('assets/css/style.css');
    }

    $path = UPLOAD_DIR . $filename;

    if (!is_file($path)) {
        return url('assets/css/style.css');
    }

    return url('assets/uploads/' . rawurlencode($filename));
}

/*
|--------------------------------------------------------------------------
| Safe local redirects
|--------------------------------------------------------------------------
*/

/**
 * Allow only application-relative redirect destinations.
 */
function safe_local_redirect(
    string $path,
    string $fallback = 'index.php'
): never {
    $path = trim($path);

    if (
        $path === ''
        || str_starts_with($path, '//')
        || str_contains($path, '\\')
        || str_contains($path, "\r")
        || str_contains($path, "\n")
        || preg_match('/^[a-z][a-z0-9+.-]*:/i', $path) === 1
    ) {
        redirect($fallback);
    }

    redirect($path);
}
