<?php
// Folosim BASE_PATH pentru header, care automat pornește și sesiunea și db.php
require_once __DIR__ . '/../includes/db.php';
include BASE_PATH . 'includes/header.php';

// 1. Verificare Securitate STRICTĂ: Doar ADMIN are voie aici
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Îi dăm kick pe pagina principală folosind BASE_URL
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// 2. Extragem statisticile generale pentru Dashboard
$users_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$events_count = $conn->query("SELECT COUNT(*) as count FROM events")->fetch_assoc()['count'];
$cities_count = $conn->query("SELECT COUNT(*) as count FROM cities")->fetch_assoc()['count'];
$categories_count = $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'];

// 3. Extragem ultimele 50 evenimente (crescut de la 5 pentru a permite scroll-ul)
$recent_events_sql = "SELECT events.id, events.title, events.date_time, users.username, cities.name AS city_name 
                      FROM events 
                      JOIN users ON events.user_id = users.id 
                      JOIN cities ON events.city_id = cities.id
                      ORDER BY events.id DESC LIMIT 50";
$recent_events = $conn->query($recent_events_sql);
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">

<div class="container admin-container pb-5">
    
    <div class="mb-4 border-bottom border-danger pb-3" style="border-color: rgba(239, 68, 68, 0.3) !important;">
        <h1 class="page-title text-danger mb-1"><i class="fa-solid fa-shield-halved me-2"></i>Admin Dashboard</h1>
        <p class="text-secondary mb-0">Platform overview and management.</p>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-6 col-lg-3">
            <a href="users.php" class="text-decoration-none">
                <div class="dark-card stat-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="stat-icon-wrapper text-info" style="background-color: rgba(0, 204, 255, 0.1);">
                            <i class="fa-solid fa-users fa-lg"></i>
                        </div>
                        <span class="fs-3 fw-bold text-white"><?php echo $users_count; ?></span>
                    </div>
                    <h6 class="text-secondary fw-bold text-uppercase mb-0" style="font-size: 0.8rem; letter-spacing: 1px;">Total Users</h6>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3">
            <a href="#recent-events-section" class="text-decoration-none">
                <div class="dark-card stat-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="stat-icon-wrapper text-success" style="background-color: rgba(40, 192, 141, 0.1);">
                            <i class="fa-solid fa-calendar-check fa-lg"></i>
                        </div>
                        <span class="fs-3 fw-bold text-white"><?php echo $events_count; ?></span>
                    </div>
                    <h6 class="text-secondary fw-bold text-uppercase mb-0" style="font-size: 0.8rem; letter-spacing: 1px;">Total Events</h6>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3">
            <a href="cities.php" class="text-decoration-none">
                <div class="dark-card stat-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="stat-icon-wrapper text-warning" style="background-color: rgba(245, 158, 11, 0.1);">
                            <i class="fa-solid fa-city fa-lg"></i>
                        </div>
                        <span class="fs-3 fw-bold text-white"><?php echo $cities_count; ?></span>
                    </div>
                    <h6 class="text-secondary fw-bold text-uppercase mb-0" style="font-size: 0.8rem; letter-spacing: 1px;">Active Cities</h6>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3">
            <a href="categories.php" class="text-decoration-none">
                <div class="dark-card stat-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="stat-icon-wrapper text-primary" style="background-color: rgba(59, 130, 246, 0.1);">
                            <i class="fa-solid fa-tags fa-lg"></i>
                        </div>
                        <span class="fs-3 fw-bold text-white"><?php echo $categories_count; ?></span>
                    </div>
                    <h6 class="text-secondary fw-bold text-uppercase mb-0" style="font-size: 0.8rem; letter-spacing: 1px;">Categories</h6>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-8" id="recent-events-section">
            <div class="dark-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-white mb-0">Recent Events Created</h5>
                    <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-sm btn-outline-secondary rounded-pill">View All Platform Events</a>
                </div>
                
                <div class="table-responsive admin-table-scroller">
                    <table class="table table-dark table-hover table-custom align-middle mb-0">
                        <thead class="sticky-top" style="background-color: #1e293b; z-index: 1;">
                            <tr>
                                <th scope="col">Event Name</th>
                                <th scope="col">Organizer</th>
                                <th scope="col">City</th>
                                <th scope="col">Date</th>
                                <th scope="col" class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_events->num_rows > 0): ?>
                                <?php while ($ev = $recent_events->fetch_assoc()): 
                                    $ev_date = new DateTime($ev['date_time']);
                                ?>
                                <tr>
                                    <td class="fw-bold text-white"><?php echo htmlspecialchars($ev['title']); ?></td>
                                    <td><span class="badge bg-secondary text-light"><?php echo htmlspecialchars($ev['username']); ?></span></td>
                                    <td class="text-secondary"><?php echo htmlspecialchars($ev['city_name']); ?></td>
                                    <td class="text-info"><?php echo $ev_date->format('d M Y'); ?></td>
                                    <td class="text-end">
                                        <a href="<?php echo BASE_URL; ?>event.php?id=<?php echo $ev['id']; ?>" class="btn btn-sm btn-outline-info rounded-pill px-3">View</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">No events found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="dark-card p-4">
                <h5 class="fw-bold text-white mb-4">Quick Management</h5>
                
                <div class="d-grid gap-3">
                    <a href="users.php" class="btn btn-admin-action d-flex justify-content-between align-items-center p-3 text-start text-decoration-none">
                        <div>
                            <i class="fa-solid fa-users-gear text-info me-2"></i> Manage Users
                        </div>
                        <i class="fa-solid fa-chevron-right text-secondary" style="font-size: 0.8rem;"></i>
                    </a>
                    
                    <a href="categories.php" class="btn btn-admin-action d-flex justify-content-between align-items-center p-3 text-start text-decoration-none">
                        <div>
                            <i class="fa-solid fa-layer-group text-primary me-2"></i> Manage Categories
                        </div>
                        <i class="fa-solid fa-chevron-right text-secondary" style="font-size: 0.8rem;"></i>
                    </a>

                    <a href="cities.php" class="btn btn-admin-action d-flex justify-content-between align-items-center p-3 text-start text-decoration-none">
                        <div>
                            <i class="fa-solid fa-map-location-dot text-warning me-2"></i> Manage Cities
                        </div>
                        <i class="fa-solid fa-chevron-right text-secondary" style="font-size: 0.8rem;"></i>
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});
</script>

<?php include BASE_PATH . 'includes/footer.php'; ?>