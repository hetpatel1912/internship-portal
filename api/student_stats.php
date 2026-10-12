<?php
session_start();
header("Content-Type: application/json");
require_once "../config/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    http_response_code(401);
    echo json_encode(["success"=>false]);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id=?");
$stmt->execute([$_SESSION["user_id"]]);
$student = $stmt->fetch();
if (!$student) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Student profile not found."]);
    exit;
}

$available = $pdo->query("SELECT COUNT(*) FROM internships WHERE deadline >= CURDATE()")->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE student_id=?");
$stmt->execute([$student["id"]]);
$total = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE student_id=? AND status='Shortlisted'");
$stmt->execute([$student["id"]]);
$shortlisted = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE student_id=? AND status='Selected'");
$stmt->execute([$student["id"]]);
$selected = $stmt->fetchColumn();

echo json_encode([
    "available"=>$available,
    "applications"=>$total,
    "shortlisted"=>$shortlisted,
    "selected"=>$selected
]);
?>