<?php
require "db.php";
session_start();

// Zobrazení všech chyb pro debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kontrola, zda je uživatel přihlášen
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Načtení dat uživatele
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$error = "";
$success = "";

// Zpracování formulářů
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // --------------------------
    // Aktualizace profilu (nick + avatar)
    // --------------------------
    if (isset($_POST["update_profile"])) {

        $nickname = trim($_POST["nickname"]);

        // Kontrola, zda uživatelské jméno už neexistuje (kromě aktuálního uživatele)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE nickname = ? AND id != ?");
        $stmt->execute([$nickname, $user_id]);
        if ($stmt->fetch()) {
            $error = "Toto uživatelské jméno již existuje. Zvol jiné.";
        } else {
            // Aktualizace nicku
            $stmt = $pdo->prepare("UPDATE users SET nickname=? WHERE id=?");
            $stmt->execute([$nickname, $user_id]);

            // Změna avataru
            if (!empty($_FILES["avatar"]["name"])) {

                if (!is_dir('uploads')) {
                    mkdir('uploads', 0777, true);
                }

                $fileName = basename($_FILES["avatar"]["name"]);
                $targetFile = "uploads/" . uniqid() . "_" . $fileName;

                if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $targetFile)) {
                    $stmt = $pdo->prepare("UPDATE users SET avatar=? WHERE id=?");
                    $stmt->execute([$targetFile, $user_id]);
                } else {
                    $error = "Nahrání avataru se nezdařilo.";
                }
            }

            if (!$error) {
                $success = "Profil byl úspěšně aktualizován.";
            }

            // Načtení aktualizovaných dat
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        }
    }

    // --------------------------
    // Změna hesla
    // --------------------------
    if (isset($_POST["change_password"])) {
        $newPass = $_POST["new_password"];
        $confirmPass = $_POST["confirm_password"];

        if ($newPass !== $confirmPass) {
            $error = "Hesla se neshodují.";
        } else {
            $hash = password_hash($newPass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?");
            $stmt->execute([$hash, $user_id]);
            $success = "Heslo bylo úspěšně změněno.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Můj profil</title>
    <link rel="stylesheet" href="/style/style.css">
</head>
<body>
<main>
    <section>
        <div class="form">
            <div class="form-header">
                <img src="/img/apexlogo.png" alt="LogoApex">
                <h2>Můj profil: <?= htmlspecialchars($user['nickname']) ?></h2>
            </div>

            <?php if($error): ?>
                <p class="form-error"><?= htmlspecialchars($error) ?></p>
            <?php elseif($success): ?>
                <p class="form-success"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>

            <div class="form-main profile-container">
                <!-- Avatar -->
                <div class="avatar-section">
                    <?php if($user['avatar']): ?>
                        <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" class="profile-avatar">
                    <?php else: ?>
                        <div class="profile-avatar placeholder">No Avatar</div>
                    <?php endif; ?>
                </div>

                <!-- Úprava profilu -->
                <form method="post" enctype="multipart/form-data" class="profile-form">
                    <label>Uživatelské jméno</label>
                    <input name="nickname" value="<?= htmlspecialchars($user['nickname']) ?>" required>

                    <label>Avatar</label>
                    <input type="file" name="avatar">

                    <button type="submit" name="update_profile" class="profile-button">Uložit změny</button>
                </form>

                <!-- Tlačítko zpět na dashboard -->
                <a href="dashboard.php" class="profile-button">Zpět na dashboard</a>

                <!-- Změna hesla -->
                <form method="post" class="profile-form" style="margin-top: 20px;">
                    <label>Nové heslo</label>
                    <input name="new_password" type="password" placeholder="Nové heslo" required>

                    <label>Potvrzení hesla</label>
                    <input name="confirm_password" type="password" placeholder="Potvrď heslo" required>

                    <button type="submit" name="change_password" class="logout-button">Změnit heslo</button>
                </form>
            </div>
        </div>
    </section>
</main>
</body>
</html>
