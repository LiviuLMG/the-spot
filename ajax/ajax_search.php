<?php
require_once __DIR__ . '/../includes/db.php';

if (isset($_GET['q'])) {
    $q = trim($_GET['q']);
    
    if (strlen($q) > 0) {
        $search_param = '%' . $q . '%';
        
        $sql = "SELECT id, title, image, date_time 
                FROM events 
                WHERE title LIKE ? AND date_time >= NOW() 
                ORDER BY date_time ASC LIMIT 5";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $search_param);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $img = !empty($row['image']) ? BASE_URL . 'uploads/'.$row['image'] : BASE_URL . 'assets/images/default-event.jpg';
                $date = new DateTime($row['date_time']);
                $formatted_date = $date->format('d M, H:i');
                $safe_title = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');
                
                echo '<a href="' . BASE_URL . 'event.php?id='.$row['id'].'" class="live-search-item">';
                echo '<img src="'.$img.'" class="live-search-img">';
                echo '<div class="live-search-details">';
                echo '<span class="live-search-title">'.$safe_title.'</span>';
                echo '<span class="live-search-date text-muted"><i class="fa-regular fa-calendar me-1"></i>'.$formatted_date.'</span>';
                echo '</div></a>';
            }
        } else {
            echo '<div class="p-3 text-muted small text-center">No events found.</div>';
        }
    }
}
?>