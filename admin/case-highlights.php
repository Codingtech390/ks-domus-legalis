<?php



require_once __DIR__ . '/../includes/auth_check.php';



$pageTitle = 'Casework Management';

$pageDescription = 'Manage important cases and pro bono work.';

$isAdminPage = true;



$errors = [];



$allowedTypes = ['important', 'pro_bono'];



$values = [

    'case_type' => 'important',

    'title' => '',

    'court' => '',

    'case_reference' => '',

    'decision_year' => '',

    'role_description' => '',

    'summary' => '',

    'outcome' => '',

    'public_url' => '',

    'status' => 'draft',

    'display_order' => '0',

];



$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);

$editId = is_int($editId) && $editId > 0 ? $editId : 0;



$activeType = (string) ($_GET['type'] ?? 'all');



if (!in_array($activeType, ['all', 'important', 'pro_bono'], true)) {

    $activeType = 'all';
}



/**

 *|--------------------------------------------------------------------------*

 *| Save or delete*

 *|--------------------------------------------------------------------------*

 */



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();



    $action = (string) ($_POST['action'] ?? '');



    if ($action === 'delete') {
        $rawId = trim((string) ($_POST['id'] ?? ''));

        if ($rawId === '' || !ctype_digit($rawId) || (int) $rawId < 1) {
            set_flash('error', 'Invalid case record.');
            redirect('admin/case-highlights.php');
        }

        $id = (int) $rawId;

        $deleteStatement = $pdo->prepare(
            'DELETE FROM case_highlights WHERE id = :id'
        );

        $deleteStatement->execute(['id' => $id]);

        if ($deleteStatement->rowCount() > 0) {
            set_flash('success', 'Case record deleted successfully.');
        } else {
            set_flash('error', 'The case record was not found or was already deleted.');
        }

        redirect('admin/case-highlights.php');
    }

    if ($action === 'save') {

        $rawId = trim((string) ($_POST['id'] ?? '0'));

        if (
            $rawId !== '0'
            && (!ctype_digit($rawId) || (int) $rawId < 1)
        ) {
            $errors[] = 'Invalid case record ID.';
            $id = 0;
        } else {
            $id = (int) $rawId;
        }



        $values = [

            'case_type' => trim((string) ($_POST['case_type'] ?? '')),

            'title' => trim((string) ($_POST['title'] ?? '')),

            'court' => trim((string) ($_POST['court'] ?? '')),

            'case_reference' => trim((string) ($_POST['case_reference'] ?? '')),

            'decision_year' => trim((string) ($_POST['decision_year'] ?? '')),

            'role_description' => trim((string) ($_POST['role_description'] ?? '')),

            'summary' => trim((string) ($_POST['summary'] ?? '')),

            'outcome' => trim((string) ($_POST['outcome'] ?? '')),

            'public_url' => trim((string) ($_POST['public_url'] ?? '')),

            'status' => trim((string) ($_POST['status'] ?? 'draft')),

            'display_order' => trim((string) ($_POST['display_order'] ?? '0')),

        ];



        if (!in_array($values['case_type'], $allowedTypes, true)) {

            $errors[] = 'Select a valid casework category.';
        }



        if ($values['title'] === '' || mb_strlen($values['title']) > 255) {

            $errors[] = 'Enter a title of up to 255 characters.';
        }



        if ($values['summary'] === '') {

            $errors[] = 'A summary is required.';
        }

        if (mb_strlen($values['summary']) > 60000) {
            $errors[] = 'The case summary is too long. Please shorten it.';
        }

        if (mb_strlen($values['outcome']) > 60000) {
            $errors[] = 'The outcome description is too long. Please shorten it.';
        }

        if (mb_strlen($values['public_url']) > 500) {
            $errors[] = 'The public reference URL must be 500 characters or fewer.';
        }



        if (mb_strlen($values['court']) > 180) {

            $errors[] = 'Court name must be 180 characters or fewer.';
        }



        if (mb_strlen($values['case_reference']) > 180) {

            $errors[] = 'Case reference must be 180 characters or fewer.';
        }



        if (mb_strlen($values['role_description']) > 500) {

            $errors[] = 'Role description must be 500 characters or fewer.';
        }



        if (!in_array($values['status'], ['draft', 'published'], true)) {

            $errors[] = 'Select a valid publication status.';
        }



        if (

            $values['decision_year'] !== ''

            && (

                !ctype_digit($values['decision_year'])

                || (int) $values['decision_year'] < 1800

                || (int) $values['decision_year'] > 2100

            )

        ) {

            $errors[] = 'Enter a valid year between 1800 and 2100.';
        }



        if (

            $values['display_order'] === ''

            || !preg_match('/^-?\d{1,6}$/', $values['display_order'])

        ) {

            $errors[] = 'Display order must be a valid integer.';
        }



        if (

            $values['public_url'] !== ''

            && (

                !filter_var($values['public_url'], FILTER_VALIDATE_URL)

                || !in_array(

                    strtolower((string) parse_url($values['public_url'], PHP_URL_SCHEME)),

                    ['http', 'https'],

                    true

                )

            )

        ) {

            $errors[] = 'The public reference must be a valid HTTP or HTTPS URL.';
        }



        if (!$errors) {

            $year = $values['decision_year'] !== ''

                ? (int) $values['decision_year']

                : null;



            $data = [

                'case_type' => $values['case_type'],

                'title' => $values['title'],

                'court' => $values['court'] !== '' ? $values['court'] : null,

                'case_reference' => $values['case_reference'] !== ''

                    ? $values['case_reference']

                    : null,

                'decision_year' => $year,

                'role_description' => $values['role_description'] !== ''

                    ? $values['role_description']

                    : null,

                'summary' => $values['summary'],

                'outcome' => $values['outcome'] !== ''

                    ? $values['outcome']

                    : null,

                'public_url' => $values['public_url'] !== ''

                    ? $values['public_url']

                    : null,

                'status' => $values['status'],

                'display_order' => (int) $values['display_order'],

            ];



            if ($id > 0) {

                $checkStatement = $pdo->prepare(

                    'SELECT id FROM case_highlights WHERE id = :id'

                );

                $checkStatement->execute(['id' => $id]);



                if (!$checkStatement->fetchColumn()) {

                    $errors[] = 'The record you are editing no longer exists.';
                } else {

                    $updateStatement = $pdo->prepare(

                        'UPDATE case_highlights

                         SET case_type = :case_type,

                             title = :title,

                             court = :court,

                             case_reference = :case_reference,

                             decision_year = :decision_year,

                             role_description = :role_description,

                             summary = :summary,

                             outcome = :outcome,

                             public_url = :public_url,

                             status = :status,

                             display_order = :display_order

                         WHERE id = :id'

                    );



                    $updateStatement->execute($data + ['id' => $id]);
                }
            } else {

                $insertStatement = $pdo->prepare(

                    'INSERT INTO case_highlights (

                        case_type,

                        title,

                        court,

                        case_reference,

                        decision_year,

                        role_description,

                        summary,

                        outcome,

                        public_url,

                        status,

                        display_order,

                        created_by

                    ) VALUES (

                        :case_type,

                        :title,

                        :court,

                        :case_reference,

                        :decision_year,

                        :role_description,

                        :summary,

                        :outcome,

                        :public_url,

                        :status,

                        :display_order,

                        :created_by

                    )'

                );



                $insertStatement->execute(

                    $data + [

                        'created_by' => (int) $_SESSION['admin_id'],

                    ]

                );
            }



            if (!$errors) {

                set_flash(

                    'success',

                    $id > 0

                        ? 'Case record updated successfully.'

                        : 'Case record added successfully.'

                );



                redirect('admin/case-highlights.php?type=' . urlencode($values['case_type']));
            }
        }
    }
}



