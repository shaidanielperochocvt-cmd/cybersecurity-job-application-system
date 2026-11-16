<?php
declare(strict_types=1);
require_once __DIR__ . '/core/bootstrap.php';
require_login();

// GET SEARCH INPUTS
$parentSearch = isset($_GET['parent_search']) &&
    is_string($_GET['parent_search'])
    ? trim($_GET['parent_search'])
    : '';

$skillSearch = isset($_GET['skill_search']) &&
    is_string($_GET['skill_search'])
    ? trim($_GET['skill_search'])
    : '';


// ==========================================
// PARENT ENTITY SEARCH
// ==========================================

$parentSql = "
    SELECT
        ja.id,
        ja.full_name,
        ja.email,
        ja.phone,
        ja.position,
        ja.education,
        ja.created_by,
        ja.date_added,
        ja.last_updated,
        ja.last_updated_by,

        CONCAT(
            creator.first_name,
            ' ',
            creator.last_name
        ) AS created_by_name,

        CASE
            WHEN updater.id IS NULL THEN NULL
            ELSE CONCAT(
                updater.first_name,
                ' ',
                updater.last_name
            )
        END AS last_updated_by_name

    FROM job_applications ja

    INNER JOIN users creator
        ON creator.id = ja.created_by

    LEFT JOIN users updater
        ON updater.id = ja.last_updated_by
";

if ($parentSearch !== '') {
    $parentSql .= "
        WHERE
            ja.full_name LIKE :parent_name
            OR ja.email LIKE :parent_email
            OR ja.position LIKE :parent_position
    ";
}

$parentSql .= " ORDER BY ja.id DESC";

$parentQuery = $pdo->prepare($parentSql);

if ($parentSearch !== '') {

    $searchValue = '%' . $parentSearch . '%';

    $parentQuery->execute([
        'parent_name' => $searchValue,
        'parent_email' => $searchValue,
        'parent_position' => $searchValue
    ]);

} else {

    $parentQuery->execute();
}

$applications = $parentQuery->fetchAll();


// ==========================================
// CHILD ENTITY SEARCH
// ==========================================

$childSql = "
    SELECT
        s.id,
        s.application_id,
        s.skill_name,
        s.created_by,
        s.date_added,
        s.last_updated,
        s.last_updated_by,

        ja.full_name AS applicant_name,

        CONCAT(
            creator.first_name,
            ' ',
            creator.last_name
        ) AS created_by_name,

        CASE
            WHEN updater.id IS NULL THEN NULL
            ELSE CONCAT(
                updater.first_name,
                ' ',
                updater.last_name
            )
        END AS last_updated_by_name

    FROM application_skills s

    INNER JOIN job_applications ja
        ON ja.id = s.application_id

    INNER JOIN users creator
        ON creator.id = s.created_by

    LEFT JOIN users updater
        ON updater.id = s.last_updated_by
";

if ($skillSearch !== '') {
    $childSql .= "
        WHERE
            s.skill_name LIKE :skill_name
            OR ja.full_name LIKE :applicant_name
    ";
}

$childSql .= " ORDER BY s.id DESC";

$childQuery = $pdo->prepare($childSql);

if ($skillSearch !== '') {

    $searchValue = '%' . $skillSearch . '%';

    $childQuery->execute([
        'skill_name' => $searchValue,
        'applicant_name' => $searchValue
    ]);

} else {

    $childQuery->execute();
}

$skills = $childQuery->fetchAll();


// ==========================================
// ACTIVITY LOG
// ==========================================

$logQuery = $pdo->query(
    "SELECT
        al.id,
        al.action_type,
        al.entity_name,
        al.record_id,
        al.details,
        al.created_at,

        CASE
            WHEN u.id IS NULL THEN 'Unknown user'
            ELSE CONCAT(
                u.first_name,
                ' ',
                u.last_name
            )
        END AS user_name

    FROM activity_logs al

    LEFT JOIN users u
        ON u.id = al.user_id

    ORDER BY al.id DESC

    LIMIT 30"
);

