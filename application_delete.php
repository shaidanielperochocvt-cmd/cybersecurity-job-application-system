<?php
declare(strict_types=1);
require_once __DIR__ . '/core/bootstrap.php';
require_login();
require_post();
verify_csrf();

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(400); exit('Invalid application ID.'); }

$delete = $pdo->prepare('DELETE FROM job_applications WHERE id = :id');
$delete->execute(['id' => $id]);

if ($delete->rowCount() > 0) {
    set_flash('success', 'Parent record deleted. Its child skill records were also deleted by the database relationship.');
} else {
    set_flash('error', 'That job application was not found or was already deleted.');
}
redirect('index.php');
