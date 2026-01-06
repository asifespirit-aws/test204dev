<?php
declare(strict_types=1);

$dsn = 'mysql:host=localhost;dbname=customphp_db4;charset=utf8mb4';
$username = 'customphp_db_user4';
$password = 'customphp_db_password4';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Database connection failed.';
    exit;
}
