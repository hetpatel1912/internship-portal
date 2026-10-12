<?php
// One-time database initializer. Protect it with SETUP_KEY and remove/disable it after use.
$expectedKey = getenv("SETUP_KEY") ?: "";
$providedKey = $_GET["key"] ?? "";
if ($expectedKey === "" || !hash_equals($expectedKey, $providedKey)) {
    http_response_code(404);
    exit("Not found.");
}

$adminPassword = getenv("ADMIN_PASSWORD") ?: "";
if (strlen($adminPassword) < 12) {
    http_response_code(500);
    exit("Set ADMIN_PASSWORD to a value of at least 12 characters before running setup.");
}

require_once __DIR__ . "/config/db.php";

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('student','admin') NOT NULL DEFAULT 'student',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS students (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            phone VARCHAR(20),
            college VARCHAR(150),
            course VARCHAR(100),
            skills TEXT,
            about TEXT,
            resume VARCHAR(255),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS internships (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(150) NOT NULL,
            company VARCHAR(150) NOT NULL,
            description TEXT NOT NULL,
            location VARCHAR(100) NOT NULL,
            duration VARCHAR(50) NOT NULL,
            stipend VARCHAR(50) NOT NULL,
            skills_required VARCHAR(255) NOT NULL,
            deadline DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS applications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            internship_id INT NOT NULL,
            applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('Pending','Shortlisted','Selected','Rejected') DEFAULT 'Pending',
            UNIQUE KEY unique_application (student_id, internship_id),
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
            FOREIGN KEY (internship_id) REFERENCES internships(id) ON DELETE CASCADE
        )
    ");

    $adminEmail = getenv("ADMIN_EMAIL") ?: "admin@internshipportal.com";
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$adminEmail]);

    if (!$check->fetch()) {
        $hash = password_hash($adminPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,'admin')");
        $stmt->execute(["Portal Admin", $adminEmail, $hash]);
    }

    $count = $pdo->query("SELECT COUNT(*) FROM internships")->fetchColumn();

    if ((int)$count === 0) {
        $sample = $pdo->prepare("
            INSERT INTO internships
            (title,company,description,location,duration,stipend,skills_required,deadline)
            VALUES (?,?,?,?,?,?,?,?)
        ");

        $sample->execute([
            "Web Development Intern",
            "TechNova Solutions",
            "Work on responsive websites and small PHP based web applications.",
            "Ahmedabad",
            "3 Months",
            "₹10,000/month",
            "HTML, CSS, JavaScript, PHP",
            date("Y-m-d", strtotime("+30 days"))
        ]);

        $sample->execute([
            "Frontend Developer Intern",
            "Creative Labs",
            "Build modern user interfaces and interactive frontend components.",
            "Surat",
            "2 Months",
            "₹8,000/month",
            "HTML, CSS, JavaScript, AngularJS",
            date("Y-m-d", strtotime("+45 days"))
        ]);

        $sample->execute([
            "PHP & MySQL Intern",
            "CodeWorks India",
            "Assist in developing database-driven web applications using PHP and MySQL.",
            "Remote",
            "3 Months",
            "₹12,000/month",
            "PHP, MySQL, JavaScript",
            date("Y-m-d", strtotime("+60 days"))
        ]);
    }

    echo "<!doctype html><html><head><title>Setup Complete</title></head><body><h1>Setup completed successfully</h1><p>Database tables are ready. Admin email: " . htmlspecialchars($adminEmail, ENT_QUOTES, "UTF-8") . "</p><p>Remove SETUP_KEY from the Render environment and disable this setup endpoint now.</p><p><a href='index.html'>Open Student Internship Portal</a></p></body></html>";

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Setup failed: " . $e->getMessage());
    echo "Setup failed. Check the server logs and database permissions.";
}
?>