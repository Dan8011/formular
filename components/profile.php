<?php
require "db.php";
session_start();

// Zobrazení všech chyb pro debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kontrola přihlášení
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

    // Úprava profilu (nickname + avatar URL)
    if (isset($_POST["update_profile"])) {

        $nickname = trim($_POST["nickname"]);
        $avatarUrl = trim($_POST["avatar_url"]);

        // Validace nickname (pouze písmena a čísla)
        if (!preg_match('/^[a-zA-Z0-9]+$/', $nickname)) {
            $error = "Uživatelské jméno smí obsahovat jen písmena a čísla.";
        } else {
            // Kontrola duplicity nickname
            $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE nickname = ? AND id != ?");
            $stmtCheck->execute([$nickname, $user_id]);
            if ($stmtCheck->fetch()) {
                $error = "Toto uživatelské jméno již existuje.";
            }
        }

        // Validace URL avataru
        if (!$error && $avatarUrl !== "" && !filter_var($avatarUrl, FILTER_VALIDATE_URL)) {
            $error = "Zadejte platnou URL obrázku avataru.";
        }

        // Pokud není chyba, uložíme změny
        if (!$error) {
            $stmtUpdate = $pdo->prepare("UPDATE users SET nickname = ?, avatar = ? WHERE id = ?");
            $stmtUpdate->execute([$nickname, $avatarUrl, $user_id]);
            $success = "Profil byl úspěšně aktualizován.";

            // Aktualizace dat uživatele
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        }
    }

    // Změna e-mailu
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

                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
            }
        }
    }

    // Změna hesla
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Můj profil</title>
    <link rel="stylesheet" href="/style/style.css" />
</head>
<body>
<main>
    <section>
        <div class="form">
            <div class="form-header">
                <img src="/img/apexlogo.png" alt="LogoApex" />
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
                    <?php
                    // Pokud je URL avataru platná, zobrazí se obrázek
                    $avatarUrl = $user['avatar'] ?? '';
                    if (!empty($avatarUrl) && filter_var($avatarUrl, FILTER_VALIDATE_URL)): ?>
                        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" class="profile-avatar" />
                    <?php else: ?>
                        <div class="profile-avatar placeholder">No Avatar</div> <!-- Placeholder pro případ, že není URL -->
                    <?php endif; ?>
                </div>

                <!-- Úprava profilu -->
                <form method="post" class="profile-form">
                    <label>Uživatelské jméno</label>
                    <input name="nickname" value="<?= htmlspecialchars($user['nickname']) ?>" required />

                    <label>URL avataru</label>
                    <input
                        name="avatar_url"
                        type="url"
                        placeholder="https://example.com/avatar.png"
                        value="<?= htmlspecialchars($user['avatar']) ?>"
                    />

                    <button type="submit" name="update_profile" class="profile-button">Uložit změny</button>
                </form>

                <!-- Změna e-mailu -->
                <form method="post" class="profile-form" style="margin-top: 20px;">
                    <label>Nový e-mail</label>
                    <input name="new_email" type="email" placeholder="Nový e-mail" required />

                    <button type="submit" name="change_email" class="profile-button">Změnit e-mail</button>
                </form>

                <!-- Změna hesla -->
                <form method="post" class="profile-form" style="margin-top: 20px;">
                    <label>Nové heslo</label>
                    <input name="new_password" type="password" placeholder="Nové heslo" required />

                    <label>Potvrzení hesla</label>
                    <input name="confirm_password" type="password" placeholder="Potvrď heslo" required />

                    <button type="submit" name="change_password" class="logout-button">Změnit heslo</button>
                </form>

                <a href="dashboard.php" class="profile-button" style="margin-top: 20px;">Zpět na dashboard</a>

            </div>
        </div>
    </section>
</main>
</body>
</html>
