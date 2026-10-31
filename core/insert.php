<?php
require_once __DIR__ . "/dbconfig.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $position = trim($_POST["position"] ?? "");
    $education = trim($_POST["education"] ?? "");
    $skills = trim($_POST["skills"] ?? "");

    if ($full_name === "" || $email === "" || $position === "") {
        die("Please fill in your name, email, and position.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Please enter a valid email address.");
    }

    try {
        $sql = "INSERT INTO applications
                (full_name, email, phone, position, education, skills)
                VALUES
                (:full_name, :email, :phone, :position, :education, :skills)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":full_name" => $full_name,
            ":email" => $email,
            ":phone" => $phone,
            ":position" => $position,
            ":education" => $education,
            ":skills" => $skills
        ]);

        // Return to index.php inside the core folder
        header("Location: index.php");
        exit;

    } catch (PDOException $e) {
        error_log($e->getMessage());
        die("Unable to submit the application. Please check your database.");
    }

} else {
    header("Location: index.php");
    exit;
}
?>