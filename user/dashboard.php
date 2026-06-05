<?php
// 1. Inițializăm sesiunea și baza de date PRIMELE
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Folosim BASE_PATH (definit teoretic în db.php, dar ca să fim siguri, chemăm db.php prin cale relativă la acest folder)
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// 2. Verificare Securitate
if (!isset($_SESSION['user_id'])) {
    // Îl trimitem la index (pagina principală) dacă nu e logat, forțând login-ul
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$msg_type = ''; // 'success' sau 'error'

// --- 3. PROCESARE FORMULARE ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Security error (invalid CSRF token). Please reload the page.");
    }

    // A. UPDATE PROFIL (Username & Email)
    if (isset($_POST['update_profile'])) {
        $new_username = trim($_POST['username']);
        $new_email = trim($_POST['email']);

        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_email->bind_param("si", $new_email, $user_id);
        $check_email->execute();
        if ($check_email->get_result()->num_rows > 0) {
            $message = "Acest email este deja folosit de alt cont.";
            $msg_type = "error";
        } else {
            $update_sql = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $update_sql->bind_param("ssi", $new_username, $new_email, $user_id);
            if ($update_sql->execute()) {
                $_SESSION['username'] = $new_username;
                $message = "Profilul a fost actualizat cu succes!";
                $msg_type = "success";
            }
        }
    }

    // B. UPDATE PAROLĂ
    if (isset($_POST['update_password'])) {
        $old_pass = $_POST['old_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        $get_pass = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $get_pass->bind_param("i", $user_id);
        $get_pass->execute();
        $user_data = $get_pass->get_result()->fetch_assoc();

        if (!password_verify($old_pass, $user_data['password'])) {
            $message = "The current password is incorrect!";
            $msg_type = "error";
        } elseif ($new_pass !== $confirm_pass) {
            $message = "The new passwords do not match!";
            $msg_type = "error";
        } elseif (strlen($new_pass) < 6) {
            $message = "The new password must be at least 6 characters long.";
            $msg_type = "error";
        } else {
            $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $update_pass = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_pass->bind_param("si", $hashed_pass, $user_id);
            if ($update_pass->execute()) {
                $message = "The password has been updated successfully!";
                $msg_type = "success";
            }
        }
    }

    // C. UPDATE AVATAR SECURIZAT
    if (isset($_POST['update_avatar']) && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['avatar']['tmp_name'];
        $file_size = $_FILES['avatar']['size'];

        // Verificare mărime (Max 2MB)
        if ($file_size > 2 * 1024 * 1024) {
            $message = "File too large (Max 2MB).";
            $msg_type = "error";
        } else {
            // VERIFICARE STRICTĂ MIME TYPE
            $mime_type = mime_content_type($tmp_name);
            $allowed_mimes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];

            if (array_key_exists($mime_type, $allowed_mimes)) {
                $ext = $allowed_mimes[$mime_type];
                $avatar_name = uniqid("avatar_", true) . "." . $ext;
                $target_file = BASE_PATH . "uploads/" . $avatar_name;

                if (compressImage($tmp_name, $target_file, 75)) {                    // Căutăm avatarul vechi ca să-l ștergem
                    $get_old = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
                    $get_old->bind_param("i", $user_id);
                    $get_old->execute();
                    $old_avatar = $get_old->get_result()->fetch_assoc()['avatar'];

                    if (!empty($old_avatar) && file_exists(BASE_PATH . "uploads/" . $old_avatar)) {
                        unlink(BASE_PATH . "uploads/" . $old_avatar);
                    }

                    // Salvăm în DB noul avatar
                    $update_avatar = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                    $update_avatar->bind_param("si", $avatar_name, $user_id);
                    $update_avatar->execute();

                    $message = "The profile picture has been updated successfully!";
                    $msg_type = "success";
                } else {
                    $message = "Error moving the uploaded file.";
                    $msg_type = "error";
                }
            } else {
                $message = "Security error: Invalid format. Only real JPG, PNG, and WEBP are allowed.";
                $msg_type = "error";
            }
        }
    } elseif (isset($_POST['update_avatar']) && isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $message = "An error occurred during upload.";
        $msg_type = "error";
    }
}

// --- 4. INCLUDEM HEADER-UL folosind BASE_PATH ---
include BASE_PATH . 'includes/header.php';

