<?php
declare(strict_types=1);

require_once __DIR__ . '/core/bootstrap.php';

require_login();

$applicationId = filter_input(
    INPUT_GET,
    'application_id',
    FILTER_VALIDATE_INT
);

if (!$applicationId || $applicationId < 1) {
    http_response_code(400);
    exit('Invalid application ID.');
}

// FIND THE PARENT APPLICATION
$parentQuery = $pdo->prepare(
    'SELECT id, full_name
     FROM job_applications
     WHERE id = :id'
);

$parentQuery->execute([
    'id' => $applicationId
]);

$application = $parentQuery->fetch();

if (!$application) {
    http_response_code(404);
    exit('Parent application not found.');
}

$errors = [];
$skillName = '';

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

            // INSERT THE CHILD ENTITY
            $insert = $pdo->prepare(
                "INSERT INTO application_skills
                (
                    application_id,
                    skill_name,
                    created_by,
                    created_at,
                    date_added
                )
                VALUES
                (
                    :application_id,
                    :skill_name,
                    :created_by,
                    CURRENT_TIMESTAMP,
                    CURRENT_TIMESTAMP
                )"
            );

            $insert->execute([
                'application_id' => $applicationId,
                'skill_name' => $skillName,
                'created_by' => $userId
            ]);

            $newId = (int) $pdo->lastInsertId();

            // SAVE THE ACTIVITY LOG
            record_activity(
                $pdo,
                $userId,
                'INSERT',
                'CHILD SKILL',
                $newId,
                'Added skill: ' . $skillName
            );

            $pdo->commit();

            set_flash(
                'success',
                'Application skill added successfully.'
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

$pageTitle = 'Add Application Skill';

require __DIR__ . '/partials/header.php';
?>

<section class="form-card narrow">

    <p class="eyebrow">CHILD ENTITY • INSERT</p>

    <h1>Add Application Skill</h1>

    <p class="muted">
        Parent Application:
        <strong><?= e($application['full_name']) ?></strong>
        (ID #<?= (int) $applicationId ?>)
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
        action="skill_create.php?application_id=<?= (int) $applicationId ?>"
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
                placeholder="Example: PHP Programming"
                required
            >
        </label>

        <div class="button-row">

            <button class="button primary" type="submit">
                Save Skill
            </button>

            <a class="button secondary" href="index.php">
                Cancel
            </a>

        </div>

    </form>

</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
