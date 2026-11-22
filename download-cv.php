
<?php

require_once __DIR__ . '/includes/functions.php';

// Only companies and admins can download CVs
require_role('company', 'admin');

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(404);
    exit('File not found.');
}

// Get application CV and verify company ownership
$sql = "
    SELECT
        a.resume_path,
        j.company_id,
        c.user_id AS company_user_id
    FROM applications a
    JOIN jobs j ON j.id = a.job_id
    JOIN companies c ON c.id = j.company_id
    WHERE a.id = ?
";

$s = $pdo->prepare($sql);
$s->execute([$id]);
$row = $s->fetch();

// Application not found
if (!$row) {
    http_response_code(404);
    exit('File not found.');
}

// Verify that the logged-in company owns this job
if (
    current_user()['role'] === 'company' &&
    (int)$row['company_user_id'] !== (int)current_user()['id']
) {
    http_response_code(404);
    exit('File not found.');
}

// Check CV path
if (empty($row['resume_path'])) {
    http_response_code(404);
    exit('CV not found.');
}

// CV files are stored in uploads/cvs
$cvDirectory = realpath(__DIR__ . '/uploads/cvs');

if ($cvDirectory === false) {
    http_response_code(404);
    exit('CV directory not found.');
}

// Use only the stored filename
$filename = basename(
    str_replace('\\', '/', $row['resume_path'])
);

$file = realpath(
    $cvDirectory . DIRECTORY_SEPARATOR . $filename
);

// Ensure the file exists inside the CV directory
if (
    $file === false ||
    !is_file($file) ||
    strpos($file, $cvDirectory . DIRECTORY_SEPARATOR) !== 0
) {
    http_response_code(404);
    exit('CV not found.');
}

// Check allowed CV file extensions
$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

$allowedExtensions = ['pdf', 'doc', 'docx'];

if (!in_array($extension, $allowedExtensions, true)) {
    http_response_code(403);
    exit('Invalid CV file type.');
}

// Determine MIME type
$mimeType = mime_content_type($file) ?: 'application/octet-stream';

// Send download headers
header('Content-Type: ' . $mimeType);

header(
    'Content-Disposition: attachment; filename="candidate-cv.' . $extension . '"'
);

header('Content-Length: ' . filesize($file));

header('X-Content-Type-Options: nosniff');

// Download CV
readfile($file);
exit;

