
<?php

require_once __DIR__ . '/../includes/functions.php';

require_role('company');

$userId = (int) current_user()['id'];

$pageTitle = 'Post a Job';
$error = '';
$editing = false;


// Get the actual company ID belonging to the logged-in user
$companyStmt = $pdo->prepare(
    'SELECT id FROM companies WHERE user_id = ? LIMIT 1'
);

$companyStmt->execute([$userId]);
$company = $companyStmt->fetch();

if (!$company) {
    exit('Company profile not found. Please contact the administrator.');
}

$companyId = (int) $company['id'];


// Default job information
$job = [
    'title' => '',
    'description' => '',
    'requirements' => '',
    'category' => 'Information Technology',
    'job_type' => 'Internship',
    'location' => 'Sialkot',
    'salary' => '',
    'deadline' => ''
];


// Check whether the company is editing an existing job
$id = (int) ($_GET['edit'] ?? 0);

if ($id > 0) {

    $stmt = $pdo->prepare(
        'SELECT * FROM jobs WHERE id = ? AND company_id = ?'
    );

    $stmt->execute([$id, $companyId]);
    $existing = $stmt->fetch();

    if (!$existing) {
        http_response_code(404);
        exit('Job not found.');
    }

    $job = $existing;
    $editing = true;
}

$pageTitle = $editing ? 'Edit Job' : 'Post a Job';


// Process submitted form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $fields = [
        'title',
        'description',
        'requirements',
        'category',
        'job_type',
        'location',
        'salary',
        'deadline'
    ];

    $data = [];

    foreach ($fields as $field) {
        $data[$field] = trim($_POST[$field] ?? '');
    }


    // Validate required information
    if (
        $data['title'] === '' ||
        $data['description'] === '' ||
        $data['location'] === '' ||
        $data['deadline'] === ''
    ) {

        $error = 'Please complete all required fields.';

    } elseif ($data['deadline'] < date('Y-m-d')) {

        $error = 'Deadline must be today or a future date.';

    } else {

        if ($editing) {

            // Update existing company job
            $stmt = $pdo->prepare(
                "UPDATE jobs SET
                    title = ?,
                    description = ?,
                    requirements = ?,
                    category = ?,
                    job_type = ?,
                    location = ?,
                    salary = ?,
                    deadline = ?,
                    status = 'pending'
                 WHERE id = ? AND company_id = ?"
            );

            $stmt->execute([
                $data['title'],
                $data['description'],
                $data['requirements'],
                $data['category'],
                $data['job_type'],
                $data['location'],
                $data['salary'],
                $data['deadline'],
                $id,
                $companyId
            ]);

        } else {

            // Insert job using the companies table ID
            $stmt = $pdo->prepare(
                "INSERT INTO jobs (
                    company_id,
                    title,
                    description,
                    requirements,
                    category,
                    job_type,
                    location,
                    salary,
                    deadline,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
            );

            $stmt->execute([
                $companyId,
                $data['title'],
                $data['description'],
                $data['requirements'],
                $data['category'],
                $data['job_type'],
                $data['location'],
                $data['salary'],
                $data['deadline']
            ]);
        }

        set_flash(
            'Job submitted successfully. It will appear publicly after admin approval.'
        );

        redirect('company/dashboard.php');
    }
}


// Load page header
require __DIR__ . '/../includes/header.php';

?>

