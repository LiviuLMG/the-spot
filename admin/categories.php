<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// 3. LOGICA PENTRU ACTIONS (Deactivate, Activate, Hard Delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        die("Security error (CSRF).");
    }
    $action = $_GET['action'];
    $cat_id = intval($_GET['id']);

    if ($action === 'deactivate') {
        $stmt = $conn->prepare("UPDATE categories SET status = 'deleted' WHERE id = ?");
        $stmt->bind_param("i", $cat_id);
        $stmt->execute();
    } elseif ($action === 'activate') {
        $stmt = $conn->prepare("UPDATE categories SET status = 'active' WHERE id = ?");
        $stmt->bind_param("i", $cat_id);
        $stmt->execute();
    } elseif ($action === 'hard_delete') {
        // Aici baza de date ar putea da eroare dacă există evenimente legate (foreign key constraint)
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $cat_id);
        @$stmt->execute(); // Punem @ ca să "ascundem" eroarea urâtă dacă ecranele sunt legate
    }

    header("Location: categories.php");
    exit();
}

// 4. Logica de Adăugare Categorie Nouă
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Security error (CSRF).");
    }

    $new_cat = trim($_POST['category_name']);
    if (!empty($new_cat)) {
        $stmt = $conn->prepare("INSERT INTO categories (name, status) VALUES (?, 'active')");
        $stmt->bind_param("s", $new_cat);
        $stmt->execute();
    }
    header("Location: categories.php");
    exit();
}

// 5. Logica de Editare Categorie
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_category_id'])) {
    $edit_id = intval($_POST['edit_category_id']);
    $edit_name = trim($_POST['category_name']);

    if (!empty($edit_name)) {
        $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $edit_name, $edit_id);
        $stmt->execute();
    }
    header("Location: categories.php");
    exit();
}

include BASE_PATH . 'includes/header.php';

// 6. Extragem TOATE categoriile (fără paginare pentru scroll)
$cats_sql = "SELECT categories.id, categories.name, categories.status, COUNT(events.id) AS events_count 
               FROM categories 
               LEFT JOIN events ON categories.id = events.category_id 
               GROUP BY categories.id 
               ORDER BY categories.status ASC, categories.name ASC";
$cats_result = $conn->query($cats_sql);
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">

<div class="container admin-container pb-5">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 border-bottom border-primary pb-3" style="border-color: rgba(59, 130, 246, 0.3) !important;">
        <div class="mb-3 mb-md-0">
            <h1 class="page-title text-primary mb-1"><i class="fa-solid fa-layer-group me-2"></i>Manage Categories</h1>
            <p class="text-secondary mb-0">Add, edit, deactivate or permanently delete categories.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="fa-solid fa-plus me-2"></i> Add Category
            </button>
            <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </div>
    </div>

    <div class="dark-card p-4">
        <div class="table-responsive admin-table-scroller">
            <table class="table table-dark table-hover table-custom align-middle mb-0">
                <thead class="sticky-top" style="background-color: #1e293b; z-index: 1;">
                    <tr>
                        <th scope="col" style="width: 50px;">ID</th>
                        <th scope="col">Category Name</th>
                        <th scope="col">Status</th>
                        <th scope="col">Total Events</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($cats_result->num_rows > 0): ?>
                        <?php while ($cat = $cats_result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-secondary fw-bold">#<?php echo $cat['id']; ?></td>
                                <td class="fw-bold text-white fs-6">
                                    <i class="fa-solid fa-tag text-primary me-2" style="opacity: 0.7;"></i>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </td>
                                <td>
                                    <?php if ($cat['status'] == 'active'): ?>
                                        <span class="badge bg-success text-white px-2 py-1"><i class="fa-solid fa-check me-1"></i>Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger text-white px-2 py-1"><i class="fa-solid fa-xmark me-1"></i>Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($cat['events_count'] > 0): ?>
                                        <span class="badge bg-info text-dark px-2 py-1"><?php echo $cat['events_count']; ?> Events</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary px-2 py-1">No events</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">

                                        <?php if ($cat['status'] == 'active'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#editCategoryModal<?php echo $cat['id']; ?>">
                                                <i class="fa-solid fa-pen"></i> Edit
                                            </button>
                                            <a href="categories.php?action=deactivate&id=<?php echo $cat['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold" onclick="return confirm('Deactivate this category? It will be hidden from users.');">
                                                <i class="fa-solid fa-eye-slash"></i> Deactivate
                                            </a>
                                        <?php else: ?>
                                            <a href="categories.php?action=activate&id=<?php echo $cat['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold">
                                                <i class="fa-solid fa-eye"></i> Activate
                                            </a>

                                            <?php if ($cat['events_count'] == 0): ?>
                                                <a href="categories.php?action=hard_delete&id=<?php echo $cat['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" onclick="return confirm('PERMANENT DELETE? This action cannot be undone.');">
                                                    <i class="fa-solid fa-trash"></i> Delete
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold" disabled title="Cannot delete physically because it contains events.">
                                                    <i class="fa-solid fa-trash"></i> Delete
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade custom-event-modal" id="editCategoryModal<?php echo $cat['id']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-sm">
                                    <div class="modal-content glass-modal-content border-0 rounded-4">
                                        <div class="modal-header glass-modal-header border-0 position-relative">
                                            <h5 class="modal-title w-100 text-center fw-bold text-white">Edit Category</h5>
                                            <button type="button" class="btn-close btn-close-white position-absolute end-0 me-4" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body px-4 py-4">
                                            <form method="POST" action="categories.php">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                                                <input type="hidden" name="edit_category_id" value="<?php echo $cat['id']; ?>">
                                                <div class="mb-4">
                                                    <label class="form-label custom-label">Category Name</label>
                                                    <div class="input-group-custom">
                                                        <input type="text" name="category_name" class="form-control custom-input with-icon" value="<?php echo htmlspecialchars($cat['name']); ?>" required>
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
                            <td colspan="5" class="text-center text-secondary py-4">No categories found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade custom-event-modal" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content glass-modal-content border-0 rounded-4">
            <div class="modal-header glass-modal-header border-0 position-relative">
                <h5 class="modal-title w-100 text-center fw-bold text-white">Add New Category</h5>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-4" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-4">
                <form method="POST" action="categories.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <input type="hidden" name="add_category" value="1">
                    <div class="mb-4">
                        <label class="form-label custom-label">Category Name</label>
                        <div class="input-group-custom">
                            <input type="text" name="category_name" class="form-control custom-input with-icon" placeholder="E.g. Concert, Party..." required autocomplete="off">
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary rounded-pill fw-bold py-2">ADD CATEGORY</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>