<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in to perform this action."]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- SCUT CSRF ---
    if (
        empty($_POST['csrf_token']) ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        echo json_encode(["status" => "error", "message" => "Security error. Please reload the page."]);
        exit();
    }

    // --- SCUT CLOUDFLARE TURNSTILE (ANTI-BOT) ---
    $turnstile_token = $_POST['cf-turnstile-response'] ?? '';
    if (!verifyTurnstile($turnstile_token)) {
        echo json_encode(["status" => "error", "message" => "Validarea de securitate a eșuat."]);
        exit();
    }

    // Extragem datele în siguranță, prevenind erorile "Undefined array key"
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $city_name = trim($_POST['city_name'] ?? '');
    $category_name = trim($_POST['category_name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $event_date = trim($_POST['event_date'] ?? '');
    $event_time = trim($_POST['event_time'] ?? '');

    $user_id = $_SESSION['user_id'];

    // Validare de siguranță: blocăm execuția dacă datele critice lipsesc
    if (empty($title) || empty($location) || empty($city_name) || empty($category_name) || empty($event_date) || empty($event_time)) {
        echo json_encode(["status" => "error", "message" => "Toate câmpurile (inclusiv locația și orașul) sunt obligatorii. Te rugăm să le completezi corect."]);
        exit();
    }

    $date_time = $event_date . ' ' . $event_time . ':00';

    // 1. Folosim funcțiile DRY cu variabilele deja curățate
    $city_id = getOrCreateCity($conn, $city_name);
    $category_id = getOrCreateCategory($conn, $category_name);

    if (!$city_id || !$category_id) {
        echo json_encode(["status" => "error", "message" => "Internal error while processing the city or category."]);
        exit();
    }

    // 2. Upload Imagine securizat
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['image']['tmp_name'];
        $file_size = $_FILES['image']['size'];

        // Verificare dimensiune
        if ($file_size > 5 * 1024 * 1024) {
            echo json_encode(["status" => "error", "message" => "Image is too large. The limit is 5MB."]);
            exit();
        }

        // VERIFICARE STRICTĂ MIME TYPE
        $mime_type = mime_content_type($tmp_name);
        $allowed_mimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        if (!array_key_exists($mime_type, $allowed_mimes)) {
            echo json_encode(["status" => "error", "message" => "Security error: Invalid image format. Only real JPG, PNG or WEBP allowed."]);
            exit();
        }

        // Preluăm extensia sigură pe baza MIME type-ului
        $ext = $allowed_mimes[$mime_type];

        // Generăm un nume unic imposibil de ghicit
        $file_name = uniqid("event_", true) . "." . $ext;
        $target_file = BASE_PATH . "uploads/" . $file_name;

        if (compressImage($tmp_name, $target_file, 75)) {
            $image_path = $file_name;
        } else {
            echo json_encode(["status" => "error", "message" => "Error saving image on server."]);
            exit();
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Event image is required."]);
        exit();
    }

    // 3. Inserare Eveniment
    $status = 'approved';
    $insert_sql = $conn->prepare("INSERT INTO events (title, description, location, city_id, date_time, image, price, category_id, user_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insert_sql->bind_param("sssissdiis", $title, $description, $location, $city_id, $date_time, $image_path, $price, $category_id, $user_id, $status);

    if ($insert_sql->execute()) {
        echo json_encode(["status" => "success", "message" => "Event published successfully!"]);
        exit();
    } else {
        echo json_encode(["status" => "error", "message" => "Server error. Could not save the event."]);
        exit();
    }
} else {
    echo json_encode(["status" => "error", "message" => "Access denied."]);
    exit();
}
