
<?php
require_once __DIR__ . '/../includes/functions.php';

require_role('admin');

$pageTitle = 'Admin Dashboard';

// Fetch platform statistics
$stats = $pdo->query("
    SELECT
        (SELECT COUNT(*) FROM users WHERE role = 'student') AS students,
        (SELECT COUNT(*) FROM users WHERE role = 'company') AS companies,
        (SELECT COUNT(*) FROM jobs) AS jobs,
        (SELECT COUNT(*) FROM applications) AS applications
")->fetch();

require __DIR__ . '/../includes/header.php';
?>

<div class="admin-dashboard-page">

    <!-- Dashboard Hero -->
    <section class="admin-dashboard-hero">

        <div class="admin-dashboard-hero-content">

            <span class="eyebrow">CAREERCONNECT · ADMINISTRATION</span>

            <h1>Control Your <span>Platform</span></h1>

            <p>
                Welcome to your administration dashboard.
                Monitor platform activity, manage registered users
                and review employer job listings from one place.
            </p>

            <div class="admin-dashboard-hero-label">
                <span class="admin-dashboard-status-dot"></span>
                Administration Panel
            </div>

        </div>

        <div class="admin-dashboard-hero-decoration">
            <div class="admin-dashboard-glow"></div>

            <div class="admin-dashboard-decoration-card">
                <span class="admin-dashboard-decoration-icon">✧</span>
                <strong>Admin</strong>
                <small>Platform Management</small>
            </div>
        </div>

    </section>

    <!-- Statistics -->
    <section class="admin-dashboard-stats-section">

        <div class="admin-dashboard-section-heading">
            <div>
                <span class="eyebrow">PLATFORM OVERVIEW</span>
                <h2>Dashboard Statistics</h2>
                <p>A quick overview of your CareerConnect community.</p>
            </div>
        </div>

        <div class="admin-dashboard-stats">

            <article class="admin-stat-card">
                <div class="admin-stat-icon">♙</div>
                <div class="admin-stat-info">
                    <span>Registered Students</span>
                    <strong><?= (int)$stats['students'] ?></strong>
                    <small>Student accounts</small>
                </div>
            </article>

            <article class="admin-stat-card">
                <div class="admin-stat-icon">▣</div>
                <div class="admin-stat-info">
                    <span>Registered Companies</span>
                    <strong><?= (int)$stats['companies'] ?></strong>
                    <small>Employer accounts</small>
                </div>
            </article>

            <article class="admin-stat-card">
                <div class="admin-stat-icon">▤</div>
                <div class="admin-stat-info">
                    <span>Total Job Listings</span>
                    <strong><?= (int)$stats['jobs'] ?></strong>
                    <small>Posted opportunities</small>
                </div>
            </article>

            <article class="admin-stat-card">
                <div class="admin-stat-icon">✉</div>
                <div class="admin-stat-info">
                    <span>Total Applications</span>
                    <strong><?= (int)$stats['applications'] ?></strong>
                    <small>Candidate applications</small>
                </div>
            </article>

        </div>

    </section>

    <!-- Management Section -->
    <section class="admin-management-section">

        <div class="admin-management-heading">
            <span class="eyebrow">ADMIN TOOLS</span>
            <h2>Platform Management</h2>
            <p>Choose an area to manage your platform.</p>
        </div>

        <div class="admin-management-grid">

            <a href="jobs.php" class="admin-management-card">

                <div class="admin-management-card-top">
                    <div class="admin-management-icon">✓</div>
                    <span class="admin-management-arrow">↗</span>
                </div>

                <div class="admin-management-card-content">
                    <span class="admin-management-label">
                        JOB MODERATION
                    </span>

                    <h3>Approve Jobs</h3>

                    <p>
                        Review employer job submissions, approve suitable
                        listings or reject those that do not meet requirements.
                    </p>

                    <span class="admin-management-link">
                        Manage job listings →
                    </span>
                </div>

            </a>

            <a href="users.php" class="admin-management-card">

                <div class="admin-management-card-top">
                    <div class="admin-management-icon">♙</div>
                    <span class="admin-management-arrow">↗</span>
                </div>

                <div class="admin-management-card-content">
                    <span class="admin-management-label">
                        ACCOUNT MANAGEMENT
                    </span>

                    <h3>Manage Users</h3>

                    <p>
                        View registered student and company accounts
                        and manage the platform's user community.
                    </p>

                    <span class="admin-management-link">
                        View registered users →
                    </span>
                </div>

            </a>

        </div>

    </section>

    <!-- Admin Information -->
    <section class="admin-dashboard-note">

        <div class="admin-dashboard-note-icon">✦</div>

        <div>
            <h3>Your Platform, Your Control</h3>
            <p>
                Keep CareerConnect organized by reviewing job listings
                and monitoring registered accounts regularly.
            </p>
        </div>

    </section>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
