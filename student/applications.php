
<?php

require_once __DIR__ . '/../includes/functions.php';

require_role('student');

$pageTitle = 'My Applications';

// Fetch student's applications with job and company details
$sql = "
    SELECT
        a.*,
        j.title,
        j.location,
        j.job_type,
        c.company_name
    FROM applications a
    JOIN jobs j
        ON j.id = a.job_id
    LEFT JOIN companies c
        ON c.id = j.company_id
    WHERE a.student_id = ?
    ORDER BY a.applied_at DESC
";

$s = $pdo->prepare($sql);
$s->execute([current_user()['id']]);

$apps = $s->fetchAll();

require __DIR__ . '/../includes/header.php';

?>

<div class="student-applications-page">

    <!-- Page Heading -->
    <section class="page-heading student-applications-hero">

        <span class="eyebrow">YOUR CAREER ACTIVITY</span>

        <h1>My Applications</h1>

        <p>
            Keep track of the opportunities you've applied for.
            Monitor your application progress and stay updated on your career journey.
        </p>

        <a class="btn" href="../jobs.php">
            Explore More Opportunities →
        </a>

    </section>


    <!-- Application Summary -->
    <section class="student-applications-summary">

        <div class="student-application-summary-card">
            <span class="student-application-summary-icon">▤</span>

            <div>
                <strong><?= count($apps) ?></strong>
                <span>Total Applications</span>
            </div>
        </div>

        <div class="student-application-summary-card">
            <span class="student-application-summary-icon">◷</span>

            <div>
                <strong>
                    <?= count(array_filter($apps, function ($app) {
                        return strtolower($app['status']) === 'under review';
                    })) ?>
                </strong>
                <span>Under Review</span>
            </div>
        </div>

        <div class="student-application-summary-card">
            <span class="student-application-summary-icon">✓</span>

            <div>
                <strong>
                    <?= count(array_filter($apps, function ($app) {
                        return strtolower($app['status']) === 'accepted';
                    })) ?>
                </strong>
                <span>Accepted</span>
            </div>
        </div>

    </section>


    <!-- Applications List -->
    <section class="section compact student-applications-section">

        <div class="section-heading student-applications-heading">

            <div>
                <span class="eyebrow">APPLICATION HISTORY</span>
                <h2>Submitted Applications</h2>
                <p>Your latest and previous job applications.</p>
            </div>

            <span class="student-applications-count">
                <?= count($apps) ?> Applications
            </span>

        </div>


        <?php if (!$apps): ?>

            <div class="empty-state student-applications-empty">

                <span class="student-applications-empty-icon">▤</span>

                <h3>Your Journey Starts Here</h3>

                <p>
                    You haven't applied for any jobs yet.
                    Explore available opportunities and submit your first application.
                </p>

                <a class="btn" href="../jobs.php">
                    Browse Available Jobs →
                </a>

            </div>

        <?php else: ?>

            <div class="student-applications-list">

                <?php foreach ($apps as $a): ?>

                    <article class="student-application-card">

                        <div class="student-application-main">

                            <div class="student-application-company-icon">
                                <?= e(strtoupper(substr(
                                    $a['company_name'] ?: 'C',
                                    0,
                                    1
                                ))) ?>
                            </div>

                            <div class="student-application-details">

                                <h3><?= e($a['title']) ?></h3>

                                <p class="student-application-company">
                                    <?= e($a['company_name'] ?: 'Company') ?>
                                </p>

                                <div class="student-application-meta">

                                    <span>
                                        <span>⌖</span>
                                        <?= e($a['location'] ?: 'Location not specified') ?>
                                    </span>

                                    <span>
                                        <span>▤</span>
                                        <?= e($a['job_type'] ?: 'Job') ?>
                                    </span>

                                    <span>
                                        <span>◷</span>
                                        Applied <?= e(date(
                                            'd M Y',
                                            strtotime($a['applied_at'])
                                        )) ?>
                                    </span>

                                </div>

                            </div>

                        </div>


                        <div class="student-application-status">

                            <span class="eyebrow">APPLICATION STATUS</span>

                            <span class="status">
                                <?= e(job_status_label($a['status'])) ?>
                            </span>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

</div>


<?php require __DIR__ . '/../includes/footer.php'; ?>
