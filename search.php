<?php 
include 'includes/header.php'; 

// Preluăm termenul de căutare din URL și îl curățăm
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$safe_search = htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8');
?>

<div class="container mt-5 mb-5" style="min-height: 60vh;">
    
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
        <h2 class="fw-bold text-black">
            Results for: <span style="color: #00ccff;">"<?php echo $safe_search; ?>"</span>
        </h2>
    </div>

    <div class="row">
        <?php
        if (!empty($search_query)) {
            // --- SETĂRI PAGINARE ---
            $events_per_page = 6;
            $current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
            if ($current_page < 1) $current_page = 1;
            $offset = ($current_page - 1) * $events_per_page;

            // Clauza de condiție comună
            $where_clause = "events.date_time >= NOW() 
                             AND (
                                 events.title LIKE ? OR 
                                 events.description LIKE ? OR 
                                 events.location LIKE ? OR 
                                 cities.name LIKE ? OR 
                                 users.username LIKE ?
                             )";

            // Pregătim termenul de căutare adăugând wildcard-urile de procente (%) pentru LIKE
            $search_param = '%' . $search_query . '%';

            // 1. Aflăm numărul total de rezultate pentru acest search
            $count_sql = "SELECT COUNT(events.id) AS total 
                          FROM events 
                          JOIN categories ON events.category_id = categories.id 
                          JOIN users ON events.user_id = users.id 
                          JOIN cities ON events.city_id = cities.id 
                          WHERE " . $where_clause;
            
            $count_stmt = $conn->prepare($count_sql);
            // Legăm același termen de căutare de 5 ori, pentru fiecare semnul întrebării (?) din clauza WHERE
            $count_stmt->bind_param("sssss", $search_param, $search_param, $search_param, $search_param, $search_param);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            
            $total_events = $count_result->fetch_assoc()['total'];
            $total_pages = ceil($total_events / $events_per_page);

            // 2. Extragem datele doar pentru pagina curentă
            $sql = "SELECT events.*, categories.name AS category_name, users.username AS organizer, cities.name AS city_name 
                    FROM events 
                    JOIN categories ON events.category_id = categories.id 
                    JOIN users ON events.user_id = users.id 
                    JOIN cities ON events.city_id = cities.id 
                    WHERE " . $where_clause . " 
                    ORDER BY events.date_time ASC 
                    LIMIT ? OFFSET ?";

            $stmt = $conn->prepare($sql);
            // Aici avem 7 parametri: cei 5 de la căutare (s), și limit/offset (i, i)
            $stmt->bind_param("sssssii", $search_param, $search_param, $search_param, $search_param, $search_param, $events_per_page, $offset);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    
                    $date = new DateTime($row['date_time']);
                    $formatted_date = strtoupper($date->format('d M Y, H:i')); 
                    
                    $safe_title = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');
                    $safe_location = htmlspecialchars($row['location'], ENT_QUOTES, 'UTF-8');
                    $safe_city = htmlspecialchars($row['city_name'], ENT_QUOTES, 'UTF-8');
                    $safe_desc = htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8');
                    $safe_org = htmlspecialchars($row['organizer'], ENT_QUOTES, 'UTF-8');
                    $safe_category_name = htmlspecialchars($row['category_name'], ENT_QUOTES, 'UTF-8');

                    // Imaginea
                    $img_src = !empty($row['image']) ? BASE_URL . "uploads/" . $row['image'] : BASE_URL . "assets/images/default-event.jpg";

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
                                <img src="'.$img_src.'" class="card-img-top" alt="'.$safe_title.'">
                                <span class="position-absolute top-0 end-0 m-2 badge rounded-pill bg-dark bg-opacity-75 backdrop-blur py-2" id="category-badge" style="z-index:10;">
                                    '.$safe_category_name.'
                                </span>
                            </div>

                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-secondary fw-medium text-uppercase">
                                        <i class="fa-regular fa-calendar me-1"></i> '.$formatted_date.'
                                    </small>
                                    '.$price_badge.'
                                </div>

                                <h5 class="card-title fw-bold mb-2 text-dark" style="font-size: 1.25rem;">'.$safe_title.'</h5>
                                
                                <p class="card-text text-muted mb-3 small">
                                    <i class="fa-solid fa-location-dot me-1 text-danger"></i> '.$safe_location.', '.$safe_city.'
                                </p>

                                <p class="card-text text-secondary desc-clamp" style="font-size: 0.85rem; line-height: 1.5;">
                                    '.$safe_desc.'
                                </p>
                            </div>

                            <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center gap-2">
                                <div class="text-truncate">
                                    <span class="fw-bold small text-truncate" style="color: #878787;" title="'.$safe_org.'">'.$safe_org.'</span>
                                </div>
                                
                                <a href="'. BASE_URL .'event.php?id=' . $row['id'] . '" class="btn btn-outline-info rounded-pill px-4 fw-bold flex-shrink-0" style="border-width: 2px;font-size: 0.8rem;">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>'; 
                
                }
            }
        }
        else {
            echo '
            <div class="col-12 text-center py-5">
                <div class="text-muted opacity-50 mb-3">
                    <i class="fa-solid fa-keyboard fa-4x"></i>
                </div>
                <h3>Introdu un termen pentru a căuta.</h3>
                <a href="'. BASE_URL .'index.php" class="btn btn-primary mt-2">Înapoi la evenimente</a>
            </div>';
        }
        ?>
    </div>
</div>

<style>
/* Același stil curat pentru butoanele de paginare */
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