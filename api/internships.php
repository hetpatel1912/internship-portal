<?php
session_start();
header("Content-Type: application/json");
require_once "../config/db.php";

$search = trim($_GET["search"] ?? "");
$location = trim($_GET["location"] ?? "");

$sql = "SELECT * FROM internships WHERE deadline >= CURDATE()";
$params = [];

if ($search !== "") {
    $sql .= " AND (title LIKE ? OR company LIKE ? OR skills_required LIKE ?)";
    $term = "%$search%";
    $params = [$term,$term,$term];
}

if ($location !== "" && $location !== "All") {
    $sql .= " AND location = ?";
    $params[] = $location;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode($stmt->fetchAll());
?>