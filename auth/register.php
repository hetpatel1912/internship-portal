<?php
session_start();
header("Content-Type: application/json");
require_once "../config/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$name = trim($data["name"] ?? "");
$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if ($name === "" || $email === "" || $password === "") {
    echo json_encode(["success"=>false,"message"=>"All fields are required."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success"=>false,"message"=>"Enter a valid email address."]);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(["success"=>false,"message"=>"Password must be at least 6 characters."]);
    exit;
}

$check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$check->execute([$email]);

if ($check->fetch()) {
    echo json_encode(["success"=>false,"message"=>"Email is already registered."]);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,'student')");
$stmt->execute([$name,$email,$hash]);

$userId = $pdo->lastInsertId();

$student = $pdo->prepare("INSERT INTO students (user_id) VALUES (?)");
$student->execute([$userId]);

echo json_encode(["success"=>true,"message"=>"Registration successful. You can now login."]);
?>