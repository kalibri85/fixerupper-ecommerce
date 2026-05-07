<?php
/**
 * Auth — Login / Register
 * @author Lana (Svetlana Muraveckaja-Odincova)
 */
require_once __DIR__ . '/includes/init.php';

// Already logged in → go to checkout or home
if (isCustomer()) {
    $dest = $_SESSION['redirect_after_login'] ?? 'index.php';
    unset($_SESSION['redirect_after_login']);
    redirect($dest);
}

$login_error    = '';
$register_error = '';
$active_tab     = 'login'; // which tab to show on load
$fields         = [];      // repopulate register form on error

// ══════════════════════════════════════════════
// LOGIN
// ══════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    checkCSRF();

    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && $user['role'] === 0 && password_verify($pass, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['customer_id']   = $user['id'];
        $_SESSION['customer_name'] = $user['name'];
        $_SESSION['customer_role'] = 0;

        $dest = $_SESSION['redirect_after_login'] ?? 'index.php';
        unset($_SESSION['redirect_after_login']);
        redirect($dest);
    } else {
        $login_error = "Invalid email or password.";
        $active_tab  = 'login';
    }
}

// ══════════════════════════════════════════════
// REGISTER
// ══════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    checkCSRF();
    $active_tab = 'register';

    $fields = [
        'name'     => trim($_POST['name']     ?? ''),
        'surname'  => trim($_POST['surname']  ?? ''),
        'email'    => trim($_POST['email']    ?? ''),
        'tel'      => trim($_POST['tel']      ?? ''),
        // address fields
        'address'  => trim($_POST['address']  ?? ''),
        'city'     => trim($_POST['city']     ?? ''),
        'postcode' => trim($_POST['postcode'] ?? ''),
        'country'  => trim($_POST['country']  ?? ''),
    ];
    $pass    = $_POST['password']         ?? '';
    $confirm = $_POST['password_confirm'] ?? '';

    // ── Validation ────────────────────────────
    if (empty($fields['name']) || empty($fields['surname']) || empty($fields['email'])) {
        $register_error = "Please fill in all required fields.";
    } elseif (!filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) {
        $register_error = "Please enter a valid email address.";
    } elseif (strlen($pass) < 8) {
        $register_error = "Password must be at least 8 characters.";
    } elseif (!preg_match('/[a-z]/', $pass)) {
        $register_error = "Password must contain at least one lowercase letter.";
    } elseif (!preg_match('/[A-Z]/', $pass)) {
        $register_error = "Password must contain at least one uppercase letter.";
    } elseif (!preg_match('/\d/', $pass)) {
        $register_error = "Password must contain at least one number.";
    } elseif (!preg_match('/[@$!%*?&#^()\-_=+]/', $pass)) {
        $register_error = "Password must contain at least one special character (@$!%*?&#^()-_=+).";
    } elseif ($pass !== $confirm) {
        $register_error = "Passwords do not match.";
    } elseif (empty($fields['address']) || empty($fields['city']) || empty($fields['postcode']) || empty($fields['country'])) {
        $register_error = "Please fill in all required address fields.";
    } else {
        // Check email not already taken
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $fields['email']);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $register_error = "An account with that email already exists.";
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $role = 0;

            // Insert user
            $stmt = $conn->prepare("
                INSERT INTO users (name, surname, email, tel, password, role)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("sssssi",
                $fields['name'],
                $fields['surname'],
                $fields['email'],
                $fields['tel'],
                $hash,
                $role
            );

            if ($stmt->execute()) {
                $user_id = $conn->insert_id;

                // Insert billing address — fullName built from name + surname
                $fullName  = $fields['name'] . ' ' . $fields['surname'];
                $addr_stmt = $conn->prepare("
                    INSERT INTO addresses (userID, fullName, phone, address, city, postcode, country, billing)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 1)
                ");
                $addr_stmt->bind_param("issssss",
                    $user_id,
                    $fullName,
                    $fields['tel'],
                    $fields['address'],
                    $fields['city'],
                    $fields['postcode'],
                    $fields['country']
                );
                $addr_stmt->execute();

                // Auto-login
                session_regenerate_id(true);
                $_SESSION['customer_id']   = $user_id;
                $_SESSION['customer_name'] = $fields['name'];
                $_SESSION['customer_role'] = 0;

                $dest = $_SESSION['redirect_after_login'] ?? 'index.php';
                unset($_SESSION['redirect_after_login']);
                redirect($dest);
            } else {
                $register_error = "Registration failed. Please try again.";
            }
        }
    }
}

include('./includes/header.php');
?>

