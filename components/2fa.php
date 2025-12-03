<?php
require "db.php";
session_start();

$error = "";

// Zkontrolujeme, zda máme uživatele v session
if (!isset($_SESSION["2fa_user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["2fa_user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $code = trim($_POST["code"]);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND 2fa_code = ?");
    $stmt->execute([$user_id, $code]);
    $user = $stmt->fetch();

    if ($user) {
        $current_time = date("Y-m-d H:i:s");
        if ($current_time > $user["2fa_expires"]) {
            $error = "Kód vypršel, prosím přihlaste se znovu.";
        } else {
            // Úspěšná 2FA, nastavíme session
            $_SESSION["user_id"] = $user["id"];
            // Vyčistíme 2FA kód
            $stmt = $pdo->prepare("UPDATE users SET 2fa_code = NULL, 2fa_expires = NULL WHERE id = ?");
            $stmt->execute([$user["id"]]);
            // Odstraníme session proměnnou pro 2FA
            unset($_SESSION["2fa_user_id"]);
            header("Location: dashboard.php");
            exit;
        }
    } else {
        $error = "Nesprávný kód.";
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Ověření</title>
    <link rel="stylesheet" href="/style/style.css">
</head>
<body>
<main>
    <section>
        <div class="form">
            <div class="form-header">
                <h2>Dvoufázové ověření</h2>
                <p>Zadejte kód, který jsme vám poslali e-mailem.</p>
            </div>

            <form method="post">
                <?php if($error): ?>
                    <p class="form-error"><?= $error ?></p>
                <?php endif; ?>

                <div class="form-main">
                    <div class="form-main-inputs">
                        <div class="form-main-inputs-label">
                            <label for="code">6místný kód</label>
                        </div>
                        <input type="text" id="code" name="code" placeholder="Zadejte kód" maxlength="6" required>
                    </div>
                </div>

                <button type="submit">Ověřit</button>
            </form>

            <div class="form-footer">
                <a href="login.php">Zpět na přihlášení</a>
            </div>
        </div>
    </section>
</main>
</body>
</html>
