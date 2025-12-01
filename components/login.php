<?php
require "db.php";
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = trim($_POST["nickname"]); // může být nickname nebo email
    $password = $_POST["password"];

    // Vyhledání uživatele podle nickname NEBO emailu
    $stmt = $pdo->prepare("SELECT * FROM users WHERE nickname = ? OR email = ?");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();

    // Zkontrolujeme heslo
    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Nesprávné přihlašovací údaje.";
    }
}
?>


<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přihlášení</title>
    <link rel="stylesheet" href="/style/style.css">
</head>
<body>
<main>
    <section>
        <div class="form">
            <div class="form-header">
                <img src="/img/apexlogo.png" alt="Logo">
                <h2>Přihlášení</h2>
            </div>

            <form method="post">
                <?php if($error): ?>
                    <p class="form-error"><?= $error ?></p>
                <?php endif; ?>

                <div class="form-main">
                    <div class="form-main-inputs">
                        <div class="form-main-inputs-label">
                            <label for="nickname">Uživatelské jméno nebo e-mail</label>
                        </div>
                        <input type="text" id="nickname" name="nickname" placeholder="Uživatelské jméno nebo e-mail" required>
                    </div>

                    <div class="form-main-inputs">
                        <div class="form-main-inputs-label">
                            <label for="password">Heslo</label>
                        </div>
                        <input type="password" id="password" name="password" placeholder="Heslo" required>
                    </div>
                </div>

                <button type="submit">Přihlásit se</button>
            </form>

            <div class="form-footer">
                <a href="register.php">
                    <span>Nemáte účet?</span>
                    Zaregistrujte se
                </a>
            </div>
        </div>
    </section>
</main>
</body>
</html>
