<?php
declare(strict_types=1);
require_once __DIR__ . '/core/bootstrap.php';
require_login();

$parentQuery = $pdo->query(
    'SELECT ja.id, ja.full_name, ja.email, ja.phone, ja.position, ja.education,
            ja.created_at, ja.updated_at,
            CONCAT(creator.first_name, " ", creator.last_name) AS created_by_name,
            CASE WHEN updater.id IS NULL THEN NULL ELSE CONCAT(updater.first_name, " ", updater.last_name) END AS updated_by_name
     FROM job_applications ja
     INNER JOIN users creator ON creator.id = ja.created_by
     LEFT JOIN users updater ON updater.id = ja.updated_by
     ORDER BY ja.id DESC'
);
$applications = $parentQuery->fetchAll();

$childQuery = $pdo->query(
    'SELECT s.id, s.application_id, s.skill_name, s.created_at, s.updated_at,
            ja.full_name AS applicant_name,
            CONCAT(creator.first_name, " ", creator.last_name) AS created_by_name,
            CASE WHEN updater.id IS NULL THEN NULL ELSE CONCAT(updater.first_name, " ", updater.last_name) END AS updated_by_name
     FROM application_skills s
     INNER JOIN job_applications ja ON ja.id = s.application_id
     INNER JOIN users creator ON creator.id = s.created_by
     LEFT JOIN users updater ON updater.id = s.updated_by
     ORDER BY s.id DESC'
);
$skills = $childQuery->fetchAll();

$pageTitle = 'Dashboard';
require __DIR__ . '/partials/header.php';
?>
<section class="welcome-row">
    <div>
        <p class="eyebrow">MAIN DASHBOARD</p>
        <h1>Welcome, <?= e($_SESSION['user']['first_name']) ?>!</h1>
        <p class="muted">Manage job applications (parent records) and the skills attached to each application (child records).</p>
    </div>
    <a class="button primary" href="application_create.php">+ Add application</a>
</section>

<section class="section-card">
    <div class="section-heading">
        <div><p class="eyebrow">PARENT ENTITY</p><h2>Job Applications</h2><p class="muted">Each application can have multiple skill records.</p></div>
        <span class="count-pill"><?= count($applications) ?> records</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Applicant</th><th>Contact</th><th>Position</th><th>Education</th><th>Created by</th><th>Last updated by</th><th>Last updated</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (!$applications): ?>
                <tr><td colspan="9" class="empty-cell">No job applications yet. Add the first parent record.</td></tr>
            <?php else: foreach ($applications as $application): ?>
                <tr>
                    <td><?= (int) $application['id'] ?></td>
                    <td><strong><?= e($application['full_name']) ?></strong><small><?= e($application['email']) ?></small></td>
                    <td><?= e($application['phone'] ?: '—') ?></td>
                    <td><?= e($application['position']) ?></td>
                    <td><?= e($application['education']) ?></td>
                    <td><?= e($application['created_by_name']) ?><small><?= e($application['created_at']) ?></small></td>
                    <td><?= e($application['updated_by_name'] ?: 'Not updated yet') ?></td>
                    <td><?= e($application['updated_at']) ?></td>
                    <td class="actions">
                        <a href="application_edit.php?id=<?= (int) $application['id'] ?>">Edit</a>
                        <a href="skill_create.php?application_id=<?= (int) $application['id'] ?>">Add skill</a>
                        <form method="post" action="application_delete.php" onsubmit="return confirm('Delete this application and all its child skill records?');">
                            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $application['id'] ?>">
                            <button class="link danger-link" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="section-card">
    <div class="section-heading">
        <div><p class="eyebrow">CHILDREN ENTITY</p><h2>Application Skills</h2><p class="muted">Every skill belongs to one job application.</p></div>
        <span class="count-pill"><?= count($skills) ?> records</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Parent application</th><th>Skill</th><th>Created by</th><th>Last updated by</th><th>Last updated</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (!$skills): ?>
                <tr><td colspan="7" class="empty-cell">No skills yet. Add a skill from a job application above.</td></tr>
            <?php else: foreach ($skills as $skill): ?>
                <tr>
                    <td><?= (int) $skill['id'] ?></td>
                    <td><?= e($skill['applicant_name']) ?> <small>(Application #<?= (int) $skill['application_id'] ?>)</small></td>
                    <td><strong><?= e($skill['skill_name']) ?></strong></td>
                    <td><?= e($skill['created_by_name']) ?><small><?= e($skill['created_at']) ?></small></td>
                    <td><?= e($skill['updated_by_name'] ?: 'Not updated yet') ?></td>
                    <td><?= e($skill['updated_at']) ?></td>
                    <td class="actions">
                        <a href="skill_edit.php?id=<?= (int) $skill['id'] ?>">Edit</a>
                        <form method="post" action="skill_delete.php" onsubmit="return confirm('Delete this skill record?');">
                            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $skill['id'] ?>">
                            <button class="link danger-link" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
