<?php
// Vercel Project Settings > Environment Variables:
// DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD
mysqli_report(MYSQLI_REPORT_OFF);
$host = getenv('DB_HOST') ?: '';
$user = getenv('DB_USER') ?: '';
$pass = getenv('DB_PASSWORD') ?: '';
$name = getenv('DB_NAME') ?: '';
$port = (int)(getenv('DB_PORT') ?: 3306);
if ($host === '' || $user === '' || $name === '') {
    http_response_code(500);
    exit('Database belum dikonfigurasi. Atur DB_HOST, DB_NAME, DB_USER, DB_PASSWORD, dan DB_PORT di Vercel.');
}
$conn = mysqli_connect($host, $user, $pass, $name, $port);
if (!$conn) {
    error_log('KasELITE database connection failed: ' . mysqli_connect_error());
    http_response_code(500);
    exit('Koneksi database gagal. Periksa Environment Variables dan akses jaringan database.');
}
$conn->set_charset('utf8mb4');
?>
