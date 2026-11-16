<?php
declare(strict_types=1);
require_once __DIR__ . '/core/bootstrap.php';
require_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit('Invalid skill ID.');
}

// FIND THE CHILD RECORD AND ITS PARENT
$select = $pdo->prepare(
    "SELECT
        s.id,
        s.application_id,
        s.skill_name,
        ja.full_name AS applicant_name
     FROM application_skills s
     INNER JOIN job_applications ja
        ON ja.id = s.application_id
     WHERE s.id = :id"
);

$select->execute(['id' => $id]);

$skill = $select->fetch();

if (!$skill) {
    http_response_code(404);
    exit('Application skill not found.');
}

$errors = [];
$skillName = $skill['skill_name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $skillName = clean_post('skill_name');

    if ($skillName === '' || text_length($skillName) > 120) {
        $errors[] = 'Enter a valid skill name.';
    }

    if (!$errors) {

        $pdo->beginTransaction();

        try {

            $userId = current_user_id();

            // UPDATE THE CHILD ENTITY
            $update = $pdo->prepare(
                "UPDATE application_skills
                 SET
                    skill_name = :skill_name,
                    updated_by = :updated_by,
                    updated_at = CURRENT_TIMESTAMP,
                    last_updated_by = :last_updated_by,
                    last_updated = CURRENT_TIMESTAMP
                 WHERE id = :id"
            );

            $update->execute([
                'skill_name' => $skillName,
                'updated_by' => $userId,
                'last_updated_by' => $userId,
                'id' => $id
            ]);

            // SAVE THE ACTIVITY LOG
            record_activity(
                $pdo,
                $userId,
                'UPDATE',
                'CHILD SKILL',
                (int) $id,
                'Updated skill: ' . $skillName
            );

            $pdo->commit();

            set_flash(
                'success',
                'Application skill updated successfully.'
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

$pageTitle = 'Edit Application Skill';

require __DIR__ . '/partials/header.php';
?>

<section class="form-card narrow">

    <p class="eyebrow">CHILD ENTITY • UPDATE</p>

    <h1>Edit Application Skill</h1>

    <p class="muted">
        Parent Application:
        <strong><?= e($skill['applicant_name']) ?></strong>
        (Application #<?= (int) $skill['application_id'] ?>)
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
        action="skill_edit.php?id=<?= (int) $id ?>"
        class="stack-form"
    >

        <?= csrf_field() ?>

        <label>
            Skill Name

            <input
                type="text"
                name="skill_name"
                maxlength="120"
                value="<?= e($skillName) ?>"
                required
            >
        </label>

        <div class="button-row">

            <button class="button primary" type="submit">
                Update Skill
            </button>

            <a class="button secondary" href="index.php">
                Cancel
            </a>

        </div>

    </form>

</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
