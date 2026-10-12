<?php
session_start();
header("Content-Type: application/json");
require_once "../config/db.php";

$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

$stmt = $pdo->prepare("SELECT id,name,email,password,role FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user["password"])) {
    echo json_encode(["success"=>false,"message"=>"Invalid email or password."]);
    exit;
}

$_SESSION["user_id"] = $user["id"];
$_SESSION["name"] = $user["name"];
$_SESSION["role"] = $user["role"];

echo json_encode([
    "success"=>true,
    "role"=>$user["role"],
    "name"=>$user["name"],
    "redirect"=>$user["role"] === "admin" ? "admin/dashboard.php" : "student/dashboard.html"
]);
?>