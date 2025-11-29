<?php
session_start();
require __DIR__ . "/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Načteme info o uživateli
$stmt = $pdo->prepare("SELECT nickname FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/style/style.css">
</head>
<body>
<main>
    <section>
        <div class="form">
            <div class="form-header">
                <img src="/img/apexlogo.png" alt="LogoApex">
                <h2>Welcome, <?= htmlspecialchars($user['nickname']) ?>!</h2>
            </div>

            <div class="form-main dashboard-buttons">
                <a href="profile.php" class="dashboard-button profile-button">Můj profil</a>
                <a href="logout.php" class="dashboard-button logout-button">Odhlásit se</a>
            </div>
        </div>
    </section>
</main>

