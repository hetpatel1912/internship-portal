<?php
session_start();
header("Content-Type: application/json");
require_once "../config/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    http_response_code(401);
    echo json_encode(["success"=>false,"message"=>"Unauthorized"]);
    exit;
}

$userId = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $stmt = $pdo->prepare("
        SELECT u.name,u.email,s.phone,s.college,s.course,s.skills,s.about,s.resume
        FROM users u
        JOIN students s ON s.user_id=u.id
        WHERE u.id=?
    ");
    $stmt->execute([$userId]);
    echo json_encode($stmt->fetch());
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $pdo->prepare("
    UPDATE users SET name=? WHERE id=?
");
$stmt->execute([trim($data["name"] ?? ""), $userId]);

$stmt = $pdo->prepare("
    UPDATE students
    SET phone=?, college=?, course=?, skills=?, about=?, resume=?
    WHERE user_id=?
");
$stmt->execute([
    trim($data["phone"] ?? ""),
    trim($data["college"] ?? ""),
    trim($data["course"] ?? ""),
    trim($data["skills"] ?? ""),
    trim($data["about"] ?? ""),
    trim($data["resume"] ?? ""),
    $userId
]);

$_SESSION["name"] = trim($data["name"] ?? "");

echo json_encode(["success"=>true,"message"=>"Profile updated successfully."]);
?>