/**

 *|--------------------------------------------------------------------------*

 *| Load record for editing*

 *|--------------------------------------------------------------------------*

 */



if ($editId > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {

    $editStatement = $pdo->prepare(

        'SELECT * FROM case_highlights WHERE id = :id LIMIT 1'

    );



    $editStatement->execute(['id' => $editId]);

    $editRecord = $editStatement->fetch(PDO::FETCH_ASSOC);



    if ($editRecord) {

        foreach ($values as $key => $value) {

            $values[$key] = (string) ($editRecord[$key] ?? $value);
        }
    } else {

        set_flash('error', 'The requested case record was not found.');

        redirect('admin/case-highlights.php');
    }
}



/**

 *|--------------------------------------------------------------------------*

 *| Load records and metrics*

 *|--------------------------------------------------------------------------*

 */



$importantCount = (int) $pdo->query(

    "SELECT COUNT(*) FROM case_highlights

     WHERE case_type = 'important' AND status = 'published'"

)->fetchColumn();



$proBonoCount = (int) $pdo->query(

    "SELECT COUNT(*) FROM case_highlights

     WHERE case_type = 'pro_bono' AND status = 'published'"

)->fetchColumn();



$sql = 'SELECT * FROM case_highlights';

$params = [];



if ($activeType !== 'all') {

    $sql .= ' WHERE case_type = :case_type';

    $params['case_type'] = $activeType;
}



$sql .= ' ORDER BY display_order ASC, decision_year DESC, id DESC';



$listStatement = $pdo->prepare($sql);

$listStatement->execute($params);

$records = $listStatement->fetchAll(PDO::FETCH_ASSOC);



require_once __DIR__ . '/../includes/header.php';



?>



<section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <!-- PAGE HEADER -->
    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">



        <div>

            <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-[#9870bd]">

                Editorial administration

            </p>



            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-[#211c29] sm:text-4xl">

                Casework management<span class="text-[#C391EE]">.</span>

            </h1>



            <p class="mt-2 max-w-xl text-sm leading-7 text-slate-500">

                Manage selected important cases and pro bono work displayed on the public website.

            </p>

        </div>



        <a

            href="<?= e(url('admin/index.php')) ?>"

            class="inline-flex items-center justify-center border border-[#24104f]/15 px-5 py-3 text-xs font-semibold text-[#24104f] transition hover:border-[#C391EE] hover:bg-[#f8f4fc]">

            ← Back to dashboard

        </a>



    </div>
    <!-- METRICS -->
    <div class="mt-8 grid gap-4 sm:grid-cols-2">



        <div class="border border-[#C391EE]/30 bg-white p-6 shadow-sm">

            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">

                Published important cases

            </p>

            <p class="mt-3 text-4xl font-semibold text-[#24104f]">

                <?= number_format($importantCount) ?>

            </p>

        </div>



        <div class="border border-[#C391EE]/30 bg-white p-6 shadow-sm">

            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">

                Published pro bono cases

            </p>

            <p class="mt-3 text-4xl font-semibold text-[#9870bd]">

                <?= number_format($proBonoCount) ?>

            </p>

        </div>



    </div>
    <!-- EDITOR -->
    <section class="mt-10 border border-[#24104f]/10 bg-white shadow-sm">



        <div class="border-b border-[#24104f]/10 px-6 py-5">

            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#9870bd]">

                <?= $editId > 0 ? 'Update existing record' : 'Create a record' ?>

            </p>



            <h2 class="mt-2 text-xl font-semibold text-[#211c29]">

                <?= $editId > 0 ? 'Edit case details' : 'Add case details' ?>

            </h2>

        </div>





        <form

            method="post"

            action="<?= e(url('admin/case-highlights.php' . ($editId > 0 ? '?edit=' . $editId : ''))) ?>"

            class="space-y-6 p-6">



            <?= csrf_field() ?>



            <input type="hidden" name="action" value="save">

            <input type="hidden" name="id" value="<?= e((string) $editId) ?>">



            <?php if ($errors !== []): ?>

                <div class="border border-[#C391EE]/40 bg-[#f8f4fc] px-5 py-4 text-sm text-[#24104f]">

                    <p class="font-bold">Please correct the following:</p>

                    <ul class="mt-2 list-disc space-y-1 pl-5">

                        <?php foreach ($errors as $error): ?>

                            <li><?= e($error) ?></li>

                        <?php endforeach; ?>

                    </ul>

                </div>

            <?php endif; ?>





            <div class="grid gap-5 md:grid-cols-2">



                <label class="block">

                    <span class="mb-2 block text-xs font-semibold text-[#211c29]">

                        Section

                    </span>



                    <select

                        name="case_type"

                        required

                        class="w-full border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-[#9870bd]">



                        <option value="important" <?= $values['case_type'] === 'important' ? 'selected' : '' ?>>

                            Important cases

                        </option>



                        <option value="pro_bono" <?= $values['case_type'] === 'pro_bono' ? 'selected' : '' ?>>

                            Pro bono / public interest

                        </option>



                    </select>

                </label>





                <label class="block">

                    <span class="mb-2 block text-xs font-semibold text-[#211c29]">

                        Publication status

                    </span>



                    <select

                        name="status"

                        required

                        class="w-full border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-[#9870bd]">



                        <option value="draft" <?= $values['status'] === 'draft' ? 'selected' : '' ?>>

                            Draft — hidden from website

                        </option>



                        <option value="published" <?= $values['status'] === 'published' ? 'selected' : '' ?>>

                            Published — visible on website

                        </option>



                    </select>

                </label>



            </div>





            <label class="block">

                <span class="mb-2 block text-xs font-semibold text-[#211c29]">

                    Case / matter title *

                </span>



                <input

                    type="text"

                    name="title"

                    maxlength="255"

                    required

                    value="<?= e($values['title']) ?>"

                    placeholder="Enter the case or matter title"

                    class="w-full border border-slate-200 px-4 py-3 text-sm outline-none focus:border-[#9870bd]">

            </label>





            <div class="grid gap-5 md:grid-cols-3">



                <label class="block">

                    <span class="mb-2 block text-xs font-semibold text-[#211c29]">

                        Court / forum

                    </span>



                    <input

                        type="text"

                        name="court"

                        maxlength="180"

                        value="<?= e($values['court']) ?>"

                        placeholder="e.g. Supreme Court of India"

                        class="w-full border border-slate-200 px-4 py-3 text-sm outline-none focus:border-[#9870bd]">

                </label>





                <label class="block">

                    <span class="mb-2 block text-xs font-semibold text-[#211c29]">

                        Case reference

                    </span>



                    <input

                        type="text"

                        name="case_reference"

                        maxlength="180"

                        value="<?= e($values['case_reference']) ?>"

                        placeholder="Case number / citation"

                        class="w-full border border-slate-200 px-4 py-3 text-sm outline-none focus:border-[#9870bd]">

                </label>





                <label class="block">

                    <span class="mb-2 block text-xs font-semibold text-[#211c29]">

                        Year

                    </span>



                    <input

                        type="number"

                        name="decision_year"

                        min="1800"

                        max="2100"

                        value="<?= e($values['decision_year']) ?>"

                        placeholder="2026"

                        class="w-full border border-slate-200 px-4 py-3 text-sm outline-none focus:border-[#9870bd]">

                </label>



            </div>





            <label class="block">

                <span class="mb-2 block text-xs font-semibold text-[#211c29]">

                    Our role / involvement

                </span>



                <input

                    type="text"

                    name="role_description"

                    maxlength="500"

                    value="<?= e($values['role_description']) ?>"

                    placeholder="e.g. Represented the petitioner / legal research / advisory"

                    class="w-full border border-slate-200 px-4 py-3 text-sm outline-none focus:border-[#9870bd]">

            </label>





            <label class="block">

                <span class="mb-2 block text-xs font-semibold text-[#211c29]">

                    Case summary *

                </span>



                <textarea

                    name="summary"

                    rows="4"

                    required

                    placeholder="Explain the legal issue and background in clear, concise language."

                    class="w-full border border-slate-200 px-4 py-3 text-sm leading-7 outline-none focus:border-[#9870bd]"><?= e($values['summary']) ?></textarea>

            </label>





            <label class="block">

                <span class="mb-2 block text-xs font-semibold text-[#211c29]">

                    Outcome / public impact

                </span>



                <textarea

                    name="outcome"

                    rows="3"

                    placeholder="Describe the judgment, result or public-interest impact, where appropriate."

                    class="w-full border border-slate-200 px-4 py-3 text-sm leading-7 outline-none focus:border-[#9870bd]"><?= e($values['outcome']) ?></textarea>

            </label>





            <label class="block">

                <span class="mb-2 block text-xs font-semibold text-[#211c29]">

                    Public judgment / source URL

                </span>



                <input

                    type="url"

                    name="public_url"

                    maxlength="500"

                    value="<?= e($values['public_url']) ?>"

                    placeholder="https\://..."

                    class="w-full border border-slate-200 px-4 py-3 text-sm outline-none focus:border-[#9870bd]">



                <span class="mt-2 block text-xs text-slate-400">

                    Optional. Use a public court judgment or reliable source.

                </span>

            </label>





            <label class="block max-w-xs">

                <span class="mb-2 block text-xs font-semibold text-[#211c29]">

                    Display order

                </span>



                <input

                    type="number"

                    name="display_order"

                    value="<?= e($values['display_order']) ?>"

                    class="w-full border border-slate-200 px-4 py-3 text-sm outline-none focus:border-[#9870bd]">



                <span class="mt-2 block text-xs text-slate-400">

                    Lower numbers appear first.

                </span>

            </label>





            <div class="flex flex-wrap gap-3 border-t border-slate-100 pt-6">



                <button

                    type="submit"

                    class="inline-flex items-center justify-center bg-[#24104f] px-7 py-3.5 text-xs font-bold text-white transition hover:bg-[#9870bd]">

                    <?= $editId > 0 ? 'Save changes' : 'Add case record' ?>

                    <span class="ml-2">↗</span>

                </button>



                <?php if ($editId > 0): ?>

                    <a

                        href="<?= e(url('admin/case-highlights.php')) ?>"

                        class="inline-flex items-center justify-center border border-slate-200 px-7 py-3.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">

                        Cancel editing

                    </a>

                <?php endif; ?>



            </div>



        </form>



    </section>
    <!-- RECORD LIST -->
    <section class="mt-10 border border-[#24104f]/10 bg-white shadow-sm">



        <div class="border-b border-[#24104f]/10 px-6 py-5">



            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">



                <div>

                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#9870bd]">

                        Content library

                    </p>



                    <h2 class="mt-2 text-xl font-semibold text-[#211c29]">

                        Manage case records

                    </h2>

                </div>



                <div class="flex flex-wrap gap-2 text-xs font-semibold">



                    <a

                        href="<?= e(url('admin/case-highlights.php')) ?>"

                        class="border px-4 py-2 <?= $activeType === 'all' ? 'border-[#24104f] bg-[#24104f] text-white' : 'border-slate-200 text-slate-600 hover:border-[#C391EE]' ?>">

                        All

                    </a>



                    <a

                        href="<?= e(url('admin/case-highlights.php?type=important')) ?>"

                        class="border px-4 py-2 <?= $activeType === 'important' ? 'border-[#24104f] bg-[#24104f] text-white' : 'border-slate-200 text-slate-600 hover:border-[#C391EE]' ?>">

                        Important cases

                    </a>



                    <a

                        href="<?= e(url('admin/case-highlights.php?type=pro_bono')) ?>"

                        class="border px-4 py-2 <?= $activeType === 'pro_bono' ? 'border-[#24104f] bg-[#24104f] text-white' : 'border-slate-200 text-slate-600 hover:border-[#C391EE]' ?>">

                        Pro bono

                    </a>



                </div>



            </div>



        </div>





        <?php if ($records === []): ?>



            <div class="px-6 py-16 text-center">

                <p class="text-lg font-semibold text-[#211c29]">

                    No case records yet.

                </p>

                <p class="mt-2 text-sm text-slate-500">

                    Add your first record using the form above.

                </p>

            </div>



        <?php else: ?>



            <div class="overflow-x-auto">



                <table class="w-full min-w-[900px] text-left">



                    <thead class="bg-[#f8f6fb] text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">

                        <tr>

                            <th class="px-5 py-4">Case / matter</th>

                            <th class="px-5 py-4">Section</th>

                            <th class="px-5 py-4">Year</th>

                            <th class="px-5 py-4">Status</th>

                            <th class="px-5 py-4 text-right">Actions</th>

                        </tr>

                    </thead>



                    <tbody class="divide-y divide-slate-100">



                        <?php foreach ($records as $record): ?>



                            <tr class="hover:bg-[#fbf9fd]">



                                <td class="px-5 py-4">

                                    <p class="font-semibold text-[#211c29]">

                                        <?= e($record['title']) ?>

                                    </p>



                                    <p class="mt-1 text-xs text-slate-500">

                                        <?= e($record['court'] ?: 'Court not specified') ?>

                                    </p>

                                </td>



                                <td class="px-5 py-4 text-xs font-semibold text-[#9870bd]">

                                    <?= $record['case_type'] === 'pro_bono' ? 'Pro bono' : 'Important case' ?>

                                </td>



                                <td class="px-5 py-4 text-sm text-slate-500">

                                    <?= e((string) ($record['decision_year'] ?? '—')) ?>

                                </td>



                                <td class="px-5 py-4">

                                    <span class="inline-flex px-3 py-1 text-[10px] font-bold uppercase tracking-wider <?= $record['status'] === 'published' ? 'bg-[#f0e7fa] text-[#60358c]' : 'bg-amber-50 text-amber-700' ?>">

                                        <?= e($record['status']) ?>

                                    </span>

                                </td>



                                <td class="px-5 py-4">

                                    <div class="flex justify-end gap-2">



                                        <a

                                            href="<?= e(url('admin/case-highlights.php?edit=' . (int) $record['id'])) ?>"

                                            class="border border-slate-200 px-3 py-2 text-xs font-semibold text-[#24104f] hover:border-[#C391EE]">

                                            Edit

                                        </a>



                                        <form

                                            method="post"

                                            action="<?= e(url('admin/case-highlights.php')) ?>"

                                            data-confirm="Delete this case record permanently?">



                                            <?= csrf_field() ?>



                                            <input type="hidden" name="action" value="delete">

                                            <input type="hidden" name="id" value="<?= e((string) $record['id']) ?>">



                                            <button

                                                type="submit"

                                                class="border border-[#C391EE]/40 px-3 py-2 text-xs font-semibold text-[#24104f] transition hover:border-[#9870bd] hover:bg-[#f8f4fc] hover:text-[#9870bd]">

                                                Delete

                                            </button>



                                        </form>



                                    </div>

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
