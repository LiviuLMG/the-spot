<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/db.php';
require_once 'includes/functions.php'; // Pentru Turnstile

// Dacă e deja logat, nu are ce căuta aici
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL);
    exit();
}

$error = '';
$success = '';
$show_form = false;
$email_to_reset = '';
$token_brut = '';

// 1. Verificăm token-ul din URL (Când omul intră pe pagină)
if (isset($_GET['token'])) {
    $token_brut = $_GET['token'];
    $token_hash = hash('sha256', $token_brut);

    $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $token_hash);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $show_form = true;
        $email_to_reset = $res->fetch_assoc()['email'];
    } else {
        $error = "The reset link is invalid or has expired. Please request a new one.";
    }
} else if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $error = "The reset link is missing.";
}

// 2. Procesăm formularul (Când omul salvează noua parolă)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token_brut = $_POST['token'] ?? '';

    if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Security error. Please try again.";
    } else {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $token_hash = hash('sha256', $token_brut);

        // Verificăm DIN NOU dacă token-ul e valid
        $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->bind_param("s", $token_hash);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows !== 1) {
            $error = "The reset session has expired. Please request a new link.";
        } else if (strlen($password) < 6) {
            $error = "The password must be at least 6 characters long.";
        } else if ($password !== $confirm_password) {
            $error = "The passwords do not match.";
        } else {
            $email_to_reset = $res->fetch_assoc()['email'];
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Actualizăm parola în tabela users
            $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update->bind_param("ss", $hashed_password, $email_to_reset);

            if ($update->execute()) {
                // ȘTERGEM token-ul ca să nu mai fie folosit
                $del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                $del->bind_param("s", $email_to_reset);
                $del->execute();

                $success = "Password successfully changed! You can log in now.";
                $show_form = false;
            } else {
                $error = "Error updating the password. Please try again.";
                $show_form = true;
            }
        }
    }
}

include 'includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/register.css">

<div class="register-hero">
    <div class="register-overlay"></div>

    <div class="container register-content mt-4">
        <div class="row justify-content-center">
            <div class="col-11 col-md-8 col-lg-5">

                <div class="text-center mb-4">
                    <h1 class="text-white fw-bold mb-2" style="font-size: 2rem;">Set a new password</h1>
                    <p class="text-secondary">Secure your The Spot account.</p>
                </div>

                <div class="glass-card p-4 p-md-5">

                    <?php if ($error): ?>
                        <div class="alert alert-danger rounded-3 border-0 py-2 d-flex align-items-center mb-4" style="background-color: rgba(239, 68, 68, 0.15); color: #ef4444;">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i> <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success rounded-3 border-0 p-4 text-center" style="background-color: rgba(40, 192, 141, 0.15); color: #28c08d;">
                            <i class="fa-solid fa-circle-check fa-3x mb-3"></i>
                            <h5 class="fw-bold">Success!</h5>
                            <p class="mb-3"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
                            <button class="btn btn-outline-success rounded-pill fw-bold w-100" data-bs-toggle="modal" data-bs-target="#loginModal">
                                Log in now
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($show_form): ?>
                        <form method="POST" action="<?php echo BASE_URL; ?>reset-password">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token_brut, ENT_QUOTES, 'UTF-8'); ?>">

                            <div class="mb-3">
                                <label class="form-label text-secondary small fw-bold text-uppercase" style="letter-spacing: 1px;">New Password</label>
                                <input type="password" name="password" class="form-control custom-input" placeholder="Min. 6 chars" required minlength="6">
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-secondary small fw-bold text-uppercase" style="letter-spacing: 1px;">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control custom-input" placeholder="Repeat password" required minlength="6">
                            </div>

                            <button type="submit" class="btn btn-outline-info text-white w-100 rounded-pill py-2 fw-bold">
                                SAVE PASSWORD
                            </button>
                        </form>
                    <?php endif; ?>

                </div>

                <div class="text-center mt-4">
                    <a href="<?php echo BASE_URL; ?>" class="text-secondary text-decoration-none small">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back to Homepage
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>