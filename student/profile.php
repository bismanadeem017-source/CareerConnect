
<?php

require_once __DIR__ . '/../includes/functions.php';

// Allow only student accounts
require_role('student');

$uid = current_user()['id'];

$pageTitle = 'My Profile';
$error = '';


// Make sure the student profile record exists
$createProfile = $pdo->prepare(
    'INSERT IGNORE INTO student_profiles (user_id) VALUES (?)'
);
$createProfile->execute([$uid]);


// Get student information
$stmt = $pdo->prepare(
    'SELECT
        u.full_name AS name,
        u.email,
        u.phone,
        p.education,
        p.skills,
        p.cv_file
     FROM users u
     LEFT JOIN student_profiles p ON p.user_id = u.id
     WHERE u.id = ?'
);
$stmt->execute([$uid]);
$profile = $stmt->fetch();


// Process profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    try {

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $education = trim($_POST['education'] ?? '');
        $skills = trim($_POST['skills'] ?? '');

        // Validate name
        if ($name === '') {
            throw new RuntimeException('Name is required.');
        }

        // Upload CV if provided
        $cv = upload_cv(
            $_FILES['cv'] ?? ['error' => UPLOAD_ERR_NO_FILE]
        );

        // Update users table
        $updateUser = $pdo->prepare(
            'UPDATE users
             SET full_name = ?, phone = ?
             WHERE id = ?'
        );

        $updateUser->execute([
            $name,
            $phone,
            $uid
        ]);

        // Update student profile information
        $updateProfile = $pdo->prepare(
            'INSERT INTO student_profiles
                (user_id, education, skills, cv_file)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                education = VALUES(education),
                skills = VALUES(skills),
                cv_file = COALESCE(VALUES(cv_file), cv_file)'
        );

        $updateProfile->execute([
            $uid,
            $education,
            $skills,
            $cv
        ]);

        // Update current session
        $_SESSION['user']['name'] = $name;

        set_flash('Profile updated successfully.');

        redirect('student/profile.php');

    } catch (Throwable $e) {

        error_log('CareerConnect profile error: ' . $e->getMessage());

        $error = $e->getMessage();
    }

    // Refresh profile information after submission
    $stmt = $pdo->prepare(
        'SELECT
            u.full_name AS name,
            u.email,
            u.phone,
            p.education,
            p.skills,
            p.cv_file
         FROM users u
         LEFT JOIN student_profiles p ON p.user_id = u.id
         WHERE u.id = ?'
    );

    $stmt->execute([$uid]);
    $profile = $stmt->fetch();
}


require __DIR__ . '/../includes/header.php';

?>

