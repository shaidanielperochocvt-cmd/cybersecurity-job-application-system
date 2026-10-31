<?php
declare(strict_types=1);
require_once __DIR__ . '/core/bootstrap.php';
require_login();

$applicationId = filter_input(INPUT_GET, 'application_id', FILTER_VALIDATE_INT);
if (!$applicationId || $applicationId < 1) { http_response_code(400); exit('Invalid parent application ID.'); }
$parent = $pdo->prepare('SELECT id, full_name FROM job_applications WHERE id = :id');
$parent->execute(['id' => $applicationId]);
$application = $parent->fetch();
if (!$application) { http_response_code(404); exit('Parent job application not found.'); }

$errors = [];
$skillName = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    $skillName = clean_post('skill_name');
    if ($skillName === '' || text_length($skillName) > 120) $errors[] = 'Enter a skill name with no more than 120 characters.';

    if (!$errors) {
        $insert = $pdo->prepare('INSERT INTO application_skills (application_id, skill_name, created_by) VALUES (:application_id, :skill_name, :created_by)');
        $insert->execute([
            'application_id' => $applicationId,
            'skill_name' => $skillName,
            'created_by' => current_user_id(),
        ]);
        set_flash('success', 'Child skill record inserted and linked to its parent application.');
        redirect('index.php');
    }
}

$pageTitle = 'Add Application Skill';
require __DIR__ . '/partials/header.php';
?>
<section class="form-card narrow">
    <p class="eyebrow">CHILD ENTITY • INSERT</p><h1>Add a Skill</h1>
    <p class="muted">Parent application: <strong><?= e($application['full_name']) ?></strong> (ID #<?= (int) $applicationId ?>)</p>
    <?php if ($errors): ?><div class="alert error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post" action="skill_create.php?application_id=<?= (int) $applicationId ?>" class="stack-form">
        <?= csrf_field() ?>
        <label>Skill name<input name="skill_name" maxlength="120" value="<?= e($skillName) ?>" required placeholder="Example: Network Security"></label>
        <div class="button-row"><button class="button primary" type="submit">Save skill</button><a class="button secondary" href="index.php">Cancel</a></div>
    </form>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
