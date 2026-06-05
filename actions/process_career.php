<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Încărcăm baza de date pentru a avea acces la $_ENV
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

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

// --- SCUT CLOUDFLARE TURNSTILE ---
$turnstile_token = $_POST['cf-turnstile-response'] ?? '';
if (!verifyTurnstile($turnstile_token)) {
    echo json_encode(["status" => "error", "message" => "Anti-spam validation failed. Please reload the page and try again."]);
    exit();
}

// Extragem și curățăm datele cu fallback pentru a preveni erori fatale
$name = trim(htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8'));
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$position = trim(htmlspecialchars($_POST['position'] ?? '', ENT_QUOTES, 'UTF-8'));
$portfolio = filter_var($_POST['portfolio_url'] ?? '', FILTER_SANITIZE_URL);
$message = trim(htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8'));

if (empty($name) || empty($email) || empty($position) || empty($portfolio) || empty($message)) {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "Please enter a valid email address."]);
    exit();
}

// Includem manual PHPMailer
require __DIR__ . '/../includes/PHPMailer/src/Exception.php';
require __DIR__ . '/../includes/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../includes/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'localhost'; // Conexiune internă
    $mail->SMTPAuth   = true;
    $mail->Username   = 'contact@thespot.ro';
    $mail->Password   = $_ENV['SMTP_PASS'];
    $mail->SMTPSecure = '';
    $mail->Port       = 25;

    $mail->setFrom('contact@thespot.ro', 'The Spot Careers');
    // Dacă pe viitor îți faci hr@thespot.ro, schimbi doar linia de mai jos
    $mail->addAddress('contact@thespot.ro'); 
    $mail->addReplyTo($email, $name); // Permite Reply direct către aplicant

    $mail->isHTML(false);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = "Aplicație nouă: " . $position . " - " . $name;
    $mail->Body    = "Ai primit o aplicație nouă.\n\n" .
                     "Nume: $name\n" .
                     "Email: $email\n" .
                     "Poziție: $position\n" .
                     "Portofoliu: $portfolio\n\n" .
                     "Mesaj/Scrisoare:\n$message\n";

    $mail->send();
    echo json_encode(["status" => "success", "message" => "Application submitted successfully!"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error connecting to the mail server."]);
}
?>