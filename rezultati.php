<?php
include 'navigation.php';
include 'baza.php'; 

$sql = "SELECT * FROM zrebanja WHERE datum_zrebanja <= NOW() ORDER BY datum_zrebanja DESC";
$result = mysqli_query($link, $sql);
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Rezultati žrebanj</title>
    <link rel="stylesheet" href="rezultati.css">
</head>
<body>

<h1>Rezultati žrebanj Eurojackpot</h1>

<?php if ($result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Datum žrebanja</th>
                <th>Glavne številke</th>
                <th>Evropske številke</th>
            </tr>
        </thead>
        <tbody>
          <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= date("d. m. Y H:i", strtotime($row["datum_zrebanja"])) ?></td>
                    <td><?= htmlspecialchars($row["glavne_stevilke"]) ?></td>
                    <td><?= htmlspecialchars($row["europske_stevilke"]) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Trenutno še ni rezultatov.</p>
<?php endif; ?>
<?php include 'footer.php' ?>
</body>
</html>
