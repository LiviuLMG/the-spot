<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db.php';

$message = '';
$status = 'error';

if (isset($_GET['token'])) {
    $raw_token = $_GET['token'];
    
    // Facem hash la jetonul din link pentru a-l putea compara cu cel din baza de date
    $token_hash = hash('sha256', $raw_token);

    // Căutăm userul cu acest token și ne asigurăm că nu a expirat
    $stmt = $conn->prepare("SELECT id, email_verified FROM users WHERE email_verification_token = ? AND email_verification_expires > NOW() LIMIT 1");
    $stmt->bind_param("s", $token_hash);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ((int)$user['email_verified'] === 1) {
            $status = 'info';
            $message = "This account is already verified. You can log in.";
        } else {
            // Activăm contul, ștergem tokenul și data de expirare
            $update = $conn->prepare("UPDATE users SET email_verified = 1, email_verification_token = NULL, email_verification_expires = NULL WHERE id = ?");
            $update->bind_param("i", $user['id']);
            
            if ($update->execute()) {
                $status = 'success';
                $message = "Email address verified successfully! You can now log in.";
            } else {
                $message = "An error occurred while updating the account. Please try again.";
            }
        }
    } else {
        $message = "Invalid or expired verification link.";
    }
} else {
    $message = "Verification token is missing.";
}

include __DIR__ . '/includes/header.php';
?>

<div class="container mt-5 pt-5 text-center" style="min-height: 60vh;">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="glass-card p-5 mt-5">
                <?php if ($status === 'success'): ?>
                    <i class="fa-solid fa-circle-check text-success fa-4x mb-3"></i>
                    <h2 class="text-white fw-bold">Verification Complete!</h2>
                <?php elseif ($status === 'info'): ?>
                    <i class="fa-solid fa-circle-info text-info fa-4x mb-3"></i>
                    <h2 class="text-white fw-bold">Already Verified</h2>
                <?php else: ?>
                    <i class="fa-solid fa-circle-xmark text-danger fa-4x mb-3"></i>
                    <h2 class="text-white fw-bold">Error</h2>
                <?php endif; ?>
                
                <p class="text-secondary mt-3 fs-5"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
                
                <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" class="btn btn-outline-info rounded-pill fw-bold mt-4 px-5 py-2">
                    Log in now
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>