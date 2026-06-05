<?php
// 1. Pornim sesiunea
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Includem conexiunea la baza de date
require_once __DIR__ . '/db.php';

// 3. Construim URL-ul de bază absolut pentru meta tag-uri
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$absolute_base_url = BASE_URL;

// 4. Logica pentru schimbarea orașului
if (isset($_GET['switch_city'])) {
    $_SESSION['city_id'] = $_GET['switch_city'];
}

if (!isset($_SESSION['city_id'])) {
    $_SESSION['city_id'] = 'all';
}

$current_city_id = $_SESSION['city_id'];
$current_city_name = "All cities";

$cities_list = [];

$cities_list[] = [
    'id' => 'all',
    'name' => 'All cities'
];

$sql_cities = "SELECT DISTINCT cities.id, cities.name 
               FROM cities 
               JOIN events ON cities.id = events.city_id 
               WHERE events.date_time >= NOW() AND cities.status = 'active'
               ORDER BY cities.name ASC";
$result_cities = $conn->query($sql_cities);

if ($result_cities) {
    while ($row = $result_cities->fetch_assoc()) {
        $cities_list[] = $row;
        if ($row['id'] == $current_city_id) {
            $current_city_name = $row['name'];
        }
    }
}

// 5. Extragem poza de profil a utilizatorului (dacă este logat)
$header_avatar_src = BASE_URL . "assets/images/default-avatar.png";

if (isset($_SESSION['user_id'])) {
    $u_id = $_SESSION['user_id'];
    $stmt_avatar = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt_avatar->bind_param("i", $u_id);
    $stmt_avatar->execute();
    $res_avatar = $stmt_avatar->get_result();

    if ($res_avatar->num_rows > 0) {
        $user_data = $res_avatar->fetch_assoc();
        if (!empty($user_data['avatar'])) {
            $header_avatar_src = BASE_URL . "uploads/" . $user_data['avatar'];
        }
    }
}

// 6. Meta tag-uri default, dacă pagina nu trimite unele dinamice
$default_title = "The Spot - Discover Urban Events";
$default_description = "Discover, join, and organize the best urban events in your city.";
$default_image = $absolute_base_url . "assets/images/logo.png";
$default_url = $protocol . $host . ($_SERVER['REQUEST_URI'] ?? BASE_URL . "index.php");

$meta_title = $og_title ?? $default_title;
$meta_description = $og_description ?? $default_description;
$meta_image = $og_image ?? $default_image;
$meta_url = $og_url ?? $default_url;
$meta_type = isset($og_title, $og_description, $og_image, $og_url) ? "article" : "website";
?>

