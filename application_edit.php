<?php
declare(strict_types=1);
require_once __DIR__ . '/core/bootstrap.php';
require_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit('Invalid application ID.');
}

// FIND THE SELECTED APPLICATION
$select = $pdo->prepare(
    'SELECT *
     FROM job_applications
     WHERE id = :id'
);

$select->execute(['id' => $id]);
$application = $select->fetch();

if (!$application) {
    http_response_code(404);
    exit('Job application not found.');
}

$errors = [];
$fullName = $application['full_name'];
$email = $application['email'];
$phone = $application['phone'] ?? '';
$position = $application['position'];
$education = $application['education'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();
    $fullName = clean_post('full_name');
    $email = strtolower(clean_post('email'));
    $phone = clean_post('phone');
    $position = clean_post('position');
    $education = clean_post('education');

    // VALIDATE INPUT
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

            // UPDATE THE PARENT ENTITY
            $update = $pdo->prepare(
                "UPDATE job_applications
                 SET
                    full_name = :full_name,
                    email = :email,
                    phone = :phone,
                    position = :position,
                    education = :education,

                    updated_by = :updated_by,
                    updated_at = CURRENT_TIMESTAMP,

                    last_updated_by = :last_updated_by,
                    last_updated = CURRENT_TIMESTAMP

                 WHERE id = :id"
            );

            $update->execute([
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone ?: null,
                'position' => $position,
                'education' => $education,

                // Save the user ID in both old and new columns.
                'updated_by' => $userId,
                'last_updated_by' => $userId,

                'id' => $id
            ]);

            // SAVE THE ACTIVITY LOG
            record_activity(
                $pdo,
                $userId,
                'UPDATE',
                'PARENT APPLICATION',
                (int) $id,
                'Updated applicant: ' . $fullName
            );

            $pdo->commit();

            set_flash(
                'success',
                'Job application updated successfully.'
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

$pageTitle = 'Edit Job Application';
require __DIR__ . '/partials/header.php';
?>
<section class="form-card">

    <p class="eyebrow">PARENT ENTITY • UPDATE</p>

    <h1>Edit Job Application #<?= (int) $id ?></h1>

    <p class="muted">
        Update the applicant's information below.
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

    <form
        method="POST"
        action="application_edit.php?id=<?= (int) $id ?>"
        class="form-grid"
    >

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
                Update Application
            </button>

            <a class="button secondary" href="index.php">
                Cancel
            </a>

        </div>

    </form>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
