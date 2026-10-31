<?php
$pageTitle = $pageTitle ?? 'Cybersecurity Job Application';
$flash = take_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="<?= is_logged_in() ? 'index.php' : 'login.php' ?>">Cybersecurity Job Application</a>
    <?php if (is_logged_in()): ?>
        <nav class="nav-links">
            <span class="signed-in">Signed in as <?= e($_SESSION['user']['first_name'] ?? 'User') ?></span>
            <a href="index.php">Dashboard</a>
            <form class="inline-form" action="logout.php" method="post">
                <?= csrf_field() ?>
                <button class="nav-logout" type="submit">Log out</button>
            </form>
        </nav>
    <?php else: ?>
        <nav class="nav-links">
            <a href="login.php">Log in</a>
            <a href="register.php">Register</a>
        </nav>
    <?php endif; ?>
</header>
<main class="container">
    <?php if ($flash): ?>
        <div class="alert <?= e($flash['type']) ?>" role="alert"><?= e($flash['message']) ?></div>
    <?php endif; ?>
