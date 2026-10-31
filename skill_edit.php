<?php
declare(strict_types=1);
require_once __DIR__ . '/core/bootstrap.php';
require_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(400); exit('Invalid skill ID.'); }
$select = $pdo->prepare(
    'SELECT s.id, s.application_id, s.skill_name, ja.full_name
     FROM application_skills s INNER JOIN job_applications ja ON ja.id = s.application_id
     WHERE s.id = :id'
);
$select->execute(['id' => $id]);
$skill = $select->fetch();
if (!$skill) { http_response_code(404); exit('Skill record not found.'); }

$errors = [];
$skillName = $skill['skill_name'];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    $skillName = clean_post('skill_name');
    if ($skillName === '' || text_length($skillName) > 120) $errors[] = 'Enter a skill name with no more than 120 characters.';

    if (!$errors) {
        $update = $pdo->prepare(
            'UPDATE application_skills SET skill_name = :skill_name, updated_by = :updated_by,
             updated_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $update->execute([
            'skill_name' => $skillName,
            'updated_by' => current_user_id(),
            'id' => $id,
        ]);
        set_flash('success', 'Child skill record updated. The system saved the user who made the update and the timestamp.');
        redirect('index.php');
    }
}

$pageTitle = 'Edit Skill';
require __DIR__ . '/partials/header.php';
?>
<section class="form-card narrow">
    <p class="eyebrow">CHILD ENTITY • UPDATE</p><h1>Edit Skill #<?= (int) $id ?></h1>
    <p class="muted">Parent application: <strong><?= e($skill['full_name']) ?></strong>. The system automatically updates the audit fields.</p>
    <?php if ($errors): ?><div class="alert error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post" action="skill_edit.php?id=<?= (int) $id ?>" class="stack-form">
        <?= csrf_field() ?>
        <label>Skill name<input name="skill_name" maxlength="120" value="<?= e($skillName) ?>" required></label>
        <div class="button-row"><button class="button primary" type="submit">Update skill</button><a class="button secondary" href="index.php">Cancel</a></div>
    </form>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
