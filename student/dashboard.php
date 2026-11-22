
<?php

require_once __DIR__ . '/../includes/functions.php';

// Only students can access this dashboard
require_role('student');

$pageTitle = 'Student Dashboard';

$uid = current_user()['id'];

// Count total applications
$s = $pdo->prepare(
    'SELECT COUNT(*) FROM applications WHERE student_id = ?'
);
$s->execute([$uid]);
$total = $s->fetchColumn();

// Count accepted applications
$s = $pdo->prepare(
    "SELECT COUNT(*) FROM applications
     WHERE student_id = ? AND status = 'accepted'"
);
$s->execute([$uid]);
$accepted = $s->fetchColumn();

// Get recent applications and company information
$s = $pdo->prepare(
    'SELECT
        a.*,
        j.title,
        j.location,
        c.company_name AS company_name
     FROM applications a
     JOIN jobs j ON j.id = a.job_id
     LEFT JOIN companies c ON c.id = j.company_id
     WHERE a.student_id = ?
     ORDER BY a.applied_at DESC
     LIMIT 5'
);
$s->execute([$uid]);
$apps = $s->fetchAll();

// Load page header
require __DIR__ . '/../includes/header.php';

?>

<div class="student-dashboard">

    <!-- Dashboard Welcome Section -->
    <section class="page-heading student-welcome">

        <span class="eyebrow">STUDENT SPACE</span>

        <h1>
            Welcome back, <?= e(current_user()['name']) ?>
        </h1>

        <p>
            Your career journey continues here. Manage your profile,
            track applications and discover opportunities that match your goals.
        </p>

        <div class="student-welcome-actions">
            <a class="btn" href="../jobs.php">
                Explore Opportunities →
            </a>

            <a class="btn btn-outline" href="profile.php">
                Manage Profile
            </a>
        </div>

    </section>


    <!-- Dashboard Statistics -->
    <section class="dashboard-stats student-stats">

        <div class="student-stat-card">
            <span class="student-stat-icon">▤</span>
            <strong><?= (int) $total ?></strong>
            <span>Total Applications</span>
            <small>Your submitted applications</small>
        </div>

        <div class="student-stat-card">
            <span class="student-stat-icon">✓</span>
            <strong><?= (int) $accepted ?></strong>
            <span>Accepted Applications</span>
            <small>Your accepted opportunities</small>
        </div>

        <div class="student-stat-card">
            <span class="student-stat-icon">♡</span>
            <strong><a href="saved-jobs.php">View</a></strong>
            <span>Saved Jobs</span>
            <small>Your bookmarked opportunities</small>
        </div>

    </section>


    <!-- Dashboard Navigation -->
    <section class="student-actions-section">

        <div class="section-heading">
            <span class="eyebrow">YOUR WORKSPACE</span>
            <h2>Manage Your Career</h2>
            <p>Everything you need to move forward in one place.</p>
        </div>

        <div class="dashboard-grid student-dashboard-grid">

            <a class="dashboard-tile student-dashboard-tile" href="profile.php">

                <b class="student-tile-icon">◎</b>

                <h3>My Profile</h3>

                <p>
                    Update your personal information, education,
                    skills and CV.
                </p>

                <span class="student-tile-link">Manage Profile →</span>

            </a>


            <a class="dashboard-tile student-dashboard-tile" href="applications.php">

                <b class="student-tile-icon">▤</b>

                <h3>My Applications</h3>

                <p>
                    Review your submitted applications and
                    follow their progress.
                </p>

                <span class="student-tile-link">View Applications →</span>

            </a>


            <a class="dashboard-tile student-dashboard-tile" href="saved-jobs.php">

                <b class="student-tile-icon">♡</b>

                <h3>Saved Jobs</h3>

                <p>
                    Keep track of interesting jobs and
                    internships you have bookmarked.
                </p>

                <span class="student-tile-link">View Saved Jobs →</span>

            </a>


            <a class="dashboard-tile student-dashboard-tile" href="../jobs.php">

                <b class="student-tile-icon">＋</b>

                <h3>Find Opportunities</h3>

                <p>
                    Discover new jobs and internships
                    to take the next step in your career.
                </p>

                <span class="student-tile-link">Explore Jobs →</span>

            </a>

        </div>

    </section>


    <!-- Recent Applications -->
    <section class="section compact student-recent-section">

        <div class="section-heading student-recent-heading">
            <div>
                <span class="eyebrow">APPLICATION ACTIVITY</span>
                <h2>Recent Applications</h2>
                <p>Keep an eye on your latest submitted applications.</p>
            </div>

            <a class="btn btn-outline" href="applications.php">
                View All →
            </a>
        </div>


        <?php if (!$apps): ?>

            <div class="empty-state student-empty-state">

                <span class="student-empty-icon">▤</span>

                <h3>No Applications Yet</h3>

                <p>
                    You have not applied for any jobs yet.
                    Explore available opportunities and submit your first application.
                </p>

                <a class="btn" href="../jobs.php">
                    Find Your First Opportunity →
                </a>

            </div>

        <?php else: ?>

            <div class="table-wrap student-table-wrap">

                <table>

                    <thead>
                        <tr>
                            <th>Position</th>
                            <th>Company</th>
                            <th>Applied Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($apps as $a): ?>

                            <tr>

                                <td>
                                    <strong><?= e($a['title']) ?></strong>
                                </td>

                                <td>
                                    <?= e($a['company_name'] ?? 'Company') ?>
                                </td>

                                <td>
                                    <?= e(date(
                                        'd M Y',
                                        strtotime($a['applied_at'])
                                    )) ?>
                                </td>

                                <td>
                                    <span class="status">
                                        <?= e(job_status_label($a['status'])) ?>
                                    </span>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </section>

</div>


<?php

// Load page footer
require __DIR__ . '/../includes/footer.php';

?>
