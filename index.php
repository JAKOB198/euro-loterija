<?php

include_once 'baza.php';
include_once 'navigation.php';
if (!isset($_SESSION['trenutni_listek'])) {
    $_SESSION['trenutni_listek'] = [];
}
if (!isset($_SESSION['listki'])) {
    $_SESSION['listki'] = [];
}
if (!isset($_SESSION['navadne'])) {
    $_SESSION['navadne'] = [];
}
if (!isset($_SESSION['euro'])) {
    $_SESSION['euro'] = [];
}

if (isset($_GET['stevilka'])) {
    $stevilka = (int)$_GET['stevilka'];
} else {
    $stevilka = 0;
}

if (isset($_GET['tip'])) {
    $tip = $_GET['tip'];
} else {
    $tip = '';
}

if ($tip === 'navadna' && $stevilka >= 1 && $stevilka <= 50) {
    if (!in_array($stevilka, $_SESSION['navadne']) && count($_SESSION['navadne']) < 5) {
        $_SESSION['navadne'][] = $stevilka;
    }
} elseif ($tip === 'euro' && $stevilka >= 1 && $stevilka <= 12) {
    if (!in_array($stevilka, $_SESSION['euro']) && count($_SESSION['euro']) < 2) {
        $_SESSION['euro'][] = $stevilka;
    }
}

if (isset($_GET['reset'])) {
    $_SESSION['navadne'] = [];
    $_SESSION['euro'] = [];
}

if (isset($_GET['dodaj_listek']) && count($_SESSION['navadne']) === 5 && count($_SESSION['euro']) === 2) {
    $_SESSION['listki'][] = array_merge($_SESSION['navadne'], $_SESSION['euro']);
    $_SESSION['navadne'] = [];
    $_SESSION['euro'] = [];
}

if (isset($_GET['reset_all'])) {
    $_SESSION['listki'] = [];
    $_SESSION['trenutni_listek'] = [];
}

if (isset($_GET['random'])) {
    $vseNavadne = range(1, 50);
    shuffle($vseNavadne);
    $_SESSION['navadne'] = array_slice($vseNavadne, 0, 5);

    $vseEuro = range(1, 12);
    shuffle($vseEuro);
    $_SESSION['euro'] = array_slice($vseEuro, 0, 2);
}

if (!isset($_SESSION['zrebanja'])) {
    $_SESSION['zrebanja'] = 1;
}

if (isset($_GET['zrebanja']) && in_array((int)$_GET['zrebanja'], [1, 2, 3, 4, 5])) {
    $_SESSION['zrebanja'] = (int)$_GET['zrebanja'];
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index.css?v=1.0">
    <title>loterija</title>
</head>
<body>




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
            <?php
            for ($i = 1; $i <= 50; $i++) {
                $class = in_array($i, $_SESSION['navadne']) ? 'krog izbrana' : 'krog';

                echo '<a href="?stevilka=' . $i . '&tip=navadna">';
                echo '<p class="' . $class . '">' . $i . '</p>';
                echo '</a>';

                if ($i % 6 == 0) {
                    echo '<div class="clear"></div>';
                }
            }
            ?>
        </div>
    </div>
    <div class="main-desna">
        <div class="izbrane-stevilke">
            <h3>Trenutni listek:</h3>
            <p><strong>Navadne številke:</strong></p>
            <?php foreach ($_SESSION['navadne'] as $nav): ?>
                <span class="krog"><?= $nav ?></span>
            <?php endforeach; ?>

            <br><br>
            <p><strong>Euro številke:</strong></p>
            <?php foreach ($_SESSION['euro'] as $euro): ?>
                <span class="krog"><?= $euro ?></span>
            <?php endforeach; ?>

            <br><br>
            <a href="?reset=true"><button>Reset trenutnega</button></a>
            <a href="?dodaj_listek=true"><button>Dodaj listek</button></a>
            <a href="?random=true"><button>Naključno izberi</button></a>
        </div>
        <div class="clear"></div>

        <div class="vsi-listki">
            <h3>Vsi listki:</h3>
            <?php 
            foreach ($_SESSION['listki'] as $listek) {
                echo '<div>';
                echo 'Listek: ';
                foreach ($listek as $stevilka) {
                    echo '<span class="krog">' . $stevilka . '</span>';
                }
                echo '</div>';
            }
            ?>
        </div>

        <br>
        <a href="?reset_all=true"><button>Resetiraj vse</button></a>

        <form action="placilo.php" method="post">
            <input type="hidden" name="zrebanja" value="<?= $_SESSION['zrebanja'] ?>">
            <input type="hidden" name="listki" value='<?= json_encode($_SESSION['listki']) ?>'>
            <button type="submit">Plačilo</button>
        </form>
    </div>
</div>
<div class="clear"></div>

<div class="stevilke-euro">
    <?php 
    for ($i = 1; $i <= 12; $i++) {
        $class = in_array($i, $_SESSION['euro']) ? 'krog izbrana' : 'krog';

        echo '<a href="?stevilka=' . $i . '&tip=euro">';
        echo '<p class="' . $class . '">' . $i . '</p>';
        echo '</a>';

        if ($i % 6 == 0) {
            echo '<div class="clear"></div>';
        }
    }
    ?>
</div>

<div class="clear"></div>

<div class="footer">
    <p><strong>V koliko žrebanjih želite sodelovati?</strong></p>
    <div class="zrebanja-izbira">
        <?php
        for ($i = 1; $i <= 5; $i++) {
            $class = ($_SESSION['zrebanja'] === $i) ? 'krog izbrana' : 'krog';

            echo '<a href="?zrebanja=' . $i . '">';
            echo '<p class="' . $class . '">' . $i . '</p>';
            echo '</a>';
        }
        ?>
    </div>
</div>
<div class="clear"></div>
<?php include 'footer.php' ?>
</body>
</html>
