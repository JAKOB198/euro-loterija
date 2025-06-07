<?php

include_once 'baza.php';
include 'navigation.php';
date_default_timezone_set('Europe/Ljubljana');

// Preveri prijavo
$id_u = $_SESSION['id_u'] ?? null;
if (!$id_u) {
    die("Napaka: uporabnik ni prijavljen.");
}

// Cena na listek za eno žrebanje
define('CENA_NA_LISTEK', 2.5);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['placaj'])) {
    $listki = json_decode($_POST['listki'] ?? '[]', true);
    $zrebanja = (int) ($_POST['zrebanja'] ?? 1);

    if (!is_array($listki) || $zrebanja < 1) {
        die("Napačni podatki.");
    }

    $stevilo_listkov = count($listki);
    $skupni_znesek = $stevilo_listkov * CENA_NA_LISTEK * $zrebanja;

    // Preveri stanje denarja uporabnika in začni transakcijo
    mysqli_begin_transaction($link);

    // Pridobi trenutno stanje denarja uporabnika
    $result = mysqli_query($link, "SELECT znesek_denarja FROM uporabniki WHERE id_u = $id_u FOR UPDATE");
    if (!$result || mysqli_num_rows($result) !== 1) {
        mysqli_rollback($link);
        die("Uporabnik ni najden v bazi.");
    }
    $row = mysqli_fetch_assoc($result);
    $trenutni_denar = (float) $row['znesek_denarja'];

    if ($trenutni_denar < $skupni_znesek) {
        mysqli_rollback($link);
        die("Nimate dovolj denarja na računu za to plačilo.");
    }

    // Pridobi naslednjih N prihajajočih žrebanj
    $result_zrebanja = mysqli_query($link, "
        SELECT id_z FROM zrebanja 
        WHERE datum_zrebanja > NOW() 
        ORDER BY datum_zrebanja ASC 
        LIMIT $zrebanja
    ");

    if (!$result_zrebanja || mysqli_num_rows($result_zrebanja) < $zrebanja) {
        mysqli_rollback($link);
        die("❌ Ni dovolj prihajajočih žrebanj v bazi.");
    }

    $id_z_list = [];
    while ($row = mysqli_fetch_assoc($result_zrebanja)) {
        $id_z_list[] = $row['id_z'];
    }

    // Pripravi pripravljeno poizvedbo za vnos listkov
    $stmt = $link->prepare("
        INSERT INTO listki (glavne_stevilke, euro_stevilke, generiran, datum_naretega_listka, id_u, stevilo_zrebanj, id_z)
        VALUES (?, ?, 0, NOW(), ?, ?, ?)
    ");

    if (!$stmt) {
        mysqli_rollback($link);
        die("Napaka pri pripravi poizvedbe: " . $link->error);
    }

    foreach ($listki as $listek) {
        $glavne = array_slice($listek, 0, 5);
        $euro = array_slice($listek, 5, 2);

        $glavne_str = implode(',', array_map('intval', $glavne));
        $euro_str = implode(',', array_map('intval', $euro));

        foreach ($id_z_list as $id_z) {
            $stmt->bind_param("ssiii", $glavne_str, $euro_str, $id_u, $zrebanja, $id_z);
            if (!$stmt->execute()) {
                mysqli_rollback($link);
                die("Napaka pri vnosu listka: " . $stmt->error);
            }
        }
    }

    // Zmanjšaj stanje denarja uporabnika
    $nov_denar = $trenutni_denar - $skupni_znesek;
    $update = mysqli_query($link, "UPDATE uporabniki SET znesek_denarja = $nov_denar WHERE id_u = $id_u");
    if (!$update) {
        mysqli_rollback($link);
        die("Napaka pri posodobitvi stanja denarja: " . mysqli_error($link));
    }

   mysqli_commit($link);
$stmt->close();
unset($_SESSION['listki']); // Po uspešnem plačilu

// Posodobi stanje denarja v seji, da se bo takoj prikazalo pravilno
$_SESSION['denar'] = $nov_denar;

echo "<script>alert('✅ Plačilo uspešno! Skupni znesek: " . number_format($skupni_znesek, 2) . " €'); window.location.href = 'index.php';</script>";
exit;
} else {
    $listki = json_decode($_POST['listki'] ?? '[]', true);
    $zrebanja = (int) ($_POST['zrebanja'] ?? 1);
    $stevilo_listkov = count($listki);
    $skupni_znesek = $stevilo_listkov * CENA_NA_LISTEK * $zrebanja;
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
