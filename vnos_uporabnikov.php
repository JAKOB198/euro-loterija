<?php
include_once 'baza.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ime = mysqli_real_escape_string($link, $_POST['ime']);
    $email = mysqli_real_escape_string($link, $_POST['email']);
    $geslo = password_hash($_POST['geslo'], PASSWORD_DEFAULT); // varno geslo

  
    $sql = "INSERT INTO uporabniki (ime, email, geslo, znesek_denarja) 
            VALUES ('$ime', '$email', '$geslo', 0)";

    if (mysqli_query($link, $sql)) {
        echo "Uporabnik uspešno dodan!";
    } else {
        echo "Napaka: " . mysqli_error($link);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Vnos uporabnika</title>
</head>
<body>
<h1>Vnos novega uporabnika</h1>
<form method="post" action="u_vbazo.php">
    Ime<br>
    <input type="text" name="ime" required><br><br>

    Email<br>
    <input type="email" name="email" placeholder="E-Pošta" required><br><br>

    Geslo<br>
    <input type="password" name="geslo" placeholder="Geslo" required><br><br>

    <input type="submit" name="submit" value="Vnesi">
</form>

<br>
<a href="index.php">Domov</a>
</body>
</html>
