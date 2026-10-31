
<?php
require_once __DIR__ . "/fetch.php";

function escape($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}
?>

<h2>Submitted Applications</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Position</th>
            <th>Education</th>
            <th>Skills</th>
            <th>Date Added</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
        <?php if (count($applications) > 0): ?>

            <?php foreach ($applications as $application): ?>
                <tr>
                    <td><?= escape($application['id']) ?></td>
                    <td><?= escape($application['full_name']) ?></td>
                    <td><?= escape($application['email']) ?></td>
                    <td><?= escape($application['phone']) ?></td>
                    <td><?= escape($application['position']) ?></td>
                    <td><?= escape($application['education']) ?></td>
                    <td><?= escape($application['skills']) ?></td>
                    <td><?= escape($application['date_added']) ?></td>

                    <td>
                        <a href="update.php?id=<?= escape($application['id']) ?>">
                            Edit
                        </a>

                        <form action="delete.php" method="POST"
                              onsubmit="return confirm('Are you sure you want to delete this application?');"
                              style="display:inline;">

                            <input type="hidden" name="id"
                                   value="<?= escape($application['id']) ?>">

                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

        <?php else: ?>
            <tr>
                <td colspan="9">No applications have been submitted.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
