<?php
session_start();
include_once 'baza.php';
date_default_timezone_set('Europe/Ljubljana');

if (!isset($_SESSION['tip']) || $_SESSION['tip'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$sporocilo = "";

// Dodajanje novega žrebanja
if (isset($_POST['dodaj_zrebanje'])) {
    $datum = $_POST['datum'];
    $glavne = $_POST['glavne'];
    $evropske = $_POST['evropske'];

    $stmt = $link->prepare("INSERT INTO zrebanja (datum_zrebanja, glavne_stevilke, europske_stevilke) VALUES (?, ?, ?)");
    if ($stmt && $stmt->bind_param("sss", $datum, $glavne, $evropske) && $stmt->execute()) {
        $sporocilo = "✅ Žrebanje dodano!";
    } else {
        $sporocilo = "❌ Napaka pri dodajanju žrebanja.";
    }
    $stmt->close();
}

// Urejanje žrebanja
if (isset($_POST['uredi_zrebanje'])) {
    $id = intval($_POST['id_z']);
    $datum = $_POST['datum'];
    $glavne = $_POST['glavne'];
    $evropske = $_POST['evropske'];

    $stmt = $link->prepare("UPDATE zrebanja SET datum_zrebanja = ?, glavne_stevilke = ?, europske_stevilke = ? WHERE id_z = ?");
    if ($stmt && $stmt->bind_param("sssi", $datum, $glavne, $evropske, $id) && $stmt->execute()) {
        $sporocilo = "✅ Žrebanje posodobljeno!";
    } else {
        $sporocilo = "❌ Napaka pri posodabljanju.";
    }
    $stmt->close();
}

// Brisanje žrebanja
if (isset($_GET['izbrisi_zrebanje'])) {
    $id = intval($_GET['izbrisi_zrebanje']);
    $stmt = $link->prepare("DELETE FROM zrebanja WHERE id_z = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit;
}

// Dodajanje denarja
if (isset($_POST['dodaj_denar'])) {
    $id_u = intval($_POST['id_u']);
    $znesek = floatval($_POST['znesek']);
    $stmt = $link->prepare("UPDATE uporabniki SET znesek_denarja = znesek_denarja + ? WHERE id_u = ?");
    if ($stmt && $stmt->bind_param("di", $znesek, $id_u) && $stmt->execute()) {
        $sporocilo = "✅ Denar dodan!";
    } else {
        $sporocilo = "❌ Napaka pri dodajanju denarja.";
    }
    $stmt->close();
}

// Obdelava žrebanj
if (isset($_POST['obdelaj_zrebanja'])) {
    $zdaj = date('Y-m-d H:i:s');
    $zrebanja = mysqli_query($link, "SELECT * FROM zrebanja WHERE datum_zrebanja <= '$zdaj' AND obdelano = 0");

    while ($zreb = mysqli_fetch_assoc($zrebanja)) {
        $id_z = $zreb['id_z'];
        $glavne = array_map('trim', explode(',', $zreb['glavne_stevilke']));
        $euro = array_map('trim', explode(',', $zreb['europske_stevilke']));

        $listki = mysqli_query($link, "SELECT * FROM listki WHERE generiran = 0 AND id_z = $id_z");

        while ($list = mysqli_fetch_assoc($listki)) {
            $id_l = $list['id_l'];
            $id_u = $list['id_u'];
            $moje_glavne = array_map('trim', explode(',', $list['glavne_stevilke']));
            $moje_euro = array_map('trim', explode(',', $list['euro_stevilke']));

            $ujema_glavne = count(array_intersect($glavne, $moje_glavne));
            $ujema_euro = count(array_intersect($euro, $moje_euro));

            $nagrada = mysqli_query($link, "
                SELECT id_n, znesek_nagrade 
                FROM nagrade 
                WHERE stevilo_glavnih_stevilk = $ujema_glavne 
                AND stevilo_eu_stevilk = $ujema_euro 
                LIMIT 1
            ");

            if (mysqli_num_rows($nagrada) > 0) {
                $n = mysqli_fetch_assoc($nagrada);
                $id_n = $n['id_n'];
                $znesek = $n['znesek_nagrade'];

                // Dodaj nagrado uporabniku
                mysqli_query($link, "UPDATE uporabniki SET znesek_denarja = znesek_denarja + $znesek WHERE id_u = $id_u");

                $id_n_sql = $id_n;
            } else {
                $id_n_sql = "NULL";
            }

            // Vstavi rezultat listka
            mysqli_query($link, "
                INSERT INTO rezultati_listkov (pravilne_glavne_stevilke, pravilne_euro_stevilke, id_l, id_n, id_z)
                VALUES ($ujema_glavne, $ujema_euro, $id_l, $id_n_sql, $id_z)
            ");

            // Označi listek kot obdelan
            mysqli_query($link, "UPDATE listki SET generiran = 1 WHERE id_l = $id_l");
        }

        // Označi žrebanje kot obdelano
        mysqli_query($link, "UPDATE zrebanja SET obdelano = 1 WHERE id_z = $id_z");
    }

    $sporocilo = "✅ Obdelava žrebanj zaključena.";
}

// Pridobi podatke
$zrebanja = mysqli_query($link, "SELECT * FROM zrebanja ORDER BY datum_zrebanja DESC");
$uporabniki = mysqli_query($link, "SELECT * FROM uporabniki ORDER BY ime ASC");
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <h1>Admin</h1>

    <?php if (!empty($sporocilo)) echo "<p><strong>$sporocilo</strong></p>"; ?>

    <h2>Dodaj žrebanje</h2>
    <form method="post">
        Datum: <input type="datetime-local" name="datum" required><br>
        Glavne (npr: 1,2,3,4,5): <input type="text" name="glavne" required><br>
        Evropske (npr: 1,2): <input type="text" name="evropske" required><br>
        <input type="submit" name="dodaj_zrebanje" value="Dodaj žrebanje">
    </form>

    <h3>Obstoječa žrebanja</h3>
    <table border="1">
        <tr>
            <th>ID</th><th>Datum</th><th>Glavne</th><th>Evropske</th><th>Uredi</th><th>Izbriši</th>
        </tr>
        <?php while ($z = mysqli_fetch_assoc($zrebanja)): ?>
        <tr>
            <form method="post">
                <input type="hidden" name="id_z" value="<?= $z['id_z'] ?>">
                <td><?= $z['id_z'] ?></td>
                <td><input type="datetime-local" name="datum" value="<?= date('Y-m-d\TH:i', strtotime($z['datum_zrebanja'])) ?>"></td>
                <td><input type="text" name="glavne" value="<?= htmlspecialchars($z['glavne_stevilke']) ?>"></td>
                <td><input type="text" name="evropske" value="<?= htmlspecialchars($z['europske_stevilke']) ?>"></td>
                <td><input type="submit" name="uredi_zrebanje" value="Shrani"></td>
                <td><a href="?izbrisi_zrebanje=<?= $z['id_z'] ?>" onclick="return confirm('Izbrisati žrebanje?')">Izbriši</a></td>
            </form>
        </tr>
        <?php endwhile; ?>
    </table>

    <h2>Obdelaj vsa pretekla žrebanja</h2>
    <form method="post">
        <input type="submit" name="obdelaj_zrebanja" value="Obdelaj žrebanja">
    </form>

    <hr>

    <h2>Uporabniki</h2>
    <table border="1">
        <tr>
            <th>ID</th><th>Ime</th><th>Email</th><th>Denar</th><th>Tip</th><th>Dodaj denar</th>
        </tr>
        <?php while ($u = mysqli_fetch_assoc($uporabniki)): ?>
        <tr>
            <form method="post">
                <input type="hidden" name="id_u" value="<?= $u['id_u'] ?>">
                <td><?= $u['id_u'] ?></td>
                <td><?= htmlspecialchars($u['ime']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= number_format($u['znesek_denarja'], 2) ?> €</td>
                <td><?= $u['tip'] ?></td>
                <td>
                    <input type="number" step="0.01" name="znesek" required>
                    <input type="submit" name="dodaj_denar" value="Dodaj">
                </td>
            </form>
        </tr>
        <?php endwhile; ?>
    </table>

    <p><a href="index.php">Nazaj</a></p>
</body>
</html>
