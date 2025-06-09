<?php
include_once 'baza.php';
include 'navigation.php';
date_default_timezone_set('Europe/Ljubljana');



$id_u = $_SESSION['id_u'] ?? null;
if (!$id_u) {
    die("Napaka: uporabnik ni prijavljen.");
}

define('CENA_NA_LISTEK', 2.5);

$listki = json_decode($_POST['listki'] ?? '[]', true);
$zrebanja = (int) ($_POST['zrebanja'] ?? 1);
$stevilo_listkov = count($listki);
$skupni_znesek = $stevilo_listkov * CENA_NA_LISTEK * $zrebanja;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['placaj'])) {
    // Preveri stanje denarja uporabnika
    $result = mysqli_query($link, "SELECT znesek_denarja FROM uporabniki WHERE id_u = $id_u");
    $trenutni_denar = (float) mysqli_fetch_assoc($result)['znesek_denarja'];

    if ($trenutni_denar < $skupni_znesek) {
        die("Nimate dovolj denarja na računu za to plačilo.");
    }

    // Pridobi prihajajoča žrebanja
    $result_zrebanja = mysqli_query($link, "SELECT id_z FROM zrebanja WHERE datum_zrebanja > NOW() ORDER BY datum_zrebanja ASC LIMIT $zrebanja");
    $id_z_list = [];
    while ($row = mysqli_fetch_assoc($result_zrebanja)) {
        $id_z_list[] = $row['id_z'];
    }
    if (count($id_z_list) < $zrebanja) {
        die("❌ Ni dovolj prihajajočih žrebanj v bazi.");
    }

    // Vnesi listke
    foreach ($listki as $listek) {
        $glavne_str = implode(',', array_map('intval', array_slice($listek, 0, 5)));
        $euro_str = implode(',', array_map('intval', array_slice($listek, 5, 2)));

        foreach ($id_z_list as $id_z) {
            $sql = "INSERT INTO listki (glavne_stevilke, euro_stevilke, generiran, datum_naretega_listka, id_u, stevilo_zrebanj, id_z)
                    VALUES ('$glavne_str', '$euro_str', 0, NOW(), $id_u, $zrebanja, $id_z)";
            if (!mysqli_query($link, $sql)) {
                die("Napaka pri vnosu listka: " . mysqli_error($link));
            }
        }
    }

    // Posodobi stanje denarja uporabnika
    $nov_denar = $trenutni_denar - $skupni_znesek;
    mysqli_query($link, "UPDATE uporabniki SET znesek_denarja = $nov_denar WHERE id_u = $id_u");

    $_SESSION['denar'] = $nov_denar;
    unset($_SESSION['listki']);

    echo "<script>alert('✅ Plačilo uspešno! Skupni znesek: " . number_format($skupni_znesek, 2) . " €'); window.location.href = 'index.php';</script>";
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
    <h3>Število žrebanj: <?= htmlspecialchars($zrebanja) ?></h3>

    <h3>Tvoji listki:</h3>
    <?php foreach ($listki as $index => $listek): ?>
        <div class="listek">
            Listek <?= $index + 1 ?>:
            <?php foreach ($listek as $stevilka): ?>
                <span class="krog"><?= htmlspecialchars($stevilka) ?></span>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <div class="skupaj">Skupni znesek za plačilo: <?= number_format($skupni_znesek, 2) ?> €</div>

    <form method="post">
        <input type="hidden" name="placaj" value="1">
        <input type="hidden" name="zrebanja" value="<?= htmlspecialchars($zrebanja) ?>">
        <input type="hidden" name="listki" value='<?= htmlspecialchars(json_encode($listki)) ?>'>
        <button type="submit">Potrdi plačilo</button>
    </form>

    <a href="index.php"><button type="button">Nazaj</button></a>
</body>
</html>
