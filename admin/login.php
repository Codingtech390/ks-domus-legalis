<?php

// declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

if (!empty($_SESSION['admin_id'])) {
    redirect('admin/index.php');
}

$pageTitle = 'Administrator Login';
$pageDescription = 'Secure editorial administration login.';
$isAdminPage = true;

$error = '';
$email = '';
$attempts = (int) ($_SESSION['_login_attempts'] ?? 0);
$lockedUntil = (int) ($_SESSION['_login_locked_until'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if (time() < $lockedUntil) {
        $remaining = $lockedUntil - time();
        $error = 'Too many failed attempts. Please wait ' . $remaining . ' seconds.';
    } else {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        if (
            !filter_var($email, FILTER_VALIDATE_EMAIL)
            || strlen($email) > 190
            || $password === ''
            || strlen($password) > 4096
        ) {
            $error = 'Enter a valid email address and password.';
        } else {
            $statement = $pdo->prepare(
                'SELECT id, name, email, password
                 FROM admins
                 WHERE email = :email
                 LIMIT 1'
            );

            $statement->execute(['email' => $email]);
            $record = $statement->fetch();

            if (
                $record
                && password_verify($password, $record['password'])
            ) {
                session_regenerate_id(true);

                $_SESSION['admin_id'] = (int) $record['id'];
                $_SESSION['admin_name'] = $record['name'];
                $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));

                unset(
                    $_SESSION['_login_attempts'],
                    $_SESSION['_login_locked_until']
                );

                redirect('admin/index.php');
            }

            $attempts++;
            $_SESSION['_login_attempts'] = $attempts;

            if ($attempts >= 5) {
                $_SESSION['_login_locked_until'] = time() + 300;
                $_SESSION['_login_attempts'] = 0;
                $error = 'Too many failed attempts. Please try again in five minutes.';
            } else {
                $error = 'The email or password is incorrect.';
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="mx-auto flex min-h-[75vh] max-w-7xl items-center justify-center px-4 py-12 sm:px-6">
    <div class="grid w-full max-w-4xl overflow-hidden bg-white shadow-editorial md:grid-cols-2">
        <div class="relative hidden min-h-[480px] flex-col justify-between overflow-hidden bg-slate-950 p-10 text-white md:flex">
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full border-[45px] border-red-600/20"></div>
            <div class="absolute -bottom-24 -left-20 h-72 w-72 rounded-full border-[55px] border-red-600/20"></div>

            <div class="relative">
                <span class="inline-flex h-12 w-12 items-center justify-center bg-red-600 text-2xl font-black">L</span>
                <p class="mt-10 text-xs font-extrabold uppercase tracking-[0.25em] text-red-400">Editorial access</p>
                <h1 class="mt-4 text-4xl font-black leading-tight">Where legal intelligence takes shape.</h1>
            </div>

            <div class="relative border-t border-white/15 pt-6">
                <p class="text-sm leading-7 text-slate-400">
                    Manage legal reporting, publish analysis and maintain the editorial archive from one secure workspace.
                </p>
            </div>
        </div>

        <div class="p-7 sm:p-10 md:py-14">
            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-red-600">Administrator portal</p>
            <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950">Welcome back.</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">Sign in to continue to your editorial dashboard.</p>

            <?php if ($error !== ''): ?>
                <div class="mt-6 border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800" role="alert">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= e(url('admin/login.php')) ?>" class="mt-8 space-y-5">
                <?= csrf_field() ?>

                <div>
                    <label for="email" class="mb-2 block text-sm font-bold text-slate-800">Email address</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        required
                        maxlength="190"
                        autocomplete="username"
                        value="<?= e($email) ?>"
                        class="w-full border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-red-600 focus:bg-white"
                        placeholder="admin@example.com">
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-bold text-slate-800">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        maxlength="4096"
                        autocomplete="current-password"
                        class="w-full border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-red-600 focus:bg-white"
                        placeholder="Enter your password">
                </div>

                <button type="submit" class="w-full bg-red-600 px-5 py-3.5 text-sm font-extrabold text-white transition hover:bg-red-700">
                    Sign in to dashboard <span aria-hidden="true">→</span>
                </button>
            </form>

            <a href="<?= e(url('index.php')) ?>" class="mt-7 inline-block text-sm font-semibold text-slate-500 hover:text-red-600">
                ← Return to the public portal
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