<div class="company-post-job-page">

    <!-- Page Introduction -->
    <section class="page-heading company-post-job-hero">

        <span class="eyebrow">EMPLOYER WORKSPACE</span>

        <h1>
            <?= $editing ? 'Update Your Opportunity' : 'Find Your Next Great Talent' ?>
        </h1>

        <p>
            <?= $editing
                ? 'Keep your opportunity details accurate and up to date.'
                : 'Create a detailed job listing and connect with talented students and professionals.'
            ?>
        </p>

    </section>


    <!-- Form Layout -->
    <section class="company-post-job-layout">

        <div class="company-post-job-main">

            <form
                class="form-card wide company-job-form"
                method="post"
            >

                <?= csrf_field() ?>


                <div class="company-form-heading">

                    <span class="company-form-icon">▤</span>

                    <div>
                        <span class="eyebrow">JOB INFORMATION</span>

                        <h2>
                            <?= $editing ? 'Edit Job Details' : 'Opportunity Details' ?>
                        </h2>

                        <p>Complete the information about this opportunity.</p>
                    </div>

                </div>


                <?php if ($error): ?>

                    <div class="alert error">
                        <?= e($error) ?>
                    </div>

                <?php endif; ?>


                <!-- Job Title -->
                <label class="company-job-field">
                    <span>Job Title <strong>*</strong></span>

                    <input
                        type="text"
                        name="title"
                        required
                        maxlength="255"
                        placeholder="e.g. PHP Developer Intern"
                        value="<?= e($_POST['title'] ?? $job['title']) ?>"
                    >
                </label>


                <!-- Description -->
                <label class="company-job-field">
                    <span>Job Description <strong>*</strong></span>

                    <textarea
                        name="description"
                        rows="7"
                        required
                        placeholder="Describe the role, responsibilities and what the selected candidate will do..."
                    ><?= e($_POST['description'] ?? $job['description']) ?></textarea>

                    <small>
                        Explain the role clearly so applicants understand the opportunity.
                    </small>
                </label>


                <!-- Requirements -->
                <label class="company-job-field">
                    <span>Requirements & Qualifications</span>

                    <textarea
                        name="requirements"
                        rows="5"
                        placeholder="Mention education, skills, experience and other requirements..."
                    ><?= e($_POST['requirements'] ?? $job['requirements']) ?></textarea>
                </label>


                <!-- Category and Job Type -->
                <div class="two-fields company-job-two-fields">

                    <label class="company-job-field">
                        <span>Job Category</span>

                        <select name="category">

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
                                    <?= ($_POST['category'] ?? $job['category']) === $v ? 'selected' : '' ?>
                                >
                                    <?= e($v) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>
                    </label>


                    <label class="company-job-field">
                        <span>Employment Type</span>

                        <select name="job_type">

                            <?php foreach (
                                [
                                    'Internship',
                                    'Full-time',
                                    'Part-time',
                                    'Contract'
                                ] as $v
                            ): ?>

                                <option
                                    value="<?= e($v) ?>"
                                    <?= ($_POST['job_type'] ?? $job['job_type']) === $v ? 'selected' : '' ?>
                                >
                                    <?= e($v) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>
                    </label>

                </div>


                <!-- Location and Salary -->
                <div class="two-fields company-job-two-fields">

                    <label class="company-job-field">
                        <span>Job Location <strong>*</strong></span>

                        <input
                            type="text"
                            name="location"
                            required
                            maxlength="255"
                            placeholder="e.g. Sialkot, Pakistan"
                            value="<?= e($_POST['location'] ?? $job['location']) ?>"
                        >
                    </label>


                    <label class="company-job-field">
                        <span>Salary / Stipend</span>

                        <input
                            type="text"
                            name="salary"
                            maxlength="255"
                            placeholder="e.g. Rs. 25,000/month"
                            value="<?= e($_POST['salary'] ?? $job['salary']) ?>"
                        >
                    </label>

                </div>


                <!-- Deadline -->
                <label class="company-job-field">
                    <span>Application Deadline <strong>*</strong></span>

                    <input
                        type="date"
                        name="deadline"
                        min="<?= date('Y-m-d') ?>"
                        required
                        value="<?= e($_POST['deadline'] ?? $job['deadline']) ?>"
                    >
                </label>


                <!-- Approval Notice -->
                <div class="company-job-approval-note">

                    <span>ⓘ</span>

                    <p>
                        Your job listing will be submitted for administrator
                        approval. It will become publicly visible after approval.
                    </p>

                </div>


                <!-- Form Actions -->
                <div class="company-job-form-actions">

                    <a class="btn btn-outline" href="dashboard.php">
                        Cancel
                    </a>

                    <button class="btn" type="submit">
                        <?= $editing ? 'Update Job →' : 'Submit for Approval →' ?>
                    </button>

                </div>

            </form>

        </div>


        <!-- Sidebar -->
        <aside class="company-post-job-sidebar">

            <div class="company-job-tip-card">

                <span class="eyebrow">RECRUITMENT GUIDE</span>

                <div class="company-job-tip-icon">✦</div>

                <h3>Create an Effective Job Listing</h3>

                <p>
                    A clear and informative job description helps
                    attract suitable candidates.
                </p>

                <ul>
                    <li>Choose a clear job title.</li>
                    <li>Explain responsibilities.</li>
                    <li>List required qualifications.</li>
                    <li>Specify location and employment type.</li>
                    <li>Set a realistic application deadline.</li>
                </ul>

            </div>


            <div class="company-job-tip-card company-job-review-card">

                <span class="eyebrow">WHAT HAPPENS NEXT?</span>

                <h3>Approval Process</h3>

                <p>
                    After submission, the administrator reviews your
                    listing before it appears on the public jobs page.
                </p>

                <a href="dashboard.php">
                    Return to Dashboard →
                </a>

            </div>

        </aside>

    </section>

</div>


<?php require __DIR__ . '/../includes/footer.php'; ?>
