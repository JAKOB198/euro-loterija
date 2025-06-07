<?php
session_start();
include_once 'baza.php';

if (!isset($_SESSION['tip']) || $_SESSION['tip'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$sporocilo = "";

// Dodajanje novega žrebanja
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dodaj_zrebanje'])) {
    $datum = $_POST['datum'];
    $glavne = array_rand(array_flip(range(1, 50)), 5);
    sort($glavne);
    $glavne_str = implode(',', $glavne);

    $evropske = array_rand(array_flip(range(1, 12)), 2);
    sort($evropske);
    $evropske_str = implode(',', $evropske);

    $stmt = $link->prepare("INSERT INTO zrebanja (datum_zrebanja, glavne_stevilke, europske_stevilke) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $datum, $glavne_str, $evropske_str);
    if ($stmt->execute()) {
        $id_zrebanja = $stmt->insert_id;
        $sporocilo = "✅ Žrebanje dodano!";

        // Pretvori številke v array
        $glavne_zreb = explode(',', $glavne_str);
        $evropske_zreb = explode(',', $evropske_str);

        // Pridobi vse vplačane listke
        $rezultat = mysqli_query($link, "SELECT * FROM listki");
        while ($listek = mysqli_fetch_assoc($rezultat)) {
            $glavne_up = explode(',', $listek['glavne']);
            $evropske_up = explode(',', $listek['evropske']);

            // Preštej ujemanja
            $ujemanja_glavne = count(array_intersect($glavne_zreb, $glavne_up));
            $ujemanja_evropske = count(array_intersect($evropske_zreb, $evropske_up));

            // Poišči nagrado
            $stmt2 = $link->prepare("SELECT odstotek_nagrade FROM nagrade WHERE stevilo_glavnih_stevilk = ? AND stevilo_eu_stevilk = ?");
            $stmt2->bind_param("ii", $ujemanja_glavne, $ujemanja_evropske);
            $stmt2->execute();
            $stmt2->bind_result($odstotek);
            if ($stmt2->fetch()) {
                $znesek = 1000 * ($odstotek / 100.0); // Osnova: 1000 EUR
                $stmt3 = $link->prepare("UPDATE uporabniki SET una_racun = una_racun + ? WHERE id = ?");
                $stmt3->bind_param("di", $znesek, $listek['uporabnik_id']);
                $stmt3->execute();
                $stmt3->close();
            }
            $stmt2->close();
        }
    } else {
        $sporocilo = "❌ Napaka pri dodajanju žrebanja.";
    }
}

// Brisanje žrebanja
if (isset($_GET['izbrisi_zrebanje'])) {
    $id = intval($_GET['izbrisi_zrebanje']);
    $stmt = $link->prepare("DELETE FROM zrebanja WHERE id_z = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: admin.php");
    exit;
}

// Dodajanje nagrade
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dodaj_nagrado'])) {
    $g = intval($_POST['glavne']);
    $e = intval($_POST['evropske']);
    $odstotek = floatval($_POST['odstotek']);
    $stmt = $link->prepare("INSERT INTO nagrade (stevilo_glavnih_stevilk, stevilo_eu_stevilk, odstotek_nagrade) VALUES (?, ?, ?)");
    $stmt->bind_param("iid", $g, $e, $odstotek);
    $sporocilo = $stmt->execute() ? "✅ Nagrada dodana!" : "❌ Napaka pri dodajanju nagrade.";
}

// Brisanje nagrade
if (isset($_GET['izbrisi_nagrado'])) {
    $idn = intval($_GET['izbrisi_nagrado']);
    $stmt = $link->prepare("DELETE FROM nagrade WHERE id_n = ?");
    $stmt->bind_param("i", $idn);
    $stmt->execute();
    header("Location: admin.php");
    exit;
}

// Pridobivanje vseh zapisov
$zrebanja = mysqli_query($link, "SELECT * FROM zrebanja ORDER BY datum_zrebanja DESC");
$nagrade = mysqli_query($link, "SELECT * FROM nagrade ORDER BY stevilo_glavnih_stevilk DESC, stevilo_eu_stevilk DESC");
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="container">
    <h1>Admin - Upravljanje</h1>

    <?php if (!empty($sporocilo)) : ?>
        <p><strong><?= $sporocilo ?></strong></p>
    <?php endif; ?>

    <!-- Žrebanja -->
    <h2>Dodaj žrebanje</h2>
    <form method="post">
        <input type="hidden" name="dodaj_zrebanje" value="1">
        <label>Datum žrebanja:</label><br>
        <input type="date" name="datum" required><br><br>
        <input type="submit" value="Dodaj žrebanje">
    </form>

    <h3>Obstoječa žrebanja</h3>
    <table border="1">
        <tr>
            <th>ID</th><th>Datum</th><th>Glavne številke</th><th>Evropske številke</th><th>Dejanje</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($zrebanja)) : ?>
            <tr>
                <td><?= $row['id_z'] ?></td>
                <td><?= $row['datum_zrebanja'] ?></td>
                <td><?= $row['glavne_stevilke'] ?></td>
                <td><?= $row['europske_stevilke'] ?></td>
                <td><a href="admin.php?izbrisi_zrebanje=<?= $row['id_z'] ?>" onclick="return confirm('Izbrisati žrebanje?')">❌ Izbriši</a></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <hr>

    <!-- Nagrade -->
    <h2>Dodaj nagrado</h2>
    <form method="post">
        <input type="hidden" name="dodaj_nagrado" value="1">
        <label>Glavne številke:</label>
        <input type="number" name="glavne" min="0" max="5" required><br>
        <label>Evropske številke:</label>
        <input type="number" name="evropske" min="0" max="2" required><br>
        <label>Odstotek nagrade (%):</label>
        <input type="number" name="odstotek" step="0.01" min="0" required><br><br>
        <input type="submit" value="Dodaj nagrado">
    </form>

    <h3>Obstoječe nagrade</h3>
    <table border="1">
        <tr>
            <th>ID</th><th>Glavne</th><th>Evropske</th><th>Odstotek (%)</th><th>Dejanje</th>
        </tr>
        <?php while ($n = mysqli_fetch_assoc($nagrade)) : ?>
            <tr>
                <td><?= $n['id_n'] ?></td>
                <td><?= $n['stevilo_glavnih_stevilk'] ?></td>
                <td><?= $n['stevilo_eu_stevilk'] ?></td>
                <td><?= number_format($n['odstotek_nagrade'], 2) ?></td>
                <td><a href="admin.php?izbrisi_nagrado=<?= $n['id_n'] ?>" onclick="return confirm('Izbrisati nagrado?')">❌ Izbriši</a></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <br>
    <a href="index.php">🏠 Nazaj na glavno stran</a>
</div>
</body>
</html>
