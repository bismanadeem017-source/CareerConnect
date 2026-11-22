
<?php

require_once __DIR__ . '/../includes/functions.php';

require_role('student');

// Only POST requests are allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('jobs.php');
}

// Verify CSRF protection
verify_csrf();

// Get submitted job ID and action
$jobId = (int) ($_POST['job_id'] ?? 0);
$action = $_POST['action'] ?? 'save';

// Validate job ID
if ($jobId <= 0) {
    set_flash('Invalid job selected.');
    redirect('jobs.php');
}

// Save or remove the job
if ($action === 'save') {

    $stmt = $pdo->prepare(
        'INSERT IGNORE INTO saved_jobs (student_id, job_id)
         VALUES (?, ?)'
    );

    $stmt->execute([
        current_user()['id'],
        $jobId
    ]);

    set_flash('Job saved successfully.');

} elseif ($action === 'remove') {

    $stmt = $pdo->prepare(
        'DELETE FROM saved_jobs
         WHERE student_id = ? AND job_id = ?'
    );

    $stmt->execute([
        current_user()['id'],
        $jobId
    ]);

    set_flash('Saved job removed successfully.');

} else {

    set_flash('Invalid action.');

}

// Return to the selected job
redirect('job.php?id=' . $jobId);

