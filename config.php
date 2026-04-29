<?php
define('SITE_URL', '');
define('SITE_NAME', 'Kebun Ndesa Tanah Merah');
define('APP_NAME', 'Kebun Ndesa Tanah Merah');
define('APP_URL', '');

require_once __DIR__ . '/env.php';
loadEnv(__DIR__ . '/.env');

function getDB() {
    $conn = new mysqli(
        $_ENV['DB_HOST'],
        $_ENV['DB_USER'],
        $_ENV['DB_PASSWORD'],
        $_ENV['DB_NAME']
    );

    if ($conn->connect_error) {
        die('Koneksi gagal: ' . $conn->connect_error);
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}