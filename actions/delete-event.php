<?php
session_start();
require_once __DIR__ . '/../includes/db.php'; 

// 1. Verificăm dacă cererea vine prin POST (Nu mai acceptăm accesare directă prin link/GET)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Dacă cineva încearcă să acceseze fișierul scriind adresa în browser, îi tăiem accesul
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// 2. Verificăm dacă utilizatorul este logat
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// 3. SCUT CSRF (Verificăm tokenul trimis prin formularul POST)
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    // Dacă tokenul nu se potrivește (sau lipsește), oprim execuția (prevenim CSRF)
    die("Security error.");
}

// 4. Preluăm ID-ul evenimentului din formularul POST
if (!isset($_POST['event_id']) || !is_numeric($_POST['event_id'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$event_id = intval($_POST['event_id']);
$user_id = $_SESSION['user_id'];
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';

// 5. Extragem datele evenimentului pentru a verifica permisiunile și a afla numele imaginii
$stmt = $conn->prepare("SELECT user_id, image FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

// Dacă evenimentul nu există
if ($result->num_rows === 0) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$event = $result->fetch_assoc();

// 6. Verificăm dacă utilizatorul curent are dreptul să șteargă acest eveniment
if ($role !== 'admin' && $event['user_id'] != $user_id) {
    // Nu e nici admin, nici proprietarul evenimentului. Acces interzis.
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// 7. ȘTERGEM IMAGINEA DE PE SERVER (Curățenie fizică)
if (!empty($event['image'])) {
    $image_path = BASE_PATH . "uploads/" . $event['image'];
    if (file_exists($image_path)) {
        unlink($image_path);
    }
}

// 8. ȘTERGEM EVENIMENTUL DIN BAZA DE DATE
$delete_stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
$delete_stmt->bind_param("i", $event_id);

if ($delete_stmt->execute()) {
    // Succes! Îl trimitem la lista lui de evenimente (e mult mai logic din punct de vedere UX decât pe prima pagină)
    header("Location: " . BASE_URL . "user/my-events.php");
    exit();
} else {
    die("Security error: Failed to delete the event from the database.");
}
?>