<div class="student-profile-page">

    <!-- Profile Hero -->
    <section class="page-heading student-profile-hero">

        <span class="eyebrow">YOUR PROFESSIONAL IDENTITY</span>

        <h1>Build Your Profile</h1>

        <p>
            Present your skills, education and experience.
            Keep your information updated to make the most of every opportunity.
        </p>

    </section>


    <!-- Profile Content -->
    <section class="student-profile-layout">

        <!-- Profile Information -->
        <div class="student-profile-main">

            <form
                class="form-card wide student-profile-form"
                method="post"
                enctype="multipart/form-data"
            >

                <?= csrf_field() ?>

                <div class="student-profile-form-heading">

                    <span class="student-profile-heading-icon">◎</span>

                    <div>
                        <span class="eyebrow">PERSONAL DETAILS</span>
                        <h2>Student Information</h2>
                        <p>Update your personal and academic information.</p>
                    </div>

                </div>


                <?php if ($error): ?>

                    <div class="alert error">
                        <?= e($error) ?>
                    </div>

                <?php endif; ?>


                <div class="student-profile-fields">

                    <!-- Full Name -->
                    <label class="student-profile-field">
                        <span>Full Name</span>

                        <input
                            type="text"
                            name="name"
                            required
                            maxlength="120"
                            value="<?= e($profile['name'] ?? '') ?>"
                            placeholder="Enter your full name"
                        >
                    </label>


                    <!-- Email -->
                    <label class="student-profile-field">
                        <span>Email Address</span>

                        <input
                            type="email"
                            value="<?= e($profile['email'] ?? '') ?>"
                            disabled
                        >

                        <small>Your registered email cannot be changed here.</small>
                    </label>


                    <!-- Phone -->
                    <label class="student-profile-field">
                        <span>Phone Number</span>

                        <input
                            type="text"
                            name="phone"
                            maxlength="30"
                            value="<?= e($profile['phone'] ?? '') ?>"
                            placeholder="Enter your phone number"
                        >
                    </label>


                    <!-- Education -->
                    <label class="student-profile-field">
                        <span>Education</span>

                        <input
                            type="text"
                            name="education"
                            maxlength="255"
                            placeholder="e.g. BS Computer Science"
                            value="<?= e($profile['education'] ?? '') ?>"
                        >
                    </label>


                    <!-- Skills -->
                    <label class="student-profile-field student-profile-full-width">
                        <span>Skills & Expertise</span>

                        <textarea
                            name="skills"
                            rows="5"
                            placeholder="Describe your skills, e.g. HTML, CSS, PHP, MySQL, teamwork..."
                        ><?= e($profile['skills'] ?? '') ?></textarea>

                        <small>
                            Mention technical skills, communication skills and other strengths.
                        </small>
                    </label>

                </div>


                <!-- CV Upload -->
                <div class="student-cv-section">

                    <div class="student-profile-form-heading">

                        <span class="student-profile-heading-icon">▤</span>

                        <div>
                            <span class="eyebrow">CAREER DOCUMENT</span>
                            <h2>Your Resume / CV</h2>
                            <p>Upload your latest CV for job applications.</p>
                        </div>

                    </div>


                    <label class="student-cv-upload">

                        <span class="student-cv-upload-icon">↑</span>

                        <strong>Choose your CV file</strong>

                        <span>PDF, DOC or DOCX · Maximum size 5 MB</span>

                        <input
                            type="file"
                            name="cv"
                            accept=".pdf,.doc,.docx"
                        >

                    </label>


                    <?php if (!empty($profile['cv_file'])): ?>

                        <div class="student-cv-existing">

                            <span class="student-cv-file-icon">✓</span>

                            <div>
                                <strong>CV already uploaded</strong>
                                <p>Your existing CV is saved in your profile.</p>
                            </div>

                        </div>

                    <?php else: ?>

                        <p class="student-cv-note">
                            You have not uploaded a CV yet.
                        </p>

                    <?php endif; ?>

                </div>


                <!-- Form Actions -->
                <div class="student-profile-actions">

                    <a class="btn btn-outline" href="dashboard.php">
                        Cancel
                    </a>

                    <button class="btn" type="submit">
                        Save Profile →
                    </button>

                </div>

            </form>

        </div>


        <!-- Profile Side Information -->
        <aside class="student-profile-sidebar">

            <div class="student-profile-side-card">

                <span class="eyebrow">YOUR CAREER JOURNEY</span>

                <div class="student-profile-side-icon">✦</div>

                <h3>Make Your Profile Stand Out</h3>

                <p>
                    A complete profile helps employers understand
                    your qualifications and professional strengths.
                </p>

                <ul>
                    <li>Keep your contact information updated.</li>
                    <li>Describe your education clearly.</li>
                    <li>Highlight your most relevant skills.</li>
                    <li>Upload an updated CV.</li>
                </ul>

            </div>


            <div class="student-profile-side-card student-profile-tip">

                <span class="eyebrow">CAREER TIP</span>

                <h3>Keep Growing</h3>

                <p>
                    Update your skills whenever you complete a course,
                    learn a new technology or gain practical experience.
                </p>

                <a href="../jobs.php">
                    Explore Opportunities →
                </a>

            </div>

        </aside>

    </section>

</div>


<?php

require __DIR__ . '/../includes/footer.php';

?>
