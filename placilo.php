<?php
session_start(); 
include_once 'baza.php';

$id_u = $_SESSION['id_u'] ?? null;
if (!$id_u) {
    die("Napaka: uporabnik ni prijavljen.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['placaj'])) {
  
    $listki = json_decode($_POST['listki'] ?? '[]', true);
    $zrebanja = (int) ($_POST['zrebanja'] ?? 1);

    foreach ($listki as $listek) {
        $glavne = array_slice($listek, 0, 5);
        $euro = array_slice($listek, 5, 2);

        $glavne_str = implode(',', array_map('intval', $glavne));
        $euro_str = implode(',', array_map('intval', $euro));

        $generiran = 0; 

        $query = "INSERT INTO listki (glavne_stevilke, euro_stevilke, generiran, datum_naretega_listka, id_u, stevilo_zrebanj)
                  VALUES ('$glavne_str', '$euro_str', $generiran, NOW(), $id_u, $zrebanja)";

        $result = mysqli_query($link, $query);

        if (!$result) {
            die(" Napaka pri vnosu: " . mysqli_error($link));
        }
    }

    echo "<script>alert('Plačilo uspešno!'); window.location.href = 'index.php';</script>";
    exit;
} else {
    $listki = json_decode($_POST['listki'] ?? '[]', true);
    $zrebanja = (int) ($_POST['zrebanja'] ?? 1);
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Plačilo</title>
    <link rel="stylesheet" href="index.css?v=1.0">
</head>
<body>
    <h1>Pregled plačila</h1>
    <h3>Število žrebanj: <?= htmlspecialchars($zrebanja) ?></h3>
    <h3>Tvoji listki:</h3>
    <?php foreach ($listki as $index => $listek): ?>
        <div>
            Listek <?= $index + 1 ?>:
            <?php foreach ($listek as $stevilka): ?>
                <span class="krog"><?= htmlspecialchars($stevilka) ?></span>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <br><br>
    <form method="post">
        <input type="hidden" name="placaj" value="1">
        <input type="hidden" name="zrebanja" value="<?= htmlspecialchars($zrebanja) ?>">
        <input type="hidden" name="listki" value='<?= htmlspecialchars(json_encode($listki)) ?>'>
        <button type="submit">Potrdi plačilo</button>
    </form>

    <a href="index.php"><button>Nazaj</button></a>
</body>
</html>
