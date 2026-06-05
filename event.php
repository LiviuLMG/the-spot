<?php
require_once __DIR__ . '/includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    include 'includes/header.php';
    echo "<div class='container mt-5'><div class='alert alert-danger shadow-sm border-0'>Invalid event!</div></div>";
    include 'includes/footer.php';
    exit();
}

$event_id = intval($_GET['id']);

$sql = "SELECT events.*, categories.name AS category_name, users.username AS organizer, 
               users.email AS contact_email, users.avatar AS organizer_avatar, cities.name AS city_name
        FROM events 
        JOIN categories ON events.category_id = categories.id 
        JOIN users ON events.user_id = users.id 
        JOIN cities ON events.city_id = cities.id
        WHERE events.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    include 'includes/header.php';
    echo "<div class='container mt-5'><div class='alert alert-warning shadow-sm border-0'>Event not found or has been deleted.</div></div>";
    include 'includes/footer.php';
    exit();
}

$event = $result->fetch_assoc();

$safe_title = htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8');
$safe_desc = nl2br(htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8'));
$safe_location = htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8');
$safe_city = htmlspecialchars($event['city_name'], ENT_QUOTES, 'UTF-8');
$safe_org = htmlspecialchars($event['organizer'], ENT_QUOTES, 'UTF-8');
$safe_email = htmlspecialchars($event['contact_email'], ENT_QUOTES, 'UTF-8');
$safe_cat = htmlspecialchars($event['category_name'], ENT_QUOTES, 'UTF-8');

$date = new DateTime($event['date_time']);

$img_src = !empty($event['image'])
    ? BASE_URL . "uploads/" . $event['image']
    : BASE_URL . "assets/images/default-event.jpg";

$org_avatar_src = !empty($event['organizer_avatar'])
    ? BASE_URL . "uploads/" . $event['organizer_avatar']
    : BASE_URL . "assets/images/default-avatar.png";

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];

function absolute_url($path, $protocol, $domain)
{
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        return $path;
    }

    return $protocol . $domain . '/' . ltrim($path, '/');
}

$plain_description = trim(strip_tags($event['description']));
$plain_description = preg_replace('/\s+/', ' ', $plain_description);

$short_description = mb_substr($plain_description, 0, 150);
if (mb_strlen($plain_description) > 150) {
    $short_description .= "...";
}

$og_title = $event['title'] . " | The Spot";
$og_description = $short_description;
$og_image = absolute_url($img_src, $protocol, $domain);
$og_url = $protocol . $domain . $_SERVER['REQUEST_URI'];

include 'includes/header.php';

$is_owner = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $event['user_id']);
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] == 'admin');

$is_participating = false;
if (isset($_SESSION['user_id'])) {
    $check_part = $conn->prepare("SELECT user_id FROM event_participants WHERE user_id = ? AND event_id = ?");
    $check_part->bind_param("ii", $_SESSION['user_id'], $event_id);
    $check_part->execute();

    if ($check_part->get_result()->num_rows > 0) {
        $is_participating = true;
    }
}

$count_part = $conn->prepare("SELECT COUNT(*) as total FROM event_participants WHERE event_id = ?");
$count_part->bind_param("i", $event_id);
$count_part->execute();
$total_participants = $count_part->get_result()->fetch_assoc()['total'];
?>