// --- 5. EXTRAGERE DATE CURENTE ---
$stmt = $conn->prepare("SELECT username, email, avatar, role, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Folosim BASE_URL pentru ca HTML-ul să găsească imaginea pe net
$avatar_src = !empty($user['avatar']) ? BASE_URL . "uploads/" . $user['avatar'] : BASE_URL . "assets/images/default-avatar.png";
$member_since = new DateTime($user['created_at']);
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/dashboard.css">

<div class="container dashboard-container pb-5">

    <div class="mb-4 border-bottom border-secondary pb-3 text-center text-md-start" style="border-color: rgba(255,255,255,0.1) !important;">
        <h1 class="page-title mb-1">My Account</h1>
        <p class="text-secondary mb-0">Manage your personal information and security settings.</p>
    </div>

    <?php if ($message): ?>
        <div class="alert <?php echo $msg_type == 'success' ? 'alert-success-custom' : 'alert-error-custom'; ?> alert-dismissible fade show mb-4" role="alert">
            <?php if ($msg_type == 'success'): ?>
                <i class="fa-solid fa-circle-check me-2"></i>
            <?php else: ?>
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <?php endif; ?>
            <strong><?php echo $message; ?></strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">

        <div class="col-lg-4">
            <div class="dark-card p-4 text-center">
                <div class="position-relative d-inline-block mb-3">
                    <img src="<?php echo $avatar_src; ?>" alt="Avatar" class="profile-avatar shadow-lg">

                    <form method="POST" action="" enctype="multipart/form-data" id="avatarForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                        <input type="file" name="avatar" id="avatarInput" class="d-none" accept="image/png, image/jpeg, image/webp" onchange="document.getElementById('avatarForm').submit();">
                        <label for="avatarInput" class="avatar-edit-btn" title="Change Profile Picture">
                            <i class="fa-solid fa-camera"></i>
                        </label>
                        <input type="hidden" name="update_avatar" value="1">
                    </form>
                </div>

                <h4 class="text-white fw-bold mb-1"><?php echo htmlspecialchars($user['username']); ?></h4>
                <p class="text-info small fw-bold text-uppercase tracking-wide mb-3"><?php echo $user['role']; ?></p>

                <hr class="border-secondary mb-3" style="opacity: 0.2;">

                <div class="text-start text-secondary small d-flex flex-column align-items-center align-items-md-start">
                    <div class="mb-2"><i class="fa-regular fa-calendar me-2"></i> Member since: <strong class="text-white"><?php echo $member_since->format('M Y'); ?></strong></div>
                    <div><i class="fa-solid fa-envelope me-2"></i> Email: <strong class="text-white"><?php echo htmlspecialchars($user['email']); ?></strong></div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="dark-card p-4 p-md-5">

                <ul class="nav nav-tabs custom-tabs mb-4 justify-content-center justify-content-md-start" id="accountTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">General Info</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">Security</button>
                    </li>
                </ul>

                <div class="tab-content" id="accountTabsContent">

                    <div class="tab-pane fade show active" id="profile" role="tabpanel">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                            <input type="hidden" name="update_profile" value="1">

                            <div class="row gx-3">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label custom-label text-left text-md-start d-block">Username</label>
                                    <div class="input-group-custom">
                                        <i class="fa-solid fa-user input-icon"></i>
                                        <input type="text" name="username" class="form-control custom-input with-icon" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label class="form-label custom-label text-left text-md-start d-block">Email Address</label>
                                    <div class="input-group-custom">
                                        <i class="fa-solid fa-at input-icon"></i>
                                        <input type="email" name="email" class="form-control custom-input with-icon" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-outline-danger rounded-pill px-5 fw-bold custom-btn-responsive">Save Changes</button>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="security" role="tabpanel">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                            <input type="hidden" name="update_password" value="1">

                            <div class="mb-4">
                                <label class="form-label custom-label text-left text-md-start d-block">Current Password</label>
                                <div class="input-group-custom">
                                    <i class="fa-solid fa-lock input-icon"></i>
                                    <input type="password" name="old_password" class="form-control custom-input with-icon" placeholder="Enter current password" required>
                                </div>
                            </div>

                            <div class="row gx-3">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label custom-label text-left text-md-start d-block">New Password</label>
                                    <div class="input-group-custom">
                                        <i class="fa-solid fa-key input-icon"></i>
                                        <input type="password" name="new_password" class="form-control custom-input with-icon" placeholder="Minimum 6 characters" required minlength="6">
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label class="form-label custom-label text-left text-md-start d-block">Confirm New Password</label>
                                    <div class="input-group-custom">
                                        <i class="fa-solid fa-check-double input-icon"></i>
                                        <input type="password" name="confirm_password" class="form-control custom-input with-icon" placeholder="Repeat new password" required minlength="6">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-outline-danger rounded-pill px-5 fw-bold custom-btn-responsive" style="border-width: 2px;">Update Password</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>