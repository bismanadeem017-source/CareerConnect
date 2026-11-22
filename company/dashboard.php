
<?php

require_once __DIR__ . '/../includes/functions.php';

require_role('company');

// Logged-in company user's ID
$uid = (int) current_user()['id'];

$pageTitle = 'Company Dashboard';

// Find the actual company profile ID
$stmt = $pdo->prepare(
    'SELECT id FROM companies WHERE user_id = ? LIMIT 1'
);
$stmt->execute([$uid]);
$company = $stmt->fetch();

if (!$company) {
    exit('Company profile not found. Please contact the administrator.');
}

$companyId = (int) $company['id'];

// Count jobs posted by this company
$stmt = $pdo->prepare(
    'SELECT COUNT(*) FROM jobs WHERE company_id = ?'
);
$stmt->execute([$companyId]);
$jobsCount = (int) $stmt->fetchColumn();

// Count applications received by this company
$stmt = $pdo->prepare(
    'SELECT COUNT(*)
     FROM applications a
     JOIN jobs j ON j.id = a.job_id
     WHERE j.company_id = ?'
);
$stmt->execute([$companyId]);
$appCount = (int) $stmt->fetchColumn();

// Get all jobs posted by this company
$stmt = $pdo->prepare(
    'SELECT *
     FROM jobs
     WHERE company_id = ?
     ORDER BY created_at DESC'
);
$stmt->execute([$companyId]);
$jobs = $stmt->fetchAll();

// Company display name
$companyName = current_user()['company_name']
    ?: current_user()['name'];

// Load header
require __DIR__ . '/../includes/header.php';

?>

<div class="company-dashboard-page">

    <!-- Company Welcome Section -->
    <section class="page-heading company-dashboard-hero">

        <span class="eyebrow">EMPLOYER WORKSPACE</span>

        <h1>Welcome, <?= e($companyName) ?></h1>

        <p>
            Find talented students, publish exciting opportunities
            and manage your recruitment activities from one place.
        </p>

        <div class="company-dashboard-actions">

            <a class="btn" href="post-job.php">
                + Post a Job
            </a>

            <a class="btn btn-outline" href="../jobs.php">
                Explore Job Listings
            </a>

        </div>

    </section>


    <!-- Company Statistics -->
    <section class="dashboard-stats company-dashboard-stats">

        <div class="company-stat-card">

            <span class="company-stat-icon">▤</span>

            <strong><?= $jobsCount ?></strong>

            <span>Jobs Posted</span>

            <small>Your published and submitted listings</small>

        </div>


        <div class="company-stat-card">

            <span class="company-stat-icon">♙</span>

            <strong><?= $appCount ?></strong>

            <span>Applications Received</span>

            <small>Students interested in your opportunities</small>

        </div>

    </section>


    <!-- Recruitment Management -->
    <section class="company-listings-section">

        <div class="section-heading company-listings-heading">

            <div>
                <span class="eyebrow">RECRUITMENT MANAGEMENT</span>

                <h2>Your Job Listings</h2>

                <p>
                    Manage your opportunities and review student applications.
                </p>
            </div>

            <a class="btn" href="post-job.php">
                + Add New Job
            </a>

        </div>


        <?php if (!$jobs): ?>

            <div class="empty-state company-empty-state">

                <span class="company-empty-icon">▤</span>

                <h3>No Job Listings Yet</h3>

                <p>
                    You haven't posted any opportunities.
                    Create your first job listing to start receiving applications.
                </p>

                <a class="btn" href="post-job.php">
                    Post Your First Job →
                </a>

            </div>

        <?php else: ?>

            <div class="table-wrap company-table-wrap">

                <table>

                    <thead>

                        <tr>
                            <th>Position</th>
                            <th>Deadline</th>
                            <th>Approval Status</th>
                            <th>Applicants</th>
                            <th>Action</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($jobs as $j): ?>

                            <tr>

                                <td>
                                    <strong><?= e($j['title']) ?></strong>
                                </td>

                                <td>
                                    <?= e(date(
                                        'd M Y',
                                        strtotime($j['deadline'])
                                    )) ?>
                                </td>

                                <td>
                                    <span class="status">
                                        <?= e(job_status_label($j['status'])) ?>
                                    </span>
                                </td>

                                <td>
                                    <a
                                        class="company-table-link"
                                        href="applicants.php?job_id=<?= (int) $j['id'] ?>"
                                    >
                                        View Applicants →
                                    </a>
                                </td>

                                <td>
                                    <a
                                        class="company-edit-link"
                                        href="post-job.php?edit=<?= (int) $j['id'] ?>"
                                    >
                                        Edit Job
                                    </a>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </section>

</div>


<?php require __DIR__ . '/../includes/footer.php'; ?>
