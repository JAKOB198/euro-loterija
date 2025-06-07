<?php
session_start(); 
include_once 'baza.php';
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

    // Pridobi naslednjih N prihajajočih žrebanj
    $result_zrebanja = mysqli_query($link, "
        SELECT id_z FROM zrebanja 
        WHERE datum_zrebanja > NOW() 
        ORDER BY datum_zrebanja ASC 
        LIMIT $zrebanja
    ");

    if (!$result_zrebanja || mysqli_num_rows($result_zrebanja) < $zrebanja) {
        die("❌ Ni dovolj prihajajočih žrebanj v bazi.");
    }

    $id_z_list = [];
    while ($row = mysqli_fetch_assoc($result_zrebanja)) {
        $id_z_list[] = $row['id_z'];
    }

    // Tukaj lahko dodaš preverjanje plačila, če hočeš (trenutno samo shrani)

    // Pripravi pripravljeno poizvedbo
    $stmt = $link->prepare("
        INSERT INTO listki (glavne_stevilke, euro_stevilke, generiran, datum_naretega_listka, id_u, stevilo_zrebanj, id_z)
        VALUES (?, ?, 0, NOW(), ?, ?, ?)
    ");

    if (!$stmt) {
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
                die("Napaka pri vnosu listka: " . $stmt->error);
            }
        }
    }

    $stmt->close();
    unset($_SESSION['listki']); // Po uspešnem plačilu
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
    <link rel="stylesheet" href="index.css?v=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff7e6;
            color: #333;
            padding: 30px;
        }
        h1 {
            color: orange;
            margin-bottom: 10px;
        }
        .krog {
            display: inline-block;
            width: 30px;
            height: 30px;
            line-height: 30px;
            border-radius: 50%;
            background-color: orange;
            color: white;
            text-align: center;
            margin-right: 5px;
            font-weight: bold;
        }
        .listek {
            margin-bottom: 10px;
            padding: 10px;
            background: #fff0b3;
            border-radius: 8px;
            border: 1px solid orange;
        }
        .skupaj {
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
            color: darkorange;
        }
        button {
            background-color: orange;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 18px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 20px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: darkorange;
        }
        a button {
            background-color: #999;
            margin-left: 10px;
        }
        a button:hover {
            background-color: #666;
        }
    </style>
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
