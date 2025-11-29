<?php
require "db.php";
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nickname = trim($_POST["nickname"]);
    $password = $_POST["password"];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE nickname = ?");
    $stmt->execute([$nickname]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user["password_hash"])) {
        $_SESSION["user_id"] = $user["id"];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Nesprávné uživatelské jméno nebo heslo.";
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
                            <label for="nickname">Uživatelské jméno</label>
                        </div>
                        <input type="text" id="nickname" name="nickname" placeholder="Uživatelské jméno" required>
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
