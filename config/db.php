<?php
$host = getenv("DB_HOST") ?: "localhost";
$db = getenv("DB_NAME") ?: "internship_portal";
$user = getenv("DB_USER") ?: "root";
$pass = getenv("DB_PASSWORD") ?: "";
$port = getenv("DB_PORT") ?: "3306";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    http_response_code(500);
    if (str_contains($_SERVER["SCRIPT_NAME"] ?? "", ".php")) {
        header("Content-Type: application/json; charset=utf-8");
    }
    die("Database connection failed. Check the deployment database environment variables.");
}
