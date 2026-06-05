<?php
// Funcție pentru extragerea sau crearea unui ORAȘ
function getOrCreateCity($conn, $city_name) {
    $city_input = ucwords(strtolower(trim($city_name))); 
    if (empty($city_input)) return 0;

    $check_city = $conn->prepare("SELECT id, status FROM cities WHERE name = ?");
    $check_city->bind_param("s", $city_input);
    $check_city->execute();
    $res_city = $check_city->get_result();

    if ($res_city->num_rows > 0) {
        $row_city = $res_city->fetch_assoc();
        $city_id = $row_city['id'];
        
        if ($row_city['status'] === 'deleted') {
            $update = $conn->prepare("UPDATE cities SET status = 'active' WHERE id = ?");
            $update->bind_param("i", $city_id);
            $update->execute();
        }
        return $city_id;
    } else {
        $insert_city = $conn->prepare("INSERT INTO cities (name, status) VALUES (?, 'active')");
        $insert_city->bind_param("s", $city_input);
        if ($insert_city->execute()) {
            return $insert_city->insert_id;
        }
    }
    return 0;
}

// Funcție pentru extragerea sau crearea unei CATEGORII
function getOrCreateCategory($conn, $category_name) {
    $cat_input = ucfirst(strtolower(trim($category_name))); 
    if (empty($cat_input)) return 0;

    $check_cat = $conn->prepare("SELECT id, status FROM categories WHERE name = ?");
    $check_cat->bind_param("s", $cat_input);
    $check_cat->execute();
    $res_cat = $check_cat->get_result();

    if ($res_cat->num_rows > 0) {
        $row_cat = $res_cat->fetch_assoc();
        $category_id = $row_cat['id'];
        
        if ($row_cat['status'] === 'deleted') {
            $update = $conn->prepare("UPDATE categories SET status = 'active' WHERE id = ?");
            $update->bind_param("i", $category_id);
            $update->execute();
        }
        return $category_id;
    } else {
        $insert_cat = $conn->prepare("INSERT INTO categories (name, status) VALUES (?, 'active')");
        $insert_cat->bind_param("s", $cat_input);
        if ($insert_cat->execute()) {
            return $insert_cat->insert_id;
        }
    }
    return 0;
}

// Funcție pentru REDIMENSIONAREA și COMPRESIA imaginilor la upload
function compressImage($source, $destination, $quality) {
    $info = getimagesize($source);
    if ($info === false) return false;

    $mime = $info['mime'];
    $width = $info[0];
    $height = $info[1];

    $maxWidth = 1200;
    $maxHeight = 1200;

    $newWidth = $width;
    $newHeight = $height;

    if ($width > $maxWidth || $height > $maxHeight) {
        $ratio = $width / $height;
        if ($maxWidth / $maxHeight > $ratio) {
            $newWidth = $maxHeight * $ratio;
            $newHeight = $maxHeight;
        } else {
            $newHeight = $maxWidth / $ratio;
            $newWidth = $maxWidth;
        }
    }

    if ($mime == 'image/jpeg') {
        $image = imagecreatefromjpeg($source);
    } elseif ($mime == 'image/png') {
        $image = imagecreatefrompng($source);
    } elseif ($mime == 'image/webp') {
        $image = imagecreatefromwebp($source);
    } else {
        return move_uploaded_file($source, $destination);
    }

    
    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    if ($mime == 'image/png' || $mime == 'image/webp') {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
    }

    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    if ($mime == 'image/jpeg') {
        imagejpeg($newImage, $destination, $quality); 
    } elseif ($mime == 'image/png') {
        $pngQuality = round(9 * (100 - $quality) / 100);
        imagepng($newImage, $destination, $pngQuality);
    } elseif ($mime == 'image/webp') {
        imagewebp($newImage, $destination, $quality);
    }

    // Aici e modificarea ta super-corectă:
    if (isset($image)) imagedestroy($image);
    if (isset($newImage)) imagedestroy($newImage);

    return true;
}

// --- CLOUDFLARE TURNSTILE VERIFICATION ---
function verifyTurnstile($token) {
    if (empty($token)) {
        return false;
    }
    
    $secret = $_ENV['TURNSTILE_SECRET'] ?? '';
    // Aici e fixul tău cu safety check:
    $remote_ip = $_SERVER['REMOTE_ADDR'] ?? '';
    
    $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    $data = [
        'secret' => $secret,
        'response' => $token,
        'remoteip' => $remote_ip
    ];
    
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context  = stream_context_create($options);
    
    $result = @file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        return false;
    }
    
    $response = json_decode($result, true);
    return $response['success'] ?? false;
}

// --- RATE LIMITING CORE FUNCTIONS ---
function checkRateLimit($conn, $action_name, $identifier, $max_attempts, $minutes) {
    $cleanup_time = date('Y-m-d H:i:s', strtotime("-$minutes minutes"));
    $cleanup = $conn->prepare("DELETE FROM rate_limits WHERE action_name = ? AND identifier = ? AND last_attempt < ?");
    $cleanup->bind_param("sss", $action_name, $identifier, $cleanup_time);
    $cleanup->execute();

    $stmt = $conn->prepare("SELECT attempts FROM rate_limits WHERE action_name = ? AND identifier = ?");
    $stmt->bind_param("ss", $action_name, $identifier);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if ($row['attempts'] >= $max_attempts) {
            return false;
        }
    }
    
    $update = $conn->prepare("
        INSERT INTO rate_limits (action_name, identifier, attempts, last_attempt) 
        VALUES (?, ?, 1, NOW()) 
        ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt = NOW()
    ");
    $update->bind_param("ss", $action_name, $identifier);
    $update->execute();

    return true;
}

function clearRateLimit($conn, $action_name, $identifier) {
    $stmt = $conn->prepare("DELETE FROM rate_limits WHERE action_name = ? AND identifier = ?");
    $stmt->bind_param("ss", $action_name, $identifier);
    $stmt->execute();
}
?>