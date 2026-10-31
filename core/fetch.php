<?php
require_once __DIR__ . "/dbconfig.php";

$stmt = $pdo->prepare("SELECT * FROM applications");

$stmt->execute();

$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);