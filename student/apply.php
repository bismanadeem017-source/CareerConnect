
<?php

require_once __DIR__ . '/../includes/functions.php';

require_role('student');

$jobId = (int)($_GET['job_id'] ?? 0);

// Find approved job
$s = $pdo->prepare("
    SELECT *
    FROM jobs
    WHERE id = ?
    AND status = 'approved'
    AND deadline >= CURDATE()
");

$s->execute([$jobId]);
$job = $s->fetch();

if (!$job) {
    set_flash('This job is no longer available.', 'warning');
    redirect('jobs.php');
}

$uid = current_user()['id'];

// Prevent duplicate applications
$s = $pdo->prepare("
    SELECT id
    FROM applications
    WHERE student_id = ?
    AND job_id = ?
");

$s->execute([$uid, $jobId]);

if ($s->fetch()) {
    set_flash('You have already applied for this job.', 'warning');
    redirect('job.php?id=' . $jobId);
}

// Get student's existing CV
$prof = $pdo->prepare("
    SELECT cv_file
    FROM student_profiles
    WHERE user_id = ?
");

$prof->execute([$uid]);

$cv = $prof->fetchColumn();

$error = '';

// Process application
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    try {

        // Upload new CV if selected
        $newCv = upload_cv(
            $_FILES['cv'] ?? ['error' => UPLOAD_ERR_NO_FILE]
        );

        $cv = $newCv ?: $cv;

        if (!$cv) {
            throw new RuntimeException(
                'Please upload your CV in your profile or with this application.'
            );
        }

        // Save application using the correct database column
        $stmt = $pdo->prepare("
            INSERT INTO applications
            (student_id, job_id, cover_letter, resume_path)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $uid,
            $jobId,
            trim($_POST['cover_letter'] ?? ''),
            $cv
        ]);

        set_flash('Your application has been submitted.');

        redirect('student/applications.php');

    } catch (Throwable $e) {

        $error = $e->getMessage();
    }
}

$pageTitle = 'Apply for job';

require __DIR__ . '/../includes/header.php';

?>

<section class="form-section">

    <form
        class="form-card wide"
        method="post"
        enctype="multipart/form-data"
    >

        <span class="eyebrow">APPLICATION</span>

        <h1><?= e($job['title']) ?></h1>

        <p class="muted">
            Submit your application to <?= e($job['title']) ?>.
        </p>

        <?= csrf_field() ?>

        <?php if ($error): ?>

            <div class="alert error">
                <?= e($error) ?>
            </div>

        <?php endif; ?>

        <label>
            Cover letter

            <textarea
                name="cover_letter"
                rows="6"
                placeholder="Tell the company why you are a good fit..."
            ><?= e($_POST['cover_letter'] ?? '') ?></textarea>

        </label>

        <label>
            Attach CV (optional if already uploaded; PDF/DOC/DOCX, max 5 MB)

            <input
                type="file"
                name="cv"
                accept=".pdf,.doc,.docx"
            >

        </label>

        <button class="btn full" type="submit">
            Submit application
        </button>

    </form>

</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
