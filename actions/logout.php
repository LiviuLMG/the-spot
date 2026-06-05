<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/db.php';

session_unset();
session_destroy();

header("Location: " . BASE_URL . "index.php");
exit();