<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($meta_title, ENT_QUOTES, 'UTF-8'); ?></title>

    <meta name="description" content="<?php echo htmlspecialchars($meta_description, ENT_QUOTES, 'UTF-8'); ?>">

    <meta property="og:title" content="<?php echo htmlspecialchars($meta_title, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_description, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($meta_image, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($meta_url, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:type" content="<?php echo htmlspecialchars($meta_type, ENT_QUOTES, 'UTF-8'); ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($meta_title, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($meta_description, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($meta_image, ENT_QUOTES, 'UTF-8'); ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>assets/images/favicon.png">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/body.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/header.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/footer.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/add-event-popup.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/hero-section.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/event.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top shadow-lg">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="<?php echo BASE_URL; ?>index.php">
                <img src="<?php echo BASE_URL; ?>assets/images/logo.png" alt="Logo" class="me-2">
                The Spot
            </a>

            <button class="navbar-toggler collapsed border-0 p-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <div id="nav-icon3">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </button>

            <div class="collapse navbar-collapse mt-3 mt-lg-0" id="mainNav">

                <div class="d-none d-lg-block dropdown ms-lg-3 me-lg-3 my-2 my-lg-0">
                    <button class="btn-glass dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span style="color: rgba(255,255,255,0.5);">Events in:</span>
                        <span class="fw-bold ms-1"><?php echo htmlspecialchars($current_city_name, ENT_QUOTES, 'UTF-8'); ?></span>
                        <i class="fa-solid fa-chevron-down ms-2 rotate-icon" style="font-size: 0.75rem; color: rgba(255,255,255,0.5);"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-custom shadow-lg" id="cities-dropdown">
                        <?php foreach ($cities_list as $city): ?>
                            <li>
                                <a class="dropdown-item dropdown-item-custom <?php echo ($city['id'] == $current_city_id) ? 'active-city' : ''; ?>"
                                    href="?switch_city=<?php echo htmlspecialchars($city['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($city['name'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <form class="d-flex mx-lg-auto my-3 my-lg-0 position-relative" style="flex-grow: 1; max-width: 500px;" action="<?php echo BASE_URL; ?>search.php" method="GET">
                    <i class="fa-solid fa-magnifying-glass search-icon-custom"></i>
                    <input class="form-control search-input" type="search" name="q" placeholder="Search events, artists, or venues..." aria-label="Search" autocomplete="off">
                </form>

                <div class="d-lg-none">

                    <div class="mb-2 text-uppercase text-secondary fw-bold" style="font-size: 0.8rem; letter-spacing: 1px;">City</div>

                    <div class="mobile-city-chips">
                        <?php foreach ($cities_list as $city): ?>
                            <a href="?switch_city=<?php echo htmlspecialchars($city['id'], ENT_QUOTES, 'UTF-8'); ?>" class="city-chip <?php echo ($city['id'] == $current_city_id) ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($city['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <div class="row g-2">
                            <div class="col-6">
                                <a href="#" class="btn-mobile-login" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a>
                            </div>
                            <div class="col-6">
                                <a href="<?php echo BASE_URL; ?>register.php" class="btn-mobile-signup">Sign Up</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <hr class="border-secondary">
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            <img src="<?php echo htmlspecialchars($header_avatar_src, ENT_QUOTES, 'UTF-8'); ?>" alt="Avatar" class="header-avatar-mobile me-3 shadow-sm">
                            <div>
                                <div class="fw-bold" style="color: white;"><?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <small style="color: rgba(255,255,255,0.7);">Logged in</small>
                            </div>
                        </div>

                        <a href="<?php echo BASE_URL; ?>user/dashboard.php" class="btn btn-outline-light w-100 mb-2">My Account</a>

                        <a href="<?php echo BASE_URL; ?>user/my-events.php" class="btn-mobile-login py-2 mb-2" style="font-size: 0.85rem;">My Events</a>
                        <a href="<?php echo BASE_URL; ?>user/my-tickets.php" class="btn-mobile-login py-2 mb-2" style="font-size: 0.85rem;">My Tickets</a>

                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                            <a href="<?php echo BASE_URL; ?>admin/index.php" class="btn-mobile-login py-2 mb-2 text-danger" style="font-size: 0.85rem;">
                                Admin Panel
                            </a>
                        <?php endif; ?>

                        <a href="<?php echo BASE_URL; ?>actions/logout.php" class="btn-mobile-login py-2" style="font-size: 0.85rem;">
                            Log Out
                        </a>
                    <?php endif; ?>
                </div>

                <ul class="navbar-nav mb-2 mb-lg-0 align-items-center d-none d-lg-flex ms-lg-3">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="<?php echo htmlspecialchars($header_avatar_src, ENT_QUOTES, 'UTF-8'); ?>" alt="Avatar" class="header-avatar-desktop me-2 shadow-sm">
                                <span class="fw-bold"><?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <i class="fa-solid fa-chevron-down ms-2 rotate-icon" style="font-size: 0.75rem; opacity: 0.7;"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-custom-account shadow-lg">
                                <li><a class="dropdown-item dropdown-item-custom" href="<?php echo BASE_URL; ?>user/dashboard.php">My Account</a></li>

                                <li><a class="dropdown-item dropdown-item-custom" href="<?php echo BASE_URL; ?>user/my-events.php">My Events</a></li>
                                <li><a class="dropdown-item dropdown-item-custom" href="<?php echo BASE_URL; ?>user/my-tickets.php">My Tickets</a></li>

                                <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <li><a class="dropdown-item dropdown-item-custom text-danger" href="<?php echo BASE_URL; ?>admin/index.php">Admin Panel</a></li>
                                <?php endif; ?>
                                <li>
                                    <hr class="dropdown-divider border-secondary">
                                </li>
                                <li><a class="dropdown-item dropdown-item-custom" href="<?php echo BASE_URL; ?>actions/logout.php">Log Out</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn-login-custom ms-2" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn ms-2" id="register-btn" href="<?php echo BASE_URL; ?>register.php">Sign Up</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>