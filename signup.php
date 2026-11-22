
<?php

require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('index.php');
}

$pageTitle = 'Create Your CareerConnect Account';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    // Get form information
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student';
    $company = trim($_POST['company_name'] ?? '');

    // Validate information
    if (
        $name === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 8 ||
        !in_array($role, ['student', 'company'], true)
    ) {
        $error = 'Enter a valid name and email, choose an account type, and use a password of at least 8 characters.';

    } elseif ($role === 'company' && $company === '') {

        $error = 'Please enter your company name.';

    } else {

        // Check whether email already exists
        $check = $pdo->prepare(
            'SELECT id FROM users WHERE email = ?'
        );

        $check->execute([$email]);

        if ($check->fetch()) {

            $error = 'An account with this email already exists.';

        } else {

            try {

                // Start database transaction
                $pdo->beginTransaction();

                // Insert user information
                $stmt = $pdo->prepare(
                    'INSERT INTO users
                    (full_name, email, password_hash, role)
                    VALUES (?, ?, ?, ?)'
                );

                $stmt->execute([
                    $name,
                    $email,
                    password_hash($password, PASSWORD_DEFAULT),
                    $role
                ]);

                // Get newly created user ID
                $id = (int) $pdo->lastInsertId();

                // Insert company information separately
                if ($role === 'company') {

                    $companyStmt = $pdo->prepare(
                        'INSERT INTO companies
                        (user_id, company_name)
                        VALUES (?, ?)'
                    );

                    $companyStmt->execute([
                        $id,
                        $company
                    ]);
                }

                // Save all database changes
                $pdo->commit();

                // Create user session
                $_SESSION['user'] = [
                    'id' => $id,
                    'name' => $name,
                    'email' => $email,
                    'role' => $role,
                    'company_name' => $company
                ];

                // Success message
                set_flash('Your account has been created successfully.');

                // Redirect according to account type
                if ($role === 'student') {
                    redirect('student/dashboard.php');
                } else {
                    redirect('company/dashboard.php');
                }

            } catch (Throwable $e) {

                // Roll back incomplete database changes
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                error_log('CareerConnect signup error: ' . $e->getMessage());

                $error = 'Unable to create your account. Please try again.';
            }
        }
    }
}

require __DIR__ . '/includes/header.php';

?>

<main class="auth-page signup-page">

    <!-- Background Effects -->
    <div class="auth-background-effects">
        <div class="auth-glow auth-glow-one"></div>
        <div class="auth-glow auth-glow-two"></div>
    </div>


    <section class="signup-container">

        <!-- Left Introduction -->
        <div class="signup-intro">

            <span class="eyebrow">JOIN CAREERCONNECT</span>

            <h1>
                Create your account.
                <span>Shape your future.</span>
            </h1>

            <p>
                Whether you're looking for your next opportunity
                or searching for talented professionals,
                CareerConnect brings your goals closer.
            </p>


            <div class="signup-benefits">

                <div class="signup-benefit">

                    <span>✦</span>

                    <div>
                        <strong>For students</strong>

                        <small>
                            Explore internships, find jobs and
                            build your professional profile.
                        </small>
                    </div>

                </div>


                <div class="signup-benefit">

                    <span>◈</span>

                    <div>
                        <strong>For employers</strong>

                        <small>
                            Connect with candidates and
                            publish career opportunities.
                        </small>
                    </div>

                </div>


                <div class="signup-benefit">

                    <span>✓</span>

                    <div>
                        <strong>One connected platform</strong>

                        <small>
                            Discover opportunities and
                            create meaningful connections.
                        </small>
                    </div>

                </div>

            </div>


            <div class="signup-brand">
                <span class="signup-brand-icon">C</span>
                <span>CareerConnect</span>
            </div>

        </div>


        <!-- Registration Form -->
        <div class="signup-form-wrapper">

            <div class="signup-form-card">

                <div class="signup-form-heading">

                    <span class="eyebrow">GET STARTED</span>

                    <h2>Create your account</h2>

                    <p>
                        Fill in your details to join CareerConnect.
                    </p>

                </div>


                <?php if ($error): ?>

                    <div class="alert error">
                        <?= e($error) ?>
                    </div>

                <?php endif; ?>


                <form class="signup-form" method="post">

                    <?= csrf_field() ?>


                    <!-- Account Type -->
                    <label class="signup-field">

                        <span>Account type</span>

                        <select name="role" id="role" required>

                            <option value="student"
                                <?= ($_POST['role'] ?? 'student') === 'student' ? 'selected' : '' ?>>
                                Student
                            </option>

                            <option value="company"
                                <?= ($_POST['role'] ?? '') === 'company' ? 'selected' : '' ?>>
                                Company / Employer
                            </option>

                        </select>

                    </label>


                    <!-- User Name -->
                    <label class="signup-field">

                        <span>Full name</span>

                        <input
                            type="text"
                            name="name"
                            required
                            maxlength="120"
                            value="<?= e($_POST['name'] ?? '') ?>"
                            placeholder="Enter your full name"
                        >

                    </label>


                    <!-- Company Name -->
                    <div id="companyField" hidden>

                        <label class="signup-field">

                            <span>Company name</span>

                            <input
                                type="text"
                                name="company_name"
                                maxlength="150"
                                value="<?= e($_POST['company_name'] ?? '') ?>"
                                placeholder="Enter your company name"
                            >

                        </label>

                    </div>


                    <!-- Email -->
                    <label class="signup-field">

                        <span>Email address</span>

                        <input
                            type="email"
                            name="email"
                            required
                            value="<?= e($_POST['email'] ?? '') ?>"
                            placeholder="Enter your email address"
                            autocomplete="email"
                        >

                    </label>


                    <!-- Password -->
                    <label class="signup-field">

                        <span>Password</span>

                        <input
                            type="password"
                            name="password"
                            minlength="8"
                            required
                            placeholder="Create a password"
                            autocomplete="new-password"
                        >

                        <small>
                            Your password must contain at least 8 characters.
                        </small>

                    </label>


                    <!-- Submit -->
                    <button class="btn full signup-submit-btn" type="submit">
                        Create my account <span>→</span>
                    </button>


                </form>


                <div class="signup-form-footer">

                    <p>
                        Already have an account?

                        <a href="<?= BASE_URL ?>/login.php">
                            Log in
                        </a>
                    </p>

                </div>

            </div>

        </div>

    </section>

</main>


<script>

const roleSelect = document.getElementById('role');

const companyField = document.getElementById('companyField');

const companyInput = document.querySelector(
    'input[name="company_name"]'
);

function toggleCompany() {

    const isCompany = roleSelect.value === 'company';

    companyField.hidden = !isCompany;

    companyInput.required = isCompany;
}

roleSelect.addEventListener('change', toggleCompany);

toggleCompany();

</script>


<?php require __DIR__ . '/includes/footer.php'; ?>
