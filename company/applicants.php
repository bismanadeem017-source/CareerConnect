
<?php
require_once __DIR__ . '/../includes/functions.php';

require_role('company');

$uid = current_user()['id'];
$jobId = (int)($_GET['job_id'] ?? 0);

// Verify that this job belongs to the logged-in company
$s = $pdo->prepare("
    SELECT j.title
    FROM jobs j
    JOIN companies c ON c.id = j.company_id
    WHERE j.id = ? AND c.user_id = ?
");
$s->execute([$jobId, $uid]);
$job = $s->fetch();

if (!$job) {
    http_response_code(404);
    exit('Job not found or you do not have permission to view it.');
}

// Update application status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $appId = (int)($_POST['application_id'] ?? 0);
    $status = $_POST['status'] ?? '';

    $statuses = [
        'Applied',
        'Under Review',
        'Shortlisted',
        'Accepted',
        'Rejected'
    ];

    if (in_array($status, $statuses, true)) {
        $s = $pdo->prepare("
            UPDATE applications a
            JOIN jobs j ON j.id = a.job_id
            JOIN companies c ON c.id = j.company_id
            SET a.status = ?
            WHERE a.id = ?
              AND a.job_id = ?
              AND c.user_id = ?
        ");

        $s->execute([$status, $appId, $jobId, $uid]);

        set_flash('Application status updated.');
    }

    redirect('company/applicants.php?job_id=' . $jobId);
}

// Fetch applicants
$s = $pdo->prepare("
    SELECT
        a.*,
        u.full_name AS name,
        u.email,
        p.education,
        p.skills
    FROM applications a
    JOIN users u ON u.id = a.student_id
    LEFT JOIN student_profiles p ON p.user_id = u.id
    WHERE a.job_id = ?
    ORDER BY a.applied_at DESC
");

$s->execute([$jobId]);
$apps = $s->fetchAll();

$pageTitle = 'Applicants';

$totalApplicants = count($apps);
$underReview = 0;
$shortlisted = 0;
$accepted = 0;

foreach ($apps as $app) {
    if ($app['status'] === 'Under Review') {
        $underReview++;
    } elseif ($app['status'] === 'Shortlisted') {
        $shortlisted++;
    } elseif ($app['status'] === 'Accepted') {
        $accepted++;
    }
}

require __DIR__ . '/../includes/header.php';
?>

<div class="company-applicants-page">

    <!-- Page Hero -->
    <section class="company-applicants-hero">

        <div class="company-applicants-hero-content">

            <span class="eyebrow">EMPLOYER SPACE · TALENT MANAGEMENT</span>

            <h1>Meet Your <span>Applicants</span></h1>

            <p>
                Review candidates, explore their qualifications,
                download CVs and manage your recruitment process.
            </p>

            <div class="company-applicants-job-title">
                <span class="company-applicants-job-icon">✦</span>
                <span>
                    Applications for
                    <strong><?= e($job['title']) ?></strong>
                </span>
            </div>

        </div>

        <div class="company-applicants-hero-decoration">
            <div class="company-applicants-glow"></div>
            <div class="company-applicants-decoration-card">
                <span>✧</span>
                <strong><?= $totalApplicants ?></strong>
                <small>Candidate Profiles</small>
            </div>
        </div>

    </section>

    <!-- Applicant Statistics -->
    <section class="company-applicants-stats">

        <article class="company-applicant-stat">
            <div class="company-applicant-stat-icon">♧</div>
            <div>
                <span>Total Applicants</span>
                <strong><?= $totalApplicants ?></strong>
            </div>
        </article>

        <article class="company-applicant-stat">
            <div class="company-applicant-stat-icon">◷</div>
            <div>
                <span>Under Review</span>
                <strong><?= $underReview ?></strong>
            </div>
        </article>

        <article class="company-applicant-stat">
            <div class="company-applicant-stat-icon">✧</div>
            <div>
                <span>Shortlisted</span>
                <strong><?= $shortlisted ?></strong>
            </div>
        </article>

        <article class="company-applicant-stat">
            <div class="company-applicant-stat-icon">✓</div>
            <div>
                <span>Accepted</span>
                <strong><?= $accepted ?></strong>
            </div>
        </article>

    </section>

    <!-- Applicants Section -->
    <section class="company-applicants-section">

        <div class="company-applicants-heading">

            <div>
                <span class="eyebrow">YOUR CANDIDATES</span>
                <h2>Applicant Directory</h2>
                <p>Review each candidate and update their application status.</p>
            </div>

            <a
                href="<?= BASE_URL ?>/company/dashboard.php"
                class="company-applicants-back"
            >
                ← Back to Dashboard
            </a>

        </div>

        <?php if (!$apps): ?>

            <div class="company-applicants-empty">

                <div class="company-applicants-empty-icon">♧</div>

                <h3>No Applicants Yet</h3>

                <p>
                    There are no applications for this job at the moment.
                    New candidates will appear here when they apply.
                </p>

                <a
                    href="<?= BASE_URL ?>/company/dashboard.php"
                    class="company-applicants-empty-btn"
                >
                    Return to Dashboard
                </a>

            </div>

        <?php else: ?>

            <div class="company-applicants-list">

                <?php foreach ($apps as $a): ?>

                    <article class="company-applicant-card">

                        <!-- Candidate Information -->
                        <div class="company-applicant-main">

                            <div class="company-applicant-profile">

                                <div class="company-applicant-avatar">
                                    <?= e(strtoupper(substr($a['name'], 0, 1))) ?>
                                </div>

                                <div class="company-applicant-identity">

                                    <h3><?= e($a['name']) ?></h3>

                                    <a href="mailto:<?= e($a['email']) ?>">
                                        <?= e($a['email']) ?>
                                    </a>

                                    <span class="company-applicant-date">
                                        Applied on
                                        <?= e(date('d M Y', strtotime($a['applied_at']))) ?>
                                    </span>

                                </div>

                            </div>

                            <div class="company-applicant-information">

                                <div class="company-applicant-info-item">
                                    <span class="company-applicant-info-label">
                                        EDUCATION
                                    </span>

                                    <p>
                                        <?= e($a['education'] ?? 'Not provided') ?>
                                    </p>
                                </div>

                                <div class="company-applicant-info-item">
                                    <span class="company-applicant-info-label">
                                        SKILLS
                                    </span>

                                    <p>
                                        <?= e($a['skills'] ?? 'Not provided') ?>
                                    </p>
                                </div>

                            </div>

                            <!-- Cover Letter -->
                            <?php if (!empty(trim($a['cover_letter'] ?? ''))): ?>

                                <div class="company-applicant-cover-letter">

                                    <span class="company-applicant-info-label">
                                        COVER LETTER
                                    </span>

                                    <p>
                                        <?= nl2br(e($a['cover_letter'])) ?>
                                    </p>

                                </div>

                            <?php endif; ?>

                        </div>

                        <!-- Candidate Actions -->
                        <div class="company-applicant-actions">

                            <?php if (!empty($a['resume_path'])): ?>

                                <a
                                    class="company-applicant-cv-btn"
                                    href="<?= BASE_URL ?>/download-cv.php?id=<?= (int)$a['id'] ?>"
                                >
                                    <span>↓</span>
                                    Download CV
                                </a>

                            <?php else: ?>

                                <span class="company-applicant-no-cv">
                                    CV not available
                                </span>

                            <?php endif; ?>

                            <form
                                method="post"
                                class="company-applicant-status-form"
                            >

                                <?= csrf_field() ?>

                                <input
                                    type="hidden"
                                    name="application_id"
                                    value="<?= (int)$a['id'] ?>"
                                >

                                <label for="status-<?= (int)$a['id'] ?>">
                                    APPLICATION STATUS
                                </label>

                                <select
                                    id="status-<?= (int)$a['id'] ?>"
                                    name="status"
                                >

                                    <?php foreach ([
                                        'Applied',
                                        'Under Review',
                                        'Shortlisted',
                                        'Accepted',
                                        'Rejected'
                                    ] as $v): ?>

                                        <option
                                            value="<?= e($v) ?>"
                                            <?= $a['status'] === $v ? 'selected' : '' ?>
                                        >
                                            <?= e($v) ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                                <button type="submit">
                                    Update Status →
                                </button>

                            </form>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
