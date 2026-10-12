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

$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id=?");
$stmt->execute([$userId]);
$student = $stmt->fetch();

if (!$student) {
    echo json_encode(["success"=>false,"message"=>"Student profile not found."]);
    exit;
}

$studentId = $student["id"];

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $stmt = $pdo->prepare("
        SELECT a.id,a.applied_at,a.status,
               i.title,i.company,i.location,i.duration,i.stipend
        FROM applications a
        JOIN internships i ON i.id=a.internship_id
        WHERE a.student_id=?
        ORDER BY a.applied_at DESC
    ");
    $stmt->execute([$studentId]);
    echo json_encode($stmt->fetchAll());
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$internshipId = (int)($data["internship_id"] ?? 0);

if ($internshipId <= 0) {
    echo json_encode(["success"=>false,"message"=>"Invalid internship."]);
    exit;
}

$check = $pdo->prepare("SELECT id FROM applications WHERE student_id=? AND internship_id=?");
$check->execute([$studentId,$internshipId]);

if ($check->fetch()) {
    echo json_encode(["success"=>false,"message"=>"You have already applied for this internship."]);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO applications (student_id,internship_id) VALUES (?,?)");
$stmt->execute([$studentId,$internshipId]);

echo json_encode(["success"=>true,"message"=>"Application submitted successfully."]);
?>