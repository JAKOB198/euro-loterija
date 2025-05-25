<?php
include_once 'baza.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pridobimo podatke iz obrazca in jih zaščitimo
    $ime = mysqli_real_escape_string($link, $_POST['ime']);
    $email = mysqli_real_escape_string($link, $_POST['email']);
    $geslo = password_hash($_POST['geslo'], PASSWORD_DEFAULT); // varno geslo
    $znesek = 0; // začetni znesek denarja

    // SQL poizvedba za vstavljanje novega uporabnika
    $query = "INSERT INTO uporabniki (ime, email, geslo, znesek_denarja)
              VALUES ('$ime', '$email', '$geslo', $znesek)";

    if (mysqli_query($link, $query)) {
        echo "✅ Uporabnik uspešno dodan. Preusmeritev v 3 sekundah...";
        header("refresh:3;url=izpis_uporabnikov.php"); // ali druga ustrezna stran
    } else {
        echo "❌ Napaka pri vnosu: " . mysqli_error($link);
    }
}
?>
