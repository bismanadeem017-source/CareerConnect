
<?php

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Login to CareerConnect';

$error = '';

// Redirect already logged-in users
if (is_logged_in()) {

    $role = current_user()['role'];

    if ($role === 'admin') {
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
        exit;
    }

    if ($role === 'company') {
        header('Location: ' . BASE_URL . '/company/dashboard.php');
        exit;
    }

    if ($role === 'student') {
        header('Location: ' . BASE_URL . '/student/dashboard.php');
        exit;
    }
}


// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verify CSRF token
    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {

        $error = 'Please enter both email and password.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Please enter a valid email address.';

    } else {

        // Fetch user and company information
        $stmt = $pdo->prepare("
            SELECT
                u.id,
                u.full_name AS name,
                u.email,
                u.password_hash,
                u.role,
                u.phone,
                c.company_name
            FROM users u
            LEFT JOIN companies c
                ON c.user_id = u.id
            WHERE u.email = ?
            LIMIT 1
        ");

        $stmt->execute([$email]);

        $user = $stmt->fetch();

        // Verify user credentials
        if ($user && password_verify($password, $user['password_hash'])) {

            // Regenerate session ID for security
            session_regenerate_id(true);

            // Remove password hash before storing user session
            unset($user['password_hash']);

            // Store logged-in user information
            $_SESSION['user'] = $user;

            // Redirect according to user role
            if ($user['role'] === 'admin') {

                header('Location: ' . BASE_URL . '/admin/dashboard.php');
                exit;

            } elseif ($user['role'] === 'company') {

                header('Location: ' . BASE_URL . '/company/dashboard.php');
                exit;

            } elseif ($user['role'] === 'student') {

                header('Location: ' . BASE_URL . '/student/dashboard.php');
                exit;

            } else {

                unset($_SESSION['user']);
                $error = 'Your account role is not recognized.';
            }

        } else {

            $error = 'Incorrect email or password. Please try again.';

        }
    }
}

require __DIR__ . '/includes/header.php';

?>

<main class="auth-page login-page">

    <div class="auth-background-effects">
        <div class="auth-glow auth-glow-one"></div>
        <div class="auth-glow auth-glow-two"></div>
    </div>

    <section class="auth-container">

        <!-- Login Information -->
        <div class="auth-intro">

            <span class="eyebrow">WELCOME BACK</span>

            <h1>
                Your next opportunity
                <span>starts with a login.</span>
            </h1>

            <p>
                Sign in to CareerConnect and continue exploring
                opportunities, connecting with employers,
                and building your professional future.
            </p>

            <div class="auth-benefits">

                <div class="auth-benefit">
                    <span>✦</span>
                    <div>
                        <strong>Discover opportunities</strong>
                        <small>Find jobs and internships that match your goals.</small>
                    </div>
                </div>

                <div class="auth-benefit">
                    <span>✓</span>
                    <div>
                        <strong>Track your applications</strong>
                        <small>Stay updated on your application progress.</small>
                    </div>
                </div>

                <div class="auth-benefit">
                    <span>◈</span>
                    <div>
                        <strong>Build your future</strong>
                        <small>Take the next step in your career journey.</small>
                    </div>
                </div>

            </div>

            <div class="auth-brand">
                <span class="auth-brand-icon">C</span>
                <span>CareerConnect</span>
            </div>

        </div>


        <!-- Login Form -->
        <div class="auth-form-wrapper">

            <div class="auth-form-card">

                <div class="auth-form-heading">

                    <span class="eyebrow">YOUR ACCOUNT</span>

                    <h2>Welcome back!</h2>

                    <p>Enter your credentials to access your account.</p>

                </div>


                <?php if ($error): ?>

                    <div class="alert error">
                        <?= e($error) ?>
                    </div>

                <?php endif; ?>


                <form method="post" action="" class="auth-form">

                    <?= csrf_field() ?>

                    <label class="auth-field">

                        <span>Email address</span>

                        <input
                            type="email"
                            name="email"
                            placeholder="Enter your email"
                            value="<?= e($_POST['email'] ?? '') ?>"
                            autocomplete="email"
                            required
                        >

                    </label>


                    <label class="auth-field">

                        <span>Password</span>

                        <input
                            type="password"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >

                    </label>


                    <button type="submit" class="btn full auth-submit-btn">
                        Sign in to your account <span>→</span>
                    </button>

                </form>


                <div class="auth-form-footer">

                    <p>
                        Don't have an account?
                        <a href="<?= BASE_URL ?>/signup.php">
                            Create an account
                        </a>
                    </p>

                </div>

            </div>

        </div>

    </section>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