<div class="event-hero-section">
    <div class="event-hero-bg-container">
        <div class="event-hero-bg" style="background-image: url('<?php echo htmlspecialchars($img_src, ENT_QUOTES, 'UTF-8'); ?>');"></div>
    </div>

    <div class="event-hero-overlay"></div>

    <div class="container position-relative z-3" style="padding-top: 5rem; padding-bottom: 3rem;">
        <div class="row gx-lg-5 align-items-center">

            <div class="col-lg-4 col-md-5 mb-4 mb-md-0 text-center text-md-start">
                <div class="event-poster-wrapper mx-auto mx-md-0" style="background-image: url('<?php echo htmlspecialchars($img_src, ENT_QUOTES, 'UTF-8'); ?>');">
                    <img src="<?php echo htmlspecialchars($img_src, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo $safe_title; ?>" class="event-poster">
                </div>
            </div>

            <div class="col-lg-8 col-md-7 text-white text-center text-md-start">
                <span class="custom-badge-category mb-3 d-inline-block text-uppercase"><?php echo $safe_cat; ?></span>
                <h1 class="display-4 fw-bold mb-3"><?php echo $safe_title; ?></h1>

                <p class="text-secondary mb-4 fs-6 fw-medium d-flex justify-content-center justify-content-md-start align-items-center">
                    <i class="fa-solid fa-location-dot text-danger me-2" id="red-location"></i>
                    <?php echo $safe_location; ?>, <?php echo $safe_city; ?>
                </p>

                <div class="d-flex justify-content-center justify-content-md-start gap-3 flex-nowrap">
                    <div class="meta-box d-flex align-items-center text-start">
                        <div class="meta-icon-wrapper">
                            <i class="fa-regular fa-calendar meta-icon"></i>
                        </div>
                        <div>
                            <small class="meta-label">DATE</small>
                            <span class="meta-value"><?php echo strtoupper($date->format('d M')); ?></span>
                        </div>
                    </div>

                    <div class="meta-box d-flex align-items-center text-start">
                        <div class="meta-icon-wrapper">
                            <i class="fa-regular fa-clock meta-icon"></i>
                        </div>
                        <div>
                            <small class="meta-label">TIME</small>
                            <span class="meta-value"><?php echo $date->format('H:i'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row gx-lg-5">

        <div class="col-lg-8 mb-4">

            <div class="dark-card p-3 custom-p-md mb-4">
                <h4 class="fw-bold text-white mb-4 border-bottom border-secondary pb-3" style="border-color: rgba(255,255,255,0.08) !important;">About this event</h4>
                <div class="event-description text-secondary">
                    <?php echo $safe_desc; ?>
                </div>
            </div>

            <?php if ($is_owner || $is_admin): ?>
                <div class="dark-card admin-card p-4 text-center text-md-start">
                    <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start mb-3">
                        <i class="fa-solid fa-circle-info text-cyan fs-5 me-md-3 mb-2 mb-md-0 mt-md-1"></i>
                        <div>
                            <h6 class="text-white mb-1 fw-bold">Admin Panel</h6>
                            <small class="text-secondary">You are the organizer of this event. Manage it below.</small>
                        </div>
                    </div>

                    <div class="d-flex flex-nowrap gap-2 mt-3 ms-md-4 ps-md-2 justify-content-center justify-content-md-start">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#editEventModal" class="btn-admin-edit rounded-pill flex-grow-1 flex-md-grow-0 text-center d-flex justify-content-center align-items-center btn-sm fw-bold" style="white-space: nowrap;">
                            <i class="fa-solid fa-pen me-2"></i>Edit Event
                        </a>

                        <form method="POST" action="<?php echo BASE_URL; ?>actions/delete-event.php" class="flex-grow-1 flex-md-grow-0 m-0 p-0 d-flex" onsubmit="return confirm('Are you sure you want to delete this event? This action cannot be undone.');">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['id'], ENT_QUOTES, 'UTF-8'); ?>">

                            <button type="submit" class="btn-admin-delete rounded-pill w-100 text-center d-flex justify-content-center align-items-center btn-sm fw-bold" style="white-space: nowrap; cursor: pointer;">
                                <i class="fa-solid fa-trash me-2"></i>Delete Event
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <div class="col-lg-4">
            <div class="position-sticky" style="top: 100px;">

                <div class="dark-card p-4 mb-4">
                    <div class="card-label">ENTRY PRICE</div>
                    <div class="mb-4 d-flex align-items-baseline">
                        <?php if ($event['price'] == 0): ?>
                            <span class="display-5 fw-bold text-white mb-0">Free</span>
                        <?php else: ?>
                            <span class="display-5 fw-bold text-white mb-0"><?php echo htmlspecialchars($event['price'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="text-secondary fw-bold fs-6 ms-2">RON</span>
                        <?php endif; ?>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form id="joinEventForm" action="<?php echo BASE_URL; ?>actions/join-event.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event_id, ENT_QUOTES, 'UTF-8'); ?>">

                            <?php if ($is_participating): ?>
                                <button type="submit" id="joinEventBtn" class="btn btn-outline-danger w-100 rounded-pill fw-bold py-2 mb-2" style="border-width: 2px;">
                                    <i class="fa-solid fa-xmark me-2"></i> Leave Event
                                </button>
                            <?php else: ?>
                                <button type="submit" id="joinEventBtn" class="btn btn-dark-map w-100 rounded-pill py-2 text-white fw-medium d-flex justify-content-center align-items-center mb-2">
                                    Join Event
                                </button>
                            <?php endif; ?>
                        </form>
                    <?php else: ?>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" class="btn btn-dark-map w-100 rounded-pill py-2 text-white fw-medium d-flex justify-content-center align-items-center mb-2">Login to Join</a>
                    <?php endif; ?>

                    <div class="text-center mt-2">
                        <small class="text-secondary fw-bold">
                            <i class="fa-solid fa-users text-cyan me-1"></i>
                            <span id="participantCount"><?php echo htmlspecialchars($total_participants, ENT_QUOTES, 'UTF-8'); ?></span> attending
                        </small>
                    </div>
                </div>

                <div class="dark-card p-4 mb-4">
                    <div class="card-label">ORGANIZED BY</div>
                    <div class="d-flex align-items-center">
                        <img src="<?php echo htmlspecialchars($org_avatar_src, ENT_QUOTES, 'UTF-8'); ?>" alt="Organizer Avatar" class="organizer-avatar-img me-3">

                        <div>
                            <h6 class="text-white fw-bold mb-1"><?php echo $safe_org; ?></h6>
                            <a href="mailto:<?php echo $safe_email; ?>" class="text-cyan text-decoration-none small fw-medium">Contact</a>
                        </div>
                    </div>
                </div>

                <div class="dark-card p-4">
                    <div class="card-label">LOCATION</div>
                    <p class="text-secondary small mb-4 lh-base"><?php echo $safe_location; ?>, <?php echo $safe_city; ?></p>

                    <a href="https://maps.google.com/?q=<?php echo urlencode($event['location'] . ', ' . $event['city_name']); ?>" target="_blank" class="btn btn-dark-map w-100 rounded-pill py-2 text-white fw-medium d-flex justify-content-center align-items-center">
                        <i class="fa-solid fa-location-dot me-2 text-cyan"></i> Open in Google Maps
                        <i class="fa-solid fa-arrow-up-right-from-square ms-2" style="font-size: 0.75rem;"></i>
                    </a>
                </div>

            </div>
        </div>

    </div>
</div>

<?php if ($is_owner || $is_admin): ?>
    <div class="modal fade custom-event-modal" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-modal-content border-0 rounded-4">

                <div class="modal-header glass-modal-header border-0 position-relative">
                    <h5 class="modal-title w-100 text-center fw-bold text-white" id="editEventModalLabel">Edit Event</h5>
                    <button type="button" class="btn-close btn-close-white position-absolute end-0 me-4" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 py-3">
                    <form id="editEventForm" method="POST" action="<?php echo BASE_URL; ?>actions/process_edit_event.php" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event_id, ENT_QUOTES, 'UTF-8'); ?>">

                        <div class="mb-3">
                            <label class="form-label custom-label">Event Title</label>
                            <input type="text" name="title" class="form-control custom-input" value="<?php echo $safe_title; ?>" required>
                        </div>

                        <div class="row gx-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label custom-label">City</label>
                                <input type="text" id="edit-city-input" class="form-control custom-input" name="city_name" value="<?php echo $safe_city; ?>" required autocomplete="off">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label custom-label">Category</label>

                                <input type="hidden" name="category_name" id="edit-category-hidden-input" value="<?php echo $safe_cat; ?>" required>

                                <div class="dropdown">
                                    <button class="form-control custom-input d-flex justify-content-between align-items-center w-100 text-start shadow-none" type="button" id="editCategoryDropdownBtn" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" style="font-weight: 400; cursor: pointer;">
                                        <span id="edit-category-selected-text" style="color: #ffffff;"><?php echo $safe_cat; ?></span>
                                        <i class="fa-solid fa-chevron-down category-arrow" style="font-size: 0.8rem; color: #64748b; transition: transform 0.3s ease;"></i>
                                    </button>

                                    <ul class="dropdown-menu w-100 glass-dropdown-menu border-0 shadow-lg mt-2" aria-labelledby="editCategoryDropdownBtn">
                                        <?php
                                        $cat_sql = "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC";
                                        $cat_res = $conn->query($cat_sql);

                                        if ($cat_res) {
                                            while ($cat = $cat_res->fetch_assoc()) {
                                                $cat_name = htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8');
                                                echo '<li><a class="dropdown-item edit-category-item" href="#" data-value="' . $cat_name . '">' . $cat_name . '</a></li>';
                                            }
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label custom-label">Exact Location</label>
                            <input type="text" id="edit-location-input" name="location" class="form-control custom-input" value="<?php echo $safe_location; ?>" required autocomplete="off">
                        </div>

                        <div class="row gx-3">
                            <div class="col-md-4 mb-3">
                                <label class="form-label custom-label">Date</label>
                                <input type="text" id="edit-event-date" name="event_date" class="form-control custom-input" value="<?php echo $date->format('Y-m-d'); ?>" required autocomplete="off" readonly style="cursor: pointer; background-color: rgba(255, 255, 255, 0.05);">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label custom-label">Time</label>
                                <input type="time" name="event_time" class="form-control custom-input" value="<?php echo $date->format('H:i'); ?>" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label custom-label">Price (RON)</label>
                                <input type="number" step="0.01" name="price" class="form-control custom-input" value="<?php echo htmlspecialchars($event['price'], ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label custom-label">Image (Leave empty to keep current)</label>
                            <div class="custom-file-upload">
                                <input type="file" id="edit-event-image" name="image" class="d-none" accept="image/*">
                                <label for="edit-event-image" class="form-control custom-input d-flex align-items-center justify-content-between m-0" style="cursor: pointer; padding-right: 0.5rem;">
                                    <span id="edit-file-name" style="color: #64748b;">No new file chosen...</span>
                                    <span class="btn custom-submit-btn btn-sm py-1 px-3 rounded-pill" id="pop-up-screen-buttons" style="font-size: 0.85rem;">Browse</span>
                                </label>
                            </div>

                            <div id="edit-image-preview-container" class="mt-3 text-center">
                                <img id="edit-image-preview" src="<?php echo htmlspecialchars($img_src, ENT_QUOTES, 'UTF-8'); ?>" alt="Current Poster" class="img-fluid rounded-3">
                                <small class="d-block text-muted mt-1">Current Image</small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label custom-label">Description</label>
                            <textarea name="description" class="form-control custom-input" rows="3" required><?php echo htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <div class="d-grid mb-2">
                            <button type="submit" class="btn custom-submit-btn rounded-pill fw-bold" id="pop-up-screen-buttons">SAVE CHANGES</button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>