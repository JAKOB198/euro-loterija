<?php
include_once 'baza.php';
include 'navigation.php';
date_default_timezone_set('Europe/Ljubljana');

if (!isset($_SESSION['id_u'])) {
    header("Location: login.php?napaka=1");
    exit;
}

$id_u = (int)$_SESSION['id_u'];
define('CENA_NA_LISTEK', 2.5);


$zrebanja = 1;
if (isset($_POST['zrebanja'])) {
    $zrebanja = (int)$_POST['zrebanja'];
}


$listki = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['listek'])) {
    $listki = $_POST['listek'];
} elseif (isset($_SESSION['listki'])) {
    $listki = $_SESSION['listki'];
}


if (!isset($_SESSION['listki'])) {
    $_SESSION['listki'] = $listki;
}

$stevilo_listkov = count($listki);
$skupni_znesek = $stevilo_listkov * CENA_NA_LISTEK * $zrebanja;


$datumi_zrebanj = [];
$sql = "SELECT datum_zrebanja FROM zrebanja WHERE datum_zrebanja > NOW() ORDER BY datum_zrebanja ASC LIMIT " . $zrebanja;
$rezultat = mysqli_query($link, $sql);
while ($vrstica = mysqli_fetch_assoc($rezultat)) {
    $cas = strtotime($vrstica['datum_zrebanja']);
    $datumi_zrebanj[] = date('d. m. Y H:i', $cas);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['placaj'])) {
  
    $sql = "SELECT znesek_denarja FROM uporabniki WHERE id_u = $id_u";
    $rezultat = mysqli_query($link, $sql);
    $vrstica = mysqli_fetch_assoc($rezultat);
    $trenutni_denar = (float)$vrstica['znesek_denarja'];

    if ($trenutni_denar < $skupni_znesek) {
        die("Nimate dovolj denarja.");
    }

    
    $id_z_list = [];
    $sql = "SELECT id_z FROM zrebanja WHERE datum_zrebanja > NOW() ORDER BY datum_zrebanja ASC LIMIT " . $zrebanja;
    $rezultat = mysqli_query($link, $sql);
    while ($vrstica = mysqli_fetch_assoc($rezultat)) {
        $id_z_list[] = $vrstica['id_z'];
    }

    if (count($id_z_list) < $zrebanja) {
        die("❌ Ni dovolj prihajajočih žrebanj.");
    }

   
    foreach ($listki as $listek) {
        $glavne = implode(",", array_slice($listek, 0, 5));
        $euro = implode(",", array_slice($listek, 5, 2));

        foreach ($id_z_list as $id_z) {
            $sql = "INSERT INTO listki (glavne_stevilke, euro_stevilke, generiran, datum_naretega_listka, id_u, stevilo_zrebanj, id_z) 
                    VALUES ('$glavne', '$euro', 0, NOW(), $id_u, $zrebanja, $id_z)";
            $rezultat = mysqli_query($link, $sql);
            if (!$rezultat) {
                die("Napaka pri vnosu: " . mysqli_error($link));
            }
        }
    }

 
    $nov_denar = $trenutni_denar - $skupni_znesek;
    $sql = "UPDATE uporabniki SET znesek_denarja = $nov_denar WHERE id_u = $id_u";
    mysqli_query($link, $sql);

    $_SESSION['denar'] = $nov_denar;
    unset($_SESSION['listki']);

    echo "<script>alert('✅ Plačilo uspešno! Znesek: " . $skupni_znesek . " €'); window.location.href = 'index.php';</script>";
    exit;
}
?>


<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Plačilo</title>
    <link rel="stylesheet" href="rezultati.css">
</head>
<body>
    <h1>Pregled plačila</h1>
    <h3>Število žrebanj: <?php echo $zrebanja; ?></h3>

    <h3>Prihajajoča žrebanja:</h3>
    <ul>
        <?php foreach ($datumi_zrebanj as $datum): ?>
            <li><?php echo $datum; ?></li>
        <?php endforeach; ?>
    </ul>

    <h3>Tvoji listki:</h3>
    <?php foreach ($listki as $index => $listek): ?>
        <div class="listek">
            Listek <?php echo $index + 1; ?>:
            <?php foreach ($listek as $stevilka): ?>
                <span class="krog"><?php echo $stevilka; ?></span>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <div class="skupaj">Skupni znesek za plačilo: <?php echo $skupni_znesek ?> €</div>

    <form method="post">
        <input type="hidden" name="placaj" value="1">
        <input type="hidden" name="zrebanja" value="<?php echo $zrebanja; ?>">

        <?php foreach ($listki as $index => $listek): ?>
            <?php foreach ($listek as $stevilka): ?>
                <input type="hidden" name="listek[<?php echo $index; ?>][]" value="<?php echo $stevilka; ?>">
            <?php endforeach; ?>
        <?php endforeach; ?>

        <button type="submit">Potrdi plačilo</button>
    </form>

    <a href="index.php"><button type="button">Nazaj</button></a>
</body>
</html>
