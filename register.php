<?php
include_once 'baza.php';

$sporocilo = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ime = $_POST['ime'];
    $email = $_POST['email'];
    $geslo = $_POST['geslo'];
    $potrdi_geslo = $_POST['potrdi_geslo'];

   
    if ($geslo !== $potrdi_geslo) {
        $sporocilo = " Gesli se ne ujemata.";
    } else {
       
        $preglej_email = "SELECT * FROM uporabniki WHERE email = '$email'";
        $rezultat_email = mysqli_query($link, $preglej_email);

        if (mysqli_num_rows($rezultat_email) > 0) {
            $sporocilo = " Email že obstaja v bazi.";
        } else {
           
            $query = "INSERT INTO uporabniki (ime, email, geslo) 
                      VALUES ('$ime', '$email', '$geslo')";
            $rezultat = mysqli_query($link, $query);

            if ($rezultat) {
                $sporocilo = " Uporabnik uspešno vnešen. Preusmeritev...";
                header("refresh:3;url=login.php");
            } else {
                $sporocilo = " Napaka pri vnosu. Poskusi znova.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
     <link rel="stylesheet" href="css/register.css?v=1.0">
    <title>Vnos uporabnika</title>
</head>
<body>
<h1>Vnos novega uporabnika</h1>




<form method="post" action="">
    Ime<br>
    <input type="text" name="ime" required><br><br>

    Email<br>
    <input type="email" name="email" placeholder="E-Pošta" required><br><br>

    Geslo<br>
    <input type="password" name="geslo" placeholder="Geslo" required><br><br>

    Potrdi geslo<br>
    <input type="password" name="potrdi_geslo" placeholder="Ponovno geslo" required><br><br>

 
    <?php if (!empty($sporocilo)) : ?>
        <p  <?= $sporocilo ?>;>
            <?= $sporocilo ?>
        </p>
    <?php endif; ?>

    <input type="submit" name="submit" value="Vnesi">
</form>

<br>
<a href="index.php">Domov</a>
</body>
</html>
