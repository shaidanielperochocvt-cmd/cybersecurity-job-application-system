<?php
require_once __DIR__ . "/dbconfig.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

    if (!$id || $id < 1) {
        die("Invalid application ID.");
    }

    try {
        $sql = "DELETE FROM applications WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":id" => $id
        ]);

        header("Location: index.php");
        exit;

    } catch (PDOException $e) {
        error_log($e->getMessage());
        die("Error: Unable to delete the application.");
    }

} else {
    header("Location: index.php");
    exit;
}
?>