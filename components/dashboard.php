<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
?>
<h1>Dashboard</h1>
<a href="profile.php">Můj profil</a><br>
<a href="logout.php">Odhlásit</a>
