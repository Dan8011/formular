<?php
require "db.php";
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // změna nicku a emailu
    if (isset($_POST["update_profile"])) {
        $nickname = $_POST["nickname"];
        $email = $_POST["email"];

        $stmt = $pdo->prepare("UPDATE users SET nickname=?, email=? WHERE id=?");
        $stmt->execute([$nickname, $email, $user_id]);
    }

    // změna hesla
    if (isset($_POST["change_password"])) {
        $newPass = $_POST["new_password"];
        $hash = password_hash($newPass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?");
        $stmt->execute([$hash, $user_id]);
    }

    // změna avataru
    if (!empty($_FILES["avatar"]["name"])) {
        $file = "uploads/" . uniqid() . "_" . $_FILES["avatar"]["name"];
        move_uploaded_file($_FILES["avatar"]["tmp_name"], $file);

        $stmt = $pdo->prepare("UPDATE users SET avatar=? WHERE id=?");
        $stmt->execute([$file, $user_id]);
    }

    header("Location: profile.php");
    exit;
}
?>
<h2>Můj profil</h2>
<img src="<?= $user['avatar'] ?>" width="80"><br>

<form method="post" enctype="multipart/form-data">
  <input name="nickname" value="<?= $user['nickname'] ?>" required>
  <input name="email" value="<?= $user['email'] ?>" required>
  <input type="file" name="avatar">
  <button name="update_profile">Uložit změny</button>
</form>

<h3>Změna hesla</h3>
<form method="post">
  <input name="new_password" type="password" placeholder="Nové heslo" required>
  <button name="change_password">Změnit heslo</button>
</form>
