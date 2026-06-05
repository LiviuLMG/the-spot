<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL);
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        empty($_POST['csrf_token']) ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $error = "Security error. Please reload the page.";
    } else {
        $turnstile_token = $_POST['cf-turnstile-response'] ?? '';

        if (!verifyTurnstile($turnstile_token)) {
            $error = "Anti-spam validation failed. Please reload the page and try again.";
        } else {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $role = 'user';

            if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
                $error = "All fields are required.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Please enter a valid email address.";
            } elseif (strlen($password) < 6) {
                $error = "Password must contain at least 6 characters.";
            } elseif ($password !== $confirm_password) {
                $error = "The passwords do not match!";
            } else {
                $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $check->bind_param("s", $email);
                $check->execute();

                if ($check->get_result()->num_rows > 0) {
                    $error = "This email is already in use!";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // --- GENERARE TOKEN PENTRU EMAIL VERIFICATION ---
                    $raw_token = bin2hex(random_bytes(32));
                    $token_hash = hash('sha256', $raw_token);
                    $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours')); // Valabil 24h

                    // Inserăm contul + token-ul în baza de date (email_verified rămâne 0 by default)
                    $insert = $conn->prepare("INSERT INTO users (username, email, password, role, email_verification_token, email_verification_expires) VALUES (?, ?, ?, ?, ?, ?)");
                    $insert->bind_param("ssssss", $username, $email, $hashed_password, $role, $token_hash, $expires_at);

                    if ($insert->execute()) {

                        // --- TRIMITERE MAIL DE VERIFICARE ---
                        require __DIR__ . '/includes/PHPMailer/src/Exception.php';
                        require __DIR__ . '/includes/PHPMailer/src/PHPMailer.php';
                        require __DIR__ . '/includes/PHPMailer/src/SMTP.php';

                        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

                        try {
                            $mail->isSMTP();
                            $mail->Host       = 'localhost';
                            $mail->SMTPAuth   = true;
                            $mail->Username   = 'contact@thespot.ro';
                            $mail->Password   = $_ENV['SMTP_PASS'];
                            $mail->SMTPSecure = '';
                            $mail->Port       = 25;

                            $mail->setFrom('contact@thespot.ro', 'The Spot');
                            $mail->addAddress($email);

                            $verify_link = BASE_URL . "verify-email?token=" . urlencode($raw_token);

                            $mail->isHTML(false);
                            $mail->CharSet = 'UTF-8';
                            $mail->Subject = 'Verify your email address - The Spot';
                            $mail->Body    = "Hello $username,\n\n" .
                                "Thank you for joining The Spot!\n" .
                                "Please click the link below to verify your email address and activate your account. This link will expire in 24 hours:\n\n" .
                                $verify_link . "\n\n" .
                                "If you didn't create an account, you can safely ignore this email.\n\n" .
                                "The Spot Team";

                            $mail->send();
                        } catch (Exception $e) {
                            // Suprimăm eroarea: dacă pică mail-ul, contul e totuși creat
                        }

                        $success = "Account created successfully! Please check your email to verify your account.";
                    } else {
                        $error = "Server error. Please try again.";
                    }
                }
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/register.css">

<div class="register-hero">
    <div class="register-overlay"></div>

    <div class="container register-content mt-4">
        <div class="row justify-content-center">
            <div class="col-11 col-md-8 col-lg-5">

                <div class="text-center mb-3">
                    <h1 class="text-white fw-bold" style="margin: 0; font-size: 2.5rem; letter-spacing: -1px;">
                        Join <span class="city-name-gradient">The Spot</span>
                    </h1>
                    <p class="text-secondary fs-5" style="margin: 0; font-size: 1.1rem !important;">
                        Discover. Experience. Connect.
                    </p>
                </div>

                <div class="glass-card p-4 p-md-5">

                    <?php if ($error): ?>
                        <div class="alert alert-danger rounded-3 border-0 py-2 d-flex align-items-center mb-4" style="background-color: rgba(239, 68, 68, 0.15); color: #ef4444;">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success rounded-3 border-0 p-4 text-center" style="background-color: rgba(40, 192, 141, 0.15); color: #28c08d;">
                            <i class="fa-solid fa-envelope-circle-check fa-3x mb-3"></i>
                            <h5 class="fw-bold">Almost there!</h5>
                            <p class="mb-3"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    <?php else: ?>

                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                            <div class="mb-3">
                                <label class="form-label text-secondary small fw-bold text-uppercase" style="letter-spacing: 1px;">Username</label>
                                <input
                                    type="text"
                                    name="username"
                                    class="form-control custom-input"
                                    placeholder="Choose a cool name"
                                    required
                                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-secondary small fw-bold text-uppercase" style="letter-spacing: 1px;">Email Address</label>
                                <input
                                    type="email"
                                    name="email"
                                    class="form-control custom-input"
                                    placeholder="you@example.com"
                                    required
                                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            </div>

                            <div class="row gx-3">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label text-secondary small fw-bold text-uppercase" style="letter-spacing: 1px;">Password</label>
                                    <input
                                        type="password"
                                        name="password"
                                        class="form-control custom-input"
                                        placeholder="Min. 6 chars"
                                        required
                                        minlength="6">
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label class="form-label text-secondary small fw-bold text-uppercase" style="letter-spacing: 1px;">Confirm Password</label>
                                    <input
                                        type="password"
                                        name="confirm_password"
                                        class="form-control custom-input"
                                        placeholder="Repeat password"
                                        required
                                        minlength="6">
                                </div>
                            </div>

                            <div
                                class="cf-turnstile mb-4 d-flex justify-content-center"
                                data-sitekey="<?php echo htmlspecialchars($_ENV['TURNSTILE_SITEKEY'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-theme="dark"
                                data-callback="enableRegisterBtn">
                            </div>

                            <button type="submit" id="btnRegister" class="btn btn-outline-danger text-white w-100 rounded-pill" disabled>
                                CREATE ACCOUNT
                            </button>

                            <script>
                                // Această funcție este apelată automat de Cloudflare doar când verificarea e cu succes
                                function enableRegisterBtn() {
                                    document.getElementById('btnRegister').disabled = false;
                                }
                            </script>
                        </form>

                    <?php endif; ?>

                </div>

                <?php if (!$success): ?>
                    <div class="text-center mt-4 text-secondary">
                        Already have an account?
                        <a href="#" class="text-cyan text-decoration-none fw-bold ms-1" data-bs-toggle="modal" data-bs-target="#loginModal">
                            Log in
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>