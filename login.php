<?php
session_start();
include_once 'baza.php';

$sporocilo = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $geslo = $_POST['geslo'];

    // Poišči uporabnika po emailu
    $query = "SELECT * FROM uporabniki WHERE email = '$email'";
    $result = mysqli_query($link, $query);

    if ($result && mysqli_num_rows($result) == 1) {
        $uporabnik = mysqli_fetch_assoc($result);

        // Preveri geslo (če uporabljaš password_hash)
        // if (password_verify($geslo, $uporabnik['geslo'])) {
        if ($geslo === $uporabnik['geslo']) { // navadno primerjanje (če nisi uporabljal password_hash)

        $_SESSION['uporabnik'] = $uporabnik['ime'];
$_SESSION['id_u'] = $uporabnik['id_u']; // Dodano!

header("Location: index.php");
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
