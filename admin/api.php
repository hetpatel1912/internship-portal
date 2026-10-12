<?php
session_start();
header("Content-Type: application/json");
require_once "../config/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    http_response_code(401);
    echo json_encode(["success"=>false,"message"=>"Admin access required."]);
    exit;
}

$action = $_GET["action"] ?? "";

if ($action === "stats") {
    echo json_encode([
        "students" => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn(),
        "internships" => (int)$pdo->query("SELECT COUNT(*) FROM internships")->fetchColumn(),
        "applications" => (int)$pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn(),
        "selected" => (int)$pdo->query("SELECT COUNT(*) FROM applications WHERE status='Selected'")->fetchColumn()
    ]);
    exit;
}

if ($action === "internships") {
    $stmt = $pdo->query("SELECT * FROM internships ORDER BY created_at DESC");
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($action === "applications") {
    $stmt = $pdo->query("
        SELECT a.id,a.applied_at,a.status,
               u.name AS student_name,u.email,
               i.title,i.company
        FROM applications a
        JOIN students s ON s.id=a.student_id
        JOIN users u ON u.id=s.user_id
        JOIN internships i ON i.id=a.internship_id
        ORDER BY a.applied_at DESC
    ");
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($action === "students") {
    $stmt = $pdo->query("
        SELECT u.id,u.name,u.email,u.created_at,s.college,s.course,s.skills
        FROM users u
        LEFT JOIN students s ON s.user_id=u.id
        WHERE u.role='student'
        ORDER BY u.created_at DESC
    ");
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if ($action === "add_internship") {
        $stmt = $pdo->prepare("
            INSERT INTO internships
            (title,company,description,location,duration,stipend,skills_required,deadline)
            VALUES (?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            trim($data["title"]),
            trim($data["company"]),
            trim($data["description"]),
            trim($data["location"]),
            trim($data["duration"]),
            trim($data["stipend"]),
            trim($data["skills_required"]),
            $data["deadline"]
        ]);
        echo json_encode(["success"=>true,"message"=>"Internship added."]);
        exit;
    }

    if ($action === "update_status") {
        $allowed = ["Pending","Shortlisted","Selected","Rejected"];
        $status = $data["status"] ?? "";

        if (!in_array($status, $allowed, true)) {
            echo json_encode(["success"=>false,"message"=>"Invalid status."]);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE applications SET status=? WHERE id=?");
        $stmt->execute([$status,(int)$data["id"]]);

        echo json_encode(["success"=>true,"message"=>"Application status updated."]);
        exit;
    }

    if ($action === "delete_internship") {
        $stmt = $pdo->prepare("DELETE FROM internships WHERE id=?");
        $stmt->execute([(int)$data["id"]]);

        echo json_encode(["success"=>true,"message"=>"Internship deleted."]);
        exit;
    }
}

echo json_encode(["success"=>false,"message"=>"Invalid request."]);
?>