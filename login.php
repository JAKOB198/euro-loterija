<?php
session_start();
include_once 'baza.php';

$sporocilo = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $geslo = $_POST['geslo'];

    $query = "SELECT * FROM uporabniki WHERE email = '$email'";
    $result = mysqli_query($link, $query);

    if ($result && mysqli_num_rows($result) == 1) {
        $uporabnik = mysqli_fetch_assoc($result);

        if ($geslo === $uporabnik['geslo']) {

            // ✅ Shranimo podatke v sejo
            $_SESSION['uporabnik'] = $uporabnik['ime'];
            $_SESSION['id_u'] = $uporabnik['id_u'];
            $_SESSION['tip'] = $uporabnik['tip']; // ⬅️ SPREMEMBA

            // ✅ Preverimo tip uporabnika
            if ($uporabnik['tip'] === 'admin') {
                header("Location: admin.php"); // ⬅️ SPREMEMBA
            } else {
                header("Location: index.php");  // navaden uporabnik
            }
            exit;

        } else {
            $sporocilo = "❌ Napačno geslo.";
        }
    } else {
        $sporocilo = "❌ Uporabnik s tem emailom ne obstaja.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="login.css">
    <title>Prijava</title>
</head>
<body>
<h1>Prijava uporabnika</h1>

<?php if (!empty($sporocilo)) : ?>
    <p style="color: <?= str_contains($sporocilo, '✅') ? 'green' : 'red' ?>;"><?= $sporocilo ?></p>
<?php endif; ?>

<form method="post" action="">
    Email<br>
    <input type="email" name="email" placeholder="E-Pošta" required><br><br>

    Geslo<br>
    <input type="password" name="geslo" placeholder="Geslo" required><br><br>

    <input type="submit" value="Prijava">
</form>

<br>
<a href="index.php">Domov</a>
</body>
</html>
