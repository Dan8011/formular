<?php
// Zobrazování chyb pro debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nickname = trim($_POST["nickname"]);
    $password = $_POST["password"];

    // validace nickname (alfanumerický)
    if (!preg_match('/^[a-zA-Z0-9]+$/', $nickname)) {
        $error = "Uživatelské jméno smí obsahovat jen písmena a čísla.";
    }
    // validace délky hesla (min 6 znaků)
    elseif (strlen($password) < 6) {
        $error = "Heslo musí mít alespoň 6 znaků.";
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (nickname, password_hash) VALUES (?, ?)");

        try {
            $stmt->execute([$nickname, $passwordHash]);
            // po úspěšné registraci přesměrování na login
            header("Location: login.php");
            exit;
        } catch (PDOException $e) {
            // pokud už nickname existuje
            if ($e->getCode() == 23000) { // duplicate entry
                $error = "Toto uživatelské jméno již existuje.";
            } else {
                $error = "Chyba databáze: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Registrace</title>
</head>
<body>

<h2>Registrace</h2>

<form method="post">
    <input name="nickname" placeholder="Uživatelské jméno" required><br><br>
    <input name="password" type="password" placeholder="Heslo" required><br><br>
    <button type="submit">Registrovat</button>
</form>

<p style="color:red;">
    <?= $error ?>
</p>

</body>
</html>
