<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$pageTitle = 'Contact the Editorial Team';
$pageDescription = 'Contact K&S Domus Legalis about the legal journal or editorial enquiries.';
$values = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();

    foreach ($values as $field => $default) {
        $input = $_POST[$field] ?? $default;
        $values[$field] = is_scalar($input) ? trim((string) $input) : $default;
    }

    $website = $_POST['website'] ?? '';

    if (!is_scalar($website) || trim((string) $website) !== '') {
        set_flash('success', 'Thank you. Your message has been received.');
        redirect('contact.php');
    }

    if ($values['name'] === '' || mb_strlen($values['name']) > 120) {
        $errors[] = 'Enter your name (up to 120 characters).';
    }

    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL) || strlen($values['email']) > 254) {
        $errors[] = 'Enter a valid email address.';
    }

    if ($values['subject'] === '' || mb_strlen($values['subject']) > 180) {
        $errors[] = 'Enter a subject (up to 180 characters).';
    }

    if ($values['message'] === '' || mb_strlen($values['message']) < 10 || mb_strlen($values['message']) > 5000) {
        $errors[] = 'Write a message between 10 and 5,000 characters.';
    }

    if ($errors === []) {
        try {
            $statement = $pdo->prepare(
                'INSERT INTO contact_messages (name, email, subject, message)
                 VALUES (:name, :email, :subject, :message)'
            );
            $statement->execute($values);
            set_flash('success', 'Your message has been received. Our editorial team will review it.');
            redirect('contact.php');
        } catch (PDOException $exception) {
            error_log('Contact form submission failed: ' . $exception->getMessage());
            $errors[] = 'Your message could not be saved. Please try again later.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- CONTACT HERO -->
<section class="relative isolate overflow-hidden bg-[#f3edf9] py-20 sm:py-24 lg:py-32">
    <div class="pointer-events-none absolute -right-24 -top-32 h-[420px] w-[420px] rounded-full border border-[#9870bd]/15"></div>
    <div class="pointer-events-none absolute -right-12 -top-20 h-[320px] w-[320px] rounded-full border border-[#9870bd]/20"></div>
    <div class="pointer-events-none absolute -bottom-36 -left-20 h-[360px] w-[360px] rounded-full bg-[#C391EE]/10 blur-3xl"></div>

    <div class="site-container relative z-10">
        <div class="mx-auto max-w-4xl text-center">
            <span class="inline-flex items-center gap-3 text-[10px] font-bold uppercase tracking-[0.3em] text-[#9870bd] sm:text-xs">
                <span class="h-px w-8 bg-[#9870bd]"></span>
                K&amp;S DOMUS LEGALIS
                <span class="h-px w-8 bg-[#9870bd]"></span>
            </span>

            <h1 class="mt-7 text-5xl font-bold leading-[1.08] tracking-[-0.055em] text-[#211c29] sm:text-6xl lg:text-7xl"
                style="font-family:'Poppins',sans-serif;">
                Let's start a
                <span class="block text-[#9870bd]">conversation.</span>
            </h1>

            <p class="mx-auto mt-7 max-w-2xl text-sm leading-8 text-[#77717e] sm:text-base">
                Thoughtful dialogue begins with a thoughtful question.
                Reach out to our editorial team with your enquiries,
                perspectives and feedback.
            </p>

            <div class="mt-10 flex items-center justify-center gap-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#77717e]">
                <span class="h-1.5 w-1.5 rounded-full bg-[#9870bd]"></span>
                Editorial enquiries
                <span class="text-[#C391EE]">/</span>
                Journal correspondence
            </div>
        </div>
    </div>
</section>

<!-- CONTACT CONTENT -->
<section class="relative overflow-hidden bg-[#faf9fc] py-20 sm:py-24 lg:py-28">
    <div class="site-container">
        <div class="grid items-start gap-12 lg:grid-cols-[0.8fr_1.2fr] lg:gap-20">

            <!-- LEFT EDITORIAL PANEL -->
            <aside class="lg:sticky lg:top-28">
                <span class="text-[10px] font-bold uppercase tracking-[0.28em] text-[#9870bd]">
                    01 / GET IN TOUCH
                </span>

                <h2 class="mt-6 max-w-md text-3xl font-bold leading-[1.2] tracking-[-0.04em] text-[#211c29] sm:text-4xl lg:text-[42px]"
                    style="font-family:'Poppins',sans-serif;">
                    Every meaningful exchange starts somewhere.
                </h2>

                <p class="mt-6 max-w-md text-sm leading-8 text-[#77717e]">
                    Whether you have an editorial enquiry, feedback on an article
                    or a question about the journal, we welcome hearing from you.
                </p>

                <div class="mt-12 border-t border-[#e9e2ef] pt-8">
                    <div class="flex gap-5">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#eee5f7] text-lg text-[#9870bd]">
                            <span aria-hidden="true">§</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-[#211c29]">Editorial correspondence</h3>
                            <p class="mt-2 max-w-xs text-xs leading-6 text-[#77717e]">
                                Share your thoughts, questions and suggestions with our editorial team.
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 flex gap-5">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#eee5f7] text-lg text-[#9870bd]">
                            <span aria-hidden="true">✳</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-[#211c29]">A considered response</h3>
                            <p class="mt-2 max-w-xs text-xs leading-6 text-[#77717e]">
                                Messages are securely stored for review by authenticated administrators.
                            </p>
                        </div>
                    </div>
                </div>

                <a href="<?= e(url('index.php#latest-news')) ?>"
                    class="group mt-12 inline-flex items-center gap-3 text-xs font-bold uppercase tracking-[0.16em] text-[#9870bd] transition-colors hover:text-[#24104f]">
                    Explore the journal
                    <span class="text-base transition-transform duration-300 group-hover:translate-x-1" aria-hidden="true">↗</span>
                </a>

                <div class="mt-14 hidden select-none text-[100px] font-light leading-none text-[#eee7f4] lg:block"
                    aria-hidden="true">§</div>
            </aside>

            <!-- FORM PANEL -->
            <div class="relative overflow-hidden rounded-[28px] border border-[#eee7f4] bg-white p-6 shadow-[0_20px_70px_rgba(52,30,76,0.07)] sm:p-9 lg:p-11">

                <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-[#C391EE]/10 blur-3xl"></div>

                <div class="relative z-10">
                    <div class="mb-9 flex items-start justify-between gap-4 border-b border-[#eee7f4] pb-7">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-[0.24em] text-[#9870bd]">
                                02 / WRITE TO US
                            </span>
                            <h3 class="mt-3 text-2xl font-bold tracking-[-0.04em] text-[#211c29] sm:text-3xl"
                                style="font-family:'Poppins',sans-serif;">
                                Send an enquiry.
                            </h3>
                            <p class="mt-2 text-xs leading-6 text-[#77717e]">
                                Complete the details below and share your message.
                            </p>
                        </div>

                        <div class="hidden h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#f3edf9] text-xl text-[#9870bd] sm:flex">
                            <span aria-hidden="true">↗</span>
                        </div>
                    </div>

                    <?php foreach ($errors as $error): ?>
                        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                            role="alert">
                            <?= e($error) ?>
                        </div>
                    <?php endforeach; ?>

                    <form method="post" action="<?= e(url('contact.php')) ?>" class="space-y-6">
                        <?= csrf_field() ?>

                        <div class="contact-honeypot" aria-hidden="true">
                            <label for="contact-website">Website</label>
                            <input id="contact-website" type="text" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="contact-name" class="mb-2.5 block text-xs font-bold text-[#37313e]">
                                    Your name <span class="text-[#9870bd]">*</span>
                                </label>
                                <input
                                    id="contact-name"
                                    name="name"
                                    type="text"
                                    maxlength="120"
                                    autocomplete="name"
                                    required
                                    value="<?= e($values['name']) ?>"
                                    placeholder="Enter your name"
                                    class="w-full rounded-xl border border-[#e9e2ef] bg-[#fcfbfd] px-4 py-3.5 text-sm text-[#211c29] outline-none transition-all placeholder:text-[#aaa3b0] hover:border-[#C391EE] focus:border-[#9870bd] focus:bg-white focus:ring-4 focus:ring-[#C391EE]/15">
                            </div>

                            <div>
                                <label for="contact-email" class="mb-2.5 block text-xs font-bold text-[#37313e]">
                                    Email address <span class="text-[#9870bd]">*</span>
                                </label>
                                <input
                                    id="contact-email"
                                    name="email"
                                    type="email"
                                    maxlength="254"
                                    autocomplete="email"
                                    required
                                    value="<?= e($values['email']) ?>"
                                    placeholder="you@example.com"
                                    class="w-full rounded-xl border border-[#e9e2ef] bg-[#fcfbfd] px-4 py-3.5 text-sm text-[#211c29] outline-none transition-all placeholder:text-[#aaa3b0] hover:border-[#C391EE] focus:border-[#9870bd] focus:bg-white focus:ring-4 focus:ring-[#C391EE]/15">
                            </div>
                        </div>

                        <div>
                            <label for="contact-subject" class="mb-2.5 block text-xs font-bold text-[#37313e]">
                                Subject <span class="text-[#9870bd]">*</span>
                            </label>
                            <input
                                id="contact-subject"
                                name="subject"
                                type="text"
                                maxlength="180"
                                required
                                value="<?= e($values['subject']) ?>"
                                placeholder="What would you like to discuss?"
                                class="w-full rounded-xl border border-[#e9e2ef] bg-[#fcfbfd] px-4 py-3.5 text-sm text-[#211c29] outline-none transition-all placeholder:text-[#aaa3b0] hover:border-[#C391EE] focus:border-[#9870bd] focus:bg-white focus:ring-4 focus:ring-[#C391EE]/15">
                        </div>

                        <div>
                            <div class="mb-2.5 flex items-center justify-between gap-3">
                                <label for="contact-message" class="block text-xs font-bold text-[#37313e]">
                                    Your message <span class="text-[#9870bd]">*</span>
                                </label>
                                <span class="text-[10px] font-medium text-[#aaa3b0]">10–5,000 characters</span>
                            </div>

                            <textarea
                                id="contact-message"
                                name="message"
                                rows="7"
                                maxlength="5000"
                                required
                                placeholder="Tell us a little about your enquiry..."
                                class="w-full resize-y rounded-xl border border-[#e9e2ef] bg-[#fcfbfd] px-4 py-4 text-sm leading-7 text-[#211c29] outline-none transition-all placeholder:text-[#aaa3b0] hover:border-[#C391EE] focus:border-[#9870bd] focus:bg-white focus:ring-4 focus:ring-[#C391EE]/15"><?= e($values['message']) ?></textarea>

                            <p class="mt-2 text-[10px] leading-5 text-[#99919f]">
                                Please do not include confidential or time-sensitive legal information.
                            </p>
                        </div>

                        <div class="flex flex-col gap-5 border-t border-[#eee7f4] pt-7 sm:flex-row sm:items-center sm:justify-between">
                            <button
                                type="submit"
                                class="group inline-flex min-h-14 items-center justify-center gap-4 rounded-xl bg-[#24104f] px-8 py-4 text-xs font-bold uppercase tracking-[0.16em] text-white shadow-[0_10px_25px_rgba(36,16,79,0.18)] transition-all duration-300 hover:-translate-y-1 hover:bg-[#9870bd] hover:shadow-[0_14px_30px_rgba(152,112,189,0.25)]">
                                Send your enquiry
                                <span class="text-base transition-transform duration-300 group-hover:translate-x-1" aria-hidden="true">↗</span>
                            </button>

                            <p class="max-w-[220px] text-[10px] leading-5 text-[#99919f]">
                                <span class="font-bold text-[#77717e]">Privacy note.</span>
                                Your message is visible only to authenticated administrators. Email delivery is not configured.
                            </p>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
