<?php
declare(strict_types=1);
require_once __DIR__ . '/core/bootstrap.php';
redirect_if_logged_in();

$errors = [];
$firstName = $lastName = $email = $phone = $address = $dateOfBirth = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();

    $firstName = clean_post('first_name');
    $lastName = clean_post('last_name');
    $email = strtolower(clean_post('email'));
    $phone = clean_post('phone');
    $address = clean_post('address');
    $dateOfBirth = clean_post('date_of_birth');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($firstName === '' || text_length($firstName) > 80) {
        $errors[] = 'Enter a first name with no more than 80 characters.';
    }
    if ($lastName === '' || text_length($lastName) > 80) {
        $errors[] = 'Enter a last name with no more than 80 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || text_length($email) > 150) {
        $errors[] = 'Enter a valid email address.';
    }
    if ($phone !== '' && text_length($phone) > 30) {
        $errors[] = 'Phone number must be 30 characters or fewer.';
    }
    if ($address !== '' && text_length($address) > 255) {
        $errors[] = 'Address must be 255 characters or fewer.';
    }
    if ($dateOfBirth !== '') {
        $date = DateTime::createFromFormat('Y-m-d', $dateOfBirth);
        if (!$date || $date->format('Y-m-d') !== $dateOfBirth) {
            $errors[] = 'Enter a valid date of birth.';
        }
    }
    if (!is_string($password) || strlen($password) < 8) {
        $errors[] = 'Password must contain at least 8 characters.';
    }
    if (!is_string($confirmPassword) || !is_string($password) || !hash_equals($password, $confirmPassword)) {
        $errors[] = 'The password confirmation does not match.';
    }

    if (!$errors) {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $check->execute(['email' => $email]);

        if ($check->fetch()) {
            $errors[] = 'That email address is already registered. Use another email or log in.';
        } else {
            $insert = $pdo->prepare(
                'INSERT INTO users (first_name, last_name, email, phone, address, date_of_birth, password_hash)
                 VALUES (:first_name, :last_name, :email, :phone, :address, :date_of_birth, :password_hash)'
            );
            $insert->execute([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone !== '' ? $phone : null,
                'address' => $address !== '' ? $address : null,
                'date_of_birth' => $dateOfBirth !== '' ? $dateOfBirth : null,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);

            set_flash('success', 'Registration successful. You can now log in with your email and password.');
            redirect('login.php');
        }
    }
}

$pageTitle = 'Create Account';
require __DIR__ . '/partials/header.php';
?>
<section class="auth-card">
    <p class="eyebrow">USER MODULE</p>
    <h1>Create your account</h1>
    <p class="muted">Register first so the system can identify who creates and updates each record.</p>

    <?php if ($errors): ?>
        <div class="alert error"><strong>Please correct the following:</strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="post" action="register.php" class="form-grid">
        <?= csrf_field() ?>
        <label>First name<input name="first_name" maxlength="80" value="<?= e($firstName) ?>" required></label>
        <label>Last name<input name="last_name" maxlength="80" value="<?= e($lastName) ?>" required></label>
        <label>Email address<input type="email" name="email" maxlength="150" value="<?= e($email) ?>" required></label>
        <label>Phone number <span class="muted">(optional)</span><input name="phone" maxlength="30" value="<?= e($phone) ?>"></label>
        <label class="full-width">Address <span class="muted">(optional)</span><input name="address" maxlength="255" value="<?= e($address) ?>"></label>
        <label>Date of birth <span class="muted">(optional)</span><input type="date" name="date_of_birth" value="<?= e($dateOfBirth) ?>"></label>
        <label>Password<input type="password" name="password" minlength="8" required autocomplete="new-password"></label>
        <label class="full-width">Confirm password<input type="password" name="confirm_password" minlength="8" required autocomplete="new-password"></label>
        <div class="full-width"><button class="button primary" type="submit">Register</button></div>
    </form>
    <p class="auth-switch">Already registered? <a href="login.php">Log in here</a>.</p>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
