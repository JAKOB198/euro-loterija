<?php
session_start();
include_once 'baza.php';

$sporocilo = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $geslo = $_POST['geslo'];

    
    $email = mysqli_real_escape_string($link, $email);
    $geslo = mysqli_real_escape_string($link, $geslo);

    $query = "SELECT * FROM uporabniki WHERE email = '$email'";
    $result = mysqli_query($link, $query);

    if ($result && mysqli_num_rows($result) == 1) {
        $uporabnik = mysqli_fetch_assoc($result);

        if ($geslo === $uporabnik['geslo']) {
            $_SESSION['uporabnik'] = $uporabnik['ime'];
            $_SESSION['id_u'] = $uporabnik['id_u'];
            $_SESSION['tip'] = $uporabnik['tip'];
            $_SESSION['denar'] = $uporabnik['znesek_denarja'];

            if ($uporabnik['tip'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $sporocilo = " Napačno geslo.";
        }
    } else {
        $sporocilo = " Uporabnik s tem emailom ne obstaja.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/login.css">
    <title>Prijava</title>
</head>
<body>
<h1>Prijava uporabnika</h1>
<?php if (isset($_GET['napaka']) && $_GET['napaka'] == 1): ?>
    <div class="opozorilo"> Najprej se moraš prijaviti, da lahko dostopaš do te strani.</div>
<?php endif; ?>



<form method="post" action="">
    <?php if (!empty($sporocilo)) : ?>
    <p class="sporocilo" <?= $sporocilo  ?>;>
        <?= $sporocilo ?>
    </p>
<?php endif; ?>
    Email<br>
    <input type="email" name="email" placeholder="E-Pošta" required><br><br>

    Geslo<br>
    <input type="password" name="geslo" placeholder="Geslo" required><br><br>

    <input type="submit" value="Prijava">
    <br>
<a href="index.php">Domov</a>
<a href="register.php">Registracija</a>
</form>


</body>
</html>
