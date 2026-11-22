
<?php
require_once __DIR__ . '/../includes/functions.php';

require_role('admin');

$pageTitle = 'Manage Users';

// Delete user account
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $id = (int)($_POST['user_id'] ?? 0);

    if ($id !== (int)current_user()['id']) {

        $s = $pdo->prepare("
            DELETE FROM users
            WHERE id = ? AND role <> 'admin'
        ");

        $s->execute([$id]);

        set_flash('User account deleted if it was not an admin account.');
    }

    redirect('admin/users.php');
}

// Fetch registered users and their company profiles
$sql = "
    SELECT
        u.id,
        u.full_name,
        u.email,
        u.role,
        u.created_at,
        c.company_name
    FROM users u
    LEFT JOIN companies c ON c.user_id = u.id
    ORDER BY u.created_at DESC
";

$users = $pdo->query($sql)->fetchAll();

// Calculate account statistics
$totalUsers = count($users);
$studentCount = 0;
$companyCount = 0;
$adminCount = 0;

foreach ($users as $user) {
    if ($user['role'] === 'student') {
        $studentCount++;
    } elseif ($user['role'] === 'company') {
        $companyCount++;
    } elseif ($user['role'] === 'admin') {
        $adminCount++;
    }
}

require __DIR__ . '/../includes/header.php';
?>

<div class="admin-users-page">

    <!-- Page Hero -->
    <section class="admin-users-hero">

        <div class="admin-users-hero-content">

            <span class="eyebrow">
                ADMINISTRATION · ACCOUNT MANAGEMENT
            </span>

            <h1>Manage Your <span>Community</span></h1>

            <p>
                Monitor registered accounts, review user information
                and maintain an organized CareerConnect community.
            </p>

            <div class="admin-users-hero-label">
                <span class="admin-users-status-dot"></span>
                User Management Center
            </div>

        </div>

        <div class="admin-users-hero-decoration">

            <div class="admin-users-glow"></div>

            <div class="admin-users-decoration-card">
                <span>♙</span>
                <strong><?= $totalUsers ?></strong>
                <small>Registered Accounts</small>
            </div>

        </div>

    </section>

    <!-- Statistics -->
    <section class="admin-users-stats">

        <article class="admin-users-stat-card">

            <div class="admin-users-stat-icon">♙</div>

            <div>
                <span>Total Accounts</span>
                <strong><?= $totalUsers ?></strong>
                <small>Registered platform users</small>
            </div>

        </article>

        <article class="admin-users-stat-card">

            <div class="admin-users-stat-icon">♧</div>

            <div>
                <span>Students</span>
                <strong><?= $studentCount ?></strong>
                <small>Student accounts</small>
            </div>

        </article>

        <article class="admin-users-stat-card">

            <div class="admin-users-stat-icon">▣</div>

            <div>
                <span>Companies</span>
                <strong><?= $companyCount ?></strong>
                <small>Employer accounts</small>
            </div>

        </article>

        <article class="admin-users-stat-card">

            <div class="admin-users-stat-icon">✧</div>

            <div>
                <span>Administrators</span>
                <strong><?= $adminCount ?></strong>
                <small>Admin accounts</small>
            </div>

        </article>

    </section>

    <!-- User Directory -->
    <section class="admin-users-list-section">

        <div class="admin-users-list-heading">

            <div>
                <span class="eyebrow">REGISTERED MEMBERS</span>
                <h2>User Directory</h2>
                <p>
                    View account information and manage registered users.
                </p>
            </div>

            <a
                href="<?= BASE_URL ?>/admin/dashboard.php"
                class="admin-users-back-link"
            >
                ← Back to Dashboard
            </a>

        </div>

        <?php if (empty($users)): ?>

            <div class="admin-users-empty">

                <div class="admin-users-empty-icon">♙</div>

                <h3>No Registered Users</h3>

                <p>
                    No accounts have been registered on CareerConnect yet.
                </p>

            </div>

        <?php else: ?>

            <div class="admin-users-table-wrap">

                <table class="admin-users-table">

                    <thead>
                        <tr>
                            <th>User Information</th>
                            <th>Email Address</th>
                            <th>Account Type</th>
                            <th>Registration Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($users as $u): ?>

                            <?php
                            $displayName =
                                $u['role'] === 'company' && !empty($u['company_name'])
                                    ? $u['company_name']
                                    : $u['full_name'];

                            $roleClass = match ($u['role']) {
                                'student' => 'student',
                                'company' => 'company',
                                default => 'admin'
                            };
                            ?>

                            <tr>

                                <td>

                                    <div class="admin-user-information">

                                        <div class="admin-user-avatar admin-user-avatar-<?= e($roleClass) ?>">
                                            <?= e(strtoupper(substr($displayName, 0, 1))) ?>
                                        </div>

                                        <div>
                                            <strong>
                                                <?= e($displayName) ?>
                                            </strong>

                                            <small>
                                                Account #<?= (int)$u['id'] ?>
                                            </small>
                                        </div>

                                    </div>

                                </td>

                                <td>
                                    <span class="admin-user-email">
                                        <?= e($u['email']) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="admin-user-role admin-user-role-<?= e($roleClass) ?>">
                                        <?= e(ucfirst($u['role'])) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="admin-user-date">
                                        <?= e(date('d M Y', strtotime($u['created_at']))) ?>
                                    </span>
                                </td>

                                <td>

                                    <?php if ($u['role'] !== 'admin'): ?>

                                        <form
                                            method="post"
                                            class="admin-user-delete-form"
                                            onsubmit="return confirm('Are you sure you want to delete this account? This action cannot be undone.');"
                                        >

                                            <?= csrf_field() ?>

                                            <input
                                                type="hidden"
                                                name="user_id"
                                                value="<?= (int)$u['id'] ?>"
                                            >

                                            <button
                                                class="admin-user-delete-btn"
                                                type="submit"
                                            >
                                                <span>×</span>
                                                Delete
                                            </button>

                                        </form>

                                    <?php else: ?>

                                        <span class="admin-user-protected">
                                            <span>✓</span>
                                            Protected
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </section>

    <!-- Admin Information -->
    <section class="admin-users-note">

        <div class="admin-users-note-icon">✦</div>

        <div>
            <h3>Account Security and Management</h3>

            <p>
                Administrator accounts are protected from deletion
                through this page. Review account details carefully
                before removing any student or company account.
            </p>
        </div>

    </section>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
