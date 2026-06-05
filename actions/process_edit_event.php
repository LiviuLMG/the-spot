<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php'; // Includem noile funcții

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in to edit an event."]);
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

    $event_id = intval($_POST['event_id'] ?? 0);
    $user_id = $_SESSION['user_id'];
    $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';

    if ($event_id === 0) {
        echo json_encode(["status" => "error", "message" => "Invalid event ID."]);
        exit();
    }

    // Verificare Permisiuni
    $check_sql = $conn->prepare("SELECT image, user_id FROM events WHERE id = ?");
    $check_sql->bind_param("i", $event_id);
    $check_sql->execute();
    $result = $check_sql->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Event not found."]);
        exit();
    }

    $current_event = $result->fetch_assoc();

    if ($role !== 'admin' && $current_event['user_id'] != $user_id) {
        echo json_encode(["status" => "error", "message" => "Access denied. You cannot edit this event."]);
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

    // Validare de siguranță: blocăm execuția dacă datele critice lipsesc
    if (empty($title) || empty($location) || empty($city_name) || empty($category_name) || empty($event_date) || empty($event_time)) {
        echo json_encode(["status" => "error", "message" => "Toate câmpurile (inclusiv locația și orașul) sunt obligatorii. Te rugăm să le completezi corect."]);
        exit();
    }

    $date_time = $event_date . ' ' . $event_time . ':00';

    // 1. Folosim funcțiile DRY
    $city_id = getOrCreateCity($conn, $city_name);
    $category_id = getOrCreateCategory($conn, $category_name);

    if (!$city_id || !$category_id) {
        echo json_encode(["status" => "error", "message" => "Internal error while processing the city or category."]);
        exit();
    }

    // 2. Gestionarea Imaginii (Upload nou + ștergere veche securizată)
    $image_path = $current_event['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['image']['tmp_name'];
        $file_size = $_FILES['image']['size'];
        $target_dir = BASE_PATH . "uploads/";

        // Verificare dimensiune
        if ($file_size > 5 * 1024 * 1024) {
            echo json_encode(["status" => "error", "message" => "Image exceeds the 5MB limit."]);
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
            echo json_encode(["status" => "error", "message" => "Security error: Invalid image format."]);
            exit();
        }

        $ext = $allowed_mimes[$mime_type];
        $file_name = uniqid("event_", true) . "." . $ext;
        $target_file = $target_dir . $file_name;

        if (compressImage($tmp_name, $target_file, 75)) {
            $image_path = $file_name;

            // Ștergem imaginea veche
            if (!empty($current_event['image']) && file_exists($target_dir . $current_event['image'])) {
                unlink($target_dir . $current_event['image']);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Error occurred while uploading the new image."]);
            exit();
        }
    }

    // 3. Update DB
    $update_sql = $conn->prepare("UPDATE events SET title=?, description=?, location=?, city_id=?, date_time=?, image=?, price=?, category_id=? WHERE id=?");
    $update_sql->bind_param("sssissdii", $title, $description, $location, $city_id, $date_time, $image_path, $price, $category_id, $event_id);

    if ($update_sql->execute()) {
        echo json_encode(["status" => "success", "message" => "Modifications saved successfully!"]);
        exit();
    } else {
        echo json_encode(["status" => "error", "message" => "Server error while saving."]);
        exit();
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid access."]);
    exit();
}
