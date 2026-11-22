
<?php

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Find Jobs & Internships';

// Get search filters
$q = trim($_GET['q'] ?? '');
$location = trim($_GET['location'] ?? '');
$type = trim($_GET['type'] ?? '');
$category = trim($_GET['category'] ?? '');

// Main query
$sql = "
    SELECT
        j.*,
        c.company_name AS company_name
    FROM jobs j
    LEFT JOIN companies c
        ON c.id = j.company_id
    WHERE j.status = 'approved'
    AND j.deadline >= CURDATE()
";

$params = [];

// Keyword search
if ($q !== '') {
    $sql .= "
        AND (
            j.title LIKE ?
            OR j.description LIKE ?
            OR c.company_name LIKE ?
        )
    ";

    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

// Location filter
if ($location !== '') {
    $sql .= " AND j.location LIKE ?";
    $params[] = "%$location%";
}

// Job type filter
if ($type !== '') {
    $sql .= " AND j.job_type = ?";
    $params[] = $type;
}

// Category filter
if ($category !== '') {
    $sql .= " AND j.category = ?";
    $params[] = $category;
}

// Latest jobs first
$sql .= " ORDER BY j.created_at DESC";

// Execute query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

// Load header
require __DIR__ . '/includes/header.php';

?>

<main class="jobs-page">

    <!-- Page Hero -->
    <section class="jobs-hero">

        <div class="jobs-hero-content">

            <span class="eyebrow">CAREERCONNECT OPPORTUNITIES</span>

            <h1>
                Find the opportunity
                <span>that moves you forward.</span>
            </h1>

            <p>
                Explore approved jobs and internships, discover
                exciting career paths, and connect with employers.
            </p>

            <div class="jobs-hero-stats">
                <div>
                    <strong><?= count($jobs) ?></strong>
                    <span>Matching opportunities</span>
                </div>

                <div>
                    <strong>Career</strong>
                    <span>Your next chapter starts here</span>
                </div>
            </div>

        </div>

        <div class="jobs-hero-decoration">
            <div class="jobs-glow-circle"></div>
            <div class="jobs-decoration-card">
                <span class="decoration-symbol">✦</span>
                <h3>Your future is full of possibilities.</h3>
                <p>Discover. Apply. Grow.</p>
                <span class="decoration-label">CAREERCONNECT</span>
            </div>
        </div>

    </section>


    <!-- Search and Filter Section -->
    <section class="jobs-filter-section">

        <div class="jobs-filter-heading">
            <span class="eyebrow">SEARCH & DISCOVER</span>
            <h2>Explore opportunities</h2>
            <p>Use the filters below to find the right position for you.</p>
        </div>

        <div class="jobs-filter-panel">

            <form method="get" class="jobs-filter-form">

                <div class="jobs-filter-grid">

                    <label class="jobs-field">
                        <span>Job title or keyword</span>

                        <input
                            type="text"
                            name="q"
                            placeholder="e.g. PHP Developer"
                            value="<?= e($q) ?>"
                        >
                    </label>

                    <label class="jobs-field">
                        <span>Location</span>

                        <input
                            type="text"
                            name="location"
                            placeholder="e.g. Sialkot or Remote"
                            value="<?= e($location) ?>"
                        >
                    </label>

                    <label class="jobs-field">
                        <span>Job type</span>

                        <select name="type">

                            <option value="">All types</option>

                            <?php foreach (
                                ['Internship', 'Full-time', 'Part-time', 'Contract']
                                as $v
                            ): ?>

                                <option
                                    value="<?= e($v) ?>"
                                    <?= $type === $v ? 'selected' : '' ?>
                                >
                                    <?= e($v) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>
                    </label>

                    <label class="jobs-field">
                        <span>Category</span>

                        <select name="category">

                            <option value="">All categories</option>

                            <?php foreach (
                                [
                                    'Information Technology',
                                    'Business',
                                    'Engineering',
                                    'Design',
                                    'Marketing',
                                    'Healthcare',
                                    'Other'
                                ] as $v
                            ): ?>

                                <option
                                    value="<?= e($v) ?>"
                                    <?= $category === $v ? 'selected' : '' ?>
                                >
                                    <?= e($v) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>
                    </label>

                </div>

                <div class="jobs-filter-actions">

                    <button class="btn jobs-search-btn" type="submit">
                        <span>⌕</span> Search opportunities
                    </button>

                    <a class="btn btn-outline jobs-reset-btn" href="jobs.php">
                        Reset filters
                    </a>

                </div>

            </form>

        </div>

    </section>


    <!-- Job Listings -->
    <section class="jobs-results-section">

        <div class="jobs-results-heading">

            <div>
                <span class="eyebrow">EXPLORE CAREERS</span>
                <h2>Available opportunities</h2>

                <p>
                    Discover openings posted by registered companies.
                </p>
            </div>

            <div class="jobs-results-count">
                <strong><?= count($jobs) ?></strong>
                <span>
                    <?= count($jobs) === 1 ? 'Opportunity found' : 'Opportunities found' ?>
                </span>
            </div>

        </div>


        <div class="jobs-listing-grid">

            <?php if (!$jobs): ?>

                <div class="jobs-empty-state">

                    <div class="empty-icon">⌕</div>

                    <h3>No opportunities found</h3>

                    <p>
                        We couldn't find any jobs matching your search.
                        Try changing your keywords or filters.
                    </p>

                    <a href="jobs.php" class="btn">
                        View all opportunities
                    </a>

                </div>

            <?php endif; ?>


            <?php foreach ($jobs as $job): ?>

                <?php
                    $companyName = $job['company_name'] ?: 'Company';

                    $companyInitial = strtoupper(
                        substr($companyName, 0, 1)
                    );
                ?>

                <article class="jobs-listing-card">

                    <div class="jobs-card-top">

                        <div class="jobs-company-logo">
                            <?= e($companyInitial) ?>
                        </div>

                        <span class="jobs-type-tag">
                            <?= e($job['job_type']) ?>
                        </span>

                    </div>


                    <div class="jobs-card-content">

                        <h3>
                            <?= e($job['title']) ?>
                        </h3>

                        <p class="jobs-company-name">
                            <?= e($companyName) ?>
                        </p>

                        <div class="jobs-card-description">

                            <span>
                                <span class="meta-icon">⌖</span>
                                <?= e($job['location']) ?>
                            </span>

                            <span>
                                <span class="meta-icon">◈</span>
                                <?= e($job['category']) ?>
                            </span>

                        </div>

                    </div>


                    <div class="jobs-card-footer">

                        <div class="jobs-salary">
                            <small>Salary</small>

                            <strong>
                                <?= e($job['salary'] ?: 'Not specified') ?>
                            </strong>
                        </div>

                        <a
                            class="jobs-details-link"
                            href="<?= BASE_URL ?>/job.php?id=<?= (int)$job['id'] ?>"
                        >
                            View details <span>→</span>
                        </a>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    </section>


    <!-- Bottom Call to Action -->
    <section class="jobs-bottom-cta">

        <div>
            <span class="eyebrow">YOUR NEXT STEP</span>

            <h2>Ready to build your future?</h2>

            <p>
                Create your student account and start applying
                for opportunities that match your goals.
            </p>
        </div>

        <a href="<?= BASE_URL ?>/signup.php" class="btn jobs-cta-btn">
            Get started <span>→</span>
        </a>

    </section>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
