<?php
session_start();
// Urcăm un folder mai sus pentru a găsi corect baza de date
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// 1. Verificare securitate (Logare)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in to join or leave an event."]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['event_id'])) {
    
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo json_encode(["status" => "error", "message" => "Security error. Please reload the page."]);
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $event_id = intval($_POST['event_id']);

    // 2. Verificăm dacă utilizatorul este deja înscris
    $check = $conn->prepare("SELECT * FROM event_participants WHERE user_id = ? AND event_id = ?");
    $check->bind_param("ii", $user_id, $event_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Este deja înscris -> ANULĂM PARTICIPAREA (Leave)
        $delete = $conn->prepare("DELETE FROM event_participants WHERE user_id = ? AND event_id = ?");
        $delete->bind_param("ii", $user_id, $event_id);
        
        if ($delete->execute()) {
            echo json_encode(["status" => "left", "message" => "You have left the event."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error occurred while trying to leave the event."]);
        }
    } else {
        // Nu este înscris -> ÎL ADĂUGĂM (Join)
        $insert = $conn->prepare("INSERT INTO event_participants (user_id, event_id) VALUES (?, ?)");
        $insert->bind_param("ii", $user_id, $event_id);
        
        if ($insert->execute()) {
            echo json_encode(["status" => "joined", "message" => "You have successfully joined the event!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error occurred while trying to join the event."]);
        }
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>