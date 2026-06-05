<?php
// 1. Includem Header-ul
include 'includes/header.php';

// 2. Stabilim orașul curent din sesiune
$city_id = isset($_SESSION['city_id']) ? $_SESSION['city_id'] : 'all';

// Luăm numele orașului pentru titlu
if ($city_id === 'all') {
    $city_name = "All Cities";
} else {
    $safe_city_id = intval($city_id);
    $city_sql = "SELECT name FROM cities WHERE id = $safe_city_id";
    $city_res = $conn->query($city_sql);
    $city_name = ($city_res && $city_res->num_rows > 0) ? $city_res->fetch_assoc()['name'] : "Unknown City";
}
?>

<div class="container-fluid p-0">
    <div class="hero-section text-white text-center">
        <div class="container">
            <h1 class="display-4 fw-bold">Discover the vibe of <span class="city-name-gradient"><?php echo $city_name; ?></span>.</h1>
            <p class="lead mb-4">Concerts, parties, and cultural experiences happening right now.</p>
            <a href="#events-section" class="btn btn-success btn-lg" id="explore-events-btn">
                EXPLORE EVENTS <i class="fa-solid fa-chevron-down ms-2" id="arrow"></i>
            </a>
        </div>
    </div>
</div>

<div class="bg-white w-100 pt-4 pb-5" style="min-height: 50vh;">
    <div class="container mb-5" id="events-section">

        <div class="mb-4 border-bottom pb-4">

            <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-center mb-4 gap-3">

                <h2 class="h2-events fw-bold text-black m-0 text-center text-md-start">Browse Events</h2>

                <?php
                $min_p_val = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
                $max_p_val = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 500;
                ?>
                <div class="price-range-container mx-auto mx-md-0">
                    <div class="d-flex justify-content-center mb-1">
                        <span class="text-secondary small fw-bold">Price Range</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 position-relative">
                        <span id="minPriceVal" class="price-val-text"><?php echo $min_p_val; ?> RON</span>
                        <span id="maxPriceVal" class="price-val-text"><?php echo ($max_p_val >= 500) ? 'Any' : $max_p_val . ' RON'; ?></span>
                    </div>
                    <div class="range-slider">
                        <div class="range-track" id="rangeTrack"></div>
                        <input type="range" id="minPrice" min="0" max="500" value="<?php echo $min_p_val; ?>" step="10">
                        <input type="range" id="maxPrice" min="0" max="500" value="<?php echo $max_p_val; ?>" step="10">
                    </div>
                </div>
            </div>

            <div class="category-scroller justify-content-md-center">
                <?php
                $active_cat = isset($_GET['cat']) ? $_GET['cat'] : 'all';

                // Păstrăm prețurile în URL pentru butoanele de categorii
                $price_params = "";
                if (isset($_GET['min_price']) && isset($_GET['max_price'])) {
                    $price_params = "&min_price=" . $min_p_val . "&max_price=" . $max_p_val;
                }
                ?>

                <a href="?cat=all<?php echo $price_params; ?>#events-section" class="btn-filter <?php echo ($active_cat == 'all') ? 'active' : ''; ?>">
                    All
                </a>

                <?php
                $sql_cats = "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC";
                $res_cats = $conn->query($sql_cats);

                if ($res_cats) {
                    while ($cat = $res_cats->fetch_assoc()) {
                        $is_active = ($active_cat == $cat['id']) ? 'active' : '';
                        echo '<a href="?cat=' . $cat['id'] . $price_params . '#events-section" class="btn-filter ' . $is_active . '">
                                ' . htmlspecialchars($cat['name']) . '
                              </a>';
                    }
                }
                ?>
            </div>

        </div>

        <div class="row">
            <?php
            // --- SETĂRI PAGINARE ---
            $events_per_page = 6; // Numărul de carduri pe pagină
            $current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
            if ($current_page < 1) $current_page = 1;

            $offset = ($current_page - 1) * $events_per_page;

            // Construim baza filtrelor (partea de WHERE care se repetă)
            // Construim baza filtrelor (partea de WHERE care se repetă)
            $where_clause = " events.date_time >= NOW()";

            if ($city_id !== 'all') {
                $where_clause .= " AND events.city_id = " . intval($city_id);
            }
            if ($active_cat != 'all' && is_numeric($active_cat)) {
                $where_clause .= " AND events.category_id = " . intval($active_cat);
            }

            // NOU: Condiția pentru filtrul dual de preț
            if (isset($_GET['min_price']) && is_numeric($_GET['min_price']) && isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
                $min_p = (int)$_GET['min_price'];
                $max_p = (int)$_GET['max_price'];

                $where_clause .= " AND events.price >= " . $min_p;
                if ($max_p < 500) { // 500 e maximul, deci înseamnă orice preț
                    $where_clause .= " AND events.price <= " . $max_p;
                }
            }

            // 1. Aflăm numărul total de evenimente ca să calculăm paginile
            $count_sql = "SELECT COUNT(events.id) AS total FROM events WHERE" . $where_clause;
            $count_result = $conn->query($count_sql);
            $total_events = $count_result->fetch_assoc()['total'];
            $total_pages = ceil($total_events / $events_per_page);

            // 2. Extragem doar evenimentele pentru pagina curentă
            $sql = "SELECT events.*, categories.name AS category_name, users.username AS organizer, cities.name AS city_name 
                    FROM events 
                    JOIN categories ON events.category_id = categories.id 
                    JOIN users ON events.user_id = users.id 
                    JOIN cities ON events.city_id = cities.id 
                    WHERE" . $where_clause . " 
                    ORDER BY events.date_time ASC 
                    LIMIT $events_per_page OFFSET $offset";

            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {

                    // Formatăm data
                    $date = new DateTime($row['date_time']);
                    $formatted_date = strtoupper($date->format('d M Y, H:i'));

                    // Securizare date
                    $safe_title = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');
                    $safe_location = htmlspecialchars($row['location'], ENT_QUOTES, 'UTF-8');
                    $safe_city = htmlspecialchars($row['city_name'], ENT_QUOTES, 'UTF-8');
                    $safe_desc = htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8');
                    $safe_org = htmlspecialchars($row['organizer'], ENT_QUOTES, 'UTF-8');
                    $safe_category_name = htmlspecialchars($row['category_name'], ENT_QUOTES, 'UTF-8');

                    // Imaginea (Folosim BASE_URL)
                    $img_src = !empty($row['image']) ? BASE_URL . "uploads/" . $row['image'] : BASE_URL . "assets/images/default-event.jpg";

                    // Logica pentru Badge-ul de preț
                    if ($row['price'] == 0) {
                        $price_badge = '<span class="badge rounded-pill px-3 py-2 fw-bold" style="font-size: 0.9rem;" id="price-badge-free">Gratis</span>';
                    } else {
                        $price_badge = '<span class="badge rounded-pill px-3 py-2 fw-bold" style="font-size: 0.9rem;" id="price-badge">' . $row['price'] . ' RON</span>';
                    }

                    echo '
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            
                            <div class="position-relative img-wrapper">
                                <div class="blurred-bg-main" style="background-image: url(\'' . $img_src . '\');"></div>
                                <img src="' . $img_src . '" class="card-img-top" alt="' . $safe_title . '">
                                <span class="position-absolute top-0 end-0 m-2 badge rounded-pill bg-dark bg-opacity-75 backdrop-blur py-2" id="category-badge" style="z-index: 10;">
                                    ' . $safe_category_name . '
                                </span>
                            </div>

                            <div class="card-body p-3">
                                
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-secondary fw-medium text-uppercase">
                                        <i class="fa-regular fa-calendar me-1"></i> ' . $formatted_date . '
                                    </small>
                                    ' . $price_badge . '
                                </div>

                                <h5 class="card-title fw-bold mb-2 text-dark" style="font-size: 1.25rem;">' . $safe_title . '</h5>
                                
                                <p class="card-text text-muted mb-3 small">
                                    <i class="fa-solid fa-location-dot me-1"></i> ' . $safe_location . ', ' . $safe_city . '
                                </p>

                                <p class="card-text text-secondary desc-clamp" style="font-size: 0.85rem; line-height: 1.5;">
                                    ' . $safe_desc . '
                                </p>
                            </div>

                            <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center gap-2">
                                <div class="text-truncate">
                                    <span class="fw-bold small text-truncate" style="color: #878787;" title="' . $safe_org . '">' . $safe_org . '</span>
                                </div>
                                
                                <a href="' . BASE_URL . 'event.php?id=' . $row['id'] . '" class="btn btn-outline-info rounded-pill px-4 fw-bold flex-shrink-0" style="border-width: 2px;font-size: 0.8rem;">
                                    View Details
                                </a>
                            </div>

                        </div>
                    </div>';
                }

                //  RENDARARE NAVIGARE PAGINARE 
                if ($total_pages > 1) {
                    echo '<div class="col-12 mt-4"><nav><ul class="pagination justify-content-center">';

                    // Butonul "Previous"
                    $prev_disabled = ($current_page <= 1) ? 'disabled' : '';
                    $prev_page = $current_page - 1;
                    echo '<li class="page-item ' . $prev_disabled . '">
                            <a class="page-link shadow-none" href="?cat=' . $active_cat . '&page=' . $prev_page . '#events-section" tabindex="-1">
                                <i class="fa-solid fa-arrow-left"></i>
                            </a>
                          </li>';

                    // Numerele paginilor
                    for ($i = 1; $i <= $total_pages; $i++) {
                        $active_class = ($i == $current_page) ? 'active' : '';
                        echo '<li class="page-item ' . $active_class . '">
                                <a class="page-link shadow-none" href="?cat=' . $active_cat . '&page=' . $i . '#events-section">' . $i . '</a>
                              </li>';
                    }

                    // Butonul "Next"
                    $next_disabled = ($current_page >= $total_pages) ? 'disabled' : '';
                    $next_page = $current_page + 1;
                    echo '<li class="page-item ' . $next_disabled . '">
                            <a class="page-link shadow-none" href="?cat=' . $active_cat . '&page=' . $next_page . '#events-section">
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                          </li>';

                    echo '</ul></nav></div>';
                }
            } else {
                // CAZUL FĂRĂ EVENIMENTE
                echo '
                <div class="col-12 text-center text-black py-5">
                    <div class="text-muted opacity-50 mb-3">
                        <i class="fa-solid fa-filter-circle-xmark fa-4x"></i>
                    </div>
                    <h3>No events found.</h3>
                    <p>Try another category or change the city filter!</p>
                    <div class="mt-3">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#addEventModal" class="btn btn-primary">Add Event</a>
                    </div>
                </div>
                ';
            }
            ?>
        </div>
    </div>
</div>

<style>
    /* Stiluri de bază pentru a face paginarea să se potrivească cu design-ul tău */
    .pagination .page-link {
        color: #0f172a;
        border: 1px solid #e2e8f0;
        margin: 0 4px;
        border-radius: 8px;
        font-weight: 600;
    }

    .pagination .page-item.active .page-link {
        background-color: #00ccff;
        border-color: #00ccff;
        color: #0f172a;
    }

    .pagination .page-link:hover {
        background-color: #f8fafc;
        color: #00ccff;
    }

    .pagination .page-item.disabled .page-link {
        color: #94a3b8;
        background-color: #f1f5f9;
    }
</style>

<?php include 'includes/footer.php'; ?>