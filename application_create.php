<?php
declare(strict_types=1);

require_once __DIR__ . '/core/bootstrap.php';

require_login();

$errors = [];

$fullName = '';
$email = '';
$phone = '';
$position = '';
$education = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $fullName = clean_post('full_name');
    $email = strtolower(clean_post('email'));
    $phone = clean_post('phone');
    $position = clean_post('position');
    $education = clean_post('education');

    // VALIDATE THE INFORMATION
    if ($fullName === '' || text_length($fullName) > 150) {
        $errors[] = 'Enter a valid applicant name.';
    }

    if (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        text_length($email) > 150
    ) {
        $errors[] = 'Enter a valid email address.';
    }

    if ($phone !== '' && text_length($phone) > 30) {
        $errors[] = 'Phone number is too long.';
    }

    if ($position === '' || text_length($position) > 120) {
        $errors[] = 'Enter a valid position.';
    }

    if ($education === '' || text_length($education) > 150) {
        $errors[] = 'Enter a valid educational background.';
    }

    if (!$errors) {

        $pdo->beginTransaction();

        try {

            $userId = current_user_id();

            // INSERT THE PARENT ENTITY
            $insert = $pdo->prepare(
                "INSERT INTO job_applications
                (
                    full_name,
                    email,
                    phone,
                    position,
                    education,
                    created_by,
                    created_at,
                    date_added
                )
                VALUES
                (
                    :full_name,
                    :email,
                    :phone,
                    :position,
                    :education,
                    :created_by,
                    CURRENT_TIMESTAMP,
                    CURRENT_TIMESTAMP
                )"
            );

            $insert->execute([
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone ?: null,
                'position' => $position,
                'education' => $education,
                'created_by' => $userId
            ]);

            $newId = (int) $pdo->lastInsertId();

            // SAVE THE ACTIVITY LOG
            record_activity(
                $pdo,
                $userId,
                'INSERT',
                'PARENT APPLICATION',
                $newId,
                'Added applicant: ' . $fullName
            );

            $pdo->commit();

            set_flash(
                'success',
                'Job application added successfully.'
            );

            redirect('index.php');

        } catch (Throwable $exception) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }
}

$pageTitle = 'Add Job Application';

require __DIR__ . '/partials/header.php';
?>

<section class="form-card">

    <p class="eyebrow">PARENT ENTITY • INSERT</p>

    <h1>Add Job Application</h1>

    <p class="muted">
        Enter the applicant's information below.
    </p>

    <?php if ($errors): ?>

        <div class="alert error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

    <?php endif; ?>

    <form method="POST"
          action="application_create.php"
          class="form-grid">

        <?= csrf_field() ?>

        <label>
            Applicant Full Name
            <input
                type="text"
                name="full_name"
                maxlength="150"
                value="<?= e($fullName) ?>"
                required
            >
        </label>

        <label>
            Email Address
            <input
                type="email"
                name="email"
                maxlength="150"
                value="<?= e($email) ?>"
                required
            >
        </label>

        <label>
            Phone Number
            <input
                type="text"
                name="phone"
                maxlength="30"
                value="<?= e($phone) ?>"
            >
        </label>

        <label>
            Position Applying For
            <input
                type="text"
                name="position"
                maxlength="120"
                value="<?= e($position) ?>"
                required
            >
        </label>

        <label class="full-width">
            Educational Background
            <input
                type="text"
                name="education"
                maxlength="150"
                value="<?= e($education) ?>"
                required
            >
        </label>

        <div class="full-width button-row">

            <button class="button primary" type="submit">
                Save Application
            </button>

            <a class="button secondary" href="index.php">
                Cancel
            </a>

        </div>

    </form>

</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
