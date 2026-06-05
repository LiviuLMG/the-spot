<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// 3. LOGICA PENTRU ACTIONS
if (isset($_GET['action']) && isset($_GET['id'])) {
    if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        die("Security error (CSRF).");
    }
    $action = $_GET['action'];
    $city_id = intval($_GET['id']);

    if ($action === 'deactivate') {
        $stmt = $conn->prepare("UPDATE cities SET status = 'deleted' WHERE id = ?");
        $stmt->bind_param("i", $city_id);
        $stmt->execute();
    } elseif ($action === 'activate') {
        $stmt = $conn->prepare("UPDATE cities SET status = 'active' WHERE id = ?");
        $stmt->bind_param("i", $city_id);
        $stmt->execute();
    } elseif ($action === 'hard_delete') {
        $stmt = $conn->prepare("DELETE FROM cities WHERE id = ?");
        $stmt->bind_param("i", $city_id);
        @$stmt->execute();
    }

    header("Location: cities.php");
    exit();
}

// 4. Logica de Adăugare Oraș Nou
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_city'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Security error. Unauthorized action.");
    }

    $new_city = trim($_POST['city_name']);
    if (!empty($new_city)) {
        $stmt = $conn->prepare("INSERT INTO cities (name, status) VALUES (?, 'active')");
        $stmt->bind_param("s", $new_city);
        $stmt->execute();
    }
    header("Location: cities.php");
    exit();
}

// 5. Logica de Editare Oraș
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_city_id'])) {
    $edit_id = intval($_POST['edit_city_id']);
    $edit_name = trim($_POST['city_name']);

    if (!empty($edit_name)) {
        $stmt = $conn->prepare("UPDATE cities SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $edit_name, $edit_id);
        $stmt->execute();
    }
    header("Location: cities.php");
    exit();
}

include BASE_PATH . 'includes/header.php';

// 6. Extragem TOATE orașele (fără limită sau paginare ca să funcționeze scroll-ul fluent)
$cities_sql = "SELECT cities.id, cities.name, cities.status, COUNT(events.id) AS events_count 
               FROM cities 
               LEFT JOIN events ON cities.id = events.city_id 
               GROUP BY cities.id 
               ORDER BY cities.status ASC, cities.name ASC";
$cities_result = $conn->query($cities_sql);
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">

<div class="container admin-container pb-5">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 border-bottom border-warning pb-3" style="border-color: rgba(245, 158, 11, 0.3) !important;">
        <div class="mb-3 mb-md-0">
            <h1 class="page-title text-warning mb-1"><i class="fa-solid fa-map-location-dot me-2"></i>Manage Cities</h1>
            <p class="text-secondary mb-0">Add, edit, deactivate or permanently delete cities.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-warning rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#addCityModal">
                <i class="fa-solid fa-plus me-2"></i> Add City
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
                        <th scope="col">City Name</th>
                        <th scope="col">Status</th>
                        <th scope="col">Total Events</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($cities_result->num_rows > 0): ?>
                        <?php while ($city = $cities_result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-secondary fw-bold">#<?php echo $city['id']; ?></td>
                                <td class="fw-bold text-white fs-6">
                                    <i class="fa-solid fa-location-dot text-warning me-2" style="opacity: 0.7;"></i>
                                    <?php echo htmlspecialchars($city['name']); ?>
                                </td>
                                <td>
                                    <?php if ($city['status'] == 'active'): ?>
                                        <span class="badge bg-success text-white px-2 py-1"><i class="fa-solid fa-check me-1"></i>Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger text-white px-2 py-1"><i class="fa-solid fa-xmark me-1"></i>Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($city['events_count'] > 0): ?>
                                        <span class="badge bg-info text-dark px-2 py-1"><?php echo $city['events_count']; ?> Events</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary px-2 py-1">No events</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">

                                        <?php if ($city['status'] == 'active'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#editCityModal<?php echo $city['id']; ?>">
                                                <i class="fa-solid fa-pen"></i> Edit
                                            </button>
                                            <a href="cities.php?action=deactivate&id=<?php echo $city['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold" onclick="return confirm('Deactivate this city? It will be hidden from users.');">
                                                <i class="fa-solid fa-eye-slash"></i> Deactivate
                                            </a>
                                        <?php else: ?>
                                            <a href="cities.php?action=activate&id=<?php echo $city['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold">
                                                <i class="fa-solid fa-eye"></i> Activate
                                            </a>

                                            <?php if ($city['events_count'] == 0): ?>
                                                <a href="cities.php?action=hard_delete&id=<?php echo $city['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" onclick="return confirm('PERMANENT DELETE? This action cannot be undone.');">
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

                            <div class="modal fade custom-event-modal" id="editCityModal<?php echo $city['id']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-sm">
                                    <div class="modal-content glass-modal-content border-0 rounded-4">
                                        <div class="modal-header glass-modal-header border-0 position-relative">
                                            <h5 class="modal-title w-100 text-center fw-bold text-white">Edit City</h5>
                                            <button type="button" class="btn-close btn-close-white position-absolute end-0 me-4" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body px-4 py-4">
                                            <form method="POST" action="cities.php">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                                                <input type="hidden" name="edit_city_id" value="<?php echo $city['id']; ?>">
                                                <div class="mb-4">
                                                    <label class="form-label custom-label">City Name</label>
                                                    <div class="input-group-custom">
                                                        <input type="text" name="city_name" class="form-control custom-input with-icon" value="<?php echo htmlspecialchars($city['name']); ?>" required>
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
                            <td colspan="5" class="text-center text-secondary py-4">No cities found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade custom-event-modal" id="addCityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content glass-modal-content border-0 rounded-4">
            <div class="modal-header glass-modal-header border-0 position-relative">
                <h5 class="modal-title w-100 text-center fw-bold text-white">Add New City</h5>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-4" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-4">
                <form method="POST" action="cities.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <input type="hidden" name="add_city" value="1">
                    <div class="mb-4">
                        <label class="form-label custom-label">City Name</label>
                        <div class="input-group-custom">
                            <input type="text" name="city_name" class="form-control custom-input with-icon" placeholder="E.g. Bucharest, Cluj..." required autocomplete="off">
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold">ADD CITY</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>