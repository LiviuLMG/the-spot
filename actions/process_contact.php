<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php'; // AICI ERA PROBLEMA. Acum știe ce e verifyTurnstile()

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
    exit();
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    echo json_encode(["status" => "error", "message" => "Security error. Please reload the page."]);
    exit();
}

// --- SCUT CLOUDFLARE TURNSTILE (ANTI-BOT / BRUTE FORCE) ---
$turnstile_token = $_POST['cf-turnstile-response'] ?? '';
if (!verifyTurnstile($turnstile_token)) {
    echo json_encode(["status" => "error", "message" => "Anti-spam validation failed. Please reload the page and try again."]);
    exit();
}

$name = trim(htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8'));
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$subject = trim(htmlspecialchars($_POST['subject'] ?? '', ENT_QUOTES, 'UTF-8'));
$message = trim(htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8'));

if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "Please enter a valid email address."]);
    exit();
}

// Includem manual fișierele critice din PHPMailer
require __DIR__ . '/../includes/PHPMailer/src/Exception.php';
require __DIR__ . '/../includes/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../includes/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Configurare server SMTP Hostico
    $mail->isSMTP();
    $mail->Host       = 'localhost';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'contact@thespot.ro'; // Adresa exactă creată în cPanel
    $mail->Password = $_ENV['SMTP_PASS'];
    $mail->SMTPSecure = ''; // Folosim SSL
    $mail->Port       = 25;

    // Expeditor și Destinatar
    $mail->setFrom('contact@thespot.ro', 'The Spot');
    $mail->addAddress('contact@thespot.ro'); // Îți trimiți mailul ție
    $mail->addReplyTo($email, $name); // Ca să poți da reply vizitatorului din Webmail

    // Conținut
    $mail->isHTML(false);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = 'Contact Platforma: ' . $subject;
    $mail->Body    = "Ai primit un mesaj nou de pe The Spot.\n\n" .
        "Nume: $name\n" .
        "Email: $email\n" .
        "Subiect: $subject\n\n" .
        "Mesaj:\n$message\n";

    $mail->send();
    echo json_encode(["status" => "success", "message" => "Your message has been sent successfully."]);
} catch (Exception $e) {
    // Returnăm eroare generală fără să afișăm detalii sensibile
    echo json_encode(["status" => "error", "message" => "Error connecting to the mail server."]);
}
?>