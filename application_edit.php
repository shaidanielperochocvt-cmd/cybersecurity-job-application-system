<?php
declare(strict_types=1);
require_once __DIR__ . '/core/bootstrap.php';
require_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(400); exit('Invalid application ID.'); }

$select = $pdo->prepare('SELECT id, full_name, email, phone, position, education FROM job_applications WHERE id = :id');
$select->execute(['id' => $id]);
$application = $select->fetch();
if (!$application) { http_response_code(404); exit('Job application not found.'); }

$errors = [];
$fullName = $application['full_name'];
$email = $application['email'];
$phone = $application['phone'] ?? '';
$position = $application['position'];
$education = $application['education'];

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
        $update = $pdo->prepare(
            'UPDATE job_applications
             SET full_name = :full_name, email = :email, phone = :phone, position = :position,
                 education = :education, updated_by = :updated_by, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $update->execute([
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone !== '' ? $phone : null,
            'position' => $position,
            'education' => $education,
            'updated_by' => current_user_id(),
            'id' => $id,
        ]);
        set_flash('success', 'Parent record updated. The system saved the user who made the update and the timestamp.');
        redirect('index.php');
    }
}

$pageTitle = 'Edit Job Application';
require __DIR__ . '/partials/header.php';
?>
<section class="form-card">
    <p class="eyebrow">PARENT ENTITY • UPDATE</p><h1>Edit Job Application #<?= (int) $id ?></h1>
    <p class="muted">The system automatically updates <code>updated_by</code> and <code>updated_at</code>.</p>
    <?php if ($errors): ?><div class="alert error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post" action="application_edit.php?id=<?= (int) $id ?>" class="form-grid">
        <?= csrf_field() ?>
        <label>Applicant full name<input name="full_name" maxlength="150" value="<?= e($fullName) ?>" required></label>
        <label>Email<input type="email" name="email" maxlength="150" value="<?= e($email) ?>" required></label>
        <label>Phone<input name="phone" maxlength="30" value="<?= e($phone) ?>"></label>
        <label>Position applying for<input name="position" maxlength="120" value="<?= e($position) ?>" required></label>
        <label class="full-width">Educational background<input name="education" maxlength="150" value="<?= e($education) ?>" required></label>
        <div class="full-width button-row"><button class="button primary" type="submit">Update application</button><a class="button secondary" href="index.php">Cancel</a></div>
    </form>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
