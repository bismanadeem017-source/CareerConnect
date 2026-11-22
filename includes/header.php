
<?php

require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? APP_NAME;
$flash = get_flash();

// Current user information
$loggedIn = is_logged_in();
$user = $loggedIn ? current_user() : null;
$role = $user['role'] ?? null;

// Identify current page for page-specific styling
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php', '.php');

?>

<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="theme-color" content="#180A12">

    <title><?= e($pageTitle) ?> | CareerConnect</title>

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/css/style.css"
    >

</head>

<body data-page="<?= e($currentPage) ?>">

    <!-- Scroll Progress -->
    <div class="scroll-progress" aria-hidden="true">
        <span></span>
    </div>


    <!-- Main Navigation -->
    <header class="site-header">

        <div class="container nav-wrap">


            <!-- CareerConnect Logo -->
            <a
                class="brand"
                href="<?= BASE_URL ?>/index.php"
                aria-label="CareerConnect Homepage"
            >

                <span class="brand-icon">C</span>

                <span class="brand-name">
                    Career<span>Connect</span>
                </span>

            </a>


            <!-- Mobile Navigation Toggle -->
            <button
                class="nav-toggle"
                type="button"
                aria-label="Toggle navigation menu"
                aria-expanded="false"
                aria-controls="primary-navigation"
            >
                <span></span>
                <span></span>
                <span></span>
            </button>


            <!-- Navigation Links -->
            <nav class="nav-links" id="primary-navigation">

                <a
                    href="<?= BASE_URL ?>/index.php"
                    class="<?= $currentPage === 'index' ? 'active' : '' ?>"
                >
                    Home
                </a>


                <a
                    href="<?= BASE_URL ?>/jobs.php"
                    class="<?= in_array($currentPage, ['jobs', 'job'], true) ? 'active' : '' ?>"
                >
                    Find Jobs
                </a>


                <?php if ($loggedIn && $role === 'student'): ?>

                    <a href="<?= BASE_URL ?>/student/dashboard.php">
                        My Dashboard
                    </a>

                <?php elseif ($loggedIn && $role === 'company'): ?>

                    <a href="<?= BASE_URL ?>/company/dashboard.php">
                        Company Dashboard
                    </a>

                <?php elseif ($loggedIn && $role === 'admin'): ?>

                    <a href="<?= BASE_URL ?>/admin/dashboard.php">
                        Admin Panel
                    </a>

                <?php endif; ?>


                <?php if ($loggedIn): ?>

                    <!-- Logged-in User -->
                    <div class="nav-account">

                        <span class="nav-user-icon">
                            <?= e(strtoupper(substr($user['name'] ?? 'U', 0, 1))) ?>
                        </span>

                        <span class="nav-user">
                            <?= e($user['name'] ?? 'User') ?>
                        </span>

                    </div>


                    <a
                        class="btn btn-small btn-outline nav-logout"
                        href="<?= BASE_URL ?>/logout.php"
                    >
                        Logout
                    </a>


                <?php else: ?>

                    <!-- Guest Navigation -->
                    <a
                        href="<?= BASE_URL ?>/login.php"
                        class="<?= $currentPage === 'login' ? 'active' : '' ?>"
                    >
                        Login
                    </a>

                    <a
                        class="btn btn-small nav-signup"
                        href="<?= BASE_URL ?>/signup.php"
                    >
                        Sign Up <span>→</span>
                    </a>

                <?php endif; ?>

            </nav>

        </div>

    </header>


    <!-- Main Page Content -->
    <main>

        <div class="container">

            <?php if ($flash): ?>

                <div class="alert <?= e($flash['type']) ?>">
                    <?= e($flash['message']) ?>
                </div>

            <?php endif; ?>

        </div>
<meta name="theme-color" content="#2B0712">