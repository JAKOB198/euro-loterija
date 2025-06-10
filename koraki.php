<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Eurojackpot - Noga</title>
   <link href="koraki.css" rel="stylesheet">
</head>
<body>


<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="koraki">
    <div class="prvi-korak">
        <p class="krog <?= $current_page === 'index.php' ? 'aktiven' : '' ?>">1</p>
        <p class="napis">Igralni listek</p>
    </div>
    <div class="drugi-korak">
        <p class="krog <?= $current_page === 'placilo.php' ? 'aktiven' : '' ?>">2</p>
        <p class="napis">Vplačilo</p>
    </div>
    <div class="clear"></div>
</div>

</html>
