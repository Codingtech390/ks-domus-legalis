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

    if (
        $values['message'] === '' ||
        mb_strlen($values['message']) < 10 ||
        mb_strlen($values['message']) > 5000
    ) {
        $errors[] = 'Write a message between 10 and 5,000 characters.';
    }

    if ($errors === []) {
        try {
            $statement = $pdo->prepare(
                'INSERT INTO contact_messages (name, email, subject, message)
                 VALUES (:name, :email, :subject, :message)',
            );
            $statement->execute($values);
            set_flash(
                'success',
                'Your message has been received. Our editorial team will review it.',
            );
            redirect('contact.php');
        } catch (PDOException $exception) {
            error_log('Contact form submission failed: ' . $exception->getMessage());
            $errors[] = 'Your message could not be saved. Please try again later.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>



<!-- CONTACT & CAREERS -->
<section class="relative isolate overflow-hidden bg-[#f3edf9] py-20 sm:py-24 lg:py-28">

    <!-- DECORATIVE ELEMENTS -->
    <div class="pointer-events-none absolute -right-24 -top-32 h-[420px] w-[420px] rounded-full border border-[#9870bd]/15"></div>
    <div class="pointer-events-none absolute -right-12 -top-20 h-[320px] w-[320px] rounded-full border border-[#9870bd]/20"></div>
    <div class="pointer-events-none absolute -bottom-36 -left-20 h-[360px] w-[360px] rounded-full bg-[#C391EE]/10 blur-3xl"></div>

    <div class="site-container relative z-10">

        <!-- SECTION INTRO -->
        <div class="mx-auto mb-14 max-w-3xl text-center sm:mb-16">

            <span class="inline-flex items-center gap-3 text-[10px] font-bold uppercase tracking-[0.3em] text-[#9870bd] sm:text-xs">

                <span class="h-px w-8 bg-[#9870bd]"></span>

                K&amp;S DOMUS LEGALIS

                <span class="h-px w-8 bg-[#9870bd]"></span>

            </span>

            <h1
                class="mt-7 text-4xl font-bold leading-[1.08] tracking-[-0.055em] text-[#211c29] sm:text-5xl lg:text-6xl"
                style="font-family:'Poppins',sans-serif;">

                Let's start a

                <span class="block text-[#9870bd]">
                    conversation.
                </span>

            </h1>

            <p class="mx-auto mt-6 max-w-2xl text-sm leading-8 text-[#77717e] sm:text-base">

                Whether you need legal guidance, wish to connect with our team,
                or are looking to build your career with us, we welcome you.

            </p>

        </div>


        <!-- TWO COLUMN LAYOUT -->
        <div class="grid grid-cols-1 gap-7 lg:grid-cols-[1.15fr_0.85fr] lg:gap-8">


            <!-- LEFT: CAREERS -->
            <div class="relative overflow-hidden rounded-[28px] border border-[#eee7f4] bg-white p-7 shadow-[0_15px_45px_rgba(36,16,79,0.05)] sm:p-9 lg:p-10">

                <!-- DECORATION -->
                <div class="pointer-events-none absolute -right-12 -top-12 h-48 w-48 rounded-full border border-[#C391EE]/15"></div>

                <div class="relative z-10">

                    <!-- ICON -->
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#f3edf9] text-[#9870bd]">

                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="7" width="18" height="14" rx="2"></rect>
                            <path d="M8 7V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v3"></path>
                            <path d="M3 12h18"></path>
                            <path d="M10 12v2h4v-2"></path>
                        </svg>

                    </div>


                    <!-- HEADING -->
                    <span class="mt-7 block text-[10px] font-bold uppercase tracking-[0.22em] text-[#9870bd]">
                        Careers
                    </span>

                    <h2
                        class="mt-3 text-3xl font-semibold leading-tight tracking-[-0.04em] text-[#211c29] sm:text-4xl"
                        style="font-family:'Poppins',sans-serif;">

                        Build your future

                        <span class="block text-[#9870bd]">
                            with us.
                        </span>

                    </h2>

                    <p class="mt-4 max-w-xl text-sm leading-7 text-[#77717e]">

                        We welcome motivated individuals who value thoughtful legal work,
                        professional integrity and continuous learning. Share your profile
                        with us and tell us where you would like to contribute.

                    </p>


                    <!-- CAREER FORM -->
                    <form
                        action="career-submit.php"
                        method="POST"
                        enctype="multipart/form-data"
                        class="mt-8 space-y-5">

                        <!-- NAME -->
                        <div>

                            <label for="career-name" class="mb-2 block text-xs font-semibold text-[#37313e]">
                                Full name <span class="text-[#9870bd]">*</span>
                            </label>

                            <input
                                type="text"
                                id="career-name"
                                name="name"
                                required
                                autocomplete="name"
                                placeholder="Your full name"
                                class="w-full rounded-xl border border-[#e8e0ef] bg-[#fcfbfd] px-4 py-3.5 text-sm text-[#211c29] outline-none transition placeholder:text-[#a39aa9] focus:border-[#C391EE] focus:ring-4 focus:ring-[#C391EE]/10">

                        </div>


                        <!-- EMAIL & PHONE -->
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                            <div>

                                <label for="career-email" class="mb-2 block text-xs font-semibold text-[#37313e]">
                                    Email address <span class="text-[#9870bd]">*</span>
                                </label>

                                <input
                                    type="email"
                                    id="career-email"
                                    name="email"
                                    required
                                    autocomplete="email"
                                    placeholder="yourname@gmail.com"
                                    class="w-full rounded-xl border border-[#e8e0ef] bg-[#fcfbfd] px-4 py-3.5 text-sm text-[#211c29] outline-none transition placeholder:text-[#a39aa9] focus:border-[#C391EE] focus:ring-4 focus:ring-[#C391EE]/10">

                            </div>


                            <div>

                                <label for="career-phone" class="mb-2 block text-xs font-semibold text-[#37313e]">
                                    Phone number <span class="text-[#9870bd]">*</span>
                                </label>

                                <input
                                    type="tel"
                                    id="career-phone"
                                    name="phone"
                                    required
                                    autocomplete="tel"
                                    placeholder="+91 98765 43210"
                                    class="w-full rounded-xl border border-[#e8e0ef] bg-[#fcfbfd] px-4 py-3.5 text-sm text-[#211c29] outline-none transition placeholder:text-[#a39aa9] focus:border-[#C391EE] focus:ring-4 focus:ring-[#C391EE]/10">

                            </div>

                        </div>


                        <!-- POSITION -->
                        <div>

                            <label for="career-position" class="mb-2 block text-xs font-semibold text-[#37313e]">
                                Applying for <span class="text-[#9870bd]">*</span>
                            </label>

                            <select
                                id="career-position"
                                name="position"
                                required
                                class="w-full rounded-xl border border-[#e8e0ef] bg-[#fcfbfd] px-4 py-3.5 text-sm text-[#211c29] outline-none transition focus:border-[#C391EE] focus:ring-4 focus:ring-[#C391EE]/10">

                                <option value="" disabled selected>Select a position</option>
                                <option value="Intern">Legal Intern</option>
                                <option value="Associate">Associate</option>
                                <option value="Senior Associate">Senior Associate</option>
                                <option value="Legal Consultant">Legal Consultant</option>
                                <option value="Research Associate">Research Associate</option>
                                <option value="Other">Other / General Application</option>

                            </select>

                        </div>


                        <!-- CV UPLOAD -->
                        <div>

                            <label for="career-cv" class="mb-2 block text-xs font-semibold text-[#37313e]">
                                Upload your CV / Resume <span class="text-[#9870bd]">*</span>
                            </label>

                            <label
                                for="career-cv"
                                class="group flex cursor-pointer flex-col items-center justify-center rounded-2xl border border-dashed border-[#C391EE]/50 bg-[#f8f6fb] px-5 py-7 text-center transition hover:border-[#9870bd] hover:bg-[#f3edf9]">

                                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-[#9870bd] shadow-sm">

                                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <path d="M17 8l-5-5-5 5"></path>
                                        <path d="M12 3v12"></path>
                                    </svg>

                                </span>

                                <span class="mt-3 text-sm font-semibold text-[#37313e]">
                                    Click to upload your CV
                                </span>

                                <span class="mt-1 text-xs text-[#898290]">
                                    PDF or DOC/DOCX · Maximum file size 5 MB
                                </span>

                                <span id="career-cv-name" class="mt-2 text-xs font-medium text-[#9870bd]">
                                    No file selected
                                </span>

                            </label>

                            <input
                                type="file"
                                id="career-cv"
                                name="cv"
                                required
                                accept=".pdf,.doc,.docx"
                                class="sr-only">

                        </div>


                        <!-- MESSAGE -->
                        <div>

                            <label for="career-message" class="mb-2 block text-xs font-semibold text-[#37313e]">
                                Brief introduction <span class="text-[#9870bd]">(Optional)</span>
                            </label>

                            <textarea
                                id="career-message"
                                name="message"
                                rows="3"
                                placeholder="Tell us briefly about yourself, your experience or interests..."
                                class="w-full resize-y rounded-xl border border-[#e8e0ef] bg-[#fcfbfd] px-4 py-3.5 text-sm text-[#211c29] outline-none transition placeholder:text-[#a39aa9] focus:border-[#C391EE] focus:ring-4 focus:ring-[#C391EE]/10"></textarea>

                        </div>


                        <!-- SUBMIT -->
                        <button
                            type="submit"
                            class="group inline-flex w-full items-center justify-center gap-3 rounded-xl bg-[#24104f] px-6 py-4 text-sm font-semibold text-white transition hover:bg-[#9870bd]">

                            Submit application

                            <span class="transition-transform duration-300 group-hover:translate-x-1">
                                ↗
                            </span>

                        </button>


                        <p class="text-center text-[11px] leading-5 text-[#898290]">

                            Your information and uploaded CV will be treated confidentially.

                        </p>

                    </form>

                </div>

            </div>


            <!-- RIGHT: CONTACT INFORMATION -->
            <div class="flex flex-col gap-7">


                <!-- CONTACT PANEL -->
                <div class="relative overflow-hidden rounded-[28px] bg-[#24104f] p-7 text-white shadow-[0_20px_60px_rgba(36,16,79,0.12)] sm:p-9 lg:p-10">

                    <!-- DECORATION -->
                    <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full border border-white/[0.08]"></div>
                    <div class="pointer-events-none absolute -bottom-20 -left-16 h-56 w-56 rounded-full bg-[#C391EE]/10 blur-3xl"></div>

                    <div class="relative z-10">

                        <span class="text-[10px] font-bold uppercase tracking-[0.22em] text-[#d8b9f5]">
                            Get in touch
                        </span>

                        <h2
                            class="mt-4 text-3xl font-semibold leading-tight tracking-[-0.04em] sm:text-4xl"
                            style="font-family:'Poppins',sans-serif;">

                            We're here

                            <span class="block text-[#d8b9f5]">
                                to help.
                            </span>

                        </h2>

                        <p class="mt-4 text-sm leading-7 text-white/60">

                            Reach out to us for enquiries, correspondence
                            or to discuss how we may assist you.

                        </p>


                        <!-- PHONE -->
                        <a
                            href="tel:+911234567890"
                            class="group mt-8 flex items-center gap-4 rounded-2xl border border-white/10 bg-white/[0.05] p-5 transition hover:border-[#C391EE]/40 hover:bg-white/[0.08]">

                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#C391EE]/15 text-[#d8b9f5]">

                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.96.36 1.9.72 2.8a2 2 0 0 1-.45 2.11L8.1 9.9a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.36 1.84.6 2.8.72A2 2 0 0 1 22 16.92z"></path>
                                </svg>

                            </span>

                            <span class="min-w-0 flex-1">

                                <span class="block text-[10px] font-semibold uppercase tracking-[0.16em] text-white/45">
                                    Call us
                                </span>

                                <span class="mt-1 block break-all text-base font-semibold text-white sm:text-lg">
                                    +91 12345 67890
                                </span>

                            </span>

                            <span class="text-lg text-[#C391EE] transition-transform group-hover:translate-x-1">
                                ↗
                            </span>

                        </a>


                        <!-- EMAIL -->
                        <a
                            href="mailto:contact@ksdomuslegalis.com"
                            class="group mt-4 flex items-center gap-4 rounded-2xl border border-white/10 bg-white/[0.05] p-5 transition hover:border-[#C391EE]/40 hover:bg-white/[0.08]">

                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#C391EE]/15 text-[#d8b9f5]">

                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                    <path d="m3 7 9 6 9-6"></path>
                                </svg>

                            </span>

                            <span class="min-w-0 flex-1">

                                <span class="block text-[10px] font-semibold uppercase tracking-[0.16em] text-white/45">
                                    Email us
                                </span>

                                <span class="mt-1 block break-all text-sm font-semibold text-white sm:text-base">
                                    contact@ksdomuslegalis.com
                                </span>

                            </span>

                            <span class="text-lg text-[#C391EE] transition-transform group-hover:translate-x-1">
                                ↗
                            </span>

                        </a>


                        <!-- CONTACT NOTE -->
                        <div class="mt-7 border-t border-white/10 pt-6">

                            <p class="text-xs leading-6 text-white/50">

                                For case-related communication and coordination,
                                we aim to remain responsive and accessible.

                            </p>

                        </div>

                    </div>

                </div>


                <!-- SMALL EDITORIAL PANEL -->
                <div class="rounded-[28px] border border-[#eee7f4] bg-white p-7 shadow-[0_15px_45px_rgba(36,16,79,0.04)] sm:p-8">

                    <div class="flex gap-5">

                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#f3edf9] font-serif text-2xl text-[#9870bd]">
                            §
                        </span>

                        <div>

                            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#9870bd]">
                                Our commitment
                            </span>

                            <h3 class="mt-2 text-lg font-semibold tracking-tight text-[#211c29]">
                                Thoughtful communication.
                            </h3>

                            <p class="mt-2 text-sm leading-7 text-[#77717e]">

                                Every enquiry deserves attention. We believe
                                meaningful communication begins with listening,
                                understanding and responding with clarity.

                            </p>

                        </div>

                    </div>

                </div>


            </div>

        </div>

    </div>

</section>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const cvInput = document.getElementById("career-cv");
    const cvName = document.getElementById("career-cv-name");

    if (!cvInput || !cvName) return;

    cvInput.addEventListener("change", function () {

        const file = this.files[0];

        if (!file) {
            cvName.textContent = "No file selected";
            return;
        }

        // Maximum 5 MB
        const maxSize = 5 * 1024 * 1024;

        if (file.size > maxSize) {

            alert("Your CV must be 5 MB or smaller.");

            this.value = "";
            cvName.textContent = "No file selected";

            return;
        }

        const allowedExtensions = ["pdf", "doc", "docx"];
        const extension = file.name.split(".").pop().toLowerCase();

        if (!allowedExtensions.includes(extension)) {

            alert("Please upload a PDF, DOC, or DOCX file.");

            this.value = "";
            cvName.textContent = "No file selected";

            return;
        }

        cvName.textContent = file.name;

    });

});
</script>




<!-- CONTACT CONTENT -->
<section class="relative overflow-hidden bg-[#f3edf9] py-20 sm:py-24 lg:py-28">

    <!-- BACKGROUND DECORATION -->
    <div class="pointer-events-none absolute -right-24 -top-32 h-[420px] w-[420px] rounded-full border border-[#9870bd]/15"></div>
    <div class="pointer-events-none absolute -right-12 -top-20 h-[320px] w-[320px] rounded-full border border-[#9870bd]/20"></div>
    <div class="pointer-events-none absolute -bottom-36 -left-20 h-[360px] w-[360px] rounded-full bg-[#C391EE]/10 blur-3xl"></div>

    <div class="site-container relative z-10">

        <!-- SECTION HEADING -->
        <div class="mx-auto mb-14 max-w-3xl text-center sm:mb-16">

            <span class="inline-flex items-center gap-3 text-[10px] font-bold uppercase tracking-[0.3em] text-[#9870bd] sm:text-xs">
                <span class="h-px w-8 bg-[#9870bd]"></span>
                K&amp;S DOMUS LEGALIS
                <span class="h-px w-8 bg-[#9870bd]"></span>
            </span>

            <h2
                class="mt-6 text-4xl font-bold leading-[1.1] tracking-[-0.055em] text-[#211c29] sm:text-5xl lg:text-6xl"
                style="font-family:'Poppins',sans-serif;">

                A conversation

                <span class="block text-[#9870bd]">
                    begins here.
                </span>
            </h2>

            <p class="mx-auto mt-6 max-w-2xl text-sm leading-8 text-[#77717e] sm:text-base">

                Every enquiry deserves attention. Whether you have a question,
                a perspective to share or wish to connect with our journal,
                we welcome hearing from you.

            </p>

        </div>


        <!-- MAIN CONTENT -->
        <div class="grid items-stretch gap-7 lg:grid-cols-[0.8fr_1.2fr] lg:gap-10">


            <!-- LEFT: CONTACT / EDITORIAL PANEL -->
            <aside class="relative flex flex-col overflow-hidden rounded-[28px] bg-[#24104f] p-7 text-white shadow-[0_20px_60px_rgba(36,16,79,0.12)] sm:p-9 lg:p-10">

                <!-- DECORATION -->
                <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full border border-white/[0.08]"></div>
                <div class="pointer-events-none absolute -right-8 -top-8 h-44 w-44 rounded-full border border-white/[0.08]"></div>
                <div class="pointer-events-none absolute -bottom-24 -left-20 h-64 w-64 rounded-full bg-[#C391EE]/10 blur-3xl"></div>

                <div class="relative z-10">

                    <span class="text-[10px] font-bold uppercase tracking-[0.28em] text-[#d8b9f5]">
                        01 / GET IN TOUCH
                    </span>

                    <h3
                        class="mt-6 max-w-md text-3xl font-semibold leading-[1.2] tracking-[-0.04em] sm:text-4xl"
                        style="font-family:'Poppins',sans-serif;">

                        Every meaningful exchange starts somewhere.

                    </h3>

                    <p class="mt-6 max-w-md text-sm leading-8 text-white/65">

                        Have an editorial enquiry, feedback on an article,
                        a question about our journal or something you would
                        like to share? Our team welcomes thoughtful correspondence.

                    </p>


                    <!-- CONTACT INFORMATION -->
                    <div class="mt-10 space-y-5 border-t border-white/10 pt-8">


                        <!-- EDITORIAL -->
                        <div class="flex gap-4">

                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#C391EE]/15 text-[#d8b9f5]">

                                <span class="font-serif text-xl" aria-hidden="true">
                                    §
                                </span>

                            </div>

                            <div>

                                <h4 class="text-sm font-semibold text-white">
                                    Editorial correspondence
                                </h4>

                                <p class="mt-2 text-xs leading-6 text-white/55">

                                    Share your thoughts, questions and suggestions
                                    with our editorial team.

                                </p>

                            </div>

                        </div>


                        <!-- RESPONSIBLE COMMUNICATION -->
                        <div class="flex gap-4">

                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#C391EE]/15 text-[#d8b9f5]">

                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11z"></path>
                                    <path d="m9 12 2 2 4-4"></path>
                                </svg>

                            </div>

                            <div>

                                <h4 class="text-sm font-semibold text-white">
                                    A considered response
                                </h4>

                                <p class="mt-2 text-xs leading-6 text-white/55">

                                    Messages are securely stored for review
                                    by authenticated administrators.

                                </p>

                            </div>

                        </div>


                    </div>


                    <!-- JOURNAL LINK -->
                    <a
                        href="<?= e(url('index.php#latest-news')) ?>"
                        class="group mt-10 inline-flex items-center gap-3 text-xs font-bold uppercase tracking-[0.16em] text-[#d8b9f5] transition-colors hover:text-white">

                        Explore the journal

                        <span class="text-base transition-transform duration-300 group-hover:translate-x-1" aria-hidden="true">
                            ↗
                        </span>

                    </a>

                </div>


                <!-- BOTTOM DECORATION -->
                <div class="relative z-10 mt-auto hidden select-none pt-12 text-[100px] font-light leading-none text-white/[0.04] lg:block" aria-hidden="true">
                    §
                </div>

            </aside>


            <!-- RIGHT: CONTACT FORM -->
            <div class="relative overflow-hidden rounded-[28px] border border-[#eee7f4] bg-white p-6 shadow-[0_20px_70px_rgba(52,30,76,0.07)] sm:p-9 lg:p-11">

                <!-- DECORATION -->
                <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-[#C391EE]/10 blur-3xl"></div>

                <div class="relative z-10">


                    <!-- FORM HEADER -->
                    <div class="mb-9 flex items-start justify-between gap-4 border-b border-[#eee7f4] pb-7">

                        <div>

                            <span class="text-[10px] font-bold uppercase tracking-[0.24em] text-[#9870bd]">
                                02 / WRITE TO US
                            </span>

                            <h3
                                class="mt-3 text-2xl font-semibold tracking-[-0.04em] text-[#211c29] sm:text-3xl"
                                style="font-family:'Poppins',sans-serif;">

                                Send an enquiry.

                            </h3>

                            <p class="mt-2 text-xs leading-6 text-[#77717e]">

                                Complete the details below and share your message.

                            </p>

                        </div>


                        <div class="hidden h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#f3edf9] text-xl text-[#9870bd] sm:flex">

                            <span aria-hidden="true">
                                ↗
                            </span>

                        </div>

                    </div>


                    <!-- ERRORS -->
                    <?php foreach ($errors as $error): ?>

                        <div
                            class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                            role="alert">

                            <?= e($error) ?>

                        </div>

                    <?php endforeach; ?>


                    <!-- FORM -->
                    <form method="post" action="<?= e(url('contact.php')) ?>" class="space-y-6">

                        <?= csrf_field() ?>


                        <!-- HONEYPOT -->
                        <div class="contact-honeypot" aria-hidden="true">

                            <label for="contact-website">
                                Website
                            </label>

                            <input
                                id="contact-website"
                                type="text"
                                name="website"
                                tabindex="-1"
                                autocomplete="off">

                        </div>


                        <!-- NAME & EMAIL -->
                        <div class="grid gap-6 sm:grid-cols-2">

                            <div>

                                <label
                                    for="contact-name"
                                    class="mb-2.5 block text-xs font-bold text-[#37313e]">

                                    Your name
                                    <span class="text-[#9870bd]">*</span>

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

                                <label
                                    for="contact-email"
                                    class="mb-2.5 block text-xs font-bold text-[#37313e]">

                                    Email address
                                    <span class="text-[#9870bd]">*</span>

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


                        <!-- SUBJECT -->
                        <div>

                            <label
                                for="contact-subject"
                                class="mb-2.5 block text-xs font-bold text-[#37313e]">

                                Subject
                                <span class="text-[#9870bd]">*</span>

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


                        <!-- MESSAGE -->
                        <div>

                            <div class="mb-2.5 flex items-center justify-between gap-3">

                                <label
                                    for="contact-message"
                                    class="block text-xs font-bold text-[#37313e]">

                                    Your message
                                    <span class="text-[#9870bd]">*</span>

                                </label>

                                <span class="text-[10px] font-medium text-[#aaa3b0]">
                                    10–5,000 characters
                                </span>

                            </div>


                            <textarea
                                id="contact-message"
                                name="message"
                                rows="7"
                                maxlength="5000"
                                required
                                placeholder="Tell us a little about your enquiry..."
                                class="w-full resize-y rounded-xl border border-[#e9e2ef] bg-[#fcfbfd] px-4 py-4 text-sm leading-7 text-[#211c29] outline-none transition-all placeholder:text-[#aaa3b0] hover:border-[#C391EE] focus:border-[#9870bd] focus:bg-white focus:ring-4 focus:ring-[#C391EE]/15"><?= e(
                                    $values['message'],
                                ) ?></textarea>


                            <p class="mt-2 text-[10px] leading-5 text-[#99919f]">

                                Please do not include confidential or time-sensitive legal information.

                            </p>

                        </div>


                        <!-- SUBMIT FOOTER -->
                        <div class="flex flex-col gap-5 border-t border-[#eee7f4] pt-7 sm:flex-row sm:items-center sm:justify-between">


                            <button
                                type="submit"
                                class="group inline-flex min-h-14 items-center justify-center gap-4 rounded-xl bg-[#24104f] px-8 py-4 text-xs font-bold uppercase tracking-[0.16em] text-white shadow-[0_10px_25px_rgba(36,16,79,0.18)] transition-all duration-300 hover:-translate-y-1 hover:bg-[#9870bd] hover:shadow-[0_14px_30px_rgba(152,112,189,0.25)]">

                                Send your enquiry

                                <span
                                    class="text-base transition-transform duration-300 group-hover:translate-x-1"
                                    aria-hidden="true">

                                    ↗

                                </span>

                            </button>


                            <p class="max-w-[220px] text-[10px] leading-5 text-[#99919f]">

                                <span class="font-bold text-[#77717e]">
                                    Privacy note.
                                </span>

                                Your message is visible only to authenticated administrators.
                                Email delivery is not configured.

                            </p>

                        </div>


                    </form>

                </div>

            </div>


        </div>

    </div>

</section>


<?php require_once __DIR__ . '/includes/footer.php'; ?>
