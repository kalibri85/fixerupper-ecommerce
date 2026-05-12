<?php
    /**
     * Admin Login
     * @author Lana (Svetlana Muraveckaja-Odincova)
     */
    require_once __DIR__ . '/includes/init.php';

    if (isAdmin()) {
        redirect('dashboard.php');
    }

    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        checkCSRF();

        $email = trim($_POST['username'] ?? '');
        $pass  = $_POST['password'] ?? '';

        $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && $user['role'] === 1 && password_verify($pass, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            redirect('dashboard.php');
        } else {
            $error = "Invalid email or password.";
        }
    }

    include('./includes/header.php');
?>

<section class="d-flex align-items-center justify-content-center login-page">
    <div class="col-md-4 col-sm-10">
        <div class="card p-4">

            <div class="text-center mb-4">
                <h4 class="fw-bold">
                    Fixer<span class="logo-accent">Upper</span>
                </h4>
                <p class="text-muted mb-0">Admin Panel</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?= csrf() ?>">

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="username" class="form-control"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           required autocomplete="email" autofocus>
                    <div class="invalid-feedback">Please enter your email.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control"
                           required autocomplete="current-password">
                    <div class="invalid-feedback">Please enter your password.</div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4">Login</button>
                </div>
            </form>

        </div>
    </div>
</section>