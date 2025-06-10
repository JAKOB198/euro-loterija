<?php
session_start();
include_once 'baza.php';
date_default_timezone_set('Europe/Ljubljana');



$sporocilo = "";


if (isset($_POST['dodaj_zrebanje'])) {
    $datum = $_POST['datum'];
    $glavne = $_POST['glavne'];
    $evropske = $_POST['evropske'];

    $link->query("INSERT INTO zrebanja (datum_zrebanja, glavne_stevilke, europske_stevilke) VALUES ('$datum', '$glavne', '$evropske')");
    $sporocilo = " Žrebanje dodano!";
}


if (isset($_POST['uredi_zrebanje'])) {
    $id = (int)$_POST['id_z'];
    $datum = $_POST['datum'];
    $glavne = $_POST['glavne'];
    $evropske = $_POST['evropske'];

    $link->query("UPDATE zrebanja SET datum_zrebanja = '$datum', glavne_stevilke = '$glavne', europske_stevilke = '$evropske' WHERE id_z = $id");
    $sporocilo = " Žrebanje posodobljeno!";
}


if (isset($_POST['izbrisi_zrebanje'])) {
    $id = (int)$_POST['izbrisi_zrebanje'];
    $link->query("DELETE FROM zrebanja WHERE id_z = $id");
    header("Location: admin.php");
    exit;
}


if (isset($_POST['dodaj_denar'])) {
    $id_u = (int)$_POST['id_u'];
    $znesek = (float)$_POST['znesek'];
    $link->query("UPDATE uporabniki SET znesek_denarja = znesek_denarja + $znesek WHERE id_u = $id_u");
    $sporocilo = " Denar dodan!";
}


if (isset($_POST['obdelaj_zrebanja'])) {
    $zdaj = date('Y-m-d H:i:s');
    $zrebanja = $link->query("SELECT * FROM zrebanja WHERE datum_zrebanja <= '$zdaj' AND obdelano = 0");

    while ($zreb = $zrebanja->fetch_assoc()) {
        $id_z = $zreb['id_z'];
        $glavne = explode(',', $zreb['glavne_stevilke']);
        $euro = explode(',', $zreb['europske_stevilke']);

        $listki = $link->query("SELECT * FROM listki WHERE generiran = 0 AND id_z = $id_z");

        while ($list = $listki->fetch_assoc()) {
            $id_l = $list['id_l'];
            $id_u = $list['id_u'];
            $moje_glavne = explode(',', $list['glavne_stevilke']);
            $moje_euro = explode(',', $list['euro_stevilke']);

            $ujema_glavne = count(array_intersect($glavne, $moje_glavne));
            $ujema_euro = count(array_intersect($euro, $moje_euro));

            $nagrada = $link->query("SELECT id_n, znesek_nagrade FROM nagrade WHERE stevilo_glavnih_stevilk = $ujema_glavne AND stevilo_eu_stevilk = $ujema_euro LIMIT 1");

            if ($nagrada->num_rows > 0) {
                $n = $nagrada->fetch_assoc();
                $id_n = $n['id_n'];
                $znesek = $n['znesek_nagrade'];

                $link->query("UPDATE uporabniki SET znesek_denarja = znesek_denarja + $znesek WHERE id_u = $id_u");
            } else {
                $id_n = "NULL";
            }

            $link->query("INSERT INTO rezultati_listkov (pravilne_glavne_stevilke, pravilne_euro_stevilke, id_l, id_n, id_z) VALUES ($ujema_glavne, $ujema_euro, $id_l, $id_n, $id_z)");

            $link->query("UPDATE listki SET generiran = 1 WHERE id_l = $id_l");
        }

        $link->query("UPDATE zrebanja SET obdelano = 1 WHERE id_z = $id_z");
    }

    $sporocilo = " Obdelava žrebanj zaključena.";
}

$zrebanja = $link->query("SELECT * FROM zrebanja ORDER BY datum_zrebanja DESC");
$uporabniki = $link->query("SELECT * FROM uporabniki ORDER BY ime ASC");
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
        <?php while ($z = $zrebanja->fetch_assoc()): ?>
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
        <?php while ($u = $uporabniki->fetch_assoc()): ?>
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
