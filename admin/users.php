<?php
// 1. Pornim manual sesiunea și conexiunea la DB ÎNAINTE de orice output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Folosim BASE_PATH
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php'; // ACEASTĂ LINIE LIPSEA

// 2. Verificare Securitate: Doar ADMIN are voie
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// 3. Logica de Ștergere Utilizator
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    if ($del_id !== $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id = $del_id");
    }
    header("Location: users.php");
    exit();
}

// 4. Logica de Editare Utilizator
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_user_id'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Security error (CSRF).");
    }

    $edit_id = intval($_POST['edit_user_id']);
    $new_username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');

    $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
    $stmt->bind_param("si", $new_username, $edit_id);
    $stmt->execute();

    // UPLOAD SECURIZAT AVATAR ADMIN
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['avatar']['tmp_name'];
        $mime_type = mime_content_type($tmp_name);
        $allowed_mimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

        if (array_key_exists($mime_type, $allowed_mimes)) {
            $ext = $allowed_mimes[$mime_type];
            $new_filename = uniqid("admin_avatar_", true) . '.' . $ext;
            $target_file = BASE_PATH . "uploads/" . $new_filename;

            if (compressImage($tmp_name, $target_file, 75)) {
                $stmt_img = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                $stmt_img->bind_param("si", $new_filename, $edit_id);
                $stmt_img->execute();
            }
        }
    }
    header("Location: users.php");
    exit();
}

include BASE_PATH . 'includes/header.php';

// Aflăm numărul total de utilizatori pentru a-l afișa în header
$count_sql = "SELECT COUNT(id) AS total FROM users";
$count_result = $conn->query($count_sql);
$total_users = $count_result->fetch_assoc()['total'];

// Extragem utilizatorii (Am pus limita la 100 pentru scroll fluent, poți ajusta)
$users_sql = "SELECT * FROM users ORDER BY id DESC LIMIT 100";
$users_result = $conn->query($users_sql);
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">

<div class="container admin-container pb-5">

    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-danger pb-3" style="border-color: rgba(239, 68, 68, 0.3) !important;">
        <div>
            <h1 class="page-title text-danger mb-1"><i class="fa-solid fa-users-gear me-2"></i>Manage Users</h1>
            <p class="text-secondary mb-0">View, edit, or remove user accounts. (Total: <?php echo $total_users; ?>)</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
            <i class="fa-solid fa-arrow-left me-2"></i> Dashboard
        </a>
    </div>

    <div class="dark-card p-4">

        <div class="table-responsive admin-table-scroller mb-3">
            <table class="table table-dark table-hover table-custom align-middle mb-0">
                <thead class="sticky-top" style="background-color: #1e293b; z-index: 1;">
                    <tr>
                        <th scope="col">User</th>
                        <th scope="col">Email</th>
                        <th scope="col">Role</th>
                        <th scope="col">Registered</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users_result->num_rows > 0): ?>
                        <?php while ($user = $users_result->fetch_assoc()):
                            $avatar_src = !empty($user['avatar']) ? BASE_URL . "uploads/" . $user['avatar'] : BASE_URL . "assets/images/default-avatar.png";
                            $reg_date = new DateTime($user['created_at']);
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo $avatar_src; ?>" alt="Avatar" class="rounded-circle me-3" style="width: 40px; height: 40px; object-fit: cover; border: 1px solid rgba(255,255,255,0.1);">
                                        <span class="fw-bold text-white"><?php echo htmlspecialchars($user['username']); ?></span>
                                    </div>
                                </td>
                                <td class="text-secondary"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if ($user['role'] == 'admin'): ?>
                                        <span class="badge bg-danger text-white px-2 py-1">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary text-white px-2 py-1">User</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-secondary small"><?php echo $reg_date->format('d M Y'); ?></td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $user['id']; ?>">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </button>

                                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                            <a href="users.php?delete_id=<?php echo $user['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" onclick="return confirm('Are you sure you want to delete this user? All their events will be affected.');">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold disabled" title="You cannot delete yourself">
                                                <i class="fa-solid fa-ban"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade custom-event-modal" id="editUserModal<?php echo $user['id']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-sm">
                                    <div class="modal-content glass-modal-content border-0 rounded-4">
                                        <div class="modal-header glass-modal-header border-0 position-relative">
                                            <h5 class="modal-title w-100 text-center fw-bold text-white">Edit User</h5>
                                            <button type="button" class="btn-close btn-close-white position-absolute end-0 me-4" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body px-4 py-4">
                                            <div class="text-center mb-4">
                                                <img src="<?php echo $avatar_src; ?>" class="rounded-circle shadow-sm" style="width: 80px; height: 80px; object-fit: cover; border: 2px solid #00ccff;">
                                            </div>
                                            <form method="POST" action="users.php" enctype="multipart/form-data">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="edit_user_id" value="<?php echo $user['id']; ?>">

                                                <div class="mb-3">
                                                    <label class="form-label custom-label">Username</label>
                                                    <input type="text" name="username" class="form-control custom-input" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label custom-label">Email (Read Only)</label>
                                                    <input type="email" class="form-control custom-input text-secondary" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="background-color: rgba(255,255,255,0.02);">
                                                </div>
                                                <div class="mb-4">
                                                    <label class="form-label custom-label">New Avatar</label>
                                                    <div class="custom-file-upload">
                                                        <input type="file" id="avatar-input-<?php echo $user['id']; ?>" name="avatar" class="d-none" accept="image/*" onchange="document.getElementById('filename-<?php echo $user['id']; ?>').textContent = this.files[0] ? this.files[0].name : 'No file chosen...';">
                                                        <label for="avatar-input-<?php echo $user['id']; ?>" class="form-control custom-input d-flex align-items-center justify-content-between m-0" style="cursor: pointer; padding-right: 0.5rem;">
                                                            <span id="filename-<?php echo $user['id']; ?>" style="color: #64748b; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">No file chosen...</span>
                                                            <span class="btn custom-submit-btn btn-sm py-1 px-3 rounded-pill" id="pop-up-screen-buttons">Browse</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="d-grid">
                                                    <button type="submit" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold">SAVE CHANGES</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">No users registered yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<style>
    /* CSS-ul rămâne aici sau poate fi mutat în admin.css, dar îl las aici pt comoditate */
    .admin-table-scroller {
        max-height: 450px;
        /* Un pic mai mare pentru pagina de useri */
        overflow-y: auto;
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .admin-table-scroller thead th {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        border-bottom: none;
    }

    .admin-table-scroller::-webkit-scrollbar {
        width: 6px;
    }

    .admin-table-scroller::-webkit-scrollbar-track {
        background: transparent;
    }

    .admin-table-scroller::-webkit-scrollbar-thumb {
        background-color: rgba(255, 255, 255, 0.15);
        border-radius: 10px;
    }

    .admin-table-scroller::-webkit-scrollbar-thumb:hover {
        background-color: rgba(255, 255, 255, 0.3);
    }
</style>

<?php include BASE_PATH . 'includes/footer.php'; ?>