$logs = $logQuery->fetchAll();

$pageTitle = 'Dashboard';

require __DIR__ . '/partials/header.php';
?>

<!-- DASHBOARD WELCOME -->

<section class="welcome-row">

    <div>
        <p class="eyebrow">MAIN DASHBOARD</p>

        <h1>
            Welcome,
            <?= e($_SESSION['user']['first_name']) ?>!
        </h1>

        <p class="muted">
            Search, manage, update, and delete
            job applications and application skills.
        </p>
    </div>

    <a class="button primary" href="application_create.php">
        + Add Application
    </a>

</section>


<!-- ======================================
     PARENT ENTITY
====================================== -->

<section class="section-card">

    <div class="section-heading">

        <div>
            <p class="eyebrow">PARENT ENTITY</p>
            <h2>Job Applications</h2>

            <p class="muted">
                Search for an applicant by name, email, or position.
            </p>
        </div>

        <span class="count-pill">
            <?= count($applications) ?> records
        </span>

    </div>


    <!-- PARENT SEARCH FORM -->

    <form method="GET" action="index.php" class="search-form">

        <input
            type="text"
            name="parent_search"
            placeholder="Search applicant name, email, or position..."
            value="<?= e($parentSearch) ?>"
        >

        <input
            type="hidden"
            name="skill_search"
            value="<?= e($skillSearch) ?>"
        >

        <button class="button primary" type="submit">
            Search Parent
        </button>

        <a class="button secondary" href="index.php">
            Clear Search
        </a>

    </form>


    <div class="table-wrap">

        <table>

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Applicant</th>
                    <th>Contact</th>
                    <th>Position</th>
                    <th>Education</th>
                    <th>Created By</th>
                    <th>Date Added</th>
                    <th>Last Updated</th>
                    <th>Last Updated By (ID)</th>
                    <th>Actions</th>
                </tr>

            </thead>

            <tbody>

            <?php if (!$applications): ?>

                <tr>
                    <td colspan="10" class="empty-cell">
                        No matching job applications found.
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($applications as $application): ?>

                    <tr>

                        <td>
                            <?= (int) $application['id'] ?>
                        </td>

                        <td>
                            <strong>
                                <?= e($application['full_name']) ?>
                            </strong>

                            <small>
                                <?= e($application['email']) ?>
                            </small>
                        </td>

                        <td>
                            <?= e($application['phone'] ?: '—') ?>
                        </td>

                        <td>
                            <?= e($application['position']) ?>
                        </td>

                        <td>
                            <?= e($application['education']) ?>
                        </td>

                        <td>
                            <?= e($application['created_by_name']) ?>

                            <small>
                                User ID: <?= (int) $application['created_by'] ?>
                            </small>
                        </td>

                        <td>
                            <?= e($application['date_added']) ?>
                        </td>

                        <td>
                            <?= e($application['last_updated'] ?: 'Not updated yet') ?>
                        </td>

                        <td>
                            <?php if ($application['last_updated_by'] !== null): ?>

                                <?= (int) $application['last_updated_by'] ?>

                                <small>
                                    <?= e($application['last_updated_by_name']) ?>
                                </small>

                            <?php else: ?>

                                Not updated yet

                            <?php endif; ?>
                        </td>

                        <td class="actions">

                            <a href="application_edit.php?id=<?= (int) $application['id'] ?>">
                                Edit
                            </a>

                            <a href="skill_create.php?application_id=<?= (int) $application['id'] ?>">
                                Add Skill
                            </a>

                            <form
                                method="POST"
                                action="application_delete.php"
                                onsubmit="return confirm('Delete this application and all its related skills?');"
                            >

                                <?= csrf_field() ?>

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $application['id'] ?>"
                                >

                                <button
                                    class="link danger-link"
                                    type="submit"
                                >
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</section>


<!-- ======================================
     CHILD ENTITY
====================================== -->