<section class="py-5">
    <div class="container">

        <nav class="breadcrumbs mb-4">
            <a href="index.php">Home</a> / <span>Account</span>
        </nav>

        <div class="row justify-content-center">
            <div class="col-md-7">

                <!-- TABS -->
                <ul class="nav nav-tabs mb-4" id="authTabs">
                    <li class="nav-item">
                        <button class="nav-link <?= $active_tab === 'login' ? 'active' : '' ?>"
                                data-bs-toggle="tab"
                                data-bs-target="#loginTab">
                            Login
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link <?= $active_tab === 'register' ? 'active' : '' ?>"
                                data-bs-toggle="tab"
                                data-bs-target="#registerTab">
                            Register
                        </button>
                    </li>
                </ul>

                <div class="tab-content">

                    <!-- ══ LOGIN TAB ══ -->
                    <div class="tab-pane fade <?= $active_tab === 'login' ? 'show active' : '' ?>" id="loginTab">
                        <div class="card p-4">
                            <h4 class="mb-4">Returning Customer</h4>

                            <?php if ($login_error): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($login_error) ?></div>
                            <?php endif; ?>

                            <form method="POST" action="" class="needs-validation" novalidate>
                                <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                                <input type="hidden" name="action" value="login">

                                <div class="mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control"
                                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                           required maxlength="255"
                                           autocomplete="email">
                                    <div class="invalid-feedback">Please enter a valid email address.</div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control"
                                           required minlength="8"
                                           autocomplete="current-password">
                                    <div class="invalid-feedback">Password is required.</div>
                                </div>

                                <button type="submit" class="btn-cta w-100">Login</button>
                            </form>
                        </div>
                    </div>

                    <!-- ══ REGISTER TAB ══ -->
                    <div class="tab-pane fade <?= $active_tab === 'register' ? 'show active' : '' ?>" id="registerTab">
                        <div class="card p-4">
                            <h4 class="mb-4">Create Account</h4>

                            <?php if ($register_error): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($register_error) ?></div>
                            <?php endif; ?>

                            <form method="POST" action="" class="needs-validation" novalidate>
                                <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                                <input type="hidden" name="action" value="register">

                                <!-- ── Personal Info ── -->
                                <h6 class="mb-3 text-muted">Personal Information</h6>

                                <div class="row mb-3">
                                    <div class="col">
                                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control"
                                               value="<?= htmlspecialchars($fields['name'] ?? '') ?>"
                                               required minlength="2" maxlength="100"
                                               pattern="[A-Za-zÀ-ÖØ-öø-ÿ\s\-']+"
                                               autocomplete="given-name">
                                        <div class="invalid-feedback">Please enter a valid first name (letters only, min 2 characters).</div>
                                    </div>
                                    <div class="col">
                                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" name="surname" class="form-control"
                                               value="<?= htmlspecialchars($fields['surname'] ?? '') ?>"
                                               required minlength="2" maxlength="100"
                                               pattern="[A-Za-zÀ-ÖØ-öø-ÿ\s\-']+"
                                               autocomplete="family-name">
                                        <div class="invalid-feedback">Please enter a valid last name (letters only, min 2 characters).</div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control"
                                           value="<?= htmlspecialchars($fields['email'] ?? '') ?>"
                                           required maxlength="255"
                                           autocomplete="email">
                                    <div class="invalid-feedback">Please enter a valid email address.</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" name="tel" class="form-control"
                                           value="<?= htmlspecialchars($fields['tel'] ?? '') ?>"
                                           pattern="[\d\s\+\-\(\)]{7,20}"
                                           maxlength="20"
                                           autocomplete="tel">
                                    <div class="invalid-feedback">Please enter a valid phone number.</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" id="reg_password" class="form-control"
                                           required minlength="8" maxlength="255"
                                           pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&amp;#^()\-_=+]).{8,}"
                                           autocomplete="new-password">
                                    <div class="invalid-feedback">Password must meet all requirements below.</div>
                                    <small class="text-muted">Min 8 characters, must include: uppercase, lowercase, number and special character (@$!%*?&#^()-_=+).</small>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirm" id="reg_confirm" class="form-control"
                                           required autocomplete="new-password">
                                    <div class="invalid-feedback">Passwords do not match.</div>
                                </div>

                                <hr class="my-4">

                                <!-- ── Billing Address ── -->
                                <h6 class="mb-3 text-muted">Billing Address</h6>

                                <div class="mb-3">
                                    <label class="form-label">Address <span class="text-danger">*</span></label>
                                    <input type="text" name="address" class="form-control"
                                           value="<?= htmlspecialchars($fields['address'] ?? '') ?>"
                                           required minlength="5" maxlength="255"
                                           autocomplete="street-address">
                                    <div class="invalid-feedback">Please enter your street address.</div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col">
                                        <label class="form-label">City <span class="text-danger">*</span></label>
                                        <input type="text" name="city" class="form-control"
                                               value="<?= htmlspecialchars($fields['city'] ?? '') ?>"
                                               required minlength="2" maxlength="100"
                                               autocomplete="address-level2">
                                        <div class="invalid-feedback">Please enter your city.</div>
                                    </div>
                                    <div class="col">
                                        <label class="form-label">Postcode <span class="text-danger">*</span></label>
                                        <input type="text" name="postcode" class="form-control"
                                               value="<?= htmlspecialchars($fields['postcode'] ?? '') ?>"
                                               required
                                               pattern="[A-Za-z]{1,2}\d{1,2}[A-Za-z]?\s?\d[A-Za-z]{2}"
                                               maxlength="10"
                                               autocomplete="postal-code">
                                        <div class="invalid-feedback">Please enter a valid UK postcode (e.g. LE1 3BK).</div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Country <span class="text-danger">*</span></label>
                                    <input type="text" name="country" class="form-control"
                                           value="<?= htmlspecialchars($fields['country'] ?? 'United Kingdom') ?>"
                                           required minlength="2" maxlength="100"
                                           autocomplete="country-name">
                                    <div class="invalid-feedback">Please enter your country.</div>
                                </div>

                                <button type="submit" class="btn-cta w-100">Create Account</button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</section>

<script>
// Bootstrap validation
(function() {
    'use strict';
    document.querySelectorAll('.needs-validation').forEach(form => {
        form.addEventListener('submit', e => {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    const pass    = document.getElementById('reg_password');
    const confirm = document.getElementById('reg_confirm');

    if (confirm) {
        confirm.addEventListener('input', () => {
            if (confirm.value !== pass.value) {
                confirm.setCustomValidity('Passwords do not match.');
            } else {
                confirm.setCustomValidity('');
            }
        });
        pass.addEventListener('input', () => {
            if (confirm.value && confirm.value !== pass.value) {
                confirm.setCustomValidity('Passwords do not match.');
            } else {
                confirm.setCustomValidity('');
            }
        });
    }
})();
</script>

<?php include('includes/footer.php'); ?>
