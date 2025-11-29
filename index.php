<?php
session_start();


require __DIR__ . "/components/db.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nickname = trim($_POST["nickname"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $terms = isset($_POST["terms"]);

    if (!preg_match('/^[a-zA-Z0-9]+$/', $nickname)) {
        $error = "Uživatelské jméno smí obsahovat jen písmena a čísla.";
    } elseif (strlen($password) < 6) {
        $error = "Heslo musí mít alespoň 6 znaků.";
    } elseif ($password !== $confirm_password) {
        $error = "Hesla se neshodují.";
    } elseif (!$terms) {
        $error = "Musíte souhlasit s podmínkami.";
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (nickname, password_hash) VALUES (?, ?)");
        try {
            $stmt->execute([$nickname, $passwordHash]);
            $success = "Registrace proběhla úspěšně. Můžete se přihlásit.";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrace</title>
    <link rel="stylesheet" href="/style/style.css">
</head>
<body>
<main>
    <section>
        <div class="form">
            <div class="form-header">
                <img src="/img/apexlogo.png" alt="LogoApex">
                <h2>Založte si účet</h2>
            </div>

            
            <form action="" method="post">
                <?php if($error): ?>
                    <p class="form-error"><?= $error ?></p>
                <?php endif; ?>

                <div class="form-main">
                    <div class="form-main-inputs">
                        <div class="form-main-inputs-label">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            <label for="nickname">Uživatelské jméno</label>
                        </div>
                        <input type="text" id="nickname" name="nickname" placeholder="Uživatelské jméno (pouze alfanumerické)" required>
                    </div>

                    <div class="form-main-inputs">
                        <div class="form-main-inputs-label">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                            <label for="password">Heslo</label>
                        </div>
                        <input type="password" id="password" name="password" placeholder="Zadejte své heslo (min. 6 znaků)" required>
                    </div>

                    <div class="form-main-inputs">
                        <div class="form-main-inputs-label">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                            <label for="confirm_password">Potvrdit heslo</label>
                        </div>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Potvrďte své heslo" required>
                    </div>
                </div>

                <div class="form-control">
                    <input type="checkbox" name="terms" id="terms" required>
                    <label for="terms">
                        Souhlasím s <a href="#">podmínky služby a zásady ochrany osobních údajů</a>
                    </label>
                </div>

                <div class="form-buttons">
                    <button type="submit">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                        </svg>
                        Založit účet
                    </button>
                </div>
            </form>

            <div class="form-footer">
                <a href="/components/login.php">
                    <span>Už máte účet?</span>
                    Přihlásit se
                </a>
            </div>
        </div>
    </section>
</main>
</body>
</html>