<section class="section-card">

    <div class="section-heading">

        <div>
            <p class="eyebrow">CHILD ENTITY</p>
            <h2>Application Skills</h2>

            <p class="muted">
                Search by skill name or applicant name.
            </p>
        </div>

        <span class="count-pill">
            <?= count($skills) ?> records
        </span>

    </div>


    <!-- CHILD SEARCH FORM -->

    <form method="GET" action="index.php" class="search-form">

        <input
            type="text"
            name="skill_search"
            placeholder="Search skill or applicant name..."
            value="<?= e($skillSearch) ?>"
        >

        <input
            type="hidden"
            name="parent_search"
            value="<?= e($parentSearch) ?>"
        >

        <button class="button primary" type="submit">
            Search Child
        </button>
        <a class="button secondary" href="index.php">
            Clear Search
        </a>
    </form>
    <div class="table-wrap">
        <table>

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Parent Application</th>
                    <th>Skill Name</th>
                    <th>Created By</th>
                    <th>Date Added</th>
                    <th>Last Updated</th>
                    <th>Last Updated By (ID)</th>
                    <th>Actions</th>
                </tr>

            </thead>

            <tbody>
            <?php if (!$skills): ?>

                <tr>
                    <td colspan="8" class="empty-cell">
                        No matching application skills found.
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($skills as $skill): ?>

                    <tr>

                        <td>
                            <?= (int) $skill['id'] ?>
                        </td>

                        <td>

                            <?= e($skill['applicant_name']) ?>

                            <small>
                                Application #<?= (int) $skill['application_id'] ?>
                            </small>

                        </td>

                        <td>
                            <strong>
                                <?= e($skill['skill_name']) ?>
                            </strong>
                        </td>

                        <td>

                            <?= e($skill['created_by_name']) ?>

                            <small>
                                User ID: <?= (int) $skill['created_by'] ?>
                            </small>

                        </td>

                        <td>
                            <?= e($skill['date_added']) ?>
                        </td>

                        <td>
                            <?= e($skill['last_updated'] ?: 'Not updated yet') ?>
                        </td>

                        <td>

                            <?php if ($skill['last_updated_by'] !== null): ?>

                                <?= (int) $skill['last_updated_by'] ?>

                                <small>
                                    <?= e($skill['last_updated_by_name']) ?>
                                </small>

                            <?php else: ?>

                                Not updated yet

                            <?php endif; ?>

                        </td>

                        <td class="actions">

                            <a href="skill_edit.php?id=<?= (int) $skill['id'] ?>">
                                Edit
                            </a>

                            <form
                                method="POST"
                                action="skill_delete.php"
                                onsubmit="return confirm('Delete this skill record?');"
                            >

                                <?= csrf_field() ?>

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $skill['id'] ?>"
                                >

                                <button
                                    class="link danger-link"
                                    type="submit"
                                >
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</section>


<!-- ======================================
     ACTIVITY LOG
====================================== -->

<section class="section-card">

    <div class="section-heading">

        <div>
            <p class="eyebrow">AUDIT TRAIL</p>
            <h2>Activity Log</h2>

            <p class="muted">
                Records user actions in the system.
            </p>
        </div>

        <span class="count-pill">
            <?= count($logs) ?> recent actions
        </span>

    </div>

    <div class="table-wrap">

        <table>

            <thead>

                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>Record ID</th>
                    <th>Details</th>
                </tr>

            </thead>

            <tbody>

            <?php if (!$logs): ?>

                <tr>
                    <td colspan="6" class="empty-cell">
                        No activity recorded yet.
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($logs as $log): ?>

                    <tr>

                        <td>
                            <?= e($log['created_at']) ?>
                        </td>

                        <td>
                            <?= e($log['user_name']) ?>
                        </td>

                        <td>
                            <span class="action-pill <?= strtolower(e($log['action_type'])) ?>">
                                <?= e($log['action_type']) ?>
                            </span>
                        </td>

                        <td>
                            <?= e($log['entity_name']) ?>
                        </td>

                        <td>
                            #<?= (int) $log['record_id'] ?>
                        </td>

                        <td>
                            <?= e($log['details']) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
