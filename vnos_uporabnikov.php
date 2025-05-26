<?php
include_once 'baza.php';

$sporocilo = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ime = $_POST['ime'];
    $email = $_POST['email'];
    $geslo = $_POST['geslo'];
    $potrdi_geslo = $_POST['potrdi_geslo'];

    // Preveri ujemanje gesel
    if ($geslo !== $potrdi_geslo) {
        $sporocilo = "❌ Gesli se ne ujemata.";
    } else {
        // Preveri ali e-mail že obstaja
        $check_email = "SELECT * FROM uporabniki WHERE email = '$email'";
        $result_check = mysqli_query($link, $check_email);

        if (mysqli_num_rows($result_check) > 0) {
            $sporocilo = "❌ Email že obstaja v bazi.";
        } else {
            // Vstavi uporabnika
            $query = "INSERT INTO uporabniki (ime, email, geslo) 
                      VALUES ('$ime', '$email', '$geslo')";
            $result = mysqli_query($link, $query);

            if ($result) {
                $sporocilo = "✅ Uporabnik uspešno vnešen. Preusmeritev...";
                header("refresh:3;url=login.php");
            } else {
                $sporocilo = "❌ Napaka pri vnosu. Poskusi znova.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
     <link rel="stylesheet" href="register.css?v=1.0">
    <title>Vnos uporabnika</title>
</head>
<body>
<h1>Vnos novega uporabnika</h1>

<!-- Prikaz sporočila -->


<form method="post" action="">
    Ime<br>
    <input type="text" name="ime" required><br><br>

    Email<br>
    <input type="email" name="email" placeholder="E-Pošta" required><br><br>

    Geslo<br>
    <input type="password" name="geslo" placeholder="Geslo" required><br><br>

    Potrdi geslo<br>
    <input type="password" name="potrdi_geslo" placeholder="Ponovno geslo" required><br><br>

    <!-- Prikaz sporočila (premaknjen sem) -->
    <?php if (!empty($sporocilo)) : ?>
        <p style="color: <?= str_contains($sporocilo, '✅') ? 'green' : 'red' ?>;">
            <?= $sporocilo ?>
        </p>
    <?php endif; ?>

    <input type="submit" name="submit" value="Vnesi">
</form>

<br>
<a href="index.php">Domov</a>
</body>
</html>
