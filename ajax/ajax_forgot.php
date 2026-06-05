<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
    exit();
}

if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    echo json_encode(["status" => "error", "message" => "Security error. Please refresh the page."]);
    exit();
}

$turnstile_token = $_POST['cf-turnstile-response'] ?? '';
if (!verifyTurnstile($turnstile_token)) {
    echo json_encode(["status" => "error", "message" => "Security validation failed. Please try again."]);
    exit();
}

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "Please enter a valid email address."]);
    exit();
}

// --- RATE LIMITING DUBLE ---
$ip_limit_ok = checkRateLimit($conn, 'forgot_ip', $ip_address, 3, 5); // Max 3 per 5 min per IP
$email_limit_ok = checkRateLimit($conn, 'forgot_email', $email, 1, 5); // Max 1 per 5 min per Email

// Dacă atinge oricare dintre limite, returnăm mesajul standard fără să trimitem mail.
if (!$ip_limit_ok || !$email_limit_ok) {
    echo json_encode(["status" => "success", "message" => "If the email exists in the system, you will receive an email with the next steps."]);
    exit();
}

// Ne apucăm de treabă
$stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $username = $user['username'];

    $token = bin2hex(random_bytes(32));
    $token_hash = hash('sha256', $token);
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
    $del->bind_param("s", $email);
    $del->execute();

    $ins = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    $ins->bind_param("sss", $email, $token_hash, $expires_at);
    $ins->execute();

    require __DIR__ . '/../includes/PHPMailer/src/Exception.php';
    require __DIR__ . '/../includes/PHPMailer/src/PHPMailer.php';
    require __DIR__ . '/../includes/PHPMailer/src/SMTP.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'localhost';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'contact@thespot.ro';
        $mail->Password   = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = '';
        $mail->Port       = 25;

        $mail->setFrom('contact@thespot.ro', 'The Spot Support');
        $mail->addAddress($email);

        $reset_link = BASE_URL . "reset-password?token=" . urlencode($token);

        $mail->isHTML(false);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Password Reset Request - The Spot';
        $mail->Body    = "Hello $username,\n\n" .
                         "We received a request to reset your password.\n" .
                         "Click the link below to set a new password. The link is valid for 1 hour:\n\n" .
                         $reset_link . "\n\n" .
                         "If you didn't request a password reset, you can safely ignore this email.\n\n" .
                         "The Spot Team";

        $mail->send();
    } catch (Exception $e) {
        // Suprimat
    }
}

echo json_encode(["status" => "success", "message" => "If the email exists in the system, you will receive an email with the next steps."]);
exit();
?>