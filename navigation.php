<?php
session_start();
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/navigation.css">
    <title>loterija</title>
</head>
<body>
<div class="header">
    <div class="logo">
        <img src="slike/ejp_logo.png" alt="Logo" height="50">
    </div>

    <div class="navigacija">
        <a href="index.php"><p class="igraj-text">Igraj</p></a>
        <a href="vseoigri.php"><p class="vseoigri-text">Vse o igri</p></a>
        <a href="rezultati.php"><p class="rezultati-text">Rezultati</p></a>
        <a href="mojilistki.php"><p class="statistika-text">Moji listki</p></a>
    </div>

   <div class="uporabnik">
    <?php if (isset($_SESSION['uporabnik'])): ?>
        <span>
            Pozdravljen, <strong><?= $_SESSION['uporabnik'] ?></strong> |
            Denar na računu: <strong><?= $_SESSION['denar']  ?> €</strong>
        </span>

        <?php if ($_SESSION['tip'] === 'admin'): ?>
            <a href="admin.php"><button class="btn-registracija">Admin Panel</button></a>
        <?php endif; ?>

        <a href="logout.php"><button class="btn-odjava">Odjava</button></a>
    <?php else: ?>
        <a href="login.php"><button class="btn-prijava">Prijava</button></a>
        <a href="register.php"><button class="btn-registracija">Registracija</button></a>
    <?php endif; ?>
</div>


    <div class="clear"></div>
</div>


  <div class="clear"></div>

<div class="nacin-igranja">
  <p>izberite nacin igranja: </p>
  <a href="index.php">igraj sam</a>
</div>
<div class="clear"></div>