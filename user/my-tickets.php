<?php
// 1. Inițializăm sesiunea PRIMA
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Conexiunea la DB
require_once __DIR__ . '/../includes/db.php';

// 2. Verificare Securitate 
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- SETĂRI PAGINARE ---
$events_per_page = 6;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $events_per_page;

// 1. Aflăm numărul total de bilete/participări
$count_sql = "SELECT COUNT(event_id) AS total FROM event_participants WHERE user_id = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$total_events = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_events / $events_per_page);

// 2. Extragem evenimentele la care utilizatorul PARTICIPĂ pentru pagina curentă
$sql = "SELECT events.*, categories.name AS category_name, cities.name AS city_name 
        FROM events 
        JOIN event_participants ON events.id = event_participants.event_id
        JOIN categories ON events.category_id = categories.id 
        JOIN cities ON events.city_id = cities.id 
        WHERE event_participants.user_id = ? 
        ORDER BY events.date_time ASC 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $events_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

$current_time = new DateTime();

// 4. Includem header-ul vizual
include BASE_PATH . 'includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/my-events.css">

<div class="container my-events-container pb-5">

    <div class="d-flex justify-content-between align-items-end mb-4 border-bottom border-secondary pb-3" style="border-color: rgba(255,255,255,0.1) !important;">
        <div>
            <h1 class="page-title mb-1">My Tickets</h1>
            <p class="text-secondary mb-0">Events you are attending.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-outline-info rounded-pill px-4 fw-bold d-none d-md-block" style="border-width: 2px;">
            <i class="fa-solid fa-magnifying-glass me-2"></i> Explore Events
        </a>
    </div>

    <div class="row g-4">
        <?php if ($result->num_rows > 0): ?>

            <?php while ($event = $result->fetch_assoc()): ?>
                <?php
                $safe_title = htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8');
                $safe_location = htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8');
                $safe_city = htmlspecialchars($event['city_name'], ENT_QUOTES, 'UTF-8');
                $safe_cat = htmlspecialchars($event['category_name'], ENT_QUOTES, 'UTF-8');

                $event_date = new DateTime($event['date_time']);

                // Imagine preluată global cu BASE_URL
                $img_src = !empty($event['image']) ? BASE_URL . "uploads/" . $event['image'] : BASE_URL . "assets/images/default-event.jpg";

                if ($event_date < $current_time) {
                    $status_class = "status-past";
                    $status_text = "PAST";
                } else {
                    $status_class = "status-upcoming";
                    $status_text = "UPCOMING";
                }
                ?>

                <div class="col-12 col-md-6 col-lg-4">
                    <div class="my-event-card">

                        <div class="my-event-img-wrapper">
                            <div class="blurred-bg" style="background-image: url('<?php echo $img_src; ?>');"></div>

                            <img src="<?php echo $img_src; ?>" alt="<?php echo $safe_title; ?>" class="my-event-img">

                            <span class="status-badge <?php echo $status_class; ?>">
                                <?php echo $status_text; ?>
                            </span>
                            <span class="cat-badge">
                                <?php echo $safe_cat; ?>
                            </span>
                        </div>
                        
                        <div class="my-event-body">
                            <div class="my-event-date">
                                <i class="fa-regular fa-calendar-days me-1"></i> <?php echo strtoupper($event_date->format('d M Y • H:i')); ?>
                            </div>

                            <h3 class="my-event-title"><?php echo $safe_title; ?></h3>

                            <div class="my-event-location">
                                <i class="fa-solid fa-location-dot me-1 text-danger"></i> <?php echo $safe_location; ?>, <?php echo $safe_city; ?>
                            </div>

                            <a href="<?php echo BASE_URL; ?>event.php?id=<?php echo $event['id']; ?>" class="btn-manage w-100">
                                View Event Details <i class="fa-solid fa-arrow-right ms-1" style="font-size: 0.8rem;"></i>
                            </a>
                        </div>

                    </div>
                </div>

            <?php endwhile; ?>

            <?php
            // --- RENDARARE NAVIGARE PAGINARE ---
            if ($total_pages > 1): ?>
                <div class="col-12 mt-4">
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php
                            $prev_disabled = ($current_page <= 1) ? 'disabled' : '';
                            echo '<li class="page-item ' . $prev_disabled . '">
                                    <a class="page-link shadow-none custom-page-link" href="?page=' . ($current_page - 1) . '">
                                        <i class="fa-solid fa-arrow-left"></i>
                                    </a>
                                  </li>';

                            for ($i = 1; $i <= $total_pages; $i++) {
                                $active_class = ($i == $current_page) ? 'active custom-page-active' : '';
                                echo '<li class="page-item ' . $active_class . '">
                                        <a class="page-link shadow-none custom-page-link" href="?page=' . $i . '">' . $i . '</a>
                                      </li>';
                            }

                            $next_disabled = ($current_page >= $total_pages) ? 'disabled' : '';
                            echo '<li class="page-item ' . $next_disabled . '">
                                    <a class="page-link shadow-none custom-page-link" href="?page=' . ($current_page + 1) . '">
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                  </li>';
                            ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>

        <?php else: ?>

            <div class="col-12">
                <div class="empty-state-card text-center">
                    <i class="fa-solid fa-ticket fa-4x mb-3" style="color: rgba(255,255,255,0.1);"></i>
                    <h3 class="text-white fw-bold mb-2">No tickets yet</h3>
                    <p class="text-secondary mb-4">You haven't joined any events. Discover what's happening around you!</p>
                    <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-outline-danger rounded-pill px-5 py-2 fw-bold">
                        Browse Events
                    </a>
                </div>
            </div>

        <?php endif; ?>
    </div>

</div>

<style>
    /* Stiluri de bază pentru paginarea dark mode */
    .pagination .custom-page-link {
        background-color: transparent;
        color: #e2e8f0;
        border: 1px solid rgba(255, 255, 255, 0.1);
        margin: 0 4px;
        border-radius: 8px;
        font-weight: 600;
    }

    .pagination .page-item.custom-page-active .custom-page-link {
        background-color: #00ccff;
        border-color: #00ccff;
        color: #0f172a;
    }

    .pagination .custom-page-link:hover {
        background-color: rgba(0, 204, 255, 0.1);
        color: #00ccff;
        border-color: #00ccff;
    }

    .pagination .page-item.disabled .custom-page-link {
        color: #64748b;
        background-color: rgba(255, 255, 255, 0.02);
        border-color: rgba(255, 255, 255, 0.05);
    }
</style>

<?php include BASE_PATH . 'includes/footer.php'; ?>