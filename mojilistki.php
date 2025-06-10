<?php

include 'baza.php';
include 'navigation.php';
// Preveri prijavo
if (!isset($_SESSION['uporabnik'])) {
    header("Location: login.php");
    exit();
}

$uporabnisko_ime = $_SESSION['uporabnik'];


// Pridobi ID uporabnika
$sql = "SELECT id_u FROM uporabniki WHERE ime = '$uporabnisko_ime'";
$result = mysqli_query($link, $sql);
if (mysqli_num_rows($result) == 0) {
    echo "Uporabnik ne obstaja.";
    exit();
}
$row = mysqli_fetch_assoc($result);
$id_u = $row['id_u'];

// Pridobi VSE listke uporabnika (tudi stare)
$sql = "
    SELECT l.id_l, l.glavne_stevilke, l.euro_stevilke, z.datum_zrebanja, 
           r.pravilne_glavne_stevilke, r.pravilne_euro_stevilke, n.znesek_nagrade
    FROM listki l
    LEFT JOIN zrebanja z ON l.id_z = z.id_z
    LEFT JOIN rezultati_listkov r ON l.id_l = r.id_l
    LEFT JOIN nagrade n ON r.id_n = n.id_n
    WHERE l.id_u = $id_u
    ORDER BY z.datum_zrebanja DESC
";

$result = mysqli_query($link, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Moji listki</title>
    <link rel="stylesheet" href="mojilistki.css">
</head>
<body>
    <h1>Moji listki</h1>
    <table border="1">
        <tr>
            <th>Glavne številke</th>
            <th>Euro številke</th>
            <th>Datum žrebanja</th>
            <th>Pravilne glavne</th>
            <th>Pravilne euro</th>
            <th>Nagrada</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= htmlspecialchars($row['glavne_stevilke']) ?></td>
            <td><?= htmlspecialchars($row['euro_stevilke']) ?></td>
            <td><?= $row['datum_zrebanja'] ?? 'Ni žrebanja' ?></td>
            <td><?= $row['pravilne_glavne_stevilke'] ?? '0' ?></td>
            <td><?= $row['pravilne_euro_stevilke'] ?? '0' ?></td>
            <td><?= isset($row['znesek_nagrade']) ? $row['znesek_nagrade'].' €' : '0 €' ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php include 'footer.php' ?>
</body>
</html>
