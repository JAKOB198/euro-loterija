
<?php
session_start();

// Inicializacija
if (!isset($_SESSION['trenutni_listek'])) {
    $_SESSION['trenutni_listek'] = [];
}
if (!isset($_SESSION['listki'])) {
    $_SESSION['listki'] = [];
}

// Dodaj številko
if (isset($_GET['stevilka'])) {
    $stevilka = $_GET['stevilka'];
    if (!in_array($stevilka, $_SESSION['trenutni_listek']) && count($_SESSION['trenutni_listek']) < 7) {
        $_SESSION['trenutni_listek'][] = $stevilka;
    }
}

// Resetiraj trenutni listek
if (isset($_GET['reset'])) {
    $_SESSION['trenutni_listek'] = [];
}

// Dodaj trenutni listek v seznam
if (isset($_GET['dodaj_listek']) && count($_SESSION['trenutni_listek']) === 7) {
    $_SESSION['listki'][] = $_SESSION['trenutni_listek'];
    $_SESSION['trenutni_listek'] = [];
}

// Po želji: počisti vse
if (isset($_GET['reset_all'])) {
    $_SESSION['listki'] = [];
    $_SESSION['trenutni_listek'] = [];
}

?>









<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="index.css" rel="stylesheet">
    <title>loterija</title>
</head>
<body>
    <div class="header">
  <div class="logo"><img src="slike/ejp_logo.png"></div>

 <div class="navigacija">
  <a href=""><p class="igraj-text">Igraj</p></a>
  <a href=""><p class="vseoigri-text">Vse o igri</p></a>
  <a href=""><p class="rezultati-text">Rezultati</p></a>
  <a href=""><p class="statistika-text">Statistika</p></a>

    <button>Prijava</button>
  <button>Registracija</button>

  
</div>

  <div class="clear"></div>
</div>
<div class="nacin-igranja">
<p>Izberite nacin igranja:</p>
<p>Izberi sam</p>
<p>Hitri izbor</p>
<p>Priljubljeni paketi</p>

</div>
<div class="clear"></div>

<div class="koraki">
<div class="prvi-korak">
<p class="krog">1</p>
<p class="napis">Igralni listek</p>

</div>
<div class="drugi-korak">
<p class="krog">2</p>
<p class="napis">Vplačilo</p>

</div>
<div class="clear"></div>
</div>
  <div class="main">
    <p>Izberi svojih 5 glavnih številk in 2 dodatni številki ali pa prepusti naključno izbiro računalniku</p>
  <div class="main-leva">
 <div class="stevilke-navadne">
    <?php for ($i = 1; $i <= 50; $i++): ?>
        <a href="?stevilka=<?= $i ?>">
            <p class="krog"><?= $i ?></p>
        </a>
        
        <?php if ($i % 6 == 0): ?>
            <div class="clear"></div>
        <?php endif; ?>
    <?php endfor; ?>
</div>
        </div>
        <div class="main-desna">
    <div class="izbrane-stevilke">
    <h3>Trenutni listek:</h3>
    <?php foreach ($_SESSION['trenutni_listek'] as $izbrana): ?>
        <span class="krog"><?= $izbrana ?></span>
    <?php endforeach; ?>
    <br><br>
    <a href="?reset=true"><button>Reset trenutnega</button></a>
    <a href="?dodaj_listek=true"><button>Dodaj listek</button></a>
</div>
<div class="clear"></div>

<div class="vsi-listki">
    <h3>Vsi listki:</h3>
    <?php foreach ($_SESSION['listki'] as $index => $listek): ?>
        <div>
            Listek <?= $index + 1 ?>:
            <?php foreach ($listek as $stevilka): ?>
                <span class="krog" ><?= $stevilka ?></span>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
    <br>
    <a href="?reset_all=true"><button>Resetiraj vse</button></a>
</div>
            </div>
            <div class="clear"></div>
<div class="stevilke-euro">
    <?php for ($i = 1; $i <= 12; $i++): ?>
        <a href="?stevilka=<?= $i ?>">
            <p class="krog"><?= $i ?></p>
        </a>
        
        <?php if ($i % 6 == 0): ?>
            <div class="clear"></div>
        <?php endif; ?>
    <?php endfor; ?>
</div>

</div>
</body>
</html>