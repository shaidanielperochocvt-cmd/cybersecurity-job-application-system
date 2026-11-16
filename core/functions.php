<?php
declare(strict_types=1);

// DISPLAY DATA SAFELY
function e(mixed $value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

// COUNT TEXT CHARACTERS
function text_length(string $value): int
{
    return function_exists('mb_strlen')
        ? mb_strlen($value, 'UTF-8')
        : strlen($value);
}

// REDIRECT TO ANOTHER PAGE
function redirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}

// GET FORM INPUT
function clean_post(string $key): string
{
    $value = $_POST[$key] ?? '';

    return is_string($value) ? trim($value) : '';
}

// GENERATE CSRF TOKEN
function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

// ADD CSRF TOKEN TO FORMS
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' .
        e(csrf_token()) . '">';
}

// VERIFY CSRF TOKEN
function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    $known = $_SESSION['csrf_token'] ?? '';

    if (
        !is_string($submitted) ||
        !is_string($known) ||
        $known === '' ||
        !hash_equals($known, $submitted)
    ) {
        http_response_code(419);
        exit('Invalid or expired form token. Please try again.');
    }
}

// REQUIRE POST REQUEST
function require_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        exit('Method not allowed.');
    }
}

// SAVE SUCCESS OR ERROR MESSAGES
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

// DISPLAY AND REMOVE FLASH MESSAGE
function take_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;

    unset($_SESSION['flash']);

    return is_array($flash) ? $flash : null;
}

// RECORD INSERT, UPDATE, AND DELETE ACTIVITIES
function record_activity(
    PDO $pdo,
    int $userId,
    string $action,
    string $entity,
    int $recordId,
    string $details
): void {

    $statement = $pdo->prepare(
        "INSERT INTO activity_logs
        (
            user_id,
            action_type,
            entity_name,
            record_id,
            details
        )
        VALUES
        (
            :user_id,
            :action_type,
            :entity_name,
            :record_id,
            :details
        )"
    );

    $statement->execute([
        'user_id' => $userId,
        'action_type' => $action,
        'entity_name' => $entity,
        'record_id' => $recordId,
        'details' => $details
    ]);
}
