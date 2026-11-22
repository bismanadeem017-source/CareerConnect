<?php
require_once __DIR__ . '/../config/database.php';

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
function redirect(string $path): never {
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}
function is_logged_in(): bool { return isset($_SESSION['user']); }
function current_user(): array { return $_SESSION['user'] ?? []; }
function require_login(): void {
    if (!is_logged_in()) { set_flash('Please log in first.', 'warning'); redirect('login.php'); }
}
function require_role(string ...$roles): void {
    require_login();
    if (!in_array(current_user()['role'], $roles, true)) {
        http_response_code(403); exit('You do not have permission to access this page.');
    }
}
function set_flash(string $message, string $type='success'): void {
    $_SESSION['flash'] = ['message'=>$message, 'type'=>$type];
}
function get_flash(): ?array {
    $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f;
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="'.e(csrf_token()).'">';
}
function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
        !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419); exit('Invalid form token. Please go back and try again.');
    }
}
function upload_cv(array $file): ?string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('CV upload failed.');
    if ($file['size'] > 5 * 1024 * 1024) throw new RuntimeException('CV must be 5 MB or smaller.');
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = ['application/pdf'=>'pdf',
                'application/msword'=>'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'docx'];
    if (!isset($allowed[$mime])) throw new RuntimeException('Only PDF, DOC and DOCX CV files are allowed.');
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $dir = __DIR__ . '/../uploads/cvs/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    if (!move_uploaded_file($file['tmp_name'], $dir . $name)) throw new RuntimeException('Could not save uploaded CV.');
    return $name;
}
function job_status_label(string $status): string {
    return ucfirst(str_replace('_', ' ', $status));
}
