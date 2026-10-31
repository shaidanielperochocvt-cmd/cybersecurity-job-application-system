<?php
declare(strict_types=1);
require_once __DIR__ . '/core/bootstrap.php';
redirect_if_logged_in();

$error = '';
$email = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    $email = strtolower(clean_post('email'));
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !is_string($password)) {
        $error = 'The email or password is incorrect.';
    } else {
        $statement = $pdo->prepare('SELECT id, first_name, last_name, email, password_hash FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => (int) $user['id'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
            ];
            $updateLogin = $pdo->prepare('UPDATE users SET last_login_at = CURRENT_TIMESTAMP WHERE id = :id');
            $updateLogin->execute(['id' => (int) $user['id']]);
            set_flash('success', 'You are now logged in.');
            redirect('index.php');
        }

        $error = 'The email or password is incorrect.';
    }
}

$pageTitle = 'Log In';
require __DIR__ . '/partials/header.php';
?>
<section class="auth-card narrow">
    <p class="eyebrow">USER MODULE</p>
    <h1>Log in</h1>
    <p class="muted">Only registered users can open the dashboard.</p>
    <?php if ($error !== ''): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="login.php" class="stack-form">
        <?= csrf_field() ?>
        <label>Email address<input type="email" name="email" maxlength="150" value="<?= e($email) ?>" required autocomplete="username"></label>
        <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
        <button class="button primary" type="submit">Log in</button>
    </form>
    <p class="auth-switch">No account yet? <a href="register.php">Register here</a>.</p>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
