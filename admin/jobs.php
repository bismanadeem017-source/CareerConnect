
<?php
require_once __DIR__ . '/../includes/functions.php';

require_role('admin');

// Handle job actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $id = (int)($_POST['job_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM jobs WHERE id = ?');
        $stmt->execute([$id]);

        set_flash('Job deleted successfully.');

    } elseif (in_array($action, ['approved', 'rejected', 'pending'], true)) {
        $stmt = $pdo->prepare(
            'UPDATE jobs SET status = ? WHERE id = ?'
        );
        $stmt->execute([$action, $id]);

        set_flash('Job status updated successfully.');
    }

    redirect('admin/jobs.php');
}

// Fetch jobs with their company names
$pageTitle = 'Manage Jobs';

$sql = '
    SELECT
        j.*,
        c.company_name
    FROM jobs j
    JOIN companies c ON c.id = j.company_id
    ORDER BY j.created_at DESC
';

$jobs = $pdo->query($sql)->fetchAll();

// Calculate job statistics
$totalJobs = count($jobs);
$approvedJobs = 0;
$pendingJobs = 0;
$rejectedJobs = 0;

foreach ($jobs as $job) {
    if ($job['status'] === 'approved') {
        $approvedJobs++;
    } elseif ($job['status'] === 'pending') {
        $pendingJobs++;
    } elseif ($job['status'] === 'rejected') {
        $rejectedJobs++;
    }
}

require __DIR__ . '/../includes/header.php';
?>

<div class="admin-jobs-page">

    <!-- Page Hero -->
    <section class="admin-jobs-hero">

        <div class="admin-jobs-hero-content">

            <span class="eyebrow">ADMINISTRATION · JOB MODERATION</span>

            <h1>Manage <span>Opportunities</span></h1>

            <p>
                Review employer submissions, monitor listing statuses
                and ensure that every opportunity meets platform standards.
            </p>

            <div class="admin-jobs-hero-label">
                <span class="admin-jobs-status-dot"></span>
                Job Management Center
            </div>

        </div>

        <div class="admin-jobs-hero-decoration">

            <div class="admin-jobs-glow"></div>

            <div class="admin-jobs-decoration-card">
                <span>▤</span>
                <strong><?= $totalJobs ?></strong>
                <small>Total Job Listings</small>
            </div>

        </div>

    </section>

    <!-- Statistics -->
    <section class="admin-jobs-stats">

        <article class="admin-jobs-stat-card">

            <div class="admin-jobs-stat-icon">▤</div>

            <div>
                <span>Total Jobs</span>
                <strong><?= $totalJobs ?></strong>
                <small>All submitted listings</small>
            </div>

        </article>

        <article class="admin-jobs-stat-card">

            <div class="admin-jobs-stat-icon">◷</div>

            <div>
                <span>Pending Review</span>
                <strong><?= $pendingJobs ?></strong>
                <small>Awaiting administration</small>
            </div>

        </article>

        <article class="admin-jobs-stat-card">

            <div class="admin-jobs-stat-icon">✓</div>

            <div>
                <span>Approved Jobs</span>
                <strong><?= $approvedJobs ?></strong>
                <small>Published opportunities</small>
            </div>

        </article>

        <article class="admin-jobs-stat-card">

            <div class="admin-jobs-stat-icon">×</div>

            <div>
                <span>Rejected Jobs</span>
                <strong><?= $rejectedJobs ?></strong>
                <small>Rejected submissions</small>
            </div>

        </article>

    </section>

    <!-- Job Management Table -->
    <section class="admin-jobs-list-section">

        <div class="admin-jobs-list-heading">

            <div>
                <span class="eyebrow">LISTING MANAGEMENT</span>
                <h2>All Job Listings</h2>
                <p>
                    Review each submission and select an action to update it.
                </p>
            </div>

            <a
                href="<?= BASE_URL ?>/admin/dashboard.php"
                class="admin-jobs-back-link"
            >
                ← Back to Dashboard
            </a>

        </div>

        <?php if (empty($jobs)): ?>

            <div class="admin-jobs-empty">

                <div class="admin-jobs-empty-icon">▤</div>

                <h3>No Job Listings Found</h3>

                <p>
                    There are currently no job listings submitted
                    by companies on CareerConnect.
                </p>

            </div>

        <?php else: ?>

            <div class="admin-jobs-table-wrap">

                <table class="admin-jobs-table">

                    <thead>
                        <tr>
                            <th>Job Information</th>
                            <th>Company</th>
                            <th>Deadline</th>
                            <th>Current Status</th>
                            <th>Manage Listing</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($jobs as $j): ?>

                            <tr>

                                <td>
                                    <div class="admin-job-information">

                                        <div class="admin-job-icon">
                                            ✧
                                        </div>

                                        <div>
                                            <strong>
                                                <?= e($j['title']) ?>
                                            </strong>

                                            <small>
                                                <?= e($j['category'] ?? 'General') ?>
                                            </small>
                                        </div>

                                    </div>
                                </td>

                                <td>
                                    <span class="admin-job-company">
                                        <?= e($j['company_name']) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="admin-job-deadline">
                                        <?= e($j['deadline']) ?>
                                    </span>
                                </td>

                                <td>

                                    <?php
                                    $statusClass = match ($j['status']) {
                                        'approved' => 'approved',
                                        'rejected' => 'rejected',
                                        default => 'pending'
                                    };
                                    ?>

                                    <span class="admin-job-status admin-job-status-<?= e($statusClass) ?>">
                                        <?= e(job_status_label($j['status'])) ?>
                                    </span>

                                </td>

                                <td>

                                    <form
                                        class="admin-job-action-form"
                                        method="post"
                                        onsubmit="return this.elements.action.value !== 'delete' || confirm('Are you sure you want to permanently delete this job?');"
                                    >

                                        <?= csrf_field() ?>

                                        <input
                                            type="hidden"
                                            name="job_id"
                                            value="<?= (int)$j['id'] ?>"
                                        >

                                        <select name="action" aria-label="Choose action for <?= e($j['title']) ?>">

                                            <option value="approved">
                                                Approve
                                            </option>

                                            <option value="rejected">
                                                Reject
                                            </option>

                                            <option value="pending">
                                                Pending
                                            </option>

                                            <option value="delete">
                                                Delete
                                            </option>

                                        </select>

                                        <button type="submit">
                                            Save
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </section>

    <!-- Admin Guidance -->
    <section class="admin-jobs-note">

        <div class="admin-jobs-note-icon">✦</div>

        <div>
            <h3>Maintain Quality Opportunities</h3>
            <p>
                Approve relevant job listings to make them visible
                to students. Review submissions carefully before
                changing their status.
            </p>
        </div>

    </section>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
