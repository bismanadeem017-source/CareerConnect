

<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Find Jobs & Internships';

// Fetch latest approved jobs with company information
$latest = $pdo->query("
    SELECT
        j.*,
        c.company_name
    FROM jobs j
    LEFT JOIN companies c ON c.id = j.company_id
    WHERE j.status = 'approved'
      AND j.deadline >= CURDATE()
    ORDER BY j.created_at DESC
    LIMIT 6
")->fetchAll();

// Fetch homepage statistics
$counts = $pdo->query("
    SELECT
        COUNT(*) AS jobs,
        COUNT(DISTINCT company_id) AS companies
    FROM jobs
    WHERE status = 'approved'
")->fetch();

require __DIR__ . '/includes/header.php';
?>

<!-- HOMEPAGE HERO -->
<section class="hero home-hero">

    <div class="hero-background-effects" aria-hidden="true">
        <span class="glow-orb orb-one"></span>
        <span class="glow-orb orb-two"></span>
        <span class="glow-orb orb-three"></span>

        <span class="floating-particle particle-one"></span>
        <span class="floating-particle particle-two"></span>
        <span class="floating-particle particle-three"></span>
        <span class="floating-particle particle-four"></span>
        <span class="floating-particle particle-five"></span>
    </div>

    <div class="hero-copy">

        <span class="eyebrow hero-eyebrow">
            <span class="live-dot"></span>
            YOUR CAREER STARTS HERE
        </span>

        <h1>
            Discover opportunities.
            <br>
            <em>Build your future.</em>
        </h1>

        <p>
            Find jobs and internships, connect with employers,
            and take the next step in your career.
        </p>

        <form class="search-panel home-search-panel"
              action="<?= BASE_URL ?>/jobs.php"
              method="get">

            <label>
                <span class="search-label-icon">⌕</span>
                Job title or keyword

                <input
                    type="text"
                    name="q"
                    placeholder="e.g. Web Developer"
                >
            </label>

            <label>
                <span class="search-label-icon">⌖</span>
                Location

                <input
                    type="text"
                    name="location"
                    placeholder="e.g. Sialkot or Remote"
                >
            </label>

            <button class="btn home-search-btn" type="submit">
                Search jobs
                <span>→</span>
            </button>

        </form>

        <div class="popular-searches">
            <span>Popular searches:</span>

            <a href="<?= BASE_URL ?>/jobs.php?q=internship">
                Internships
            </a>

            <a href="<?= BASE_URL ?>/jobs.php?q=developer">
                Developers
            </a>

            <a href="<?= BASE_URL ?>/jobs.php?location=Remote">
                Remote
            </a>
        </div>

    </div>

    <!-- DECORATIVE CAREER CARD -->
    <div class="hero-art">

        <div class="hero-art-glow"></div>

        <div class="art-card">

            <div class="art-card-top">
                <span class="art-icon">✦</span>

                <span class="art-status">
                    <span></span>
                    Opportunities await
                </span>
            </div>

            <div class="art-main-icon">
                <span>▣</span>
            </div>

            <span class="art-small-label">
                YOUR NEXT CHAPTER
            </span>

            <h3>
                Your next opportunity
                <span>is waiting.</span>
            </h3>

            <p>
                One platform. New possibilities.
                Endless opportunities.
            </p>

            <div class="art-divider"></div>

            <div class="art-bottom">
                <span class="art-pill">
                    CAREERCONNECT
                </span>

                <span class="art-arrow">↗</span>
            </div>

        </div>

        <div class="floating-info info-one">
            <span class="info-icon">✦</span>
            <span>New opportunities</span>
        </div>

        <div class="floating-info info-two">
            <span class="info-check">✓</span>
            <span>Build your future</span>
        </div>

    </div>

    <div class="hero-bottom-glow"></div>

</section>


<!-- HOMEPAGE STATISTICS -->
<section class="stats home-stats">

    <div class="stat-card">

        <span class="stat-icon">▤</span>

        <strong><?= (int)$counts['jobs'] ?>+</strong>

        <span>Approved opportunities</span>

    </div>

    <div class="stat-card">

        <span class="stat-icon">♙</span>

        <strong><?= (int)$counts['companies'] ?>+</strong>

        <span>Hiring companies</span>

    </div>

    <div class="stat-card">

        <span class="stat-icon">✦</span>

        <strong>All in one</strong>

        <span>Job and internship search</span>

    </div>

</section>


<!-- POPULAR CATEGORIES -->
<section class="section home-categories">

    <div class="section-heading">

        <div>

            <span class="eyebrow">
                <span class="heading-line"></span>
                EXPLORE OPPORTUNITIES
            </span>

            <h2>
                Find your <em>career path.</em>
            </h2>

            <p>
                Explore professional fields and discover
                opportunities that match your interests.
            </p>

        </div>

        <a class="text-link"
           href="<?= BASE_URL ?>/jobs.php">

            Browse all jobs
            <span>→</span>

        </a>

    </div>


    <div class="category-grid">

        <?php foreach ([
            'Information Technology' => '⌘',
            'Business' => '▤',
            'Engineering' => '⚙',
            'Design' => '✎',
            'Marketing' => '◈',
            'Healthcare' => '＋'
        ] as $cat => $icon): ?>

            <a class="category-card"
               href="<?= BASE_URL ?>/jobs.php?category=<?= urlencode($cat) ?>">

                <span class="category-icon">
                    <?= $icon ?>
                </span>

                <h3><?= e($cat) ?></h3>

                <small>
                    Explore opportunities
                    <span>→</span>
                </small>

            </a>

        <?php endforeach; ?>

    </div>

</section>


<!-- LATEST JOB OPPORTUNITIES -->
<section class="section home-latest-jobs">

    <div class="section-heading">

        <div>

            <span class="eyebrow">
                <span class="heading-line"></span>
                RECENTLY POSTED
            </span>

            <h2>
                Latest <em>opportunities.</em>
            </h2>

            <p>
                Discover the latest openings from registered companies.
            </p>

        </div>

        <a class="text-link"
           href="<?= BASE_URL ?>/jobs.php">

            View all jobs
            <span>→</span>

        </a>

    </div>


    <div class="job-grid">

        <?php if (!$latest): ?>

            <div class="empty-state">

                <span class="empty-icon">⌕</span>

                <h3>No opportunities available yet</h3>

                <p>
                    Company listings will appear here after
                    admin approval.
                </p>

            </div>

        <?php endif; ?>


        <?php foreach ($latest as $job): ?>

            <article class="job-card home-job-card">

                <div class="job-card-top">

                    <span class="company-mark">

                        <?= e(
                            strtoupper(
                                substr(
                                    $job['company_name'] ?: 'C',
                                    0,
                                    1
                                )
                            )
                        ) ?>

                    </span>

                    <span class="tag">
                        <?= e($job['job_type']) ?>
                    </span>

                </div>


                <h3><?= e($job['title']) ?></h3>


                <p class="muted company-name">
                    <?= e($job['company_name'] ?: 'Company') ?>
                </p>


                <div class="job-meta">

                    <span>
                        <span class="meta-icon">⌖</span>
                        <?= e($job['location']) ?>
                    </span>

                    <span>
                        <span class="meta-icon">◈</span>
                        <?= e($job['category']) ?>
                    </span>

                </div>


                <div class="job-card-bottom">

                    <strong>
                        <?= e(
                            $job['salary'] ?: 'Salary not specified'
                        ) ?>
                    </strong>

                    <a href="<?= BASE_URL ?>/job.php?id=<?= (int)$job['id'] ?>">
                        View details
                        <span>→</span>
                    </a>

                </div>

            </article>

        <?php endforeach; ?>

    </div>

</section>


<!-- HOW CAREERCONNECT WORKS -->
<section class="section home-process">

    <div class="section-heading centered">

        <span class="eyebrow">
            <span class="heading-line"></span>
            SIMPLE PROCESS
            <span class="heading-line"></span>
        </span>

        <h2>
            Your journey starts <em>here.</em>
        </h2>

        <p>
            Three simple steps to move closer to your career goals.
        </p>

    </div>


    <div class="steps home-steps">

        <div class="process-card">

            <div class="process-number">01</div>

            <span class="process-icon">♙</span>

            <h3>Create an account</h3>

            <p>
                Register as a student or company
                and set up your account.
            </p>

        </div>


        <div class="process-card">

            <div class="process-number">02</div>

            <span class="process-icon">⌕</span>

            <h3>Find your match</h3>

            <p>
                Search jobs and internships by keyword,
                location and employment type.
            </p>

        </div>


        <div class="process-card">

            <div class="process-number">03</div>

            <span class="process-icon">↗</span>

            <h3>Apply and track</h3>

            <p>
                Submit your CV and follow
                your application status.
            </p>

        </div>

    </div>

</section>


<!-- FINAL CALL TO ACTION -->
<section class="home-cta">

    <div class="cta-decoration" aria-hidden="true">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <div class="cta-content">

        <span class="eyebrow">
            YOUR NEXT CHAPTER BEGINS NOW
        </span>

        <h2>
            Ready to discover
            <em>what's next?</em>
        </h2>

        <p>
            Explore opportunities and take the next step
            towards your professional future.
        </p>

        <a href="<?= BASE_URL ?>/jobs.php" class="btn">
            Explore opportunities
            <span>→</span>
        </a>

    </div>

</section>


<?php require __DIR__ . '/includes/footer.php'; ?>
