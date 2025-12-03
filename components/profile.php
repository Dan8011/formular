<?php
require "db.php";
session_start();

// DEBUG režim
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kontrola přihlášení
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Načtení uživatele
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$error = "";
$success = "";

// ------------------- ZPRACOVÁNÍ FORMULÁŘŮ -------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // ********** UPDATE PROFILU (nickname + avatar Base64) **********
    if (isset($_POST["update_profile"])) {

        $nickname = trim($_POST["nickname"]);
        $avatarBase64 = $user["avatar"]; // výchozí hodnota

        // Validace nicku
        if (!preg_match('/^[a-zA-Z0-9]+$/', $nickname)) {
            $error = "Uživatelské jméno smí obsahovat jen písmena a čísla.";
        } else {
            // Kontrola duplicity
            $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE nickname = ? AND id != ?");
            $stmtCheck->execute([$nickname, $user_id]);
            if ($stmtCheck->fetch()) {
                $error = "Toto uživatelské jméno již existuje.";
            }
        }

        // ********** AVATAR – Base64 z formuláře **********
        if (!$error && !empty($_POST['avatar_base64'])) {
            $avatarBase64Input = $_POST['avatar_base64'];
            if (strlen($avatarBase64Input) > 3 * 1024 * 1024) { // max ~3MB
                $error = "Soubor je příliš velký (max 2 MB).";
            } else {
                $avatarBase64 = $avatarBase64Input;
            }
        }

        if (!$error) {
            $stmtUpdate = $pdo->prepare("UPDATE users SET nickname=?, avatar=? WHERE id=?");
            $stmtUpdate->execute([$nickname, $avatarBase64, $user_id]);
            $success = "Profil byl úspěšně aktualizován.";

            // Reload uživatele
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        }
    }

    // ********** ZMĚNA EMAILU **********
    if (isset($_POST["change_email"])) {
        $newEmail = trim($_POST["new_email"]);
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $error = "Zadejte platný e-mail.";
        } else {
            $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmtCheck->execute([$newEmail, $user_id]);
            if ($stmtCheck->fetch()) {
                $error = "Tento e-mail je již používán.";
            } else {
                $stmtUpdate = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                $stmtUpdate->execute([$newEmail, $user_id]);
                $success = "E-mail byl úspěšně změněn.";
            }
        }
    }

    // ********** ZMĚNA HESLA **********
    if (isset($_POST["change_password"])) {
        $newPass = $_POST["new_password"];
        $confirmPass = $_POST["confirm_password"];
        if ($newPass !== $confirmPass) {
            $error = "Hesla se neshodují.";
        } elseif (strlen($newPass) < 6) {
            $error = "Heslo musí mít alespoň 6 znaků.";
        } else {
            $hash = password_hash($newPass, PASSWORD_DEFAULT);
            $stmtUpdate = $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?");
            $stmtUpdate->execute([$hash, $user_id]);
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

    <?php if ($error): ?>
        <p class="form-error"><?= htmlspecialchars($error) ?></p>
    <?php elseif ($success): ?>
        <p class="form-success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <div class="form-main profile-container">

        <!-- Avatar -->
        <div class="avatar-section">
            <?php if (!empty($user['avatar'])): ?>
                <img src="<?= $user['avatar'] ?>" class="profile-avatar">
            <?php else: ?>
                <div class="profile-avatar placeholder">No Avatar</div>
            <?php endif; ?>
        </div>

        <!-- Úprava profilu -->
        <form method="post" enctype="multipart/form-data" class="profile-form">

            <label>Uživatelské jméno</label>
            <input name="nickname" value="<?= htmlspecialchars($user['nickname']) ?>" required>

            <label>Avatar (soubor)</label>
            <input type="file" id="avatarInput" accept="image/*">
            <input type="hidden" name="avatar_base64" id="avatarBase64">

            <button type="submit" name="update_profile" class="profile-button">Uložit změny</button>
        </form>

        <script>
        document.getElementById('avatarInput').addEventListener('change', function(e){
            const file = e.target.files[0];
            if(!file) return;

            if(file.size > 2 * 1024 * 1024){
                alert("Soubor je příliš velký (max 2 MB).");
                e.target.value = "";
                return;
            }

            const allowedTypes = ["image/png","image/jpeg","image/jpg","image/webp"];
            if(!allowedTypes.includes(file.type)){
                alert("Avatar musí být obrázek (PNG, JPG, WEBP).");
                e.target.value = "";
                return;
            }

            const reader = new FileReader();
            reader.onload = function(){
                document.getElementById('avatarBase64').value = reader.result;
            };
            reader.readAsDataURL(file);
        });
        </script>

        <!-- Změna e-mailu -->
        <form method="post" class="profile-form">
            <label>Nový e-mail</label>
            <input name="new_email" type="email" placeholder="Nový e-mail" required>
            <button type="submit" name="change_email" class="profile-button">Změnit e-mail</button>
        </form>

        <!-- Změna hesla -->
        <form method="post" class="profile-form">
            <label>Nové heslo</label>
            <input name="new_password" type="password" placeholder="Nové heslo" required>

            <label>Potvrzení hesla</label>
            <input name="confirm_password" type="password" placeholder="Potvrď heslo" required>

            <button type="submit" name="change_password" class="logout-button">Změnit heslo</button>
        </form>

        <a href="dashboard.php" class="profile-button" style="margin-top:20px;">Zpět na dashboard</a>

    </div>
</div>
</section>
</main>
</body>
</html>
