
<?php

require_once __DIR__ . '/../includes/functions.php';

require_role('student');

$pageTitle = 'Saved Jobs';

// Fetch student's saved jobs
$s = $pdo->prepare("
    SELECT
        j.*,
        c.company_name
    FROM saved_jobs s
    JOIN jobs j ON j.id = s.job_id
    JOIN companies c ON c.id = j.company_id
    WHERE s.student_id = ?
    ORDER BY s.created_at DESC
");

$s->execute([current_user()['id']]);
$jobs = $s->fetchAll();

require __DIR__ . '/../includes/header.php';

?>

<div class="student-saved-jobs-page">

    <!-- Page Heading -->
    <section class="page-heading student-saved-hero">

        <span class="eyebrow">YOUR PERSONAL COLLECTION</span>

        <h1>Saved Opportunities</h1>

        <p>
            All your bookmarked jobs and internships in one place.
            Revisit interesting opportunities whenever you're ready to apply.
        </p>

        <a class="btn" href="../jobs.php">
            Discover More Jobs →
        </a>

    </section>


    <!-- Saved Jobs Summary -->
    <section class="student-saved-summary">

        <div class="student-saved-summary-card">

            <span class="student-saved-summary-icon">♡</span>

            <div>
                <strong><?= count($jobs) ?></strong>
                <span>Saved Opportunities</span>
            </div>

        </div>

        <div class="student-saved-summary-text">
            <span class="eyebrow">YOUR NEXT MOVE</span>
            <p>
                Explore your saved opportunities and take the next step
                towards your career goals.
            </p>
        </div>

    </section>


    <!-- Saved Jobs Listing -->
    <section class="section compact student-saved-section">

        <div class="section-heading student-saved-heading">

            <div>
                <span class="eyebrow">YOUR COLLECTION</span>
                <h2>Bookmarked Opportunities</h2>
                <p>Jobs and internships you've saved for later.</p>
            </div>

            <span class="student-saved-count">
                <?= count($jobs) ?> Saved
            </span>

        </div>


        <?php if (!$jobs): ?>

            <div class="empty-state student-saved-empty">

                <span class="student-saved-empty-icon">♡</span>

                <h3>No Saved Jobs Yet</h3>

                <p>
                    You haven't bookmarked any opportunities.
                    Browse available jobs and save the ones that interest you.
                </p>

                <a class="btn" href="../jobs.php">
                    Browse Opportunities →
                </a>

            </div>

        <?php else: ?>

            <div class="job-grid student-saved-grid">

                <?php foreach ($jobs as $j): ?>

                    <article class="job-card student-saved-card">

                        <div class="student-saved-card-top">

                            <span class="tag">
                                <?= e($j['job_type']) ?>
                            </span>

                            <span class="student-saved-heart">♡</span>

                        </div>


                        <div class="student-saved-card-content">

                            <span class="eyebrow">
                                <?= e($j['category'] ?: 'Career Opportunity') ?>
                            </span>

                            <h3><?= e($j['title']) ?></h3>

                            <p class="student-saved-company">
                                <?= e($j['company_name']) ?>
                            </p>

                            <div class="job-meta">

                                <span>
                                    ⌖ <?= e($j['location'] ?: 'Location not specified') ?>
                                </span>

                                <span>
                                    ▤ <?= e($j['category'] ?: 'General') ?>
                                </span>

                            </div>

                        </div>


                        <div class="student-saved-card-footer">

                            <a
                                class="text-link"
                                href="../job.php?id=<?= (int) $j['id'] ?>"
                            >
                                View Opportunity →
                            </a>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

</div>


<?php require __DIR__ . '/../includes/footer.php'; ?>
