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

// --- VALIDARE CSRF ---
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
    echo json_encode(["status" => "error", "message" => "Security validation failed. Please reload the page and try again."]);
    exit();
}

// 1. Preluarea și curățarea datelor
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// 2. Validare primară (NU atingem baza de date dacă datele sunt gunoi)
if (empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Please fill in all fields."]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "Please enter a valid email address."]);
    exit();
}

// 3. Creăm cheia unică (Email + IP) pentru Rate Limiting echilibrat
$login_identifier = strtolower($email) . '|' . $ip_address;

// 4. Verificare Rate Limit (Max 5 încercări la 15 minute per Combinație)
if (!checkRateLimit($conn, 'login', $login_identifier, 5, 15)) {
    echo json_encode(["status" => "error", "message" => "Too many failed attempts. Please try again in 15 minutes."]);
    exit();
}

// 5. Căutăm utilizatorul în baza de date
$stmt = $conn->prepare("SELECT id, username, password, role, email_verified FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Dacă nu există emailul, ieșim direct cu mesaj generic
if ($result->num_rows !== 1) {
    echo json_encode(["status" => "error", "message" => "Incorrect email or password!"]);
    exit();
}

$user = $result->fetch_assoc();

// 6. Verificăm parola MAI ÎNTÂI
if (!password_verify($password, $user['password'])) {
    echo json_encode(["status" => "error", "message" => "Incorrect email or password!"]);
    exit();
}

// 7. Dacă parola e corectă, verificăm statusul contului
if ((int)$user['email_verified'] !== 1) {
    echo json_encode(["status" => "error", "message" => "Please verify your email address before logging in."]);
    exit();
}

// --- LOGIN SUCCESSFUL ---
// 8. Curățăm istoricul de penalizări pentru această combinație
clearRateLimit($conn, 'login', $login_identifier);

session_regenerate_id(true);

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];

echo json_encode(["status" => "success", "message" => "Login successful!"]);
exit();
?>