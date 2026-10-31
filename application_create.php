<?php
declare(strict_types=1);
require_once __DIR__ . '/core/bootstrap.php';
require_login();

$errors = [];
$fullName = $email = $phone = $position = $education = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    $fullName = clean_post('full_name');
    $email = strtolower(clean_post('email'));
    $phone = clean_post('phone');
    $position = clean_post('position');
    $education = clean_post('education');

    if ($fullName === '' || text_length($fullName) > 150) $errors[] = 'Applicant name is required (150 characters maximum).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || text_length($email) > 150) $errors[] = 'Enter a valid applicant email.';
    if ($phone !== '' && text_length($phone) > 30) $errors[] = 'Phone number must be 30 characters or fewer.';
    if ($position === '' || text_length($position) > 120) $errors[] = 'Position is required (120 characters maximum).';
    if ($education === '' || text_length($education) > 150) $errors[] = 'Education is required (150 characters maximum).';

    if (!$errors) {
        $insert = $pdo->prepare(
            'INSERT INTO job_applications (full_name, email, phone, position, education, created_by)
             VALUES (:full_name, :email, :phone, :position, :education, :created_by)'
        );
        $insert->execute([
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone !== '' ? $phone : null,
            'position' => $position,
            'education' => $education,
            'created_by' => current_user_id(),
        ]);
        set_flash('success', 'Parent record inserted successfully. The system saved your user ID as Created by.');
        redirect('index.php');
    }
}

$pageTitle = 'Add Job Application';
require __DIR__ . '/partials/header.php';
?>
<section class="form-card">
    <p class="eyebrow">PARENT ENTITY • INSERT</p><h1>Add Job Application</h1>
    <p class="muted">The logged-in user's ID is saved automatically in <code>created_by</code>.</p>
    <?php if ($errors): ?><div class="alert error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post" action="application_create.php" class="form-grid">
        <?= csrf_field() ?>
        <label>Applicant full name<input name="full_name" maxlength="150" value="<?= e($fullName) ?>" required></label>
        <label>Email<input type="email" name="email" maxlength="150" value="<?= e($email) ?>" required></label>
        <label>Phone<input name="phone" maxlength="30" value="<?= e($phone) ?>"></label>
        <label>Position applying for<input name="position" maxlength="120" value="<?= e($position) ?>" required></label>
        <label class="full-width">Educational background<input name="education" maxlength="150" value="<?= e($education) ?>" required></label>
        <div class="full-width button-row"><button class="button primary" type="submit">Save application</button><a class="button secondary" href="index.php">Cancel</a></div>
    </form>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
