<?php
require "db.php";
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nickname = trim($_POST["nickname"]);
    $password = $_POST["password"];

    // najdeme uživatele podle nickname
    $stmt = $pdo->prepare("SELECT * FROM users WHERE nickname = ?");
    $stmt->execute([$nickname]);
    $user = $stmt->fetch();

    // kontrola hesla
    if ($user && password_verify($password, $user["password_hash"])) {
        // uložíme id uživatele do session
        $_SESSION["user_id"] = $user["id"];
        // přesměrování na dashboard
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
    <title>Přihlášení</title>
</head>
<body>

<h2>Přihlášení</h2>

<form method="post">
    <input type="text" name="nickname" placeholder="Uživatelské jméno" required><br><br>
    <input type="password" name="password" placeholder="Heslo" required><br><br>
    <button type="submit">Přihlásit se</button>
</form>

<p style="color:red;">
    <?= $error ?>
</p>

</body>
</html>
