<?php
define('BASE_PATH', __DIR__ . '/../');
define('BASE_URL', 'https://thespot.ro/');

function loadEnv(string $path)
{
    if (!file_exists($path)) {
        die("Critical error: Missing environment configuration file (.env)");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        if (strpos($line, '=') === false) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);

        $_ENV[trim($name)] = trim($value, " \t\n\r\0\x0B\"");
    }
}

loadEnv(BASE_PATH . '.env');

$conn = new mysqli(
    $_ENV['DB_HOST'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS'],
    $_ENV['DB_NAME']
);

if ($conn->connect_error) {
    die("Error connecting to the database.");
}

$conn->set_charset("utf8mb4");

if (session_status() === PHP_SESSION_ACTIVE && empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}