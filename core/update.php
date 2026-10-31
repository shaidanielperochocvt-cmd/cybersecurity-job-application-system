<?php
require_once __DIR__ . "/dbconfig.php";

function escape($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $position = trim($_POST["position"] ?? "");
    $education = trim($_POST["education"] ?? "");
    $skills = trim($_POST["skills"] ?? "");

    if (!$id || $id < 1 ||
        $full_name === "" ||
        $email === "" ||
        $position === "") {
        die("Please provide a valid ID and fill in the required fields.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Please enter a valid email address.");
    }

    try {
        $sql = "UPDATE applications SET
                full_name = :full_name,
                email = :email,
                phone = :phone,
                position = :position,
                education = :education,
                skills = :skills
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":full_name" => $full_name,
            ":email" => $email,
            ":phone" => $phone,
            ":position" => $position,
            ":education" => $education,
            ":skills" => $skills,
            ":id" => $id
        ]);

        // Redirect to index.php inside the core folder
        header("Location: index.php");
        exit;

    } catch (PDOException $e) {
        error_log($e->getMessage());
        die("Unable to update the application. Please check your database.");
    }
}

// Get the selected application for editing
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    die("Invalid application ID.");
}

$stmt = $pdo->prepare(
    "SELECT * FROM applications WHERE id = :id"
);

$stmt->execute([":id" => $id]);

$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    die("Application not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Cybersecurity Application</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #f4f6f9;
        }

        .form-container {
            max-width: 600px;
            padding: 20px;
            background: white;
            border-radius: 8px;
        }

        h2 {
            color: #17365d;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin: 8px 0 15px;
            box-sizing: border-box;
        }

        button {
            padding: 10px 15px;
            cursor: pointer;
        }
    </style>
</head>

<body>

<div class="form-container">

    <h2>Edit Cybersecurity Application</h2>

    <form action="update.php" method="POST">

        <input type="hidden" name="id"
               value="<?= escape($application['id']) ?>">

        <label>Full Name:</label>
        <input type="text" name="full_name" required
               value="<?= escape($application['full_name']) ?>">

        <label>Email:</label>
        <input type="email" name="email" required
               value="<?= escape($application['email']) ?>">

        <label>Phone:</label>
        <input type="text" name="phone"
               value="<?= escape($application['phone'] ?? '') ?>">

        <label>Position Applied For:</label>
        <select name="position" required>
            <option value="">Select a position</option>

            <option value="Cybersecurity Analyst"
                <?= $application['position'] === 'Cybersecurity Analyst' ? 'selected' : '' ?>>
                Cybersecurity Analyst
            </option>

            <option value="Penetration Tester"
                <?= $application['position'] === 'Penetration Tester' ? 'selected' : '' ?>>
                Penetration Tester
            </option>

            <option value="Security Engineer"
                <?= $application['position'] === 'Security Engineer' ? 'selected' : '' ?>>
                Security Engineer
            </option>

            <option value="Network Security Specialist"
                <?= $application['position'] === 'Network Security Specialist' ? 'selected' : '' ?>>
                Network Security Specialist
            </option>
        </select>

        <label>Educational Background:</label>
        <input type="text" name="education"
               value="<?= escape($application['education'] ?? '') ?>">

        <label>Cybersecurity Skills:</label>
        <textarea name="skills" rows="4"><?= escape($application['skills'] ?? '') ?></textarea>

        <button type="submit">Save Changes</button>

    </form>

    <p><a href="index.php">Back to Applications</a></p>

</div>

</body>
</html>