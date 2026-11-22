
<?php

require_once __DIR__ . '/includes/functions.php';

// Get job ID
$id = (int)($_GET['id'] ?? 0);

// Fetch job details with company information
$sql = "
    SELECT
        j.*,
        c.company_name,
        u.full_name AS contact_name
    FROM jobs j
    JOIN companies c
        ON c.id = j.company_id
    LEFT JOIN users u
        ON u.id = c.user_id
    WHERE j.id = ?
    AND j.status = 'approved'
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

$job = $stmt->fetch();

// Job not found
if (!$job) {
    http_response_code(404);

    $pageTitle = 'Job not found';

    require __DIR__ . '/includes/header.php';
    ?>

    <main class="job-not-found-page">

        <div class="job-not-found-card">

            <div class="job-not-found-icon">!</div>

            <span class="eyebrow">OPPORTUNITY UNAVAILABLE</span>

            <h1>Job not found</h1>

            <p>
                Sorry, this opportunity is unavailable or may have
                been removed. Explore other available opportunities.
            </p>

            <a href="<?= BASE_URL ?>/jobs.php" class="btn">
                Browse available jobs
            </a>

        </div>

    </main>

    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $job['title'];

$saved = false;
$applied = false;

// Check saved and applied status for student
if (is_logged_in() && current_user()['role'] === 'student') {

    $s = $pdo->prepare(
        'SELECT id FROM saved_jobs WHERE student_id = ? AND job_id = ?'
    );

    $s->execute([current_user()['id'], $id]);

    $saved = (bool)$s->fetch();

    $a = $pdo->prepare(
        'SELECT id FROM applications WHERE student_id = ? AND job_id = ?'
    );

    $a->execute([current_user()['id'], $id]);

    $applied = (bool)$a->fetch();
}

require __DIR__ . '/includes/header.php';

?>

<main class="job-detail-page">

    <!-- Job Detail Hero -->
    <section class="job-detail-hero">

        <div class="job-detail-hero-content">

            <a class="job-back-link" href="<?= BASE_URL ?>/jobs.php">
                <span>←</span> Back to opportunities
            </a>

            <span class="eyebrow">CAREERCONNECT OPPORTUNITY</span>

            <div class="job-detail-heading">

                <div class="job-detail-company-logo">
                    <?= e(strtoupper(substr($job['company_name'], 0, 1))) ?>
                </div>

                <div>
                    <span class="job-detail-type">
                        <?= e($job['job_type']) ?>
                    </span>

                    <h1><?= e($job['title']) ?></h1>

                    <p class="job-detail-company">
                        <?= e($job['company_name']) ?>
                    </p>
                </div>

            </div>

            <div class="job-detail-meta">

                <span>
                    <b>⌖</b> <?= e($job['location']) ?>
                </span>

                <span>
                    <b>◈</b> <?= e($job['category']) ?>
                </span>

                <span>
                    <b>◷</b> Deadline: <?= e($job['deadline']) ?>
                </span>

            </div>

        </div>

        <div class="job-detail-hero-decoration">
            <div class="job-detail-glow"></div>

            <div class="job-detail-decoration-card">
                <span>✦</span>
                <h3>Every opportunity is a new beginning.</h3>
                <p>Discover your potential with CareerConnect.</p>
            </div>
        </div>

    </section>


    <!-- Main Job Information -->
    <section class="job-detail-main">

        <div class="job-detail-layout">

            <!-- Left Content -->
            <div class="job-detail-content">

                <article class="job-information-card">

                    <div class="job-section-heading">
                        <span class="job-section-icon">▤</span>

                        <div>
                            <span class="eyebrow">ABOUT THE POSITION</span>
                            <h2>Job description</h2>
                        </div>
                    </div>

                    <div class="job-detail-description">
                        <?= nl2br(e($job['description'])) ?>
                    </div>

                </article>


                <?php if (!empty($job['requirements'])): ?>

                    <article class="job-information-card">

                        <div class="job-section-heading">
                            <span class="job-section-icon">✓</span>

                            <div>
                                <span class="eyebrow">WHAT YOU NEED</span>
                                <h2>Requirements</h2>
                            </div>
                        </div>

                        <div class="job-detail-description">
                            <?= nl2br(e($job['requirements'])) ?>
                        </div>

                    </article>

                <?php endif; ?>


                <article class="job-company-card">

                    <span class="eyebrow">HIRING COMPANY</span>

                    <div class="job-company-information">

                        <div class="job-company-logo">
                            <?= e(strtoupper(substr($job['company_name'], 0, 1))) ?>
                        </div>

                        <div>
                            <h3><?= e($job['company_name']) ?></h3>

                            <p>
                                Registered company on CareerConnect
                            </p>
                        </div>

                    </div>

                </article>

            </div>


            <!-- Right Sidebar -->
            <aside class="job-application-sidebar">

                <div class="job-apply-card">

                    <span class="eyebrow">OPPORTUNITY DETAILS</span>

                    <h2>Job summary</h2>

                    <div class="job-summary-item">
                        <span class="job-summary-icon">₨</span>

                        <div>
                            <small>Salary</small>

                            <strong>
                                <?= e($job['salary'] ?: 'Not specified') ?>
                            </strong>
                        </div>
                    </div>


                    <div class="job-summary-item">
                        <span class="job-summary-icon">▣</span>

                        <div>
                            <small>Employment type</small>

                            <strong>
                                <?= e($job['job_type']) ?>
                            </strong>
                        </div>
                    </div>


                    <div class="job-summary-item">
                        <span class="job-summary-icon">⌖</span>

                        <div>
                            <small>Location</small>

                            <strong>
                                <?= e($job['location']) ?>
                            </strong>
                        </div>
                    </div>


                    <div class="job-summary-item">
                        <span class="job-summary-icon">◈</span>

                        <div>
                            <small>Category</small>

                            <strong>
                                <?= e($job['category']) ?>
                            </strong>
                        </div>
                    </div>


                    <div class="job-summary-item">
                        <span class="job-summary-icon">◷</span>

                        <div>
                            <small>Application deadline</small>

                            <strong>
                                <?= e($job['deadline']) ?>
                            </strong>
                        </div>
                    </div>


                    <div class="job-apply-actions">

                        <?php if (!is_logged_in()): ?>

                            <a
                                class="btn full job-apply-button"
                                href="<?= BASE_URL ?>/login.php"
                            >
                                Login to apply <span>→</span>
                            </a>

                            <p class="job-sidebar-note">
                                Login with your student account to apply.
                            </p>


                        <?php elseif (current_user()['role'] === 'student'): ?>

                            <?php if ($applied): ?>

                                <div class="alert success">
                                    You have already applied for this opportunity.
                                </div>

                            <?php else: ?>

                                <a
                                    class="btn full job-apply-button"
                                    href="<?= BASE_URL ?>/student/apply.php?job_id=<?= $id ?>"
                                >
                                    Apply now <span>→</span>
                                </a>

                            <?php endif; ?>


                            <form
                                method="post"
                                action="<?= BASE_URL ?>/student/save-job.php"
                                class="job-save-form"
                            >

                                <?= csrf_field() ?>

                                <input
                                    type="hidden"
                                    name="job_id"
                                    value="<?= $id ?>"
                                >

                                <button
                                    class="btn btn-outline full job-save-button"
                                    name="action"
                                    value="<?= $saved ? 'remove' : 'save' ?>"
                                >
                                    <?= $saved ? '♥ Remove saved job' : '♡ Save this job' ?>
                                </button>

                            </form>


                        <?php else: ?>

                            <div class="job-sidebar-note">
                                Student accounts can apply for this opportunity.
                            </div>

                        <?php endif; ?>

                    </div>

                </div>


                <div class="job-sidebar-promo">

                    <span>✦</span>

                    <h3>Your career journey starts here.</h3>

                    <p>
                        Explore more opportunities and take the next
                        step towards your professional goals.
                    </p>

                    <a href="<?= BASE_URL ?>/jobs.php">
                        Explore more jobs →
                    </a>

                </div>

            </aside>

        </div>

    </section>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
